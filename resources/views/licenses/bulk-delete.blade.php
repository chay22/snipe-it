@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/form.bulk_delete') }}
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
    <p>{{ trans('admin/licenses/form.bulk_delete_help') }}</p>
    <form class="form-horizontal" method="post" action="{{ route('licenses/bulkdelete') }}" autocomplete="off" role="form">
      {{csrf_field()}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title" style="color: red">{{ trans('admin/licenses/form.bulk_delete_warn', ['license_count' => count($licenses)]) }}</h3>
        </div>

        <div class="box-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td></td>
                <td>{{ trans('general.id') }}</td>
                <td>{{ trans('admin/licenses/table.title') }}</td>
                <td>{{ trans('admin/accessories/general.total') }}</td>
                <td>{{ trans('admin/accessories/general.remaining') }}</td>
                <td>{{ trans('admin/licenses/form.to_email') }}</td>
                <td>{{ trans('admin/licenses/form.to_name') }}</td>
              </tr>
            </thead>
            <tbody>
              @foreach ($licenses as $license)
              <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $license->id }}" checked="checked"></td>
                <td>{{ $license->id }}</td>
                <td>{{ $license->present()->name() }}</td>
                <td>{{ $license->seats }}</td>
                <td>{{ count($license->freeSeats) }}</td>
                <td>{{ $license->license_email }}</td>
                <td>{{ $license->license_name }}</td>
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
