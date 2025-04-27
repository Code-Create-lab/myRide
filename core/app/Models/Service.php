<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Service extends Model
{
    use  GlobalStatus;

    protected $appends = ['adjusted_city_recommend_fare']; // Virtual attribute


    public function getAdjustedCityRecommendFareAttribute()
    {
        $currentTime = Carbon::now()->format('H:i'); // Current time in HH:MM format
        $peakHours = unserialize($this->peak_hours); // Unserialize stored peak hours

        if (!is_array($peakHours)) return $this->city_recommend_fare; // If invalid, return original fare

        foreach ($peakHours as $timeRange) {
            list($start, $end) = explode('-', $timeRange); // Split start and end time
            
            if ($currentTime >= $start && $currentTime <= $end) {
                return $this->city_recommend_fare * $this->peak_hour_price; // Apply multiplier
                // dd($currentTime, $start, $this->city_recommend_fare * $this->peak_hour_price);
            }
        }

        return $this->city_recommend_fare; // Return normal fare if no match
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($service) {
            $service->updateCityRecommendFare();
        });
    }

    public function updateCityRecommendFare()
    {
        $currentTime = Carbon::now()->format('H:i'); // Get current time in HH:MM format
        $peakHours = unserialize($this->peak_hours); // Unserialize stored peak hours

        if (!is_array($peakHours)) return; // If peak_hours is invalid, do nothing

        foreach ($peakHours as $timeRange) {
            list($start, $end) = explode('-', $timeRange); // Split start and end time

            if ($currentTime >= $start && $currentTime <= $end) {
                $calculatedPrice = $this->city_recommend_fare * $this->peak_hour_price;
                $this->city_recommend_fare =   $calculatedPrice; // Apply multiplier
                // dd($this->city_recommend_fare,$this->peak_hour_price, $calculatedPrice);
                // $this->save(); // Save updated fare
                break; // No need to check further if matched
            }
        }
    }

}
