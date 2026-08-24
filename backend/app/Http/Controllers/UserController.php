<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        return response()->json(User::create($data), 201);
    }

    public function show(User $user): User
    {
        return $user;
    }

    public function update(Request $request, User $user): User
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['sometimes', 'string', 'min:8'],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return $user->fresh();
    }

    public function destroy(User $user): JsonResponse
    {
        $this->deleteAvatar($user);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    private function deleteAvatar(User $user): void
    {
        if ($avatar = $user->getRawOriginal('avatar')) {
            Storage::disk('public')->delete($avatar);
        }
    }
}
