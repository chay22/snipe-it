@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/consumables/form.bulk_delete') }}
@parent
@stop

@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <!-- left column -->
  <div class="col-md-12">
    <p>{{ trans('admin/consumables/form.bulk_delete_help') }}</p>
    <form class="form-horizontal" method="post" action="{{ route('consumables/bulkdelete') }}" autocomplete="off" role="form">
      {{csrf_field()}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title" style="color: red">{{ trans('admin/consumables/form.bulk_delete_warn', ['consumable_count' => count($consumables)]) }}</h3>
        </div>

        <div class="box-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td></td>
                <td>{{ trans('general.id') }}</td>
                <td>{{ trans('admin/consumables/table.title') }}</td>
                <td>{{ trans('admin/consumables/general.consumable_category') }}</td>
                <td>{{ trans('admin/consumables/general.total') }}</td>
                <td>{{ trans('general.min_amt') }}</td>
                <td>{{ trans('admin/consumables/general.remaining') }}</td>
              </tr>
            </thead>
            <tbody>
              @foreach ($consumables as $consumable)
              <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $consumable->id }}" checked="checked"></td>
                <td>{{ $consumable->id }}</td>
                <td>{{ $consumable->present()->name() }}</td>
                <td>{{ $consumable->category ? $consumable->category->name : '' }}</td>
                <td>{{ $consumable->qty }}</td>
                <td>{{ $consumable->min_amt }}</td>
                <td>{{ $consumable->numRemaining() }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->

        <div class="box-footer text-right">
          <a class="btn btn-link" href="{{ URL::previous() }}" method="post" enctype="multipart/form-data">{{ trans('button.cancel') }}</a>
          <button type="submit" class="btn btn-success" id="submit-button"><i class="fa fa-check icon-white"></i> {{ trans('general.delete') }}</button>
        </div><!-- /.box-footer -->
      </div><!-- /.box -->
    </form>
  </div> <!-- .col-md-12-->
</div><!--.row-->
@stop
