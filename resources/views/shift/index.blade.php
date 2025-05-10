@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Shift') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Shift') }}</li>
@endsection

@section('action-button')
    @can('Create Designation')
        <a href="javascript:void(0)" data-url="{{ route('shift.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Shift') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection



@section('content')
<div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Shift') }}</th>
                                <th>{{ __('Start Time') }}</th>
                                <th>{{ __('End Time') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->name }}</td>
                                    <td>{{ $shift->company_start_time}}</td>
                                    <td>{{ $shift->company_end_time}}</td>
                                    <td class="Action">
                                        <span>

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="javascript:void(0)" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{  route('shift.edit', $shift->id) }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Shift') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>



                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['shift.destroy', $shift->id], 'id' => 'delete-form-' . $shift->id]) !!}
                                                    <a href="javascript:void(0)" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>

                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection
