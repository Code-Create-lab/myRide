@props(['widget'])
<div class="row responsive-row">
    {{-- <x-admin.ui.widget.group.dashboard.driver :widget="$widget" /> --}}
    <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.two :url="route('admin.rider.all')" variant="primary" title="Total Users" :value="$widget['total_users']"
            icon="las la-users" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.report.rider.payment')" variant="success" title="Total Payment" :value="$widget['total_payment']"
            icon="la la-money-bill-wave" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.driver.all')" variant="primary" title="Total Driver" :value="$widget['total_driver']"
            icon="las la-users" :currency="false" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.rides.running')" variant="primary" title="Running Ride" :value="$widget['running_ride']"
            icon="las la-car" :currency="false" />
    </div>


    {{-- <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.report.driver.commission')" variant="primary" title="Total Commission" :value="$widget['total_commission']"
            icon="la la-percentage" />
    </div> --}}
    {{-- <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.report.rider.payment') . '?payment_type='.Status::PAYMENT_TYPE_CASH.''" variant="success" title="Total Cash Payment" :value="$widget['total_cash_payment']"
            icon="fa-solid fa-sack-dollar" />
    </div> --}}
    {{-- <div class="col-xxl-3 col-sm-6">
        <x-admin.ui.widget.four :url="route('admin.report.rider.payment') . '?payment_type='.Status::PAYMENT_TYPE_GATEWAY.''" variant="warning" title="Total Online Payment" :value="$widget['total_online_payment']"
            icon="lab la-cc-paypal" />
    </div> --}}
</div>
