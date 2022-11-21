<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login']);
    }

    /**
     * Login request
     *
     * @param \App\Http\Requests\Api\LoginRequest $request
     * @return \App\Http\Resources\Api\AuthResource
     */
    public function login(Request $request)
    {
        /** @var \App\Models\User */
        $user = User::where(['email' => $request->email])->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'default-browser')->plainTextToken;
        return (new AuthResource($user))->additional([
            'token' => $token,
            'type'  => $user->type
        ]);
    }

    public function me()
    {
        $auth = Auth::user();
        $user = User::findOrFail($auth->id);

        $datas = [
            'user_id'        => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'type'           => $user->type,
            'last_login'     => $user->tokens->last()->last_used_at ? $user->tokens->last()->last_used_at->format('d M Y, H:i') : Carbon::now()->format('d M Y, H:i'),
        ];

        return response(['success' => true, 'message' => 'Yeey, this user is authenticated', 'data' => $datas],200);
    }
}
