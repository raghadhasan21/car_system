<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|digits:10',
        ], [
            'email.regex' => 'يجب أن يكون الإيميل @gmail.com',
            'phone.digits' => 'يجب أن يكون رقم الهاتف 10 خانات',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم التسجيل بنجاح',
            'user' => $user,
            'token' => $token
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            $user->avatar = asset('storage/' . $user->avatar);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الحساب بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|digits:10',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->phone = $request->phone;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        if ($user->avatar) {
            $user->avatar = asset('storage/' . $user->avatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user' => $user
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدخول - للأدمن فقط'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}