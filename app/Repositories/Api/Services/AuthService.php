<?php

namespace App\Repositories\Api\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function users(): object
    {
        return User::paginate(10);
    }

    public function login(array $params): array|null
    {
        if (Auth::attempt($params)) {
            $user = Auth::user();
            $token = $user->createToken($user->email)->plainTextToken;
            $data = [
                'id' => $user->id,
                'image' => image_path($user->image),
                'email' => $user->email,
                'password' => $user->password,
                'token' => $token,
            ];
            return $data;
        }

        return null;
    }

    public function register(array $params): User
    {
        if ($params['image']) {
            $params['image'] = uploadImageFile($params['image'], 'user-profile/');
        }

        $params['password'] = bcrypt($params['password']);
        $user = User::create($params);
        $user['token'] = $user->createToken($user)->plainTextToken;

        return $user;
    }

    public function userProfileUpdate(array $params, object $user): User
    {
        if (isset($params['image'])) {
            deleteImage($user->image);
            $params['image'] = uploadImageFile($params['image'], 'user-profile/');
        } else {
            $params['image'] = $user->image;
        }

        $user->update([
            'name' => $params['name'],
            'email' => $params['email'],
            'image' => $params['image']
        ]);

        return $user;
    }

    public function userChangePassword(string $newPassword, object $user): void
    {
        $user->update($user->id, [
            'password' => bcrypt($newPassword)
        ]);
    }

    public function userDelete(): void
    {
        $user = auth()->user();

        if ($user) {
            deleteImage($user->image);
        }

        $user->delete();
    }

    public function logout()
    {
        return auth()->user()->currentAccessToken()->delete();
    }
}