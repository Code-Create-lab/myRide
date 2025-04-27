<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            padding: 0;
            margin: 0;
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .receipt-header {
            padding: 20px;
            background-color: #000;
            color: white;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .logo-tag {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .ride-summary {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .ride-detail {
            display: flex;
            margin-bottom: 15px;
        }
        
        .ride-detail-icon {
            width: 24px;
            margin-right: 15px;
            color: #555;
            text-align: center;
        }
        
        .ride-detail-content {
            flex: 1;
        }
        
        .ride-detail-title {
            font-size: 14px;
            color: #888;
            margin-bottom: 2px;
        }
        
        .ride-detail-value {
            font-size: 16px;
            color: #333;
        }
        
        .route-map {
            height: 120px;
            background-color: #f0f0f0;
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 14px;
            border-radius: 6px;
        }
        
        .fare-breakdown {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .fare-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .fare-item.sub-item {
            padding-left: 20px;
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .fare-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 1px solid #ddd;
            font-weight: bold;
            font-size: 18px;
        }
        
        .payment-info {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .payment-icon {
            width: 30px;
            height: 30px;
            background-color: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .receipt-footer {
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }
        
        .rating {
            margin: 15px 0;
            text-align: center;
        }
        
        .stars {
            color: #ffb400;
            font-size: 24px;
            letter-spacing: 3px;
        }
        
        .receipt-id {
            margin-top: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .action-button {
            display: block;
            text-align: center;
            background-color: #1c1c1c;
            color: white;
            padding: 12px;
            margin: 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }

        .icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="logo"><img src="https://yellowrides.com/img/header_logo.png" width="200px" alt=""></div>
            <div class="logo-tag">Your Ride Receipt</div>
        </div>
        
        <div class="ride-summary">
            <div class="ride-detail">
                {{-- <div class="ride-detail-icon">
                    <img src="https://yellowrides.com/assets/images/Sedan.png" alt="Date" class="icon">
                </div> --}}
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Ride ID</div>
                    <div class="ride-detail-value">{{ $data->uid }}</div>
                </div>
            </div>
            <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowrides.com/assets/images/Sedan.png" alt="Date" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Service</div>
                    <div class="ride-detail-value">{{ $data->service->name }}</div>
                </div>
            </div>
            <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowrides.com/img/calendar.png" alt="Date" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Date & Time</div>
                    <div class="ride-detail-value">{{ Carbon\Carbon::parse($data->created_at)->format('F j, Y \a\t g:i A') }}</div>
                </div>
            </div>
            
            {{-- <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowride.yugasa.org/img/clock.png" alt="Duration" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Trip Duration</div>
                    <div class="ride-detail-value">{{ $data->duration }}</div>
                </div>
            </div> --}}
            
            <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowride.yugasa.org/img/pickup.png" alt="Pickup" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Pickup</div>
                    <div class="ride-detail-value">{{ $data->pickup_location }}</div>
                </div>
            </div>
            
            <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowride.yugasa.org/img/dropoff.png" alt="Dropoff" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Dropoff</div>
                    <div class="ride-detail-value">{{ $data->destination }}</div>
                </div>
            </div>
            
            {{-- <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowride.yugasa.org/img/car_logo.png" alt="Vehicle" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Vehicle</div>
                    <div class="ride-detail-value">Sedan • KA 01 AB 1234</div>
                </div>
            </div> --}}
            
            {{-- <div class="ride-detail">
                <div class="ride-detail-icon">
                    <img src="https://yellowride.yugasa.org/img/driver.png" alt="Driver" class="icon">
                </div>
                <div class="ride-detail-content">
                    <div class="ride-detail-title">Driver</div>
                    <div class="ride-detail-value">Raj Kumar</div>
                </div>
            </div> --}}
        </div>
        
        <div class="fare-breakdown">
            <h3 style="margin-bottom: 15px;">Fare Breakdown</h3>
            
            {{-- <div class="fare-item">
                <span>Base Fare</span>
                <span> <img src="https://admin.yellowride.yugasa.org/img/rupee-indian.png" width="10px" alt="">60.00</span>
            </div>
            
            <div class="fare-item">
                <span>Distance (12.4 km)</span>
                <span> <img src="https://admin.yellowride.yugasa.org/img/rupee-indian.png" width="10px" alt="">160.00</span>
            </div>
            
            <div class="fare-item">
                <span>Time (24 mins)</span>
                <span> <img src="https://admin.yellowride.yugasa.org/img/rupee-indian.png" width="10px" alt="">48.00</span>
            </div>
            
            <div class="fare-item">
                <span>Waiting Charge (2 mins)</span>
                <span> <img src="https://admin.yellowride.yugasa.org/img/rupee-indian.png" width="10px" alt="">10.00</span>
            </div> --}}
            
            <div class="fare-item">
                <span>Subtotal</span>
                <span style="margin-left: 65%"> <img src="https://yellowrides.com/img/rupee-indian.png" width="10px" alt="">{{ number_format($data->recommend_amount, 2, '.', '') }}</span>
            </div>
            
            <div class="fare-item" >
                <span>Coupon</span>
                <span style="color: green;margin-left: 65%">-  <img src="https://yellowrides.com/img/rupee-indian.png" width="10px" alt="">{{ number_format($data->discount_amount, 2, '.', '') }}</span>
            </div>
            
            <div class="fare-item sub-item">
                <span>Platform Fee</span>
                <span style="margin-left: 57%"> <img src="https://yellowrides.com/img/rupee-indian.png" width="10px"  alt="" >{{ number_format($data->service->platform_fee, 2, '.', '') }}</span>
            </div>
            
            <div class="fare-item sub-item">
                <span>GST ({{ $data->service->gst }}%)</span>
                <span style="margin-left: 60%">+  <img src="https://yellowrides.com/img/rupee-indian.png" width="10px" alt="">{{ number_format((($data->service->gst/100) * $data->recommend_amount), 2, '.', '') }}</span>
            </div>

              
            <div class="fare-total">
                <span>Total <span style="font-size:12px; font-weight:100 ">(rounded off) </span></span>
                <span style="margin-left: 50%"> <img src="https://yellowrides.com/img/rupee-indian.png" width="12px" alt="">{{ round((( $data->recommend_amount - $data->discount_amount )  + ( $data->service->platform_fee + ($data->service->gst/100) * $data->recommend_amount) ))}}</span>
            </div>
        </div>
        
        {{-- <div class="payment-info">
            <h3 style="margin-bottom: 15px;">Payment</h3>
            
            <div class="payment-method">
                <div class="payment-icon">
                    <img src="https://yellowride.yugasa.org/img/card.png" alt="Payment" width="16" height="16">
                </div>
                <div>
                    <div style="font-weight: bold;">Paid via Credit Card</div>
                    <div style="font-size: 13px; color: #666;">•••• •••• •••• 1234</div>
                </div>
            </div>
            
        </div> --}}
        {{-- <a href="#" class="action-button">Download Invoice</a> --}}
        
        <div class="receipt-footer">
            {{-- <div class="rating">
                <div style="margin-bottom: 5px; font-weight: bold;">How was your ride?</div>
                <div class="stars">
                    <img src="https://yellowride.yugasa.org/img/star-filled.png" alt="Star" width="20" height="20">
                    <img src="https://yellowride.yugasa.org/img/star-filled.png" alt="Star" width="20" height="20">
                    <img src="https://yellowride.yugasa.org/img/star-filled.png" alt="Star" width="20" height="20">
                    <img src="https://yellowride.yugasa.org/img/star-filled.png" alt="Star" width="20" height="20">
                    <img src="https://yellowride.yugasa.org/img/star-filled.png" alt="Star" width="20" height="20">
                </div>
            </div> --}}
            
            {{-- <div>Need help with this trip?</div>
            <div style="margin-top: 5px;">
                <a href="#" style="color: #333; text-decoration: none; font-weight: bold;">Contact Support</a>
            </div>
            
            <div class="receipt-id">
                Receipt ID: RIDEGO115478369
            </div> --}}
        </div>
    </div>
</body>
</html>