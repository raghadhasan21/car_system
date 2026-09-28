<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\NewRatingPending;

class RatingController extends Controller
{
    public function store(Request $request, $carId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $car = Car::find($carId);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تقييم سيارتك الخاصة'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $rating = Rating::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'car_id' => $carId,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending',
            ]
        );

        // إشعار لجميع الأدمن
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewRatingPending($car, $request->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التقييم للمراجعة. سيظهر بعد موافقة الأدمن.',
            'data' => $rating
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function index($carId)
    {
        $car = Car::find($carId);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $ratings = $car->ratings()->where('status', 'approved')->with('user:id,name')->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'ratings' => $ratings,
                'average' => $car->averageRating(),
                'count' => $car->ratingsCount(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Request $request, $carId)
    {
        $rating = Rating::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'التقييم غير موجود'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function show(Request $request, $carId)
    {
        $rating = Rating::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $rating
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}