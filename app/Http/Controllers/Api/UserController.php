<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerSyncRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Public check used while typing. Does not say which account owns the number. */
    public function phoneAvailable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);
        $canonical = Phone::canonical($data['phone']);
        if ($canonical === null) {
            return response()->json([
                'valid' => false,
                'taken' => false,
                'message' => 'Use a Philippine mobile number (09XXXXXXXXX).',
            ]);
        }

        $taken = User::query()->where('phone', $canonical)->exists();

        return response()->json([
            'valid' => true,
            'taken' => $taken,
            'message' => $taken ? 'This mobile number is already registered.' : null,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('search'), function ($q, $search) {
                $term = '%'.Str::lower($search).'%';
                $q->where(fn ($q) => $q
                    ->whereRaw('lower(first_name) like ?', [$term])
                    ->orWhereRaw('lower(last_name) like ?', [$term])
                    ->orWhereRaw('lower(email) like ?', [$term]));
            })
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($users);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = User::create($this->attributes($request));

        return response()->json($user->refresh(), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('driver'));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $user->update($this->attributes($request));

        return response()->json($user->refresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    public function flag(Request $request, User $user, \App\Services\DeskInbox $inbox): JsonResponse
    {
        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Only a renter account can be labeled.'], 422);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:400'],
            'labeled_by' => ['required', Rule::exists('users', 'user_id')],
        ]);

        $staff = User::findOrFail($data['labeled_by']);
        if (! in_array($staff->role, ['admin', 'staff'], true)) {
            return response()->json(['message' => 'Only admin or staff can label a renter.'], 403);
        }

        $user->update([
            'is_bad_renter' => true,
            'bad_renter_reason' => $data['reason'],
            'bad_renter_labeled_by' => $staff->user_id,
            'bad_renter_labeled_at' => now(),
        ]);

        $inbox->notifyCustomer(
            $user,
            'Account labeled Bad Renter',
            'Your account was labeled Bad Renter. Reason: '.$data['reason'].' Staff who labeled you: '.$staff->name.'.',
            $staff->user_id
        );

        return response()->json($user->refresh()->load('labeledBy'));
    }

    /**
     * Finds or creates a customer by email and updates their profile.
     * The frontend still signs people in locally, so it never knows the user's password;
     * new users get a random one until login moves to the backend.
     */
    public function sync(CustomerSyncRequest $request): JsonResponse
    {
        $data = $request->validated();
        $email = Str::lower($data['email']);
        unset($data['email']);

        $user = DB::transaction(function () use ($email, $data) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update(array_filter($data, fn ($value) => $value !== null));

                return $user;
            }

            return User::create([
                ...$data,
                'email' => $email,
                'role' => 'customer',
                'password_hash' => Str::random(40),
            ]);
        });

        return response()->json($user->refresh(), $user->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(UserRequest $request): array
    {
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password_hash'] = $request->input('password');
            $data['password_changed_at'] = now();
        }

        return $data;
    }
}
