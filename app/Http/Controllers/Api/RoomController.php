<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class RoomController extends Controller
{
    //
    public function roomlist(Request $r)
    {
        // Retrieve POST data
        $checkinpost = $r->input('checkin');
        $checkoutpost = $r->input('checkout');
        $childrenpost = $r->input('children');
        $adultspost = $r->input('adults');
    
        // Validate input
        if (empty($checkinpost) || empty($checkoutpost)) {
            return response()->json(['error' => 'Check-in and check-out dates are required'], 400);
        }
    
        // Booking status condition
        $status = "bookingstatus != 1 AND bookingstatus != 5";
    
        // Fetch booking data
        $exits = DB::table('booked_info')
            ->where('checkindate', '<=', $checkinpost)
            ->where('checkoutdate', '>', $checkinpost)
            ->whereRaw($status)
            ->get();
    
        $exit = DB::table('booked_info')
            ->where('checkindate', '<', $checkoutpost)
            ->where('checkoutdate', '>=', $checkoutpost)
            ->whereRaw($status)
            ->get();
    
        $check = DB::table('booked_info')
            ->where('checkindate', '>', $checkinpost)
            ->where('checkoutdate', '<=', $checkoutpost)
            ->whereRaw($status)
            ->get();
    
        // Fetch room numbers
        $numberlist = DB::table('tbl_roomnofloorassign')->pluck('roomid')->toArray();
        $bookedRooms = [];
        foreach ([$exits, $exit, $check] as $bookings) {
            foreach ($bookings as $booked) {
                $bookedRooms[] = $booked->room_no;
            }
        }
    
        // Determine available rooms
        if (empty($bookedRooms)) {
            $availableRooms = $numberlist;
        } else {
            $availableRooms = array_diff($numberlist, $bookedRooms);
        }
    
        // Fetch room details for available rooms
        $roominfo = DB::table('roomdetails')
            ->leftJoin('room_image', 'room_image.room_id', '=', 'roomdetails.roomid')
            ->whereIn('roomdetails.roomid', $availableRooms)
            ->select('roomdetails.*', 'room_image.room_imagename')
            ->get();
    
        // Return the response as JSON
        return response()->json(['roominfo' => $roominfo]);
    }
    
    public function roomDetail(Request $r)
    {
        // Retrieve request data
        $roomid = $r->input('roomid');
        $checkin = $r->input('checkin');
        $checkout = $r->input('checkout');
        $adults = $r->input('adults');
        $children = $r->input('children');
    
        // Validate input
        if (empty($roomid) || empty($checkin) || empty($checkout)) {
            return response()->json(['error' => 'Room ID, check-in, and check-out dates are required'], 400);
        }
    
        // Booking status condition
        $status = "bookingstatus != 1 AND bookingstatus != 5";
        $croom = "FIND_IN_SET($roomid, roomid)";
    
        // Fetch booking data
        $exits = DB::table('booked_info')
            ->where('checkindate', '<=', $checkin)
            ->where('checkoutdate', '>', $checkin)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->get();
    
        $exit = DB::table('booked_info')
            ->where('checkindate', '<', $checkout)
            ->where('checkoutdate', '>=', $checkout)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->get();
    
        $check = DB::table('booked_info')
            ->where('checkindate', '>=', $checkin)
            ->where('checkoutdate', '<=', $checkout)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->get();
    
        // Fetch total room counts
        $totalroom1 = DB::table('booked_info')
            ->where('checkindate', '<=', $checkin)
            ->where('checkoutdate', '>', $checkin)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->sum('total_room');
    
        $totalroom2 = DB::table('booked_info')
            ->where('checkindate', '<', $checkout)
            ->where('checkoutdate', '>=', $checkout)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->sum('total_room');
    
        $totalroom3 = DB::table('booked_info')
            ->select('checkindate', DB::raw('max(total_room) as allroom'))
            ->where('checkindate', '>=', $checkin)
            ->where('checkoutdate', '<=', $checkout)
            ->whereRaw($status)
            ->whereRaw($croom)
            ->groupBy('checkindate')
            ->get();
    
        $allbokedroom3 = $totalroom3->isEmpty() ? 0 : max($totalroom3->pluck('allroom')->toArray());
    
        $roomdetails = DB::table('roomdetails')
            ->leftJoin('room_image', 'room_image.room_id', '=', 'roomdetails.roomid')
            ->where('roomdetails.roomid', $roomid)
            ->select('roomdetails.*', 'room_image.room_imagename')
            ->first();
    
        $numberlist = DB::table('tbl_roomnofloorassign')
            ->where('roomid', $roomid)
            ->get();
    
        $roomlist = $numberlist->pluck('roomno')->implode(',');
    
        if (empty($exits) && empty($exit) && empty($check)) {
            $freeroom = $roomlist;
            $isfound = 0;
        } else {
            $bookedroom = collect([$exits, $exit, $check])->flatten()->pluck('room_no')->implode(',');
    
            $allbokedroom = max((int)$totalroom1, (int)$totalroom2, (int)$allbokedroom3);
            $allfreeroom = $numberlist->count();
    
            if ($allfreeroom > $allbokedroom) {
                $freeroom = $this->differences($bookedroom, $roomlist);
                $isfound = !empty($freeroom) ? 1 : 2;
            } else {
                $freeroom = '';
                $isfound = 2;
            }
        }
    
        return response()->json([
            'roominfo' => $roomdetails,
            'freeroom' => $freeroom,
            'isfound' => $isfound,
        ]);
    }
    
    private function differences($booked, $allrooms)
    {
        $bookedArray = explode(',', $booked);
        $allroomsArray = explode(',', $allrooms);
        $availableRooms = array_diff($allroomsArray, $bookedArray);
        return implode(',', $availableRooms);
    }



    public function updateCart(Request $r)
    {
        $r->merge(array_map('trim', $r->all()));
    
        $validator = Validator::make($r->all(), [
           
            'guest' => 'nullable|string',
            'password' => 'nullable|string',
            'specialinstruction' => 'nullable|string',
            'checkin' => 'required|date',
            'checkout' => 'required|date',
            'adult' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'roomid' => 'required|integer',
            'discount' => 'nullable|numeric',
            'amount' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
    
        $customerId = $r->customer_id;
    
        $room = DB::table('roomdetails')->where('roomid',$r->input('roomid'))->first();
        $customer = DB::table('customerinfo')->where('customerid', $customerId)->first();
    
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
            ], 404);
        }
    
        $amount = $r->input('amount') - $r->input('discount', 0);
        $taxRate = DB::table('tbl_taxmgt')->where('isactive', 1)->sum('rate');
        $taxAmount = ($taxRate * $amount) / 100;
        $serviceChargeRate = 10; 
        $serviceAmount = ($amount * $serviceChargeRate) / 100;
        $grandTotal = $amount + $taxAmount + $serviceAmount;
    
        $bookingData = [
            'customer_id' => $customerId,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => $customer->email,
            'phone' => $customer->cust_phone,
            'guest' => $r->input('guest'),
            'special_instruction' => $r->input('specialinstruction'),
            'room_id' => $room->roomid,
            'room_type' => $room->roomtype,
            'room_rate' => $room->rate,
            'discount' => $r->input('discount', 0),
            'amount' => $amount,
            'checkin_date' => $r->input('checkin'),
            'checkout_date' => $r->input('checkout'),
            'adults' => $r->input('adult'),
            'children' => $r->input('children', 0),
            'tax' => $taxAmount,
            'service_charge' => $serviceAmount,
            'grand_total' => $grandTotal,
        ];
    
        try {
            DB::beginTransaction();
    
            $cart = DB::table('cart')->insertGetId($bookingData);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
            ], 201); 
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    


}
