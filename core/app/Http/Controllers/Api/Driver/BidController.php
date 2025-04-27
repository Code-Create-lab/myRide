<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\Bid;
use App\Models\Ride;
use App\Constants\Status;
use App\Events\Ride as EventsRide;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Events\NewRide;
use App\Events\AcceptRide;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class BidController extends Controller
{
    public function accceptRide(Request $request, $id)
    {
        // $validator = Validator::make($request->all(), [
        //     'bid_amount' => 'required|numeric|gt:0',
        // ]);

        // if ($validator->fails()) {
        //     return apiResponse("validation_error", "error", $validator->errors()->all());
        // }

        $ride   = Ride::pending()->find($id);


        if (!$ride) {
            $notify[] = "Invalid ride";
            return apiResponse("invalid", "error", $notify);
        }

        $driver = Driver::where('online_status', Status::YES)
            ->where('zone_id', $ride->pickup_zone_id)
            ->where("service_id", $ride->service_id)
            ->where('dv', Status::VERIFIED)
            ->where('vv', Status::VERIFIED)
            ->notRunning()
            ->where('id', auth()->id())
            ->first();


        if (!$driver) {
            $notify[] = "You are not eligible to accept this ride.";
            return apiResponse("not_eligible", "error", $notify);
        }

        // if ($driver->balance < gs('negative_balance_driver')) {
        //     $notify[] = "You have reached the maximum allowable negative balance. Please deposit funds to continue.";
        //     return apiResponse("limit", "error", $notify);
        // }

        // if ($request->bid_amount < $ride->min_amount || $request->bid_amount > $ride->max_amount) {
        //     $notify[] = 'Bid amount must be a minimum ' .  showAmount($ride->min_amount) . ' to a maximum of ' . showAmount($ride->max_amount);
        //     return apiResponse("limit", "error", $notify);
        // }

        $bidExists = Bid::where('ride_id', $id)->where('driver_id', $driver->id)->whereIn('status', [Status::BID_PENDING, Status::BID_ACCEPTED])->first();

        // if ($bidExists) {
        //     $notify[] = 'You have already bid ' . showAmount($bidExists->bid_amount) . ' on this ride';
        //     return apiResponse("exists", "error", $notify);
        // }


        $bid             = new Bid();
        $bid->ride_id    = $ride->id;
        $bid->driver_id  = $driver->id;
        // $bid->bid_amount = $request->bid_amount;
        $bid->bid_amount = $ride->amount;
        $bid->status     = Status::BID_PENDING;
        $bid->save();



        $bid->status      = Status::BID_ACCEPTED;
        $bid->accepted_at = now();
        $bid->save();

        //all the bid rejected after the one accept this bid
        Bid::where('id', '!=', $bid->id)->where('ride_id', $bid->ride_id)->update(['status' => Status::BID_REJECTED]);

        if($ride->is_scheduled || $ride->is_scheduled == 1){
            $ride->status            = 4;
            $ride->payment_status    = 2;

        }

        if(!$ride->is_scheduled){

            $ride->status    = Status::RIDE_ACTIVE;
        }




       // $ride            = $bid->ride;
       $ride->driver_id = $bid->driver_id;
       $ride->otp       = getNumber(4);
       $ride->amount    = $bid->bid_amount;
       $ride->save();


       $start_time = Carbon::parse($ride->start_time); // Example start time
       $end_time = Carbon::parse($ride->end_time);   // Example end time

       $diffInMinutes = number_format($start_time->diffInMinutes($end_time), 2, '.', '');

        $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
            ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
                "clientId" => "yellow_rides",
                "channel" => "whatsapp",
                "token" => "",
                "send_to" => $ride->user->mobile,
                "button" => false,
                "header" => "",
                "footer" => "",
                "parameters" => [ $ride->uid, $ride->service->name,  $ride->pickup_location, $ride->destination, number_format($ride->distance, 2, '.', ''), number_format($ride->recommend_amount, 2, '.', '')],
                "msg_type" => "TEXT",
                "templateName" => "booking_confirmation",
                "media_url" => "",
                "buttonUrlParam" => "",
                "userName" => "",
                "lang" => "en"
            ]);

        // Get response
        // dd($response->json());
        $data['waba_response'] = $response->json();

        // Debug response


    //    dd( $ride,$ride->status);

        //$bid->load('driver');

        $ride->load('driver', 'driver.brand', 'service', 'user');

       // initializePusher();


     //  event(new NewRide("new-ride-for-driver-$ride->driver_id", ['ride' => $ride], 'bid_accept'));  // ON Accepting RIde from user App


        $data['bid']                = $bid;
        $data['ride']                = $ride;
        $data['brand_image_path']   = getFilePath('brand');
        $data['service_image_path'] = getFilePath('service');


        // event(new NewRide("new-ride-for-driver-$ride->driver_id", ['ride' => $ride], 'bid_accept'));
        // $data['driver_id'] = $ride->driver_id;
        initializePusher();
        event(new AcceptRide($ride, 'ride_accept', $data)); // on Clicking On Create Bid
        // event(new AcceptRide($ride, 'ride_accept_drive', $data)); // on Clicking On Create Bid
     
        $getDrivers = Driver::active()->get();
        
        foreach ($getDrivers as $driver) {
     
            event(new NewRide("new-ride-for-driver-$driver->id", ['ride' => $ride], 'ride_accept_driver'));
        }
   
            //    event(new EventsRide($ride, 'new_bid', $data)); // on Clicking On Create Bid
        // dd("bid_accept");

       
        notify($ride->user, 'ACCEPT_RIDE', [
            'ride_id'         => $ride->uid,
            'amount'          => showAmount($ride->amount),
            'driver'           => $ride->driver->firstname." ". $ride->driver->lastname,
            'service'         => $ride->service->name,
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'duration'        => $ride->duration,
            'distance'        => $ride->distance,
            'pickup_date_time' => Carbon::parse($ride->pickup_date_time)->format('F j, Y \a\t g:i A') 
        ]);

        // event(new EventsRide($ride, 'new_bid', $data));
        // $notify[] = 'Bid placed successfully';

        $notify[] = 'Ride accepted successfully';
        return apiResponse('accepted', 'success', $notify, [
            'ride' => $ride
        ]);
    }

    public function create(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bid_amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }

        $ride   = Ride::pending()->find($id);

        if (!$ride) {
            $notify[] = "Invalid ride";
            return apiResponse("invalid", "error", $notify);
        }

        $driver = Driver::where('online_status', Status::YES)
            ->where('zone_id', $ride->pickup_zone_id)
            ->where("service_id", $ride->service_id)
            ->where('dv', Status::VERIFIED)
            ->where('vv', Status::VERIFIED)
            ->notRunning()
            ->where('id', auth()->id())
            ->first();

        if (!$driver) {
            $notify[] = "You are not eligible to place a bid on this ride.";
            return apiResponse("not_eligible", "error", $notify);
        }

        if ($driver->balance < gs('negative_balance_driver')) {
            $notify[] = "You have reached the maximum allowable negative balance. Please deposit funds to continue.";
            return apiResponse("limit", "error", $notify);
        }

        if ($request->bid_amount < $ride->min_amount || $request->bid_amount > $ride->max_amount) {
            $notify[] = 'Bid amount must be a minimum ' .  showAmount($ride->min_amount) . ' to a maximum of ' . showAmount($ride->max_amount);
            return apiResponse("limit", "error", $notify);
        }

        $bidExists = Bid::where('ride_id', $id)->where('driver_id', $driver->id)->whereIn('status', [Status::BID_PENDING, Status::BID_ACCEPTED])->first();

        if ($bidExists) {
            $notify[] = 'You have already bid ' . showAmount($bidExists->bid_amount) . ' on this ride';
            return apiResponse("exists", "error", $notify);
        }

        $bid             = new Bid();
        $bid->ride_id    = $ride->id;
        $bid->driver_id  = $driver->id;
        $bid->bid_amount = $request->bid_amount;
        $bid->status     = Status::BID_PENDING;
        $bid->save();

        $bid->load('driver');

        initializePusher();

        $ride->load('driver', 'driver.brand', 'service', 'user');

        $data['bid']                = $bid;
        $data['brand_image_path']   = getFilePath('brand');
        $data['service_image_path'] = getFilePath('service');

        event(new EventsRide($ride, 'new_bid', $data));
        $notify[] = 'Bid placed successfully';

        return apiResponse("bid_success", 'success', $notify, [
            'bid' => $bid
        ]);
    }

    public function cancel($id)
    {

        $ride   = Ride::find($id);
        if (!$ride) {
            $notify[] = "Invalid ride";
            return apiResponse("invalid", "error", $notify);
        }
        $driver = auth()->user();
        $bid    = Bid::where('driver_id', $driver->id)->where('status', Status::BID_PENDING)->where('ride_id', $ride->id)->first();
        if (!$bid) {
            $notify[] = 'The bid is not found';
            return apiResponse("not_found", "error", $notify);
        }

        $bid->status = Status::BID_CANCELED;
        $bid->save();

        $notify[] = 'Bid has been canceled successfully';
        return apiResponse("canceled", "success", $notify, [
            'bid' => $bid
        ]);
    }
}
