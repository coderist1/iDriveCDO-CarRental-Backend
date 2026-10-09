<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThreadMessageRequest;
use App\Models\MessageThread;
use Illuminate\Http\JsonResponse;

class ThreadMessageController extends Controller
{
    public function index(MessageThread $messageThread): JsonResponse
    {
        return response()->json($messageThread->messages()->get());
    }

    public function store(ThreadMessageRequest $request, MessageThread $messageThread): JsonResponse
    {
        $message = $messageThread->messages()->create($request->validated());

        return response()->json($message->refresh(), 201);
    }
}
