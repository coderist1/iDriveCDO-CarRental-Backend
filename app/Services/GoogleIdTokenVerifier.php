<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Verifies a Google Identity Services ID token (RS256 JWT) without trusting the browser:
 * signature against Google's published certificates, issuer, audience, expiry, verified email.
 */
class GoogleIdTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v1/certs';

    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    private const LEEWAY_SECONDS = 60;

    public function __construct(private readonly ?string $clientId) {}

    /**
     * @return array{sub: string, email: string, given_name: string, family_name: string, name: string, picture: string}
     */
    public function verify(string $jwt): array
    {
        if (! $this->clientId) {
            throw new RuntimeException('Google sign-in is not configured on the server.');
        }

        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Malformed Google credential.');
        }
        [$h64, $p64, $s64] = $parts;
        $header = json_decode($this->b64($h64), true);
        $claims = json_decode($this->b64($p64), true);
        $signature = $this->b64($s64);
        if (! is_array($header) || ! is_array($claims) || ($header['alg'] ?? null) !== 'RS256' || empty($header['kid'])) {
            throw new RuntimeException('Unsupported Google credential.');
        }

        $cert = $this->certificates()[$header['kid']] ?? null;
        if (! $cert) {
            Cache::forget('google_oauth_certs');
            $cert = $this->certificates()[$header['kid']] ?? null;
        }
        if (! $cert || openssl_verify("$h64.$p64", $signature, $cert, OPENSSL_ALGO_SHA256) !== 1) {
            throw new RuntimeException('Google credential signature is invalid.');
        }

        $now = time();
        if (! in_array($claims['iss'] ?? '', self::ISSUERS, true)) {
            throw new RuntimeException('Google credential has the wrong issuer.');
        }
        if (($claims['aud'] ?? null) !== $this->clientId) {
            throw new RuntimeException('Google credential was issued for a different app.');
        }
        if ((int) ($claims['exp'] ?? 0) < $now - self::LEEWAY_SECONDS) {
            throw new RuntimeException('Google credential has expired. Please try again.');
        }
        if ((int) ($claims['iat'] ?? 0) > $now + self::LEEWAY_SECONDS) {
            throw new RuntimeException('Google credential is not valid yet.');
        }
        $verified = $claims['email_verified'] ?? false;
        if (empty($claims['email']) || ! ($verified === true || $verified === 'true')) {
            throw new RuntimeException('Your Google email address is not verified.');
        }

        return [
            'sub' => (string) $claims['sub'],
            'email' => strtolower((string) $claims['email']),
            'given_name' => (string) ($claims['given_name'] ?? ''),
            'family_name' => (string) ($claims['family_name'] ?? ''),
            'name' => (string) ($claims['name'] ?? ''),
            'picture' => (string) ($claims['picture'] ?? ''),
        ];
    }

    /** @return array<string, string> kid => PEM certificate */
    private function certificates(): array
    {
        $cached = Cache::get('google_oauth_certs');
        if (is_array($cached)) {
            return $cached;
        }
        $response = Http::timeout(8)->get(self::CERTS_URL);
        if (! $response->ok()) {
            throw new RuntimeException('Could not reach Google to verify sign-in.');
        }
        $certs = $response->json();
        $ttl = 3600;
        if (preg_match('/max-age=(\d+)/', (string) $response->header('Cache-Control'), $m)) {
            $ttl = max(60, (int) $m[1]);
        }
        Cache::put('google_oauth_certs', $certs, $ttl);

        return $certs;
    }

    private function b64(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4));
    }
}
