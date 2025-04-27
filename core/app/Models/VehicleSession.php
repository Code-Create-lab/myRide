<?php

namespace App\Models;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class VehicleSession extends Model
{
    use GlobalStatus;
    //
    protected $guarded = [];


        public function vehicle()
        {
            return $this->belongsTo(Vehicle::class, 'vehicle_id');
        }
        
        public function driver()
        {
            return $this->belongsTo(Driver::class, 'driver_id');
        }
}
