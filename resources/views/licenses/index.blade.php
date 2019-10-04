@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/general.software_licenses') }}
@parent
@stop


@section('header_right')
@can('create', \App\Models\License::class)
    <a href="{{ route('licenses.create') }}" class="btn btn-primary pull-right">
      {{ trans('general.create') }}
    </a>
    @endcan
@stop

{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-body">
        {{ Form::open([
          'method' => 'POST',
          'route' => ['licenses/bulkedit'],
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
                  data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
                  data-cookie-id-table="licensesTable"
                  data-pagination="true"
                  data-search="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-export="true"
                  data-show-footer="true"
                  data-show-refresh="true"
                  data-sort-order="asc"
                  data-sort-name="name"
                  data-toolbar="#toolbar"
                  id="licensesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.licenses.index') }}"
                  data-export-options='{
                "fileName": "export-licenses-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              </table>

            </div><!-- /.col -->
          </div><!-- /.row -->
        {{ Form::close() }}
      </div><!-- /.box-body -->

      <div class="box-footer clearfix">
      </div>
    </div><!-- /.box -->
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

@stop
