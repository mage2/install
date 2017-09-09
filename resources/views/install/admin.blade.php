@extends('mage2-install::layouts.install')

@section('content')


    <div class="col-md-6">
        <div class="card card-default">
            <div class="card-header"><h1>Welcome to Mage2 Ecommerce Installation</h1></div>
            <div class="card-body">


                <h4 class="text-center">Create Admin Account</h4>

                {!! Form::open(['method'=> 'post','id' => 'create-admin-user-form','action' => route('mage2.install.admin.post')]) !!}
                {!! Form::text('first_name','First Name') !!}
                {!! Form::text('last_name','Last Name') !!}
                {!! Form::text('email','Email') !!}

                {!! Form::password('password','Password') !!}
                {!! Form::password('password_confirmation','Confirm Password') !!}

                {!! Form::select('language','Language',['en' => 'English']) !!}

                {!! Form::button('Continue',['class' => 'btn btn-primary create-user-button']) !!}

                {!! Form::close() !!}

            </div>
        </div>
    </div>

    @push('styles')
    <script>
        jQuery(document).ready(function () {
            jQuery('.create-user-button').click(function (e) {
                e.preventDefault();
                jQuery(e.target).button('loading');
                jQuery('#create-admin-user-form').submit();
            });

        });
    </script>
    @endpush
@endsection