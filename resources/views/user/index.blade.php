@extends('layouts.app')

@section('content')

@if (request()->routeIs('user.index'))
<x-header
    backgroundImage="{{ asset('images/cvsu-banner.jpg') }}"
    title="User Dashboard"
    :breadcrumbs="[
        ['url' => route('home.index'), 'label' => 'Home'],
        'User Dashboard'
    ]"/>
@else
@endif

    <main class="container" style="padding-top: 1em;"> 
    <div class="mb-4 pb-4"></div>
            <section class="my-account container">
            <h2 class="page-title">My Account</h2>
            <div class="row">
                <div class="col-lg-3">
                    @include('user.account__nav')
                </div>
                <div class="col-lg-9">
                <div class="page-content my-account__dashboard">
                    <p>Hello <strong>{{ Auth::user()->name }}</strong></p>
                    <p>From your account dashboard you can view your <a class="underline-link" href="{{route ('user.orders')}}">recent
                        orders</a>, and <a class="underline-link" href="{{route ('user.profile')}}">edit your password and account
                        details.</a></p>
                </div>
                </div>
            </div>
            </section>
    </main>


@endsection

