<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageThreadRequest;
use App\Models\MessageThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageThreadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $threads = MessageThread::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('kind'), fn ($q, $kind) => $q->where('kind', $kind))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->has('unread_staff'), fn ($q) => $q->where('unread_staff', $request->boolean('unread_staff')))
            ->latest('updated_at')
            ->paginate($this->perPage($request));

        return response()->json($threads);
    }

    public function store(MessageThreadRequest $request): JsonResponse
    {
        $thread = MessageThread::create($request->validated());

        return response()->json($thread->refresh(), 201);
    }

    public function show(MessageThread $messageThread): JsonResponse
    {
        return response()->json($messageThread->load(['messages', 'booking', 'customer']));
    }

    public function update(MessageThreadRequest $request, MessageThread $messageThread): JsonResponse
    {
        $messageThread->update($request->validated());

        return response()->json($messageThread->refresh());
    }

    public function destroy(MessageThread $messageThread): JsonResponse
    {
        $messageThread->delete();

        return response()->json(['message' => 'Thread deleted.']);
    }
}
