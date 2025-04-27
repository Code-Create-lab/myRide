<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Ride;
use App\Models\Zone;
use App\Models\Coupon;
use App\Models\Driver;
use App\Models\Service;
use App\Models\SosAlert;
use App\Constants\Status;
use App\Events\CancelRide;
use App\Events\NewRide;
use App\Events\Ride as EventsRide;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Bid;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Mail\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideController extends Controller
{

    public function findFareAndServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'service_id'            => 'required|integer',
            'pickup_latitude'       => 'required|numeric',
            'pickup_longitude'      => 'required|numeric',
            'destination_latitude'  => 'required|numeric',
            'destination_longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        // $service = Service::active()->find($request->service_id);
        $services = Service::active()->orderBy('name')->get();
        $result = array();
        foreach($services as $service){

            // dd($service);

        if (!$services) {
            $notify[] = 'This service is currently unavailable';
            return apiResponse("not_found", 'error', $notify);
        }

        $zoneData = $this->getZone($request);
        if (@$zoneData['status'] == 'error') {
            $notify[] = $zoneData['message'];
            return apiResponse('not_found', 'error', $notify);
        }
        $googleMapData = $this->getGoogleMapData($request);

        if (@$googleMapData['status'] == 'error') {
            $notify[] = $googleMapData['message'];
            return apiResponse('api_error', 'error', $notify);
        }

        // Log::info('findFareAndServices', [
        //     'zoneData' => $zoneData,
        //     'googleMapData' =>   $googleMapData,
        //     'request' => $request->all()
        // ]);

        $pickUpZone      = $zoneData['pickup_zone'];
        $destinationZone = $zoneData['destination_zone'];
        $distance        = $googleMapData['distance'];
        $data = $googleMapData;
        // $data = $service->toArray();
        $data = array_merge($googleMapData, $service->toArray());
        if ($pickUpZone->id == $destinationZone->id) {
            $data['min_amount']       = $service->city_min_fare * $distance;
            $data['max_amount']       = $service->city_max_fare * $distance;
            $data['recommend_amount'] = $service->city_recommend_fare * $distance;
            $fare = $service->city_recommend_fare * $distance;
            $formattedFare = number_format($fare, 2, '.', ''); // Convert to 10.99 format
            $fareString = (string) $formattedFare; // Convert to string

            $data['city_recommend_fare'] = $fareString ;
            $data['ride_type']        = Status::CITY_RIDE;
        } else {
            $data['min_amount']       = $service->intercity_min_fare * $distance;
            $data['max_amount']       = $service->intercity_max_fare * $distance;
            $data['recommend_amount'] = $service->intercity_recommend_fare * $distance;
            $data['ride_type']        = Status::INTER_CITY_RIDE;
        }
        $result[] = $data; // Alternative to array_push($result, $data);
        
     }
    //  Log::info('findFareAndServices', [
    //     'zoneData' => $zoneData,
    //     'googleMapData' =>   $googleMapData,
    //     'request' => $request->all(),
    //     'result' => $result
    // ]);
        return apiResponse("ride_data", 'success', data: $result);
    }

    public function findFareAndDistance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'            => 'required|integer',
            'pickup_latitude'       => 'required|numeric',
            'pickup_longitude'      => 'required|numeric',
            'destination_latitude'  => 'required|numeric',
            'destination_longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        $service = Service::active()->find($request->service_id);

        if (!$service) {
            $notify[] = 'This service is currently unavailable';
            return apiResponse("not_found", 'error', $notify);
        }

        $zoneData = $this->getZone($request);

        if (@$zoneData['status'] == 'error') {
            $notify[] = $zoneData['message'];
            return apiResponse('not_found', 'error', $notify);
        }
        $googleMapData = $this->getGoogleMapData($request);

        if (@$googleMapData['status'] == 'error') {
            $notify[] = $googleMapData['message'];
            return apiResponse('api_error', 'error', $notify);
        }

        $pickUpZone      = $zoneData['pickup_zone'];
        $destinationZone = $zoneData['destination_zone'];
        $distance        = $googleMapData['distance'];
        $data            = $googleMapData;

        if ($pickUpZone->id == $destinationZone->id) {
            $data['min_amount']       = $service->city_min_fare * $distance;
            $data['max_amount']       = $service->city_max_fare * $distance;
            $data['recommend_amount'] = $service->city_recommend_fare * $distance;
            $data['ride_type']        = Status::CITY_RIDE;
        } else {
            $data['min_amount']       = $service->intercity_min_fare * $distance;
            $data['max_amount']       = $service->intercity_max_fare * $distance;
            $data['recommend_amount'] = $service->intercity_recommend_fare * $distance;
            $data['ride_type']        = Status::INTER_CITY_RIDE;
        }
        return apiResponse("ride_data", 'success', data: $data);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'            => 'required|integer',
            'pickup_latitude'       => 'required|numeric',
            'pickup_longitude'      => 'required|numeric',
            'destination_latitude'  => 'required|numeric',
            'destination_longitude' => 'required|numeric',
            'note'                  => 'nullable',
            // 'number_of_passenger'   => 'required|integer',
            // 'offer_amount'          => 'required|numeric',
            // 'payment_type'          => ['required', Rule::in(Status::PAYMENT_TYPE_GATEWAY, Status::PAYMENT_TYPE_CASH)],
            // 'gateway_currency_id'   => $request->payment_type == Status::PAYMENT_TYPE_GATEWAY ? 'required|exists:gateway_currencies,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        $service = Service::active()->find($request->service_id);

        if (!$service) {
            $notify[] = 'This service is currently unavailable';
            return apiResponse("not_found", 'error', $notify);
        }

        $zoneData = $this->getZone($request);

        if (@$zoneData['status'] == 'error') {
            $notify[] = $zoneData['message'];
            return apiResponse('not_found', 'error', $notify);
        }

        $googleMapData = $this->getGoogleMapData($request);

        if (@$googleMapData['status'] == 'error') {
            $notify[] = $googleMapData['message'];
            return apiResponse('api_error', 'error', $notify);
        }

        $data            = $googleMapData;
        $pickUpZone      = $zoneData['pickup_zone'];
        $destinationZone = $zoneData['destination_zone'];
        $distance        = $googleMapData['distance'];
        $user            = auth()->user();

        if ($pickUpZone->country !=  $destinationZone->country) {  // can not create ride between two country
            $notify[] = "The pickup zone and destination zone must be within the same country.";
            return apiResponse('zone_error', 'error', $notify);
        }

        if ($pickUpZone->id == $destinationZone->id) {  // city ride
            $data['min_amount']            = $service->city_min_fare * $distance;
            $data['max_amount']            = $service->city_max_fare * $distance;
            $data['recommend_amount']      = $service->city_recommend_fare * $distance;
            $data['ride_type']             = Status::CITY_RIDE;
            $data['commission_percentage'] = $service->city_fare_commission;
        } else {
            $data['min_amount']            = $service->intercity_min_fare * $distance;
            $data['max_amount']            = $service->intercity_max_fare * $distance;
            $data['recommend_amount']      = $service->intercity_recommend_fare * $distance;
            $data['ride_type']             = Status::INTER_CITY_RIDE;
            $data['commission_percentage'] = $service->intercity_fare_commission;
        }

        // if ($distance < gs('min_distance')) {

        //     $notify[] = 'Minimum distance must be ' . getAmount(gs('min_distance')) . ' km';
        //     return apiResponse('limit_error', 'error', $notify);
        // }

        // if ($request->offer_amount < $data['min_amount'] || $request->offer_amount > $data['max_amount']) {
        //     $notify[] = 'The offer amount must be a minimum of ' . showAmount($data['min_amount']) . ' to a maximum of ' . showAmount($data['max_amount']);
        //     return apiResponse('limit_error', 'error', $notify);
        // }

        $ride                        = new Ride();
        $ride->uid                   = getTrx(10);
        $ride->user_id               = $user->id;
        $ride->service_id            = $request->service_id;
        $ride->pickup_location       = @$data['origin_address'];
        $ride->pickup_latitude       = $request->pickup_latitude;
        $ride->pickup_longitude      = $request->pickup_longitude;
        $ride->pickup_date_time      = $request->pickup_date_time ?? Carbon::now();
        $ride->is_scheduled          = $request->is_scheduled;
        $ride->destination           = @$data['destination_address'];
        $ride->destination_latitude  = $request->destination_latitude;
        $ride->destination_longitude = $request->destination_longitude;
        $ride->ride_type             = $data['ride_type'];
        $ride->note                  = $request->note;
        $ride->number_of_passenger   = $request->number_of_passenger;
        $ride->distance              = $distance;
        $ride->duration              = $data['duration'];
        $ride->pickup_zone_id        = $pickUpZone->id;
        $ride->destination_zone_id   = $destinationZone->id;
        $ride->recommend_amount      = $data['recommend_amount'];
        $ride->min_amount            = $data['min_amount'];
        $ride->max_amount            = $data['max_amount'];
        $ride->amount                = $request->offer_amount;
        $ride->payment_type          = $request->payment_type;
        $ride->commission_percentage = $data['commission_percentage'];
        $ride->gateway_currency_id   = $request->payment_type == Status::PAYMENT_TYPE_GATEWAY ? $request->gateway_currency_id : 0;
        $ride->save();

        $drivers = Driver::active()
            ->where('online_status', Status::YES)
            ->where('zone_id', $ride->pickup_zone_id)
            ->where("service_id", $ride->service_id)
            ->where('dv', Status::VERIFIED)
            ->where('vv', Status::VERIFIED)
            ->notRunning()
            ->notActive()
            ->notEnd()
            ->get();

          

        $shortCode = [
            'ride_id'         => $ride->uid,
            'service'         => $ride->service->name,
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'duration'        => $ride->duration,
            'distance'        => $ride->distance,
            'pickup_date_time' => Carbon::parse($ride->pickup_date_time)->format('F j, Y \a\t g:i A') 
        ];

        $ride->load('user', 'service', 'driver', 'driver.brand');
        initializePusher();
      
        foreach ($drivers as $driver) {

            if($driver->ride ){

           
            notify($driver, 'NEW_RIDE', $shortCode);
            event(new NewRide("new-ride-for-driver-$driver->id", [
                'ride'              => $ride,
                'driver_image_path' => getFilePath('driver'),
                'user_image_path'   => getFilePath('user'),
            ]));
        }

        // dd( $drivers );

    }

        $notify[] = 'Ride created successfully';
        return apiResponse('ride_create_success', 'success', $notify, [
            'ride' => $ride
        ]);
    }

    public function createAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'            => 'required|integer',
            'pickup_latitude'       => 'required|numeric',
            'pickup_longitude'      => 'required|numeric',
            'destination_latitude'  => 'required|numeric',
            'destination_longitude' => 'required|numeric',
            'note'                  => 'nullable',
            // 'number_of_passenger'   => 'required|integer',
            // 'offer_amount'          => 'required|numeric',
            // 'payment_type'          => ['required', Rule::in(Status::PAYMENT_TYPE_GATEWAY, Status::PAYMENT_TYPE_CASH)],
            // 'gateway_currency_id'   => $request->payment_type == Status::PAYMENT_TYPE_GATEWAY ? 'required|exists:gateway_currencies,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        $service = Service::active()->find($request->service_id);

        if (!$service) {
            $notify[] = 'This service is currently unavailable';
            return apiResponse("not_found", 'error', $notify);
        }

        $zoneData = $this->getZone($request);

        if (@$zoneData['status'] == 'error') {
            $notify[] = $zoneData['message'];
            return apiResponse('not_found', 'error', $notify);
        }

        $googleMapData = $this->getGoogleMapData($request);

        if (@$googleMapData['status'] == 'error') {
            $notify[] = $googleMapData['message'];
            return apiResponse('api_error', 'error', $notify);
        }

        $data            = $googleMapData;
        $pickUpZone      = $zoneData['pickup_zone'];
        $destinationZone = $zoneData['destination_zone'];
        $distance        = $googleMapData['distance'];
        $user            = auth()->user();

        if ($pickUpZone->country !=  $destinationZone->country) {  // can not create ride between two country
            $notify[] = "The pickup zone and destination zone must be within the same country.";
            return apiResponse('zone_error', 'error', $notify);
        }

        if ($pickUpZone->id == $destinationZone->id) {  // city ride
            $data['min_amount']            = $service->city_min_fare * $distance;
            $data['max_amount']            = $service->city_max_fare * $distance;
            $data['recommend_amount']      = $service->city_recommend_fare * $distance;
            $data['ride_type']             = Status::CITY_RIDE;
            $data['commission_percentage'] = $service->city_fare_commission;
        } else {
            $data['min_amount']            = $service->intercity_min_fare * $distance;
            $data['max_amount']            = $service->intercity_max_fare * $distance;
            $data['recommend_amount']      = $service->intercity_recommend_fare * $distance;
            $data['ride_type']             = Status::INTER_CITY_RIDE;
            $data['commission_percentage'] = $service->intercity_fare_commission;
        }

        // if ($distance < gs('min_distance')) {

        //     $notify[] = 'Minimum distance must be ' . getAmount(gs('min_distance')) . ' km';
        //     return apiResponse('limit_error', 'error', $notify);
        // }

        // if ($request->offer_amount < $data['min_amount'] || $request->offer_amount > $data['max_amount']) {
        //     $notify[] = 'The offer amount must be a minimum of ' . showAmount($data['min_amount']) . ' to a maximum of ' . showAmount($data['max_amount']);
        //     return apiResponse('limit_error', 'error', $notify);
        // }

        $ride                        = new Ride();
        $ride->uid                   = getTrx(10);
        $ride->user_id               = $user->id;
        $ride->service_id            = $request->service_id;
        $ride->pickup_location       = @$data['origin_address'];
        $ride->pickup_latitude       = $request->pickup_latitude;
        $ride->pickup_longitude      = $request->pickup_longitude;
        $ride->pickup_date_time      = $request->pickup_date_time ?? Carbon::now();
        $ride->is_scheduled          = $request->is_scheduled;
        $ride->destination           = @$data['destination_address'];
        $ride->destination_latitude  = $request->destination_latitude;
        $ride->destination_longitude = $request->destination_longitude;
        $ride->ride_type             = $data['ride_type'];
        $ride->note                  = $request->note;
        $ride->number_of_passenger   = $request->number_of_passenger;
        $ride->distance              = $distance;
        $ride->duration              = $data['duration'];
        $ride->pickup_zone_id        = $pickUpZone->id;
        $ride->destination_zone_id   = $destinationZone->id;
        $ride->recommend_amount      = $data['recommend_amount'];
        $ride->min_amount            = $data['min_amount'];
        $ride->max_amount            = $data['max_amount'];
        $ride->amount                = $request->offer_amount;
        $ride->payment_type          = $request->payment_type;
        // $ride->status                =  5;
        $ride->commission_percentage = $data['commission_percentage'];
        $ride->gateway_currency_id   = $request->payment_type == Status::PAYMENT_TYPE_GATEWAY ? $request->gateway_currency_id : 0;
        $ride->save();

        // $drivers = Driver::active()
        //     ->where('online_status', Status::YES)
        //     ->where('zone_id', $ride->pickup_zone_id)
        //     ->where("service_id", $ride->service_id)
        //     ->where('dv', Status::VERIFIED)
        //     ->where('vv', Status::VERIFIED)
        //     ->notRunning()
        //     ->get();

        // $shortCode = [
        //     'ride_id'         => $ride->uid,
        //     'service'         => $ride->service->name,
        //     'pickup_location' => $ride->pickup_location,
        //     'destination'     => $ride->destination,
        //     'duration'        => $ride->duration,
        //     'distance'        => $ride->distance
        // ];

        // $ride->load('user', 'service', 'driver', 'driver.brand');
        // initializePusher();

        // foreach ($drivers as $driver) {
        //     notify($driver, 'NEW_RIDE', $shortCode);
        //     event(new NewRide("new-ride-for-driver-$driver->id", [
        //         'ride'              => $ride,
        //         'driver_image_path' => getFilePath('driver'),
        //         'user_image_path'   => getFilePath('user'),
        //     ]));
        // }

        $notify[] = 'Ride created successfully';
        return apiResponse('ride_create_success', 'success', $notify, [
            'ride' => $ride
        ]);
    }

    public function details($id)
    {
        $ride = Ride::with(['bids', 'userReview', 'driverReview', 'driver', 'service', 'driver.brand'])->where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'Invalid ride';
            return apiResponse('not_found', 'error', $notify);
        }
        $notify[] = 'Ride Details';
        return apiResponse('ride_details', 'success', $notify, [
            'ride'               => $ride,
            'service_image_path' => getFilePath('service'),
            'brand_image_path'   => getFilePath('brand'),
            'user_image_path'    => getFilePath('user'),
            'driver_image_path'  => getFilePath('driver'),
        ]);
    }

    public function cancel(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        $ride = Ride::whereIn('status', [Status::RIDE_PENDING, Status::RIDE_ACTIVE, 5])->where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'Ride not found';
            return apiResponse("not_found", 'error', $notify);
        }

        $cancelRideCount = Ride::where('user_id', auth()->id())
            ->where('canceled_user_type', Status::USER)
            ->count();

        // if ($cancelRideCount >= gs('user_cancellation_limit')) {
        //     $notify[] = 'You have already exceeded the cancellation limit for this month';
        //     return apiResponse("limit_exceeded", 'error', $notify);
        // }

        if ($ride->status == Status::RIDE_ACTIVE) {
            notify($ride->driver, 'CANCEL_RIDE', [
                'ride_id'         => $ride->uid,
                'reason'          => $ride->cancel_reason,
                'amount'          => showAmount($ride->amount, currencyFormat: false),
                'service'         => $ride->service->name,
                'pickup_location' => $ride->pickup_location,
                'destination'     => $ride->destination,
                'duration'        => $ride->duration,
                'distance'        => $ride->distance,
            ]);

      
        }

        $ride->cancel_reason      = $request->cancel_reason;
        $ride->canceled_user_type = Status::USER;
        $ride->status             = Status::RIDE_CANCELED;
        $ride->cancelled_at       = now();
        $ride->save();

        $ride->load('user', 'service');

        $getDriver = $ride->driver;
  
        initializePusher();
        // dd($getDriver);

        // foreach($getDrivers  as $driver){

            if(!$ride->is_scheduled && $ride->driver){

              event(new CancelRide("new-ride-for-driver-".$getDriver->id ));   

            }
      

      
        // initializePusher();
      
        
        $getDrivers = Driver::active()
            ->where('online_status', Status::YES)
            ->where('zone_id', $ride->pickup_zone_id)
            ->where("service_id", $ride->service_id)
            ->where('dv', Status::VERIFIED)
            ->where('vv', Status::VERIFIED)
            ->notRunning()
            ->notActive()
            ->notEnd()
            ->get();
        // $getDrivers = $ride->driver;

        // dd($getDriver);

        foreach($getDrivers  as $driver){

            if(!$ride->is_scheduled && $ride->driver_id == 0){

                event(new CancelRide("new-ride-for-driver-".$driver->id ));   

            }
        }

        // event(new NewRide("new-ride-for-driver-1", [
        //     'ride'              => $ride,
        //     'driver_image_path' => getFilePath('driver'),
        //     'user_image_path'   => getFilePath('user'),
        // ]));
        
        // event(new NewRide("new-ride-for-driver-$ride->driver_id", ['ride' => $ride], 'cancel-ride'));

        // event(new NewRide("new-ride-for-driver-$ride->driver_id", ['ride' => $ride], 'cancel-ride'));

        

       

        $notify[] = 'Ride canceled successfully';
        return apiResponse("canceled_ride", 'success', $notify);
    }

    public function sos(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'message'   => 'nullable',
        ]);

        if ($validator->fails()) {
            return apiResponse('validation_error', 'error', $validator->errors()->all());
        }

        $ride = Ride::running()->where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'The ride is not found';
            return apiResponse('invalid_ride', 'error', $notify);
        }

        $sosAlert            = new SosAlert();
        $sosAlert->ride_id   = $id;
        $sosAlert->latitude  = $request->latitude;
        $sosAlert->longitude = $request->longitude;
        $sosAlert->message   = $request->message;
        $sosAlert->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $ride->user->id;
        $adminNotification->title     = 'A new SOS Alert has been created, please take action';
        $adminNotification->click_url = urlPath('admin.rides.detail', $ride->id);
        $adminNotification->save();

        $notify[] = 'SOS request successfully';
        return apiResponse("sos_request", "success", $notify);
    }


    public function list()
    {
        // $rides = Ride::with(['driver', 'user', 'service'])
        //     ->filter(['ride_type', 'status'])
        //     ->where('user_id', auth()->id())
        //     ->orderBy('id', 'desc')
        //     ->paginate(getPaginate());
        $rides = Ride::with(['driver', 'user', 'service'])
            ->filter(['ride_type', 'status'])
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('is_scheduled', '!=', 1)
                      ->orWhere('payment_status', 1);
            })
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
            

            
        // dd($rides);
        $notify[]      = "Get the ride list";
        $data['rides'] = $rides;
        return apiResponse("ride_list", 'success', $notify, $data);
    }

    private function getZone($request)
    {
        $zones           = Zone::active()->get();
        $pickupAddress   = ['lat' => $request->pickup_latitude, 'long' => $request->pickup_longitude];
        $pickupZone      = null;
        $destinationZone = null;

        foreach ($zones as $zone) {
            $pickupZone = insideZone($pickupAddress, $zone);
            if ($pickupZone) {
                $pickupZone = $zone;
                break;
            }
        }

        if (!$pickupZone) {
            return [
                'status'  => 'error',
                'message' => 'The pickup location is not inside of our zones'
            ];
        }

        $destinationAddress = ['lat' => $request->destination_latitude, 'long' => $request->destination_longitude];

        foreach ($zones as $zone) {
            $destinationZone = insideZone($destinationAddress, $zone);

            if ($destinationZone) {
                $destinationZone = $zone;
                break;
            }
        }

        if (!$destinationZone) {
            return [
                'status'  => 'error',
                'message' => 'The destination location is not inside of our zones'
            ];
        }

        return [
            'pickup_zone'      => $pickupZone,
            'destination_zone' => $destinationZone,
            'status'           => 'success'
        ];
    }
    private function getGoogleMapData($request)
    {
        $apiKey        = gs('google_maps_api');
        $url           = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$request->pickup_latitude},{$request->pickup_longitude}&destinations={$request->destination_latitude},{$request->destination_longitude}&units=driving&key={$apiKey}";
        $response      = file_get_contents($url);
        $googleMapData = json_decode($response);

        if ($googleMapData->status != 'OK') {
            return [
                'status'  => 'error',
                'message' => 'Something went wrong!'
            ];
        }

        if ($googleMapData->rows[0]->elements[0]->status == 'ZERO_RESULTS') {
            return [
                'status'  => 'error',
                'message' => 'Direction not found'
            ];
        }

        $distance = $googleMapData->rows[0]->elements[0]->distance->value / 1000;
        $duration = $googleMapData->rows[0]->elements[0]->duration->text;

        return [
            'distance'            => $distance,
            'duration'            => $duration,
            'origin_address'      => $googleMapData->origin_addresses[0],
            'destination_address' => $googleMapData->destination_addresses[0],
        ];
    }

    public function bids($id)
    {
        $ride = Ride::where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'The ride is not found';
            return apiResponse('not_found', 'error', $notify);
        }

        $bids     = Bid::with(['driver', 'driver.service', 'driver.brand'])->where('ride_id', $ride->id)->whereIn('status', [Status::BID_PENDING, Status::BID_ACCEPTED])->get();
        $notify[] = 'All Bid';

        return apiResponse("bids", "success", $notify, [
            'bids'              => $bids,
            'ride'              => $ride,
            'driver_image_path' => getFilePath('driver'),
            'user_image_path'   => getFilePath('user'),
        ]);
    }

    public function accept($bidId)
    {
        $bid = Bid::pending()->with('ride')->whereHas('ride', function ($q) {
            return $q->pending()->where('user_id', auth()->id());
        })->find($bidId);

        if (!$bid) {
            $notify[] = 'Invalid bid';
            return apiResponse('not_found', 'error', $notify);
        }

        $bid->status      = Status::BID_ACCEPTED;
        $bid->accepted_at = now();
        $bid->save();

        //all the bid rejected after the one accept this bid
        Bid::where('id', '!=', $bid->id)->where('ride_id', $bid->ride_id)->update(['status' => Status::BID_REJECTED]);

        $ride            = $bid->ride;
        $ride->status    = Status::RIDE_ACTIVE;
        $ride->driver_id = $bid->driver_id;
        $ride->otp       = getNumber(4);
        $ride->amount    = $bid->bid_amount;
        $ride->save();

        $ride->load('driver', 'driver.brand', 'service', 'user');

        initializePusher();

        event(new NewRide("new-ride-for-driver-$ride->driver_id", ['ride' => $ride], 'bid_accept'));

        notify($ride->driver, 'ACCEPT_RIDE', [
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

        $notify[] = 'Bid accepted successfully';
        return apiResponse('accepted', 'success', $notify, [
            'ride' => $ride
        ]);
    }

    public function reject($id)
    {
        $bid = Bid::pending()->with('ride')->find($id);

        if (!$bid) {
            $notify[] = 'Invalid bid';
            return apiResponse('not_found', 'error', $notify);
        }

        $ride = $bid->ride;
        if ($ride->user_id != auth()->id()) {
            $notify[] = 'This ride is not for this rider';
            return apiResponse('unauthenticated', 'error', $notify);
        }

        $bid->status = Status::BID_REJECTED;
        $bid->save();

        initializePusher();

        event(new EventsRide($ride, 'bid_reject'));

        notify($ride->user, 'BID_REJECT', [
            'ride_id'         => $ride->uid,
            'amount'          => showAmount($bid->bid_amount),
            'service'         => $ride->service->name,
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'duration'        => $ride->duration,
            'distance'        => $ride->distance
        ]);

        $notify[] = 'Bid rejected successfully';

        return apiResponse('rejected_bid', 'success', $notify);
    }

    public function payment($id)
    {
        $ride = Ride::where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'The ride is not found';
            return apiResponse('not_found', 'error', $notify);
        }
        $ride['amount'] =         round((( $ride->recommend_amount - $ride->discount_amount )  + ( $ride->service->platform_fee + (($ride->service->gst/100) * ($ride->recommend_amount - $ride->discount_amount))) ));
      
        // $ride->amount =  $ride['amount'] ;
        $ride->save();
        $ride['gst'] = $ride->service->gst;
        $ride['platform_fee'] = $ride->service->platform_fee;
        
        
        $ride['payable_amount'] = round((( $ride->recommend_amount - $ride->discount_amount )  + ( $ride->service->platform_fee + (($ride->service->gst/100) * ($ride->recommend_amount - $ride->discount_amount))) ));
        $ride->load('driver', 'driver.brand', 'service', 'user', 'coupon');

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->active();
            // ->automatic()
        })->with('method')->orderby('method_code')->get();

        $notify[] = "Ride Payments";
        return apiResponse('payment', 'success', $notify, [
            'gateways'          => $gatewayCurrency,
            'image_path'        => getFilePath('gateway'),
            'ride'              => $ride,
            'coupons'           => Coupon::orderBy('id', 'desc')->active()->get(),
            'driver_image_path' => getFilePath('driver'),
        ]);
    }

    public function paymentSave(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => ['required', Rule::in(Status::PAYMENT_TYPE_GATEWAY, Status::PAYMENT_TYPE_CASH)],
            'method_code'  => 'required_if:payment_type,1',
            'currency'     => 'required_if:payment_type,1',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", 'error', $validator->errors()->all());
        }

        $ride  = Ride::where('user_id', auth()->id())->find($id);

        if (!$ride) {
            $notify[] = 'The ride is not found';
            return apiResponse('not_found', 'error', $notify);
        }
        if ($request->payment_type == Status::PAYMENT_TYPE_GATEWAY) {


                // $ride->load('driver', 'driver.brand', 'service', 'user');
                // initializePusher();

                // event(new EventsRide($ride, 'online-payment-received'));


            return $this->paymentViaGateway($request, $ride);




    // Get response
    // $data['waba_response'] = $response->json();


        } else {
            initializePusher();
            $ride->load('driver', 'user', 'service');
            event(new EventsRide($ride, 'cash-payment-request'));
            event(new EventsRide($ride, 'new-cash-payment'));
            if($ride->is_scheduled){

                $notify[] = "Advance Ride Booked";
            }else{

                $notify[] = "Please give the driver " . showAmount($ride->amount) . " in cash.";
            }
            return apiResponse('cash_payment', 'success', $notify, [
                'ride' => $ride
            ]);
        }
    }

    private function paymentViaGateway($request, $ride)
    {
        $amount = $ride->amount - $ride->discount_amount;

        $gateway = GatewayCurrency::whereHas('method', function ($gateway) {
            $gateway->active()->automatic();
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();

        if (!$gateway) {
            $notify[] = "Invalid gateway selected";
            return apiResponse('not_found', 'error', $notify);
        }

        if ($gateway->min_amount > $amount) {
            $notify[] = 'Minimum limit for this gateway is ' . showAmount($gateway->min_amount);
            return apiResponse('limit_exists', 'error', $notify);
        }
        if ($gateway->max_amount < $amount) {
            $notify[] = 'Maximum limit for this gateway is ' . showAmount($gateway->max_amount);
            return apiResponse('limit_exists', 'error', $notify);
        }

       
        $calculatedAmount = round((($ride->recommend_amount - $ride->discount_amount ?? 0) + (($ride->service->platform_fee + (($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount))))));
        $charge      = ($ride->service->platform_fee + ($ride->service->gst / 100)); //0
        $payable     = $amount + $charge;
        $finalAmount = $payable * $gateway->rate;
        $user        = auth()->user();
        // dd( $finalAmount, $amount, $charge, $gateway->rate);
        $data                  = new Deposit();
        $data->from_api        = 1;
        $data->user_id         = $user->id;
        $data->method_code     = $gateway->method_code;
        $data->method_currency = strtoupper($gateway->currency);
        $data->amount          = $calculatedAmount;
        $data->charge          = $charge;
        $data->rate            = $gateway->rate;
        $data->final_amount    = $calculatedAmount;
        $data->ride_id         = $ride->id;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->success_url     = urlPath('user.deposit.history');
        $data->failed_url      = urlPath('user.deposit.history');
        $data->trx             = getTrx();
        $data->save();

        $ride->amount = $calculatedAmount;
        $ride->paid_amount = $calculatedAmount;
        $ride->save();

        // $paramWaba = [
        //     (float) number_format($ride->recommend_amount, 2, '.', ''),
        //     (float) ($ride->service->platform_fee ?? 0),
        //     (float) ($ride->service->gst ?? 0),
        //     (float) number_format($ride->discount_amount, 2, '.', ''),
        //     (float) number_format(
        //         round(
        //             (($ride->recommend_amount - $ride->discount_amount) +
        //             ($ride->service->platform_fee + ($ride->service->gst / 100) * $ride->recommend_amount))
        //         ),
        //         2,
        //         '.',
        //         ''
        //     )
        //     ];

        // $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => $ride->user->mobile,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "ridecompleted",
        //     "media_url" =>"",
        //     "buttonUrlParam" =>$ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);


        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 7979068408,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "ridecompleted",
        //     "media_url" => "",
        //     "buttonUrlParam" =>  $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 8766271520,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "ridecompleted",
        //     "media_url" => "",
        //     "buttonUrlParam" => $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

        // $data['waba_response'] = $response ;
        // $data['waba_param'] = $paramWaba ;
        // Mail::to($ride->user->email)->bcc(['snehal.yugasa@gmail.com', 'shivanisingh.yugasa@gmail.com'])->send(new Invoice($ride));



        Log::info('paymentViaGateway:', $data->toArray());
        notify($ride->driver, 'WALLET_RIDE_PAYMENT', [
            'ride_uid'         => $ride->uid,
            'ride_amount'          => showAmount($ride->amount),
            'rider'           => $ride->user->firstname." ". $ride->user->lastname,
            'pickup_location'         => $ride->pickup_location,
            'destination'        => $ride->destination,
            'destination'     => $ride->destination,
            'completed_at'     => $ride->updated_at,
        ]);


        $notify[] = "Online Payment";

        return apiResponse("gateway_payment", "success", $notify, [
            'deposit'      => $data,
            'redirect_url' => route('deposit.app.confirm', encrypt($data->id))
        ]);
    }
}
