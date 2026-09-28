<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'هذا الإيميل غير مسجل لدينا',
        ]);

        $otp = rand(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now(),
            ]
        );

        Mail::raw("رمز إعادة تعيين كلمة المرور: $otp", function ($message) use ($request) {
            $message->to($request->email)->subject('رمز إعادة التعيين');
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز إلى بريدك الإلكتروني'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('token', $request->code)->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'الرمز غير صحيح أو منتهي الصلاحية'
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $user = \App\Models\User::where('email', $record->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('token', $request->code)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}