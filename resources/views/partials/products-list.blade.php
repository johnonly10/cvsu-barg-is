<div class="row g-5">
    @foreach ($products as $product)
        @if (!$product->archived)
        <div class="col-md-4 mb-4 d-flex justify-content-center">
            
            <div class="product-item d-flex flex-column">
                <div class="product-img position-relative flex-grow-1">
                 
                    <div class="swiper-container js-swiper-slider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                            <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}" class="text-decoration-none text-dark" style="cursor: pointer;">
                                <img loading="lazy"
                                    src="{{ asset('uploads/products') }}/{{ $product->image }}"
                                    alt="{{ $product->name }}" class="img-fluid d-block mx-auto"
                                    style="object-fit: cover; aspect-ratio: 1/1;">
                            </a>
                            </div>
                            <div class="swiper-slide">
                                @foreach (explode(',', $product->images) as $gimg)
                                    <img loading="lazy"
                                        src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                        alt="{{ $product->name }}" class="img-fluid d-block mx-auto"
                                        style="object-fit: cover; aspect-ratio: 1/1;">
                                @endforeach
                            </div>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                
                </div>
                

                <div class="product-info px-3 py-3" onclick="window.location.href='{{ route('shop.product.details', ['product_slug' => $product->slug]) }}'"> 
                    <h1 class="title-cards fw-normal text-capitalize mb-2 display-2 ">{{ $product->name }}</h1>
                    <p class="span-description fw-light mb-2">{{ $product->short_description }}</p>
                    <div class="avail d-flex justify-content-center">                                                 
                        <span class="availability fw-bold me-2">Available items: </span>                                                  
                        <span class="avail-tags" style="color:                                                     
                            @php
                                $totalQuantity = $product->attributeValues->isNotEmpty() 
                                    ? $product->attributeValues->sum('quantity') 
                                    : $product->quantity;
                                
                                echo $totalQuantity > 0 ? ($totalQuantity == 1 ? 'orange' : 'green') : 'red';
                            @endphp
                        ;">
                            {{ $totalQuantity > 0 ? $totalQuantity : 0 }}
                        </span>
                                                                  
                    </div>
                                            
                    <h2 class="product-type text-primary pt-2">
                        &#8369; 
                        @if ($product->attributeValues->isNotEmpty() && isset($product->attributeValues[0]->price))
                            {{ number_format($product->attributeValues[0]->price, 2) }} 
                        @else
                            {{ number_format($product->price, 2) }}
                        @endif  
                    </h2>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>