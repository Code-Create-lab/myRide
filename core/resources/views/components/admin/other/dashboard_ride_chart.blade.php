<div class="col-xl-12">
    <x-admin.ui.card class="tc-card">
        <x-admin.ui.card.header class="flex-between gap-2 py-3">
            <h5 class="card-title mb-0 fs-16">@lang('Ride Report')</h5>
            <div class="d-flex gap-2 flex-wrap flex-md-nowrap ride-report">
                <select class="form-select form-control form-select-sm ride_chart">
                    <option value="daily" selected>@lang('Daily')</option>
                    <option value="monthly">@lang('Monthly')</option>
                    <option value="yearly">@lang('Yearly')</option>
                    <option value="date_range">@lang('Date Range')</option>
                </select>
                <div class="date-picker-wrapper-ride d-none w-100">
                    <input type="text" class="form-control form-control-sm date-picker date-picker-ride" name="date"
                        placeholder="@lang('Select Date')">
                </div>
            </div>
        </x-admin.ui.card.header>
        <x-admin.ui.card.body>
            <div id="rideChartArea"></div>
        </x-admin.ui.card.body>
    </x-admin.ui.card>
</div>

@push('script')
    <script>
        "use strict";
        (function($) {

            let tcChart = barChart(
                document.querySelector("#rideChartArea"),
                @json(__(gs('count') ?? '')),
                [{
                        name: 'Deposited',
                        data: []
                    }
                ],
                [],
            );
            const transactionChart = (startDate, endDate) => {
                const url = @json(route('admin.chart.rideReport'));
                const timePeriod = $(".ride_chart").val();
                if (timePeriod == 'date_range') {
                    $(".date-picker-wrapper-ride").removeClass('d-none')
                } else {
                    $(".date-picker-wrapper-ride").addClass('d-none')
                }
                const date = $(".date-picker-ride").val();
                const data = {
                    time_period: timePeriod,
                    date: date
                }

                $.get(url, data,
                    function(data, status) {
                        if (status == 'success') {
                            const plusAmount = Object.values(data).map(item => item.plus_amount);
                            const updatedData = [{
                                    name: "Ride Count",
                                    data: plusAmount,
                                }
                            ]

                            tcChart.updateSeries(updatedData);
                            tcChart.updateOptions({
                                xaxis: {
                                    categories: Object.keys(data),
                                }
                            });
                        }
                    }
                );
            }
            transactionChart();

            $(".ride-report").on('change', 'select', function(e) {
                transactionChart();
            });
            $(".date-picker-wrapper-ride").on('change', '.date-picker', function(e) {
                transactionChart();
            });
        })(jQuery);
    </script>
@endpush
