@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-12">
            <x-admin.ui.card>
                <x-admin.ui.card.body :paddingZero=true>
                    <x-admin.ui.table.layout searchPlaceholder="Search service" :renderExportButton="false">
                        <x-admin.ui.table>
                            <x-admin.ui.table.header>
                                <tr>
                                    <th>@lang('Service')</th>
                                    <th>@lang('City Fare')</th>
                                    {{-- <th>@lang('Intercity Fare')</th> --}}
                                    <th>@lang('Commission')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </x-admin.ui.table.header>
                            <x-admin.ui.table.body>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <div class="flex-thumb-wrapper gap-1">
                                                <div class="thumb">
                                                    <img src="{{ imageGet('service', $service->image) }}">
                                                </div>
                                                <span>
                                                    {{ __($service->name) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{-- <span class=" d-block">
                                                    @lang('Fare'):
                                                    {{ showAmount($service->city_min_fare, currencyFormat: false) }}
                                                    - {{ showAmount($service->city_max_fare) }}
                                                </span> --}}
                                                <span class="d-block">
                                                    @lang('Recommended'):
                                                    <span class="fw-bold">
                                                        {{ showAmount($service->city_recommend_fare) }}
                                                    </span>
                                                </span>
                                            </div>
                                        </td>
                                        {{-- <td>
                                            <div>
                                                <span class="d-block">
                                                    @lang('Range'):
                                                    {{ showAmount($service->intercity_min_fare, currencyFormat: false) }}
                                                    - {{ showAmount($service->intercity_max_fare) }}
                                                </span>
                                                <span class="d-block">
                                                    @lang('Recommended'):
                                                    <span class="fw-bold">
                                                        {{ showAmount($service->intercity_recommend_fare) }}
                                                    </span>
                                                </span>
                                            </div>
                                        </td> --}}
                                        <td>
                                            <div>
                                                <span class="d-block">
                                                    @lang('City'):
                                                    <span class="fw-bold">
                                                        {{ getAmount($service->city_fare_commission) }}%
                                                    </span>
                                                </span>
                                                {{-- <span class="d-block">
                                                    @lang('Intercity'):
                                                    <span class="fw-bold">
                                                        {{ getAmount($service->intercity_fare_commission) }}%
                                                    </span>
                                                </span> --}}
                                            </div>
                                        </td>
                                        <td>
                                            <x-admin.other.status_switch :status="$service->status" :action="route('admin.service.status', $service->id)"
                                                title="service" />
                                        </td>
                                        <td>
                                            <x-admin.ui.btn.edit tag="button" :data-image="imageGet('service', $service->image)" :data-resource="$service" />
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.ui.table.empty_message />
                                @endforelse
                            </x-admin.ui.table.body>
                        </x-admin.ui.table>
                        @if ($services->hasPages())
                            <x-admin.ui.table.footer>
                                {{ paginateLinks($services) }}
                            </x-admin.ui.table.footer>
                        @endif
                    </x-admin.ui.table.layout>
                </x-admin.ui.card.body>
            </x-admin.ui.card>
        </div>
    </div>

    <x-admin.ui.modal id="modal" class="modal-xl">
        <x-admin.ui.modal.header>
            <h4 class="modal-title"></h4>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close">
                <i class="las la-times"></i>
            </button>
        </x-admin.ui.modal.header>
        <x-admin.ui.modal.body>
            <form action="{{ route('admin.service.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>@lang('Image')</label>
                    <x-image-uploader type="service" />
                </div>
                <div class="form-group">
                    <label>@lang('Name')</label>
                    <input class="form-control" name="name" type="text" required value="{{ old('name') }}">
                </div>
                <div class="row mb-3">
                    <div class="py-2">
                        <h5 class="divider-title">@lang('City Fare')</h5>
                    </div>
                    <div class="col-lg-6"  style="display: none">
                        <div class="form-group">
                            <label>@lang('Fare')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="city_min_fare" type="number" step="any" required
                                    value="{{ old('city_min_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}/@lang('KM')</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3" style="display: none">
                        <div class="form-group">
                            <label>@lang('Max')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="city_max_fare" type="number" step="any" required
                                    value="{{ old('city_max_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}/@lang('KM')</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 " style="display: " >
                        <div class="form-group">
                            <label>@lang('Fare')
                               
                                <span style="color: red; font-size:0.750rem; font-weight:300"> (Please fill fare on each update) </span>
                            </label>
                            <div class="input-group input--group">
                                <input class="form-control" name="city_recommend_fare" type="number" step="any"
                                    required value="{{ old('city_recommend_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}/@lang('KM')</span>
                            </div>
                        </div>
                    </div>
                   
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Commission')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="city_fare_commission" type="number" step="any"
                                    required value="{{ old('city_fare_commission') }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Platform Fee')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="platform_fee" type="number" step="any"
                                    required value="{{ old('platform_fee') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('GST')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="gst" type="number" step="any"
                                    required value="{{ old('gst') }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="py-2">
                            <h5 class="divider-title">@lang('Peak Hour')</h5>
                        </div>
                        <div class="row">

                      
                        <div class="col-lg-12" style="display: " >
                            <div class="form-group">
                                <label>@lang('Peak Hour Price')</label>
                                <div class="input-group input--group">
                                    <input class="form-control" name="peak_hour_price" type="number" step="any"
                                        required value="{{ old('peak_hour_price') }}">
                                    <span class="input-group-text">times</span>
                                </div>
                            </div>
                        </div>
                        <div class="peak_hour_time">
                            <div class="row peak_hour_row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>@lang('Peak Hour Start')</label>
                                        <div class="input-group input--group">
                                            <input class="form-control" name="peak_hour_start[]" type="time" step="any" required>
                                            <span class="input-group-text"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>@lang('Peak Hour End')</label>
                                        <div class="input-group input--group">
                                            <input class="form-control" name="peak_hour_end[]" type="time" step="any" required>
                                            <span class="input-group-text"></span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-warning add-more">Add More+</button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    </div>
                    {{-- <div class="col-12">
                        <div class="py-2">
                            <h5 class="divider-title">@lang('Intercity Fare')</h5>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>@lang('Min')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="intercity_min_fare" type="number" step="any" required
                                    value="{{ old('intercity_min_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}/@lang('KM')</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>@lang('Max')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="intercity_max_fare" type="number" step="any" required
                                    value="{{ old('intercity_max_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>@lang('Recommended')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="intercity_recommend_fare" type="number" step="any"
                                    required value="{{ old('intercity_recommend_fare') }}">
                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>@lang('Commission')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="intercity_fare_commission" type="number"
                                    step="any" required value="{{ old('intercity_fare_commission') }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div> --}}
                </div>
                <div class="form-group">
                    <x-admin.ui.btn.modal />
                </div>
            </form>
        </x-admin.ui.modal.body>
    </x-admin.ui.modal>
@endsection

@push('script')
    <script>

            // Add More button functionality
        $(document).on("click", ".add-more", function () {
            let newRow = `
                <div class="row peak_hour_row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Peak Hour Start')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="peak_hour_start[]" type="time" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Peak Hour End')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="peak_hour_end[]" type="time" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger remove">Remove</button>
                    </div>
                </div>
            `;

            $(".peak_hour_time").append(newRow);
        });

        // Remove button functionality
        $(document).on("click", ".remove", function () {
            $(this).closest(".peak_hour_row").remove();
        });

        (function($) {
            "use strict";
            const $modal = $("#modal");

            $(".edit-btn").on('click', function(e) {
                const data = $(this).data('resource');
                const imagePath = $(this).data('image');
                const action = "{{ route('admin.service.store', ':id') }}";
                $("input[name='name']").val(data.name);
                $("input[name='city_min_fare']").val(getAmount(data.city_min_fare));
                $("input[name='platform_fee']").val(getAmount(data.platform_fee));
                $("input[name='gst']").val(getAmount(data.gst));
                $("input[name='city_max_fare']").val(getAmount(data.city_max_fare));
              //  $("input[name='city_recommend_fare']").val(getAmount(data.city_recommend_fare));
                $("input[name='city_fare_commission']").val(getAmount(data.city_fare_commission, 2));
                $("input[name='intercity_min_fare']").val(getAmount(data.intercity_min_fare));
                $("input[name='intercity_max_fare']").val(getAmount(data.intercity_max_fare));
                $("input[name='intercity_recommend_fare']").val(getAmount(data.intercity_recommend_fare));
                $("input[name='intercity_fare_commission']").val(getAmount(data.intercity_fare_commission, 2));
                $modal.find(".modal-title").text("@lang('Edit Service')");
                $modal.find(".image-upload img").attr('src', imagePath);
                $modal.find(".image-upload [type=file]").attr('required', false);
                $modal.find('form').attr('action', action.replace(':id', data.id));
                
                $("input[name='peak_hour_price']").val(getAmount(data.peak_hour_price));
                 // Deserialize the peak_hours data
    let peakHoursString = data.peak_hours; // Serialized string (e.g., `a:2:{i:0;s:11:"12:47-13:47";i:1;s:11:"16:47-17:47";}`)
                // console.log("peakHoursString",peakHoursString,data);
                let peakHoursArray = [];
                if(peakHoursString){
                
                 peakHoursArray = unserialize(peakHoursString); // Convert it to an array
            }      

    // Clear existing peak hour rows before adding new ones
    $(".peak_hour_time").empty();

    if (peakHoursArray.length > 0) {
        peakHoursArray.forEach((timeSlot, index) => {
            let times = timeSlot.split("-"); // Split into start and end time

            let newRow = `
                <div class="row peak_hour_row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Peak Hour Start')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="peak_hour_start[]" type="time" required value="${times[0]}">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Peak Hour End')</label>
                            <div class="input-group input--group">
                                <input class="form-control" name="peak_hour_end[]" type="time" required value="${times[1]}">
                            </div>
                        </div>
                        ${index === 0 ? '<button type="button" class="btn btn-warning add-more">Add More+</button>' : '<button type="button" class="btn btn-danger remove">Remove</button>'}
                    </div>
                </div>
            `;

            $(".peak_hour_time").append(newRow);
        });
    } else {
        // Add an empty row if no data exists
        $(".peak_hour_time").append(`
            <div class="row peak_hour_row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>@lang('Peak Hour Start')</label>
                        <div class="input-group input--group">
                            <input class="form-control" name="peak_hour_start[]" type="time" required>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>@lang('Peak Hour End')</label>
                        <div class="input-group input--group">
                            <input class="form-control" name="peak_hour_end[]" type="time" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning add-more">Add More+</button>
                </div>
            </div>
        `);
    }

                $modal.modal("show");
            });

            // Function to unserialize PHP serialized data in JavaScript
            function unserialize(serializedString) {
                let matches = serializedString.match(/s:\d+:"(.*?)"/g); // Extracts all values
                if (matches) {
                    return matches.map(item => item.replace(/s:\d+:"|";/g, "")); // Cleans up extracted values
                }
                return [];
            }


            $(".add-btn").on('click', function(e) {
                const action = "{{ route('admin.service.store') }}";
                $modal.find(".modal-title").text("@lang('Add Service')");
                $modal.find('form').trigger('reset');
                $modal.find('form').attr('action', action);
                $modal.find(".image-upload img").attr('src', "{{ asset('assets/images/drag-and-drop.png') }}");
                $modal.find(".image-upload [type=file]").attr('required', true);
                $modal.modal("show");
            });
        })(jQuery);
    </script>
@endpush


@push('modal')
    <x-confirmation-modal />
@endpush

@push('breadcrumb-plugins')
    <x-admin.ui.btn.add tag="button" />
@endpush


@push('style')
    <style>
        .divider-title {
            position: relative;
            text-align: center;
            width: max-content;
            margin: 0 auto;
        }

        .divider-title::before {
            position: absolute;
            content: '';
            top: 14px;
            left: -90px;
            background: #6b6b6b65;
            height: 2px;
            width: 80px;
        }

        .divider-title::after {
            position: absolute;
            content: '';
            top: 14px;
            right: -90px;
            background: #6b6b6b65;
            height: 2px;
            width: 80px;
        }
    </style>
@endpush
