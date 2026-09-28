<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول - الأدمن فقط'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        return $next($request);
    }
}