@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/categories/general.update') }}
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

    <p>{{ trans('admin/categories/form.bulk_update_help') }}</p>

    <div class="callout callout-warning">
      <i class="fa fa-warning"></i> {{ trans('admin/categories/form.bulk_update_warn', ['category_count' => count($categories)]) }}
    </div>

    <form class="form-horizontal" method="post" action="{{ route('categories/bulksave') }}" autocomplete="off" role="form">
      {{ csrf_field() }}

      <div class="box box-default">
        <div class="box-body">

          <!-- Type -->
          <div class="form-group {{ $errors->has('category_type') ? ' has-error' : '' }}">
              <label for="category_type" class="col-md-3 control-label">{{ trans('general.type') }}</label>
              <div class="col-md-7 required">
                  {{ Form::select('category_type', $category_types , Input::old('category_type', $item->category_type), array('class'=>'select2', 'style'=>'min-width:350px', $item->itemCount() > 0 ? 'disabled' : '')) }}
                  {!! $errors->first('category_type', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              </div>
          </div>

          <!-- EULA text -->
          <div class="form-group {{ $errors->has('eula_text') ? 'error' : '' }}">
              <label for="eula_text" class="col-md-3 control-label">{{ trans('admin/categories/general.eula_text') }}</label>
              <div class="col-md-7">
                  {{ Form::textarea('eula_text', Input::old('eula_text', $item->eula_text), array('class' => 'form-control')) }}
                  <p class="help-block">{!! trans('admin/categories/general.eula_text_help') !!} </p>
                  <p class="help-block">{!! trans('admin/settings/general.eula_markdown') !!} </p>

                  {!! $errors->first('eula_text', '<span class="alert-msg">:message</span>') !!}
              </div>
          </div>

          <!-- Use default checkbox -->
          <div class="form-group">
              <div class="col-md-3">
              </div>
              <div class="col-md-9">
                  @if ($snipeSettings->default_eula_text!='')
                      {!! trans('admin/categories/general.use_default_eula') !!}

                      <label class="radio-inline">
                          {{ Form::radio('use_default_eula', '1', old('use_default_eula'), ['class'=>'minimal', 'disabled' => 'disabled']) }}
                          Yes
                      </label>
                      <label class="radio-inline">
                          {{ Form::radio('use_default_eula', '0', old('use_default_eula'), ['class'=>'minimal', 'disabled' => 'disabled']) }}
                          No
                      </label>

                  @else
                      <div class="icheckbox disabled">
                          {!! trans('admin/categories/general.use_default_eula_disabled') !!}
                      </div>
                  @endif
              </div>
          </div>


          <!-- Require Acceptance -->
          <div class="form-group">
              <div class="col-md-3">
              </div>
              <div class="col-md-9">
                  {{ trans('admin/categories/general.require_acceptance') }}
                  <label class="radio-inline">
                    {{ Form::radio('require_acceptance', '1', old('require_acceptance'), ['class'=>'minimal']) }}
                    Yes
                  </label>
                  <label class="radio-inline">
                    {{ Form::radio('require_acceptance', '0', old('require_acceptance'), ['class'=>'minimal']) }}
                    No
                  </label>
              </div>
          </div>


          <!-- Email on Checkin -->
          <div class="form-group">
              <div class="col-md-3">
              </div>
              <div class="col-md-9">
                  {{ trans('admin/categories/general.checkin_email') }}
                <label class="radio-inline">
                  {{ Form::radio('checkin_email', '1', old('checkin_email'), ['class'=>'minimal']) }}
                  Yes
                </label>
                <label class="radio-inline">
                  {{ Form::radio('checkin_email', '0', old('checkin_email'), ['class'=>'minimal']) }}
                  No
                </label>
              </div>
          </div>

          @foreach ($categories as $key => $value)
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
