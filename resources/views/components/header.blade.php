<header id="header-pages">
    <div class="hero page-inner hero-overlay">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-9 text-center mt-5">
                    <h1 class="heading" data-aos="fade-up">{{ $title }}</h1>
                    <nav aria-label="breadcrumb" data-aos="fade-up" data-aos-delay="200">
                        <ol class="breadcrumb text-center justify-content-center">
                            @foreach ($breadcrumbs as $breadcrumb)
                                @if (is_array($breadcrumb) && isset($breadcrumb['url']))
                                 
                                    <li class="breadcrumb-item text-white-50">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                                    </li>
                                @else
                                    
                                    <li class="breadcrumb-item active text-white-50" aria-current="page" style="opacity: 0.9;">
                                        {{ is_array($breadcrumb) ? $breadcrumb['label'] : $breadcrumb }}
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
