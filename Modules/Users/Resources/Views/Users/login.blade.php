@extends('layouts.simple')

@section('content')
  {{ Form::open(array('route' => 'login', 'class' => 'form-horizontal col-md-6')) }}
  <div class="ui middle aligned center aligned grid" style="padding-top: 5%;">
    <div class="three column row">
      <div class="column">
        <h2 class="ui teal image header">
          <div class="content">
            Log-in to your account
          </div>
        </h2>
        <form class="ui equal width large form">

          <div class="ui stacked segment">
            <div class="field">
              <div class="ui left icon input full width">
                <i class="user icon"></i>

                {{ Form::text('username', Request::get('username'), [ 'placeholder' => 'Username' ]) }}
              </div>
            </div>
            <div class="field">
              <div class="ui left icon input full width">
                <i class="lock icon"></i>

                {{ Form::password('password', [ 'placeholder' => 'Password' ]) }}
              </div>
            </div>

            {{ Form::submit('Login', array('class' => 'ui fluid large blue submit button')) }}
          </div>

        </form>

        <div class="ui message">
          New to us? <a href="{{ route('register') }}">Sign Up</a><br/>
          <a href="{{ route('forgot_password') }}">Forgot your password?</a>
        </div>
      </div>
    </div>
  </div>
  {{ Form::close() }}
@stop