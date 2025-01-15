<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function signIn(Request $request)
    {
        $user = $this->user->where('email', $request->username)
            ->orWhere('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('', 'Credenciales incorrectas');
        }

        if ($user->status == 0) {
            return ApiResponse::error('', 'Usuario inactivo',);
        }

        return ApiResponse::success($this->userState($user));
    }

    public function signOut(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success('Sesión cerrada, hasta luego');
    }

    public function user(Request $request)
    {
        $user =  $request->user();
        return ApiResponse::success($this->userState($user));
    }

    private function userState($user)
    {
        $role = $this->getUserRole($user);

        return [
            'token' => $this->getUserToken($user),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role->name,
                'redirectTo' => $role->redirect_to,
            ],
            'permissions' => implode('|', $user->getAllPermissions()->pluck('name')->toArray()),
        ];
    }

    private function getUserToken($user)
    {
        $token = $user->currentAccessToken();

        if (!$token) {
            return $user->createToken($user->email)->plainTextToken;
        }
        return null;
    }

    private function getUserRole($user)
    {
        $role = Role::where('name', $user->getRoleNames()[0])->first();

        if (!$role) {
            return ApiResponse::error('El usuario no tiene un rol asignado', 401);
        }

        return $role;
    }
}
