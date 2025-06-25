
document.addEventListener("DOMContentLoaded", function() {
    const toggles = document.querySelectorAll(".menu-toggle");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", function(e) {
            const parent = e.target.closest(".main-menu-item");
            const submenu = parent.querySelector(".submenu");

            // Optional: close others
            document.querySelectorAll(".submenu").forEach(sm => {
                if (sm !== submenu) sm.style.display = "none";
            });

            if (submenu) {
                submenu.style.display = submenu.style.display === "block" ? "none" : "block";
            }
        });
    });
});

jQuery(document).ready(function ($) {
  const cartElement = document.querySelector('.elementor-element-133802e');
  const myAccToggle = $('#my-acc-toggle');

  if (!cartElement) return;

  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === 'class') {
        if (cartElement.classList.contains('elementor-menu-cart--shown')) {
          myAccToggle.hide(); // or use .slideDown()
        } else {
          myAccToggle.show(); // or use .slideUp()
        }
      }
    });
  });

  observer.observe(cartElement, {
    attributes: true,
    attributeFilter: ['class']
  });
});




document.addEventListener("DOMContentLoaded", function () {
    const menu = document.querySelector("#menu-main-menu");
    const items = menu.querySelectorAll(".menu-item-has-children");

    items.forEach(item => {
        const next = item.nextElementSibling;
        if (next && next.classList.contains("submenu")) {
            // Create a wrapper div
            const wrapper = document.createElement("div");
            wrapper.classList.add("menu-group");

            // Move the item and submenu into the wrapper
            item.parentNode.insertBefore(wrapper, item);
            wrapper.appendChild(item);
            wrapper.appendChild(next);
        }
    });

    const groups = document.querySelectorAll(".menu-group");

    groups.forEach(group => {
        const h6 = group.querySelector("h6");

        group.addEventListener("mouseenter", function () {
            const submenu = group.querySelector(".submenu");
            if (submenu) submenu.style.display = "flex";
            if (h6) h6.classList.add("it-active");
        });

        group.addEventListener("mouseleave", function () {
            const submenu = group.querySelector(".submenu");
            if (submenu) submenu.style.display = "none";
            if (h6) h6.classList.remove("it-active");
        });
    });
});




jQuery(document).ready(function ($) {
    const $mobileWrapper = $('.mobile-menu-wrapper');
    const $menu = $('#menu-main-menu-1');

    // 1. Insert back button ONLY into subhead1 and subhead2
    $('.mobile-menu-wrapper .subhead1, .mobile-menu-wrapper .subhead2').each(function () {
        const $submenu = $(this);
        if (!$submenu.find('.back-button').length) {
            const $back = $(`
                <li class="back-button">
                    <a href="#"><svg style="transform: rotate(90deg); margin-right: 10px;" width="12" height="12" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/>
                    </svg> Back</a>
                </li>
            `);
            $submenu.prepend($back);
        }
    });

    // 2. Mobile menu open/close
    function openMenu() {
        $('.mobile-menu-wrapper .submenu').hide().removeClass('sub-active');
        $('.the-item1, .the-item2, .the-item3, .the-item4').removeClass('the-item-active');
        $mobileWrapper.css({ position: 'fixed', top: 0, right: '-100%', display: 'flex' }).animate({ right: '0' }, 200);
        $('body').addClass('body-fixed');
        $('.hamburger-icon').hide();
        $('.the-close').show();
    }

    function closeMenu() {
        $('.mobile-menu-wrapper .submenu').hide().removeClass('sub-active');
        $('.the-item1, .the-item2, .the-item3, .the-item4').removeClass('the-item-active');
        $mobileWrapper.animate({ right: '-100%' }, 200, function () {
            $mobileWrapper.hide();
        });
        $('body').removeClass('body-fixed');
        $('.hamburger-icon').show();
        $('.the-close').hide();
    }

    $('.hamburger-icon').on('click', openMenu);
    $('.the-close').on('click', closeMenu);

    // 3. Handle first-level items (slide in submenu)
    $('.the-item1, .the-item2').on('click', function (e) {
        e.preventDefault();
        const className = $(this).attr('class').split(' ').find(c => c.startsWith('the-item'));
        const submenuClass = className.replace('the-item', 'subhead');
        const $submenu = $('.' + submenuClass);

        $('.mobile-menu-wrapper .submenu').hide().removeClass('sub-active');
        $('.the-item1, .the-item2').removeClass('the-item-active');
        $(this).addClass('the-item-active');
        $('.the-close').hide();

        $submenu.show().addClass('sub-active');
    });

    // 4. Handle back button (in submenu)
    $menu.on('click', '.back-button a', function (e) {
        e.preventDefault();
        $('.mobile-menu-wrapper .submenu').hide().removeClass('sub-active');
//         $('.the-item1, .the-item2').removeClass('the-item-active');
         $('.the-item1, .the-item2, .the-item3, .the-item4').removeClass('the-item-active');
        $('.the-close').show();
    });

    // 5. Simple toggle for item3 and item4
    $('.the-item3, .the-item4').on('click', function (e) {
        e.preventDefault();
        const className = $(this).attr('class').split(' ').find(c => c.startsWith('the-item'));
        const submenuClass = className.replace('the-item', 'subhead');
        const $submenu = $('.' + submenuClass);
        const isOpen = $submenu.is(':visible');

        $('.mobile-menu-wrapper .submenu').hide().removeClass('sub-active');
        $('.the-item3, .the-item4').removeClass('the-item-active');

        if (!isOpen) {
            $submenu.slideDown().addClass('sub-active');
            $(this).addClass('the-item-active');
        }
    });
});
