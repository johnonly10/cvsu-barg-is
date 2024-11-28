$('.owl-carousel').owlCarousel({
    loop: true, 
    margin: 10,
    nav: true,
    navText: [
        '<div class="nav-button nav-prev">Prev</div>',
        '<div class="nav-button nav-next">next</div>'
    ],
    responsiveClass: true,
    responsive: {
        0: {
            items: 1,
        },
        768: {
            items: 2,
        },
        1100: {
            items: 3,
        },
        1400: {
            items: 3,
            loop: false,
        }
    },
    autoplay: true, 
    autoplayTimeout: 8000, 
    autoplayHoverPause: true, 
    smartSpeed: 800, 
    fluidSpeed: true, 
});
