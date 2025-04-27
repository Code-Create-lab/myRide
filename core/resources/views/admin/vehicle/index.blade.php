@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-12">
            <x-admin.ui.card>
                <x-admin.ui.card.body :paddingZero=true>
                    <x-admin.ui.table.layout searchPlaceholder="Search Vehicle" :renderExportButton="false">
                        <x-admin.ui.table>
                            <x-admin.ui.table.header>
                                <tr>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Model')</th>
                                    <th>@lang('Number')</th>
                                    <th>@lang('Color')</th>
                                    <th>@lang('Image')</th>
                                    <th>@lang('RC')</th>
                                    <th>@lang('Insurance')</th>
                                    <th>@lang('Pollution')</th>
                                    <th>@lang('Active')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </x-admin.ui.table.header>
                            <x-admin.ui.table.body>
                                @forelse($vehicles as $vehicle)
                                    <tr>
                                        <td style="text-align: center;">
                                          @if(!$vehicle->is_occupied)

                                          <img class="notRunning" src="{{ imageGet('service', $vehicle->service->image) }}" title="Not Running">
                                         {{-- <img src="{{ $vehicle->service->image }}" alt="">  --}}
                                          @else
                                          <img class="running" src="{{ imageGet('service', $vehicle->service->image) }}" title="Running">
                                          {{-- {{ $vehicle->service->image }} --}}
                                          @endif
                                        </td>
                                        <td>
                                            <span class="d-block">
                                                {{ __($vehicle->model) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $vehicle->number }}
                                        </td>
                                        <td>
                                            {{ $vehicle->color }}
                                        </td>
                                        <td>
                                            @if($vehicle->image)
                                            <a target="new"  href="{{ url('core/storage/app/public//'.$vehicle->image) }}">

                                                <i class="fa fa-file"></i> Image
                                            </a>
                                            {{-- core/storage/app/public/vehicles/images/rM0N7rfyFPXQyCXICK16jymscyiJjYRaWwHXvAn8.png --}}
                                              {{-- <img src="{{ url('core/storage/app/public//'.$vehicle->image) }}" alt="">   --}}
                                              {{-- {{ ($vehicle->image) }} --}}
                                              @endif
                                        </td>
                                        <td>

                                            @if($vehicle->rc)
                                            <a target="new"  href="{{ url('core/storage/app/public//'.$vehicle->rc) }}">

                                                <i class="fa fa-file"></i> RC
                                            </a>
                                            {{-- <img src="{{ url('core/storage/app/public//'.$vehicle->image) }}" alt="">   --}}
                                                {{-- {{ ($vehicle->rc) }} --}}

                                                @endif
                                        </td>
                                        <td>
                                            @if($vehicle->rc)
                                            <a target="new" href="{{ url('core/storage/app/public//'.$vehicle->insurance) }}">

                                                <i class="fa fa-file"></i> Insurance
                                            </a>
                                                {{-- {{ ($vehicle->polution_certificate) }} --}}
                                                @endif
                                        </td>
                                        <td>
                                            @if($vehicle->rc)
                                            <a target="new" href="{{ url('core/storage/app/public//'.$vehicle->polution_certificate) }}">

                                                <i class="fa fa-file"></i> Pollution Certificatie
                                            </a>
                                            {{-- <a href="{{ route('admin.rides.all') }}?applied_coupon_id={{$vehicle->id }}"
                                                class=" badge badge--success">
                                                {{ $vehicle->rides_count }} @lang('times')
                                            </a> --}}
                                            @endif
                                        </td>
                                        <td>
                                            <x-admin.other.status_switch :status="$vehicle->status" :action="route('admin.vehicle.status.change', $vehicle->id)"
                                                title="coupon" />
                                        </td>
                                        <td>
                                            @if($vehicle->is_occupied)
                                            <button class="btn  btn-outline--primary edit-btn" disabled>
                                                <i class="las la-pencil-alt me-1"></i> Edit
                                            </button>
                                            @else
                                            <x-admin.ui.btn.edit tag="button" : :data-resource="$vehicle" />
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.ui.table.empty_message />
                                @endforelse
                            </x-admin.ui.table.body>
                        </x-admin.ui.table>
                        @if ($vehicles->hasPages())
                            <x-admin.ui.table.footer>
                                {{ paginateLinks($vehicles) }}
                            </x-admin.ui.table.footer>
                        @endif
                    </x-admin.ui.table.layout>
                </x-admin.ui.card.body>
            </x-admin.ui.card>
        </div>
    </div>

    <x-admin.ui.modal id="modal">
        <x-admin.ui.modal.header>
            <h4 class="modal-title"></h4>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close">
                <i class="las la-times"></i>
            </button>
        </x-admin.ui.modal.header>
        <x-admin.ui.modal.body>
            <form action="{{ route('admin.vehicle.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-12 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Vehicle Model') </label>
                           
                            <select class="form-control" name="service_id" required id="service_id">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}">@lang($service->name)</option>
                              @endforeach
                                </option>
                            </select> 
                            {{-- <input class="form-control" name="service_id" type="text" value="{{ old('service_id') }}"
                                required /> --}}
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Vehicle Model') </label>
                            <input class="form-control" name="model" type="text" value="{{ old('model') }}"
                                required />
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Vehicle Number')</label>
                            <input class="form-control" name="number" type="text" value="{{ old('number') }}"
                                required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Vehicle Color')</label>
                            <div class=" input--group input-group">
                                <input class="form-control" name="color" type="text"
                                    value="{{ old('color') }}" required step="any">
                                {{-- <span class="input-group-text">{{ __(gs('cur_text')) }}</span> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Insurance')(PDF)</label>
                            <input class="form-control"  type="file" name="insurance" id="insurance">
                            {{-- <select class="form-control" name="discount_type" required>
                                <option value="{{ Status::DISCOUNT_PERCENT }}">@lang('%')</option>
                                <option value="{{ Status::DISCOUNT_FIXED }}" @selected(old('discount_type') == Status::DISCOUNT_FIXED)>
                                    {{ __(gs('cur_text')) }}
                                </option>
                            </select> --}}
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('RC')(PDF)</label>
                            <div class="input-group input--group">
                            <input class="form-control"  type="file" name="rc" id="rc">
                                {{-- <input class="form-control" name="amount" type="number" value="{{ old('amount') }}"
                                    step="any" required> --}}
                                {{-- <span class="input-group-text">@lang('%')</span> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Image')(jpg, png, jpeg)</label>
                            <div class="input-group input--group">
                            <input class="form-control"  type="file" name="image" id="image">

                                {{-- <input class="form-control" name="maximum_using_time" type="number"
                                    value="{{ old('maximum_using_time') }}" step="any" required> --}}
                                {{-- <span class="input-group-text">@lang('times')</span> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Pollution Certificate') (PDF)</label>
                            <div class="input-group input--group">
                            <input class="form-control"  type="file" name="polution_certificate" id="polution_certificate">

                                {{-- <input class="form-control" name="maximum_using_time" type="number"
                                    value="{{ old('maximum_using_time') }}" step="any" required> --}}
                                {{-- <span class="input-group-text">@lang('times')</span> --}}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('Pollution Certificate')</label>
                            <input class="form-control date-picker" name="start_from" type="text"
                                value="{{ old('start_from') }}" autocomplete="off" >
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>@lang('End At')</label>
                            <input class="form-control date-picker" name="end_at" type="text"
                                value="{{ old('end_at') }}" autocomplete="off" >
                        </div>
                    </div> --}}
                    {{-- <div class="col-12">
                        <div class="form-group">
                            <label for="description">@lang('Description')</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
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
        (function($) {
            "use strict";
            const $modal = $("#modal");

            $(".edit-btn").on('click', function(e) {

                const data = $(this).data('resource');
                const action = "{{ route('admin.vehicle.store', ':id') }}";
                console.log(data)
                $("input[name='model']").val(data.model);
                $("input[name='number']").val(data.number);
                $("input[name='color']").val((data.color));
                document.getElementById('service_id').value = data.service_id
                // $("input[name='service_id']").val((data.service_id));
                // $("select[name='insurance']").val(data.insurance);
                // $("input[name='image']").val(getAmount(data.image));
                // $("input[name='rc']").val(data.rc);
                // $("input[name='polution_certificate']").val(data.polution_certificate);
                // $("input[name='maximum_using_time']").val(data.maximum_using_time);
                // $("textarea[name='description']").val(data.description);

                $modal.find(".modal-title").text("@lang('Edit Vehicle')");
                $modal.find('form').attr('action', action.replace(':id', data.id));
                $modal.modal("show");
            });


            $(".add-btn").on('click', function(e) {
                const action = "{{ route('admin.vehicle.store') }}";
                $modal.find(".modal-title").text("@lang('Add Vehicle')");
                $modal.find('form').trigger('reset');
                $("select[name='discount_type']");
                $modal.find('form').attr('action', action);
                $modal.modal("show");
            });

            $("select[name='discount_type']").on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue == "{{ Status::DISCOUNT_FIXED }}") {
                    $("input[name='amount']").attr('placeholder', "@lang('Enter fixed amount')");
                    $("input[name='amount']").siblings('.input-group-text').text(
                        "{{ gs('cur_text') }}");
                } else {
                    $("input[name='amount']").attr('placeholder', "@lang('Enter percentage')");
                    $("input[name='amount']").siblings('.input-group-text').text('%');
                }
            }).change();

            $(".date-picker").flatpickr({
                minDate: new Date(),
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


@push('script-lib')
    <script src="{{ asset('assets/global/js/flatpickr.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/global/css/flatpickr.min.css') }}">
@endpush
