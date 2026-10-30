<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\User;
use App\Services\DeskInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DataRequest::query()
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($rows);
    }

    public function store(Request $request, DeskInbox $inbox): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'user_id')],
            'type' => ['required', Rule::in(DataRequest::TYPES)],
        ]);

        $user = User::findOrFail($data['user_id']);
        $payload = null;
        $status = 'pending';

        if ($data['type'] === 'view') {
            $payload = $this->exportUser($user);
            $status = 'fulfilled';
        }

        $row = DataRequest::create([
            'user_id' => $user->user_id,
            'type' => $data['type'],
            'status' => $status,
            'payload' => $payload,
            'fulfilled_at' => $status === 'fulfilled' ? now() : null,
        ]);

        if ($data['type'] === 'delete') {
            $inbox->notifyCustomer(
                $user,
                'Data deletion request',
                'We received your request to delete your personal data under the Data Privacy Act (RA 10173). Admin or staff will review it. Your bookings stay on record until the request is fulfilled.'
            );
        }

        return response()->json($row->refresh(), 201);
    }

    public function update(Request $request, DataRequest $data_request, DeskInbox $inbox): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(DataRequest::STATUSES)],
            'handled_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
        ]);

        if (($data['status'] ?? '') === 'fulfilled' && $data_request->type === 'delete') {
            $user = $data_request->user;
            if ($user) {
                $user->update([
                    'status' => 'disabled',
                    'first_name' => 'Deleted',
                    'last_name' => 'Renter',
                    'phone' => null,
                    'address' => null,
                    'avatar' => null,
                    'license_no' => null,
                ]);
                $inbox->notifyCustomer(
                    $user,
                    'Data deletion completed',
                    'Your personal data request was fulfilled. The account is disabled and identifying fields were cleared.'
                );
            }
        }

        $data_request->fill($data);
        if (in_array($data['status'], ['fulfilled', 'denied'], true) && ! $data_request->fulfilled_at) {
            $data_request->fulfilled_at = now();
        }
        $data_request->save();

        return response()->json($data_request->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function exportUser(User $user): array
    {
        $bookings = $user->bookings()->with(['vehicle', 'renterDocument'])->latest()->get()->map(function ($booking) {
            $doc = $booking->renterDocument;

            return [
                'ref' => $booking->ref,
                'status' => $booking->status,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'vehicle' => $booking->vehicle?->name,
                'has_id_photo' => (bool) ($doc?->id_photo || $doc?->license_photo),
                'id_type' => $doc?->id_type,
                'id_number' => $doc?->id_number,
                'license_no' => $doc?->license_no,
            ];
        });

        return [
            'user' => [
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'address' => $user->address,
                'birthdate' => $user->birthdate,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
            'bookings' => $bookings,
        ];
    }
}
