@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/accessories/form.update') }}
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

    <p>{{ trans('admin/accessories/form.bulk_update_help') }}</p>

    <div class="callout callout-warning">
      <i class="fa fa-warning"></i> {{ trans('admin/accessories/form.bulk_update_warn', ['accessory_count' => count($accessories)]) }}
    </div>

    <form class="form-horizontal" method="post" action="{{ route('accessories/bulksave') }}" autocomplete="off" role="form">
      {{ csrf_field() }}

      <div class="box box-default">
        <div class="box-body">
          @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
          @include ('partials.forms.edit.category-select', ['translated_name' => trans('general.category'), 'fieldname' => 'category_id', 'required' => 'true','category_type' => 'accessory'])
          @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])
          @include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id', 'required' => 'true'])
          @include ('partials.forms.edit.model_number')
          @include ('partials.forms.edit.order_number')
          @include ('partials.forms.edit.purchase_date')
          @include ('partials.forms.edit.purchase_cost')
          @include ('partials.forms.edit.quantity')
          @include ('partials.forms.edit.minimum_quantity')

          @foreach ($accessories as $key => $value)
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
