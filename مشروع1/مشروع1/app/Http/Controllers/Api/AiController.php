<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Auction;
use App\Models\Favorite;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->message;

        // ========== بيانات إحصائية ==========
        $carsCount = Car::where('status', 'available')->count();
        $soldCount = Car::where('status', 'sold')->count();
        $brands = Car::select('brand')->distinct()->pluck('brand')->implode('، ');
        $colors = Car::select('color')->distinct()->pluck('color')->implode('، ');
        $avgPrice = round(Car::where('status', 'available')->avg('price'));
        $discounts = Car::where('discount_percent', '>', 0)->count();
        $favoritesCount = Favorite::count();
        $ratingsCount = Rating::count();
        $usersCount = User::count();

        // ========== تفاصيل كل سيارات المتاحة ==========
        $carsDetails = Car::where('status', 'available')
            ->limit(10)
            ->get()
            ->map(function ($car) {
                $finalPrice = $car->discounted_price;
                return "{$car->brand} {$car->model} ({$car->year}) - لون: {$car->color} - السعر: {$car->price} - السعر النهائي: {$finalPrice} - القير: {$car->transmission} - الوقود: {$car->fuel_type} - المسافة: {$car->mileage}";
            })->implode("\n");

        // ========== السيارات المباعة ==========
        $soldCars = Car::where('status', 'sold')
            ->limit(10)
            ->get()
            ->map(fn($car) => "{$car->brand} {$car->model} ({$car->year}) - لون: {$car->color} - السعر: {$car->price}")
            ->implode('، ');

        // ========== السيارات المخفضة ==========
        $discountedCars = Car::where('discount_percent', '>', 0)
            ->where('status', 'available')
            ->limit(10)
            ->get()
            ->map(function ($car) {
                $finalPrice = $car->discounted_price;
                return "{$car->brand} {$car->model} - السعر قبل: {$car->old_price} - السعر بعد: {$finalPrice} - نسبة الخصم: {$car->discount_percent}%";
            })->implode('، ');

        // ========== المزادات النشطة ==========
        $auctionDetails = Auction::where('status', 'active')
            ->with('car:id,brand,model')
            ->limit(10)
            ->get()
            ->map(fn($a) => "{$a->car->brand} {$a->car->model} - سعر البداية: {$a->start_price} - السعر الحالي: {$a->current_price} - ينتهي في: {$a->end_time}")
            ->implode('، ');

        // ========== أعلى تقييم ==========
        $topRated = Car::where('status', 'available')
            ->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating')
            ->limit(5)
            ->get()
            ->map(fn($car) => "{$car->brand} {$car->model} (" . round($car->ratings_avg_rating, 1) . " ⭐️)")
            ->implode('، ');

        // ========== Prompt شامل ==========
        $prompt = "أنت مساعد ذكي في متجر سيارات اسمه Car Dealership. لديك معلومات حقيقية:

إحصائيات:
- السيارات المتاحة: $carsCount
- السيارات المباعة: $soldCount
- الماركات: $brands
- الألوان: $colors
- متوسط الأسعار: $avgPrice
- السيارات المخفضة: $discounts
- المفضلة: $favoritesCount
- التقييمات: $ratingsCount
- المستخدمين: $usersCount

تفاصيل السيارات المتاحة:
$carsDetails

السيارات المباعة:
$soldCars

السيارات المخفضة:
$discountedCars

المزادات النشطة:
$auctionDetails

أعلى السيارات تقييماً:
$topRated

أجب بالعربية فقط. كن دقيقاً. سؤال المستخدم: $message";
try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('NVIDIA_API_KEY'),
                'Content-Type' => 'application/json',
 ])->post('https://integrate.api.nvidia.com/v1/chat/completions', [
                'model' => 'meta/llama-3.1-8b-instruct',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'top_p' => 0.7,
                'max_tokens' => 1500,
                'stream' => false,
            ]);

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? 'لا يوجد رد';

            return response()->json([
                'success' => true,
                'reply' => $reply,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر الاتصال بخدمة الذكاء الاصطناعي',
            ], 500);
        }
    }
}