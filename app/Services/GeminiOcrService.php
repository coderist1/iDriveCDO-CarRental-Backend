<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiOcrService
{
    /**
     * @return array{fullName:?string,idNumber:?string,birthDate:?string,address:?string,expiryDate:?string}
     */
    public function readId(string $image): array
    {
        $key = (string) config('services.gemini.key');
        if ($key === '') {
            throw new RuntimeException('ID reading is not set up yet. Type the details or ask the desk to add GEMINI_API_KEY.');
        }

        [$mime, $bytes] = $this->decodeImage($image);
        $preferred = (string) config('services.gemini.model', 'gemini-3-flash-preview');
        $models = array_values(array_unique(array_filter([
            $preferred,
            'gemini-3-flash-preview',
            'gemini-3.8-flash',
            'gemini-flash-latest',
        ])));

        $lastMessage = '';
        foreach ($models as $model) {
            try {
                $response = $this->callModel($key, $model, $mime, $bytes);
            } catch (RuntimeException $e) {
                $lastMessage = $e->getMessage();
                continue;
            }

            if ($response->status() === 429 || $response->status() === 503) {
                $lastMessage = 'The ID reader is busy. Wait a moment and upload again.';
                continue;
            }
            if ($response->status() === 401 || $response->status() === 403) {
                throw new RuntimeException('The ID reader key was rejected. Ask the desk to check GEMINI_API_KEY in the server .env.');
            }
            if ($response->status() === 404) {
                $lastMessage = 'The ID reader model is unavailable. Trying another model…';
                continue;
            }
            if (! $response->successful()) {
                $apiMessage = (string) data_get($response->json(), 'error.message', '');
                report(new RuntimeException('Gemini OCR HTTP '.$response->status().' model='.$model.': '.$apiMessage));
                $lastMessage = 'The ID reader could not process this photo. Upload a clearer picture, or type the details.';
                continue;
            }

            $mapped = $this->mapFields($response->json());
            if ($mapped !== null) {
                return $mapped;
            }
            $lastMessage = 'No text was found on this photo. If it is blurry, take another picture in better light and upload again.';
        }

        throw new RuntimeException($lastMessage !== '' ? $lastMessage : 'The ID reader could not process this photo. Upload a clearer picture, or type the details.');
    }

    /**
     * @return \Illuminate\Http\Client\Response
     */
    private function callModel(string $key, string $model, string $mime, string $bytes)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';

        try {
            return Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['x-goog-api-key' => $key])
                ->post($url, [
                    'contents' => [[
                        'parts' => [
                            ['text' => $this->prompt()],
                            ['inline_data' => ['mime_type' => $mime, 'data' => $bytes]],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (RequestException $e) {
            throw new RuntimeException('Could not reach the ID reader. Check the connection and upload again.');
        } catch (\Throwable $e) {
            throw new RuntimeException('Could not reach the ID reader. Check the connection and upload again.');
        }
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array{fullName:?string,idNumber:?string,birthDate:?string,address:?string,expiryDate:?string}|null
     */
    private function mapFields(?array $json): ?array
    {
        $text = data_get($json, 'candidates.0.content.parts.0.text');
        $fields = is_string($text) ? json_decode($text, true) : null;
        if (! is_array($fields)) {
            // Some responses wrap JSON in fences or return the object directly.
            if (is_string($text) && preg_match('/\{.*\}/s', $text, $m)) {
                $fields = json_decode($m[0], true);
            }
        }
        if (! is_array($fields) && is_array($json) && isset($json['fullName'])) {
            $fields = $json;
        }
        if (! is_array($fields)) {
            return null;
        }

        $mapped = [
            'fullName' => $this->clean($fields['fullName'] ?? $fields['full_name'] ?? null),
            'idNumber' => $this->clean($fields['idNumber'] ?? $fields['id_number'] ?? null),
            'birthDate' => $this->date($fields['birthDate'] ?? $fields['birth_date'] ?? $fields['birthdate'] ?? null),
            'address' => $this->clean($fields['address'] ?? null),
            'expiryDate' => $this->date($fields['expiryDate'] ?? $fields['expiry_date'] ?? $fields['expiry'] ?? null),
        ];

        if (! $mapped['fullName'] && ! $mapped['idNumber'] && ! $mapped['expiryDate'] && ! $mapped['birthDate'] && ! $mapped['address']) {
            return null;
        }

        return $mapped;
    }

    private function prompt(): string
    {
        return <<<'TXT'
Read this Philippine government ID or driver's license. Return JSON only with keys:
fullName, idNumber, birthDate, address, expiryDate.
Use ISO dates YYYY-MM-DD. If a field is missing or unreadable, use null.
Do not guess values that are not printed on the card.
TXT;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function decodeImage(string $image): array
    {
        $image = trim($image);
        if (preg_match('#^data:(image/(?:jpeg|jpg|png|webp));base64,(.+)$#i', $image, $m)) {
            $mime = strtolower($m[1]) === 'image/jpg' ? 'image/jpeg' : strtolower($m[1]);
            $bytes = $m[2];
        } elseif (preg_match('#^[A-Za-z0-9+/=\s]+$#', $image)) {
            $mime = 'image/jpeg';
            $bytes = preg_replace('/\s+/', '', $image) ?? '';
        } else {
            throw new RuntimeException('Upload a JPEG, PNG, or WebP photo of the ID.');
        }

        $raw = base64_decode($bytes, true);
        if ($raw === false || strlen($raw) < 80) {
            throw new RuntimeException('That photo could not be read. Upload the image again.');
        }
        if (strlen($raw) > 4_500_000) {
            throw new RuntimeException('That photo is too large. Use a smaller picture and upload again.');
        }

        return [$mime, base64_encode($raw)];
    }

    private function clean(mixed $value): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');

        return $text === '' || strtolower($text) === 'null' ? null : mb_substr($text, 0, 180);
    }

    private function date(mixed $value): ?string
    {
        $text = $this->clean($value);
        if (! $text || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            return null;
        }

        return $text;
    }
}
