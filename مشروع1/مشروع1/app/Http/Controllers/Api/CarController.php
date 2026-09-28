<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Auction;
use App\Models\User;
use App\Notifications\NewAuction;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::query();

        if ($request->has('brand')) {
            $query->where('brand', 'LIKE', '%' . $request->brand . '%');
        }

        if ($request->has('year_min')) {
            $query->where('year', '>=', $request->year_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('discounted')) {
            $query->where('discount_percent', '>', 0);
        }

        $query->where('status', 'available');

        $cars = $query->latest()->paginate(10);

        foreach ($cars as $car) {
            if ($car->image) {
                $car->image = asset('storage/' . $car->image);
            }
            $car->average_rating = $car->averageRating();
            $car->ratings_count = $car->ratingsCount();
            $car->final_price = $car->discounted_price;
        }

        return response()->json([
            'success' => true,
            'data' => $cars
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $car = Car::with('ratings')->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->image) {
            $car->image = asset('storage/' . $car->image);
        }

        $car->average_rating = $car->averageRating();
        $car->ratings_count = $car->ratingsCount();
        $car->final_price = $car->discounted_price;

        return response()->json([
            'success' => true,
            'data' => $car
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'price' => 'required|numeric',
            'mileage' => 'required|integer',
            'fuel_type' => 'required|string',
            'transmission' => 'required|in:manual,automatic',
            'color' => 'required|string',
            'contact_phone' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'user_id' => $request->user()->id,
            'brand' => $request->brand,
            'model' => $request->model,
            'year' => $request->year,
            'price' => $request->price,
            'mileage' => $request->mileage,
            'fuel_type' => $request->fuel_type,
            'transmission' => $request->transmission,
            'color' => $request->color,
            'description' => $request->description,
            'status' => 'available',
            'contact_phone' => $request->contact_phone,
            'old_price' => $request->old_price,
            'discount_percent' => $request->discount_percent ?? 0,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cars', 'public');
            $data['image'] = $path;
        }

        $car = Car::create($data);

        if ($request->is_auction == true && $request->auction_start_price && $request->auction_end_time) {
            $auction = Auction::create([
                'car_id' => $car->id,
                'seller_id' => $request->user()->id,
                'start_price' => $request->auction_start_price,
                'current_price' => $request->auction_start_price,
                'end_time' => $request->auction_end_time,
            ]);
 $users = User::all();
            foreach ($users as $user) {
                $user->notify(new NewAuction($auction));
            }
        }

        if ($car->image) {
            $car->image = asset('storage/' . $car->image);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة السيارة بنجاح',
            'data' => $car
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function update(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه السيارة'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'brand', 'model', 'year', 'price', 'mileage',
            'fuel_type', 'transmission', 'color', 'description', 'contact_phone',
            'old_price', 'discount_percent'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cars', 'public');
            $data['image'] = $path;
        }

        $car->update($data);

        if ($car->image) {
            $car->image = asset('storage/' . $car->image);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل السيارة بنجاح',
            'data' => $car
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذه السيارة'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $car->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السيارة بنجاح'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markAsSold(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتغيير حالة هذه السيارة'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $car->status = 'sold';
        $car->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة السيارة إلى مباعة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markAsAvailable(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'السيارة غير موجودة'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($car->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتغيير حالة هذه السيارة'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $car->status = 'available';
        $car->save();
return response()->json([
            'success' => true,
            'message' => 'تم إعادة السيارة إلى متاحة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function myCars(Request $request)
    {
        $cars = Car::where('user_id', $request->user()->id)->latest()->paginate(10);

        foreach ($cars as $car) {
            if ($car->image) {
                $car->image = asset('storage/' . $car->image);
            }
            $car->final_price = $car->discounted_price;
        }

        return response()->json([
            'success' => true,
            'data' => $cars
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}