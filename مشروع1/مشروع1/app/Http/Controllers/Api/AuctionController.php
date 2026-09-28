<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\NewAuction;
use App\Notifications\NewBid;
use App\Notifications\AuctionWon;
use App\Notifications\CarSold;

class AuctionController extends Controller
{
    public function index()
    {
        $auctions = Auction::where('status', 'active')
            ->with('car', 'bids.user')
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $auctions], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_price' => 'required|numeric',
            'end_time' => 'required|date',
        ]);

        $car = Car::find($request->car_id);

        if ($car->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $auction = Auction::create([
            'car_id' => $request->car_id,
            'seller_id' => $request->user()->id,
            'start_price' => $request->start_price,
            'current_price' => $request->start_price,
            'end_time' => $request->end_time,
        ]);

        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new NewAuction($auction));
        }

        return response()->json(['success' => true, 'data' => $auction], 201);
    }

    public function bid(Request $request, $auctionId)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $auction = Auction::find($auctionId);

        if (!$auction || $auction->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'المزاد غير متاح'], 400);
        }

        if ($request->amount <= $auction->current_price) {
            return response()->json(['success' => false, 'message' => 'يجب أن يكون المبلغ أعلى من السعر الحالي'], 400);
        }

        $bid = Bid::create([
            'auction_id' => $auctionId,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
        ]);

        $auction->current_price = $request->amount;
        $auction->save();

        $auction->seller->notify(new NewBid($auction, $bid));

        return response()->json(['success' => true, 'data' => $bid], 201);
    }

    public function bids($auctionId)
    {
        $bids = Bid::where('auction_id', $auctionId)->with('user:id,name')->latest()->get();
        return response()->json(['success' => true, 'data' => $bids], 200);
    }

    public function finishExpired(Request $request, $auctionId)
    {
        $auction = Auction::find($auctionId);

        if (!$auction || $auction->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'المزاد غير متاح'], 400);
        }

        if ($auction->seller_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $auction->status = 'ended';
        $auction->save();

        $winner = $auction->bids()->orderBy('amount', 'desc')->first();

        if ($winner) {
            // تحديث حالة السيارة إلى مباعة
            $auction->car->update(['status' => 'sold']);

            $winner->user->notify(new AuctionWon($auction, $winner->amount));
            $auction->seller->notify(new CarSold($auction, $winner));
        }

        return response()->json(['success' => true, 'message' => 'تم إنهاء المزاد']);
    }

    public function autoFinish()
    {
        $ended = Auction::where('status', 'active')->where('end_time', '<', now())->get();

        foreach ($ended as $auction) {
            $auction->status = 'ended';
            $auction->save();

            $winner = $auction->bids()->orderBy('amount', 'desc')->first();
 if ($winner) {
                // تحديث حالة السيارة إلى مباعة
                $auction->car->update(['status' => 'sold']);

                $winner->user->notify(new AuctionWon($auction, $winner->amount));
                $auction->seller->notify(new CarSold($auction, $winner));
            }
        }

        return response()->json(['success' => true, 'message' => 'تم إنهاء المزادات المنتهية']);
    }
}