<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Notifications\CarFavorited;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()->paginate(10);

        foreach ($favorites as $car) {
            if ($car->image) {
                $car->image = asset('storage/' . $car->image);
            }
            $car->is_favorite = true;
        }

        return response()->json([
            'success' => true,
            'data' => $favorites
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

     public function store(Request $request, $carId)
    {
        $car = Car::find($carId);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة موجودة مسبقاً في المفضلة'
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'car_id' => $carId,
        ]);

        if ($car->user_id !== $request->user()->id) {
            $car->user->notify(new CarFavorited($car, $request->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة السيارة إلى المفضلة'
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Request $request, $carId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة في المفضلة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'تمت إزالة السيارة من المفضلة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function check(Request $request, $carId)
    {
        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $exists
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}