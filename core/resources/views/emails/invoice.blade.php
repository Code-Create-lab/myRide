<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Receipt</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; width: 100%; margin: 0; padding: 0;">

    <table align="center" width="400" cellspacing="0" cellpadding="0"
        style="background: white; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
        <tr>
            <td
                style="background-color: #000; color: white; text-align: center; padding: 15px; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <img src="https://yellowride.yugasa.org/img/header_logo.png" width="200px" alt="Logo"
                    style="margin-bottom: 5px;">
                <div style="font-size: 14px; opacity: 0.8;">Your Ride Receipt</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px;">


                 <!-- Date & Time -->
                 <table width="100%" style="
                 margin-bottom: 15px;
             ">
                     <tr style="display: flex; flex-direction: column; gap: 5px;">
                         {{-- <!-- <td width="30"><img src="https://yellowride.yugasa.org/img/calendar.png" alt=""
                                 style="width: 20px;"></td> --> --}}
                         <td style="font-size: 14px; color: gray;">Ride ID</td>
                     </tr>
                     <tr>
                         <td colspan="2" style="font-size: 16px; ">{{ $data->uid }}</td>
                     </tr>
                 </table>
             
                 <!-- Date & Time -->
                 <table width="100%" style="
                 margin-bottom: 15px;
             ">
                     <tr style="display: flex; flex-direction: column; gap: 5px;">
                         <td width="30"><img src="https://yellowrides.com/assets/images/Sedan.png" alt=""
                                 style="width: 20px;"></td>
                         <td style="font-size: 14px; color: gray;">Service</td>
                     </tr>
                     <tr>
                         <td colspan="2" style="font-size: 16px; ">{{ $data->service->name }}</td>
                     </tr>
                 </table>

                 

                <!-- Date & Time -->
                <table width="100%" style="
                margin-bottom: 15px;
            ">
                    <tr style="display: flex; flex-direction: column; gap: 5px;">
                        <td width="30"><img src="https://yellowride.yugasa.org/img/calendar.png" alt=""
                                style="width: 20px;"></td>
                        <td style="font-size: 14px; color: gray;">Date & Time</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 16px; ">{{ Carbon\Carbon::parse($data->created_at)->format('F j, Y \a\t g:i A') }}</td>
                    </tr>
                </table>


                <!-- Trip Duration -->
                {{-- <table width="100%" style="
                margin-bottom: 15px;
            ">
                    <tr style="display: flex; flex-direction: column; gap: 5px;">
                        <td width="30"><img src="https://yellowride.yugasa.org/img/clock.png" alt=""
                                style="width: 20px;"></td>
                        <td style="font-size: 14px; color: gray;">Trip Duration</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 16px;">{{ $data->duration }}</td>
                    </tr>
                </table> --}}


                <!-- Pickup -->
                <table width="100%" style="
                margin-bottom: 15px;
            ">
                    <tr style="display: flex; flex-direction: column; gap: 5px;">
                        <td width="30"><img src="https://yellowride.yugasa.org/img/pickup.png" alt=""
                                style="width: 20px;"></td>
                        <td style="font-size: 14px; color: gray;">Pickup</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 16px;">{{ $data->pickup_location }}</td>
                    </tr>
                </table>


                <!-- Dropoff -->
                <table width="100%">
                    <tr style="display: flex; flex-direction: column; gap: 5px;">
                        <td width="30"><img src="https://yellowride.yugasa.org/img/dropoff.png" alt=""
                                style="width: 20px;"></td>
                        <td style="font-size: 14px; color: gray;">Dropoff</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
                            {{ $data->destination }}</td>
                    </tr>
                </table>


                <!-- Fare Breakdown -->
                <table width="100%">
                    <tr style="">
                        <td colspan="2">
                            <h3 style="margin-top: 10px;">Fare Breakdown</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td align="right">₹{{ number_format($data->recommend_amount, 2, '.', '') }}</td>
                    </tr>
                    <tr style="color: green;">
                        <td>Coupon</td>
                        <td align="right">- ₹{{ number_format($data->discount_amount, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td>Platform Fee</td>
                        <td align="right">₹{{ number_format($data->service->platform_fee, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td>GST ({{ $data->service->gst }}%)</td>
                        <td align="right">+ ₹{{ number_format((($data->service->gst/100) * $data->recommend_amount), 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border-top: 1px solid #ddd; padding-bottom: 6px;"></td>
                    </tr>
                    <tr>
                        <td> <strong>Total <span style="font-size: 12px;">(rounded off)</span></strong></td>
                        <td align="right" style="font-size: 18px; font-weight: bold;">₹{{ round((( $data->recommend_amount - $data->discount_amount )  + ( $data->service->platform_fee + ($data->service->gst/100) * $data->recommend_amount) ))}}</td>
                    </tr>
                    <!-- <hr style="border-bottom: 1px solid #ddd; padding-bottom: 6px;"> -->
                </table>

            </td>
        </tr>
    </table>

</body>

</html>