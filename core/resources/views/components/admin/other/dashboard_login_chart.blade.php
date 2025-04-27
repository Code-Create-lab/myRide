<x-admin.ui.card class="tc-card h-100">
    <x-admin.ui.card.header class="py-4">
        <h5 class="card-title fs-16">@lang('Ride by Status')</h5>
    </x-admin.ui.card.header>
    <x-admin.ui.card.body>
        <div id="userBrowserChart"></div>
    </x-admin.ui.card.body>
</x-admin.ui.card>
{{-- @dd($widget) --}}
@push('script')
    <script>
        "use strict";
        (function($) {
            (function() {
                const labels = ['Running Ride', 'Cancelled Ride', "Complete Ride"];
                const data   = @json([$widget['running_ride'] , $widget['canceled_ride'] ?? 0, $widget['completed_ride'] ?? 0 ]);
                const total  = data.reduce((a, b) => a + b, 0);

                const legendLabels = labels.map((label, index) => {
                    const percent = ((data[index] ?? 1 / total) * 100).toFixed(2);
                    return `<div class=" d-flex  flex-column gap-1  align-items-start mb-3 me-1"><span>${percent}%</span> <span>${label}</span> </div>`;
                });
                const options = {
                    series: data,
                    chart: {
                        type: 'donut',
                        height: 420,
                        width: '100%'
                    },
                    labels: labels,
                    dataLabels: {
                        enabled: false,

                    },
                    legend: {
                        position: 'bottom',
                        markers: {
                            show: false // Hide the default markers
                        },
                        formatter: function(seriesName, opts) {
                            return legendLabels[opts.seriesIndex];
                        }
                    }
                };
                new ApexCharts(document.getElementById('userBrowserChart'), options).render();
            })()
        })(jQuery);
    </script>
@endpush
