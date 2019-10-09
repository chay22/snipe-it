@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/categories/general.asset_categories') }}
@parent
@stop


@section('header_right')
<a href="{{ route('categories.create') }}" class="btn btn-primary pull-right">
  {{ trans('general.create') }}</a>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <div class="table-responsive">
        {{ Form::open([
          'method' => 'POST',
          'route' => ['categories/bulkedit'],
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
                data-columns="{{ \App\Presenters\CategoryPresenter::dataTableLayout() }}"
                data-cookie-id-table="categoryTable"
                data-pagination="true"
                data-id-table="categoryTable"
                data-search="true"
                data-show-footer="true"
                data-side-pagination="server"
                data-show-columns="true"
                data-show-export="true"
                data-show-refresh="true"
                data-sort-order="asc"
                id="categoryTable"
                data-toolbar="#toolbar"
                class="table table-striped snipe-table"
                data-url="{{ route('api.categories.index') }}"
                data-export-options='{
                  "fileName": "export-categories-{{ date('Y-m-d') }}",
                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                  }'>
              </table>


              </div><!-- /.col -->
            </div><!-- /.row -->
          {{ Form::close() }}

        </div>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table',
      ['exportFile' => 'category-export',
      'search' => true,
      'columns' => \App\Presenters\CategoryPresenter::dataTableLayout()
  ])

  <script>
    $(function () {
        function toggleBulkDeleteAbility() {
            if ($('#toolbar [name="bulk_actions"]').val() == 'delete') {
              var undeleteable_selected = $('#categoryTable > tbody > tr.selected').filter(function () {
                return $(this).find('.delete-asset.disabled').length > 0
              });

              $('#bulkEdit').prop('disabled', undeleteable_selected.length > 0)
            }
        }

        $('#categoryTable').on('change', '[name="btSelectItem"]', toggleBulkDeleteAbility);
        $('#toolbar').on('change', '[name="bulk_actions"]', toggleBulkDeleteAbility);
    });
  </script>
@stop

