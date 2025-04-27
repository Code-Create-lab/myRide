<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\RidePayment;
use App\Models\Transaction;

class RidePaymentManager
{
    public function payment($ride, $paymentType)
    {
        // $amount = $ride->amount - $ride->discount_amount;
        $amount = (($ride->recommend_amount - $ride->discount_amount) + ($ride->service->platform_fee + (($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount))));
        $driver = $ride->driver;
        $user   = $ride->user;

        if ($paymentType ==  Status::PAYMENT_TYPE_GATEWAY) {

            $user->balance -= $amount;
            $user->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '-';
            $transaction->trx          = $ride->uid;
            $transaction->remark       = 'payment';
            $transaction->details      = 'Ride payment ' . showAmount($amount) . ' and ride uid ' . $ride->uid . '';
            $transaction->save();
        }

        $this->ridePayment($ride, $paymentType);

        if ($paymentType ==  Status::PAYMENT_TYPE_GATEWAY) {

            if(isset( $driver->balance)){

                $driver->balance += $amount;
            }
            if(!$ride->is_scheduled){

                $driver->save();
           

            $transaction               = new Transaction();
            $transaction->driver_id    = $driver->id;
            $transaction->amount       = $amount;
            $transaction->post_balance = $driver?->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '+';
            $transaction->trx          = $ride->uid;
            $transaction->remark       = 'payment_received';
            $transaction->details      = 'Ride payment received ' . showAmount($amount) . ' and ride uid ' . $ride->uid . '';
            $transaction->save();
          }
        }

      
        if(!$ride->is_scheduled){

            $commissionAmount  = $ride->commission_amount;
            $driver->balance  -= $commissionAmount;
            $driver->save();
       
        $transaction               = new Transaction();
        $transaction->driver_id    = $driver?->id;
        $transaction->amount       = $commissionAmount;
        $transaction->post_balance = $driver->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->trx          = $ride->uid;
        $transaction->remark       = 'ride_commission';
        $transaction->details      = 'Subtract ride commission amount ' . showAmount($commissionAmount) . ' and ride uid ' . $ride->uid . '';
        $transaction->save();
     }
    }

    public function ridePayment($ride, $paymentType)
    {
        $payment               = new RidePayment();
        $payment->ride_id      = $ride->id;
        $payment->rider_id     = $ride->user_id;
        $payment->driver_id    = $ride->driver_id;
        $payment->amount       = $ride->amount - $ride->discount_amount;
        $payment->payment_type = $paymentType;
        $payment->save();

        $ride->payment_status = Status::PAID;
        $ride->payment_type   = $paymentType;
        if( $ride->is_scheduled){
            
            $ride->status         = 5;
        }else{

            $ride->status         = Status::RIDE_COMPLETED;
        }
        $ride->save();
    }
}
