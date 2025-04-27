<?php

namespace App\Http\Controllers;

use App\Events\AcceptRide;
use App\Events\CancelRide;
use App\Events\NewRide;
use App\Events\Ride as EventsRide;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use PDF; // Use Barryvdh\DomPDF\Facade as PDF

class PDFController extends Controller
{
    //

    public function generatePDF(Request $request,$id)
    {

        // dd($id);

        // try {
        //     $credentialsFilePath = getFilePath('pushConfig').'/push_config.json';
            
        //     // Load credentials directly
        //     $credentials = json_decode(file_get_contents($credentialsFilePath), true);
            
        //     // Print project ID from credentials
        //     print_r("Project ID from credentials: " . ($credentials['project_id'] ?? 'Not found'));
        //     echo"<br>";
        //     // Compare with your config
        //     print_r("Project ID from config: " . gs('firebase_config')->projectId);
        //     echo"<br>";
            
        //     // Auth with Google client
        //     $client = new \Google_Client();
        //     $client->setAuthConfig($credentialsFilePath);
        //     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        //     $client->fetchAccessTokenWithAssertion();
        //     $token = $client->getAccessToken();
            
        //     // Print token info
        //     print_r("Token type: " . ($token['token_type'] ?? 'Not found'));
        //     echo"<br>";

        //     print_r("Token expires: " . ($token['expires_in'] ?? 'Not found'));
        //     echo"<br>";

            
        //     // Make a simple test call
        //     $ch = curl_init();
        //     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/'.gs('firebase_config')->projectId.'/messages:send');
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $token['access_token']]);
        //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     $response = curl_exec($ch);
        //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //     curl_close($ch);
            
        //     print_r("Test response code: " . $httpCode);
        //     echo"<br>";

        //     print_r("Test response: " . $response);
        //     echo"<br>";

        //     dd($httpCode, $response);
        //     return "Test completed, check logs";
        // } catch(\Exception $e) {
        //     return "Error: " . $e->getMessage();
        // }


        // $data = [
        //     'name' => 'Laravel PDF Example',
        //     'ride_amount' => 100,
        //     'discount_amount' => 10
        // ];

        $data = Ride::where('uid', $id)->first();

        // initializePusher();

        // $ride = Ride::where('uid', 'INEZFQKE4P')->first();
        // $user = User::first();
     
        // $shortCode = [
        //     'ride_id'         => $ride->uid,
        //     'service'         => $ride->service->name,
        //     'pickup_location' => $ride->pickup_location,
        //     'destination'     => $ride->destination,
        //     'duration'        => $ride->duration,
        //     'distance'        => $ride->distance
        // ];

       
        // notify($ride->user, 'BID_REJECT', [
        //     'ride_id'         => $ride->uid,
        //     'amount'          => 100,
        //     'service'         => $ride->service->name,
        //     'pickup_location' => $ride->pickup_location,
        //     'destination'     => $ride->destination,
        //     'duration'        => $ride->duration,
        //     'distance'        => $ride->distance
        // ],['push']);


        // $getDrivers = Driver::where('id', 5)->get();

        
        // foreach($getDrivers  as $driver){
        //     // dd($getDrivers);

        //     notify($driver, 'NEW_RIDE', $shortCode, ['push']);
        //     // event(new CancelRide("new-ride-for-driver-".$driver->id));      
        // }
        
        // dd($data);
        
        // event(new NewRide("new-ride-for-driver-$data->driver_id", ['ride' => $data], 'ride_accept_driver'));
        // event(new EventsRide($data, 'online-payment-received'));
        // event(new EventsRide($data, 'TestDrive'));
        // event(new AcceptRide($data, 'ride_accept_drive', $data->toArray())); // on Clicking On Create Bi
        // dd($data);

        if($data){
            
            $pdf = Pdf::loadView('pdf.invoice', compact('data'))->setPaper('A4', 'portrait');
            return $pdf->stream('invoice.pdf');
        }else{

            return view('errors.404');
        }
        // dd($data);
        // Load the view and pass the data

        // return view('pdf.invoice', compact('data'));
    }
}
