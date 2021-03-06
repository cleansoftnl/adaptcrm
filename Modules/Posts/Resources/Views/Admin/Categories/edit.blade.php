@extends('layouts.admin')

@section('content')
  {{ Form::model($model, [ 'class' => 'ui form' ]) }}

  <h1>Edit Category</h1>

  <div class="alert alert-danger">
    <ul>
      @foreach ($errors as $fields)
        @foreach($fields as $error)
          <li>{{ $error }}</li>
        @endforeach
      @endforeach
    </ul>
  </div>

  <div class="required field">
    {{ Form::label('name') }}
    {{ Form::text('name') }}
  </div>

  <div class="field">
    {{ Form::label('meta_keywords') }}

    <div class="ui fluid multiple search selection dropdown allowAdditions">
      {{ Form::hidden('meta_keywords') }}
      <i class="dropdown icon"></i>
      <div class="default text">Keywords</div>
      <div class="menu"></div>
    </div>
  </div>

  <div class="field">
    {{ Form::label('meta_description') }}
    {{ Form::textarea('meta_description') }}
  </div>

  {{ Form::submit('Save', [ 'class' => 'ui button primary' ]) }}

  {{ Form::close() }}
@stop
