<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255', // جعلتها nullable لأن النموذج الثاني لا يتطلبها
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6', // أخذت الحد الأدنى 6 من النموذج الثاني
        ]);

        $userData = [
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        // إضافة الاسم إذا كان موجوداً في الطلب
        if ($request->has('name')) {
            $userData['name'] = $request->name;
        }

        $user = User::create($userData);

        // إنشاء token JWT إذا كان النظام يستخدم JWT
        if (interface_exists('Tymon\JWTAuth\Contracts\JWTSubject')) {
            $token = JWTAuth::fromUser($user);
            
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        }

        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // إذا كان النظام يستخدم JWT
        if (interface_exists('Tymon\JWTAuth\Contracts\JWTSubject')) {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return response()->json([
                'message' => 'Login done successfully',
                'token' => $token
            ]);
        }

        // نظام تسجيل الدخول بدون JWT
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Login done successfully']);
    }

    public function logout()
    {
        // إذا كان النظام يستخدم JWT
        if (interface_exists('Tymon\JWTAuth\Contracts\JWTSubject')) {
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['error' => 'Failed to logout'], 500);
            }
        }

        // إذا لم يكن يستخدم JWT، يمكنك إضافة منطق تسجيل الخروج هنا
        return response()->json(['message' => 'Logout functionality may require client-side token handling']);
    }
}