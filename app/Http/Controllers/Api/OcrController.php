<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class OcrController extends Controller
{
    public function read(Request $request, GeminiOcrService $ocr): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'string', 'max:7500000'],
        ]);

        try {
            $fields = $ocr->readId($data['image']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'The ID reader failed. Upload a clearer photo or type the details.',
            ], 502);
        }

        return response()->json(['fields' => $fields]);
    }
}
