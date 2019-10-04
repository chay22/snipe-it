@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/form.update') }}
@parent
@stop


@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-sm btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">

    <p>{{ trans('admin/licenses/form.bulk_update_help') }}</p>

    <div class="callout callout-warning">
      <i class="fa fa-warning"></i> {{ trans('admin/licenses/form.bulk_update_warn', ['license_count' => count($licenses)]) }}
    </div>

    <form class="form-horizontal" method="post" action="{{ route('licenses/bulksave') }}" autocomplete="off" role="form">
      {{ csrf_field() }}

      <div class="box box-default">
        <div class="box-body">

          @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
          @include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id', 'required' => 'true'])

          <!-- Licensed to name -->
          <div class="form-group {{ $errors->has('license_name') ? ' has-error' : '' }}">
              <label for="license_name" class="col-md-3 control-label">{{ trans('admin/licenses/form.to_name') }}</label>
              <div class="col-md-7">
                  <input class="form-control" type="text" name="license_name" id="license_name" value="{{ old('license_name', $item->license_name) }}" />
                  {!! $errors->first('license_name', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>
          </div>

          <!-- Licensed to email -->
          <div class="form-group {{ $errors->has('license_email') ? ' has-error' : '' }}">
              <label for="license_email" class="col-md-3 control-label">{{ trans('admin/licenses/form.to_email') }}</label>
              <div class="col-md-7">
                  <input class="form-control" type="text" name="license_email" id="license_email" value="{{ old('license_email', $item->license_email) }}" />
                  {!! $errors->first('license_email', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>
          </div>

          @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])
          @include ('partials.forms.edit.purchase_cost')
          @include ('partials.forms.edit.purchase_date')

          <!-- Expiration Date -->
          <div class="form-group {{ $errors->has('expiration_date') ? ' has-error' : '' }}">
              <label for="expiration_date" class="col-md-3 control-label">{{ trans('admin/licenses/form.expiration') }}</label>

              <div class="input-group col-md-3">
                  <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"  data-autoclose="true">
                      <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="expiration_date" id="expiration_date" value="{{ old('expiration_date', $item->expiration_date) }}">
                      <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                  </div>
                  {!! $errors->first('expiration_date', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>

          </div>

          <!-- Termination Date -->
          <div class="form-group {{ $errors->has('termination_date') ? ' has-error' : '' }}">
              <label for="termination_date" class="col-md-3 control-label">{{ trans('admin/licenses/form.termination_date') }}</label>

              <div class="input-group col-md-3">
                  <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"  data-autoclose="true">
                      <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="termination_date" id="termination_date" value="{{ old('termination_date', $item->termination_date) }}">
                      <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                  </div>
                  {!! $errors->first('termination_date', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>
          </div>

          {{-- @TODO How does this differ from Order #? --}}
          <!-- Purchase Order -->
          <div class="form-group {{ $errors->has('purchase_order') ? ' has-error' : '' }}">
              <label for="purchase_order" class="col-md-3 control-label">{{ trans('admin/licenses/form.purchase_order') }}</label>
              <div class="col-md-3">
                  <input class="form-control" type="text" name="purchase_order" id="purchase_order" value="{{ old('purchase_order', $item->purchase_order) }}" />
                  {!! $errors->first('purchase_order', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>
          </div>

          @foreach ($licenses as $key => $value)
            <input type="hidden" name="ids[{{ $key }}]" value="1">
          @endforeach
        </div> <!--/.box-body-->

        <div class="box-footer text-right">
          <button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> {{ trans('general.save') }}</button>
        </div>
      </div> <!--/.box.box-default-->
    </form>
  </div> <!--/.col-md-8-->
</div>
@stop
