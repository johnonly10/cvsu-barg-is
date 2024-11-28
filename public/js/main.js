
  window.onscroll = function() {scrollFunction()};
  
    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("goTop").style.display = "block";
      } else {
        document.getElementById("goTop").style.display = "none";
      }
    }
  
    // Scroll to the top of the document when the button is clicked
    document.getElementById("goTopbtn").onclick = function(e) {
      e.preventDefault(); 
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
    
    // Initialize tooltips
  document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  });
  
  
  function changeImage(image) {
    var mainImage = document.getElementById('mainImage');
    mainImage.src = image.src;
  }
  

  function updateSubtotal(index) {
      const price = parseFloat(document.querySelectorAll('.shopping-cart__product-price')[index].innerText.replace(
          '₱', ''));
      const qty = qtyInputs[index].value;
      const subtotal = price * qty;
      document.querySelectorAll('.shopping-cart__subtotal')[index].innerText = '₱' + subtotal.toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.category-link');
    const mainContent = document.getElementById('mainContent');

    links.forEach(link => {
      link.addEventListener('click', function(event) {
        event.preventDefault();
        mainContent.innerHTML = this.getAttribute('data-content');
      });
    });
  });

  
  document.addEventListener("DOMContentLoaded", function() {
    var navbar = document.querySelector(".navbar");
    var lastScrollTop = 0;
    var scrollThreshold = 5; // Adjust this value to control sensitivity
    var navbarHeight = navbar.offsetHeight;

    window.addEventListener("scroll", function() {
        var currentScroll = window.pageYOffset || document.documentElement.scrollTop;

        // Check if the scroll difference exceeds the threshold
        if (Math.abs(lastScrollTop - currentScroll) > scrollThreshold) {
            if (currentScroll > lastScrollTop && currentScroll > navbarHeight) {
                // Scrolling down
                navbar.style.top = `-${navbarHeight}px`;
            } else {
                // Scrolling up
                navbar.style.top = "0";
            }
            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; 
        }
    });
});



document.addEventListener('DOMContentLoaded', function() {
  const searchToggle = document.getElementById('searchToggle');
  const searchPopup = document.querySelector('.search-popup');
  const searchInput = document.querySelector('.search-field__input');
  const searchSubmit = document.querySelector('.search-popup__submit');
  const searchIcon = document.getElementById('searchIcon');
  const closeIcon = document.getElementById('closeIcon');

  searchToggle.addEventListener('click', function(e) {
    e.preventDefault();
    searchPopup.classList.toggle('js-hidden-content');
    searchIcon.classList.toggle('d-none');
    closeIcon.classList.toggle('d-none');
    if (!searchPopup.classList.contains('js-hidden-content')) {
      searchInput.focus();
    }
  });

  searchSubmit.addEventListener('click', function(e) {
    e.preventDefault();
    console.log('Searching for:', searchInput.value);
  });

  // Close search popup when clicking outside
  document.addEventListener('click', function(e) {
    if (!searchPopup.contains(e.target) && !searchToggle.contains(e.target)) {
      searchPopup.classList.add('js-hidden-content');
      searchIcon.classList.remove('d-none');
      closeIcon.classList.add('d-none');
    }
  });
});
