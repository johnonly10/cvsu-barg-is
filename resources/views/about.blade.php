@extends('layouts.app')
@section('content')

@php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        // Initialize breadcrumbs array with the Home link
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

        // Handle Shop pages
        if ($currentRoute === 'shop.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
        } elseif ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
            $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
        } elseif ($currentRoute === 'about.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
        } elseif ($currentRoute === 'cart.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Cart Page'];

            // Handle Contact page
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];

            // Add more pages as needed
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp


    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />
    
<main class="container my-5">

    <div class="row">
        <section class="about-us">
        <div class="title-h2 text-center">
            <h2 class="position-relative d-inline-block" id="about">University Mission and Vission</h2>
        </div>
        <div class="we-help-section">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-6 mb-6 mb-lg-0">
                        <div class="imgs-grid">
                            <div class="grid grid-1"><img src="{{ asset('./images/cvsu-banner.jpg') }}"></div>
                            <div class="grid grid-2"><img src="{{ asset('./images/cvsu-banner.jpg') }}"></div>
                            <div class="grid grid-3"><img src="{{ asset('./images/cvsu-banner.jpg') }}"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 ps-lg-6">
                        <h3>Vision</h3>
                        <hr>
                        <p>The Premier University in historic Cavite globally recognized for excellence in character development, academics, research, innovation and sustainable community engagement.</p>
                        <h3>Mission</h3>
                        <hr>
                        <p>Cavite State University shall provide excellent, equitable, and relevant educational opportunities in the arts, sciences and technology through quality instruction and responsive research and development activities. <br><br>
                        It shall produce professional, skilled and morally upright individuals for global competitiveness.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Footer Section -->
        </section>
    </div>

    <div class="row">
        <section class="about-us">
        <div class="title-h2 text-center">
            <h2 class="position-relative d-inline-block" id="about">BaRG Mission and Vission</h2>
        </div>
        <div class="we-help-section">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-6 ps-lg-6">
                        <h3>BaRG Vision</h3>
                        <hr>
                        <p>The office of Production and Resource Generation of Cavite State University as an aggressive arm of the University in resource generation and an entrepreneurship model of SUC's in this country.</p>     
                        <h3>BaRG Mission</h3>
                        <hr>
                        <p>To vigorously pursue a sustainable resource generation program using the University's resources while developing the capabilities of its staff, faculty, students and other partners.</p>  
                    </div>
                    <div class="col-lg-6 mb-6 mb-lg-0">
                        <div class="imgs-grid">
                            <div class="grid grid-1"><img src="{{ asset('./images/OVPEBA.jpg') }}"></div>
                            <div class="grid grid-2"><img src="{{ asset('./images/ovp.jpg') }}"></div>
                            <div class="grid grid-3"><img src="{{ asset('./images/ovp.jpg') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Footer Section -->
        </section>
    </div>

    <div class="row">
        <section class="about-us">
        <div class="title-h2 text-center">
            <h2 class="position-relative d-inline-block" id="about">Quality Policy and Core Values</h2>
        </div>
        <div class="we-help-section">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-6 ps-lg-6">
                        <h3>Quality Policy</h3>
                        <hr>
                        <p>We commit to the highest standards of education. Value our stakeholder. Strive for continual improvement of our products and services, and Uphold the University's tenets of Truth, Excellence, and Service to produce globally, competitive and morally upright individuals.</p>
                    </div>
                    <div class="col-lg-6 ps-lg-6">
                        <h3>Core Values</h3>
                        <hr>
                       <ul class="custom-list d-flex flex-column">
                            <li><h3>Truth<h3></li>
                            <li><h3>Excellence<h3></li>
                            <li><h3>Service<h3></li>
                       </ul>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- End of Footer Section -->
        </section>
    </div>
        
</main>


@endsection

