<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\User;
use App\Models\Rating;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Notifications\CarRated;
use App\Notifications\RatingStatusChanged;

class AdminController extends Controller
{
    
    public function users()
    {
        $users = User::latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف أدمن آخر'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function cars()
    {
        $cars = Car::with('user:id,name,email')->latest()->paginate(20);

        foreach ($cars as $car) {
            if ($car->image) {
                $car->image = asset('storage/' . $car->image);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $cars
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteCar($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $car->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السيارة بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markCarAsSold($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $car->status = 'sold';
        $car->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة السيارة إلى مباعة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markCarAsAvailable($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $car->status = 'available';
        $car->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة السيارة إلى متاحة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'users_count' => User::count(),
                'cars_count' => Car::count(),
                'available_cars' => Car::where('status', 'available')->count(),
                'sold_cars' => Car::where('status', 'sold')->count(),
                'ratings_count' => Rating::count(),
                'favorites_count' => Favorite::count(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function storeCar(Request $request)
    {
        $carController = new CarController();
        return $carController->store($request);
    }

    // ========== التقييمات ==========

    public function pendingRatings()
    {
        $ratings = Rating::where('status', 'pending')->with('user:id,name', 'car:id,brand,model')->latest()->paginate(20);
 return response()->json([
            'success' => true,
            'data' => $ratings
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function approveRating($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['success' => false, 'message' => 'التقييم غير موجود'], 404);
        }

        $rating->status = 'approved';
        $rating->save();

        // إشعار لصاحب السيارة
        $car = $rating->car;
        $car->user->notify(new CarRated($car, $rating->user, $rating->rating, $rating->comment));

        // إشعار للمستخدم اللي أضاف التقييم
        $rating->user->notify(new RatingStatusChanged('approved', $car));

        return response()->json(['success' => true, 'message' => 'تمت الموافقة على التقييم']);
    }

    public function rejectRating($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['success' => false, 'message' => 'التقييم غير موجود'], 404);
        }

        $rating->status = 'rejected';
        $rating->save();

        // إشعار للمستخدم اللي أضاف التقييم
        $car = $rating->car;
        $rating->user->notify(new RatingStatusChanged('rejected', $car));

        return response()->json(['success' => true, 'message' => 'تم رفض التقييم']);
    }
}