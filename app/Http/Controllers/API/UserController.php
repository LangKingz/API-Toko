<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|unique:users,username|max:100',
                'phone' => 'required|string|unique:users,phone|max:15', // 'phone' => 'required|string|unique:users,phone|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|min:10
                'email' => 'required|email|unique:users,email|max:255',
                'password' => ['required', 'string', 'min:8', new Password]
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ],
                'User Registered'
            );
        } catch (Exception $error) {
            //throw $th;
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error,
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            if (!auth()->attempt($credentials)) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Unauthorized',
                    ],
                    'Authentication Failed',
                    500
                );
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ],
                'User Logged In'
            );
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $th,
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),
            'Data profile user berhasil diambil'
        );
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'username' => 'string|max:100',
            'phone' => 'string|max:15',
            'email' => 'string|email|max:255',
            'password' => 'string|min:8',
        ]);

        $data = $request->all();
        $user = $request->user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return ResponseFormatter::success(
            [],
            'Token Revoked'
        );
    }
}
