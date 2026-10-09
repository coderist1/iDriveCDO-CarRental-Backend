<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerSyncRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
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
