<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogRequest;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user')
            ->when($request->query('action'), fn ($q, $action) => $q->where('action', $action))
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($logs);
    }

    public function store(AuditLogRequest $request): JsonResponse
    {
        $log = AuditLog::create([
            'ip_address' => $request->ip(),
            ...$request->validated(),
        ]);

        return response()->json($log->refresh(), 201);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        return response()->json($auditLog->load('user'));
    }
}
