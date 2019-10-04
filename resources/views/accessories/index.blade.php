@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.accessories') }}
@parent
@stop

@section('header_right')
    @can('create', \App\Models\Accessory::class)
        <a href="{{ route('accessories.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">

    <div class="box box-default">
      <div class="box-body">
        {{ Form::open([
          'method' => 'POST',
          'route' => ['accessories/bulkedit'],
          'class' => 'form-inline',
           'id' => 'bulkForm']) }}
          <div class="row">
            <div class="col-md-12">
              @if (request()->get('status')!='Deleted')
              <div id="toolbar">
                <select name="bulk_actions" class="form-control select2">
                  <option value="edit">{{ trans('button.edit') }}</option>
                  <option value="delete">{{ trans('button.delete') }}</option>
                </select>
                <button class="btn btn-primary" id="bulkEdit" disabled>Go</button>
              </div>
              @endif

              <table
                  data-click-to-select="true"
                  data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                  data-cookie-id-table="accessoriesTable"
                  data-pagination="true"
                  data-id-table="accessoriesTable"
                  data-search="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-export="true"
                  data-show-refresh="true"
                  data-show-footer="true"
                  data-sort-order="asc"
                  data-toolbar="#toolbar"
                  id="accessoriesTable"
                  class="table table-striped snipe-table"
                  data-url="{{route('api.accessories.index', ['status' => request('status')]) }}"
                  data-export-options='{
                      "fileName": "export-accessories-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'>
            </table>

            </div><!-- /.col -->
          </div><!-- /.row -->
        {{ Form::close() }}

      </div>

      <div class="box-footer clearfix">
      </div>

    </div>
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
