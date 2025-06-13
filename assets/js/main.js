'use strict';
(function ($) {

    // ============
    //      Start Document Ready function
    // ============
    $(document).ready(function () {

        // ============ Header Hide Click On Body Js ============
        $('.header-button').on('click', function () {
            $('.body-overlay').toggleClass('show');
        });
        $('.body-overlay').on('click', function () {
            $('.header-button').trigger('click');
            $(this).removeClass('show');
        });

        // ============ Header Sticky Js ============
        $(window).on('scroll', function () {
            if ($(window).scrollTop() >= 300) {
                $('.header').addClass('fixed-header');
            } else {
                $('.header').removeClass('fixed-header');
            }
        });

        // ============ Scroll To Top Icon Js ============
        var btn = $('.scroll-top');

        $(window).scroll(function () {
            if ($(window).scrollTop() > 300) {
                btn.addClass('show');
            } else {
                btn.removeClass('show');
            }
        });

        btn.on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, '300');
        });

        // ============ Header Hide Scroll Bar Js ============
        $('.navbar-toggler.header-button').on('click', function () {
            $('body').toggleClass('scroll-hide-sm');
        });
        $('.body-overlay').on('click', function () {
            $('body').removeClass('scroll-hide-sm');
        });


        // ============ Add Attribute For Bg Image Js Start ============
        $('.bg-img').css('background', function () {
            var bg = 'url(' + $(this).data('background-image') + ')';
            return bg;
        });


        // ============ Slick Slider Js ============
        // Technology Slider One
        $('.technology-item-list-slider-01').slick({
            speed: 4500,
            autoplay: true,
            autoplaySpeed: 0,
            centerMode: false,
            cssEase: 'linear',
            slidesToShow: 5,
            slidesToScroll: 1,
            variableWidth: true,
            infinite: true,
            initialSlide: 1,
            arrows: false,
            buttons: false,
            responsive: [
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 3
                    }
                }
            ]
        });

        // Payment Methods Slider One
        $('.payment-methods-slider-1').slick({
            speed: 12000,
            autoplay: true,
            autoplaySpeed: 0,
            centerMode: true,
            cssEase: 'linear',
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true,
            infinite: true,
            initialSlide: 1,
            arrows: false,
            buttons: false,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 5
                    }
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 3
                    }
                }
            ]
        });

        // Payment Methods Slider Two 
        $('.payment-methods-slider-2').slick({
            speed: 12000,
            autoplay: true,
            autoplaySpeed: 0,
            centerMode: true,
            cssEase: 'linear',
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true,
            infinite: true,
            initialSlide: 1,
            arrows: false,
            buttons: false,
            rtl: true,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 3
                    }
                }
            ]
        });


        // Lightcase Video Popup 
    if ($('.popup_video').length > 0) {
        $('.popup_video').lightcase({
            transition: 'elastic',
            showSequenceInfo: false,
            slideshow: false,
            swipe: true,
            showTitle: false,
            showCaption: false,
            controls: true
        });
    }
    });
    // ============
    //      End Document Ready function
    // ============

    // ============ Wow Js ============
    const wow = new WOW(
        {
            boxClass: 'wow',      // default
            animateClass: 'animated', // default
            offset: 0,          // default
            mobile: false,       // default
            live: true        // default
        }
    )
    wow.init();

    // ========================= Swiper Js =====================
    var swiper = new Swiper(".swiper", {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 4,
        spaceBetween: -60,
        coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 85,
            modifier: 4,
            slideShadows: true
        },
        keyboard: {
            enabled: true
        },
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        breakpoints: {
            1400: {
                slidesPerView: 4,
                spaceBetween: -60,
            },
            768: {
                coverflowEffect: {

                    depth: 80,

                },
                spaceBetween: -40,
                modifier: 3,
            },
            575: {
                overflowEffect: {

                    depth: 10,
                    modifier: 3,

                },
                slidesPerView: 4,
                depth: -50,
            },
            320: {
                coverflowEffect: {

                    depth: 100,
                    modifier: 2,

                },

                slidesPerView: 4,
            },

        }

    });


    // 20. Register GSAP
    gsap.registerPlugin(ScrollTrigger);

    if (window.matchMedia("(min-width: 1200px)").matches) {
        // Banner Title Zoom
        if ($('.banner-content__title').length) {
            gsap.to(".banner-content__title", {
                scale: .6,
                scrollTrigger: {
                    trigger: ".banner-thumb",
                    start: "top bottom",
                    end: "bottom center",
                    scrub: true,
                }
            });
        }

        // Banner Thumb Zoom
        if ($('.banner-thumb').length) {
            gsap.to(".banner-thumb", {
                scale: 1.1,
                rotateX: 0,
                scrollTrigger: {
                    trigger: ".banner-thumb",
                    start: "top bottom",
                    end: "bottom center",
                    scrub: true,
                }
            });
        }
    }
    // ============ Preloader Js ============
    $(window).on('load', function () {
        setTimeout(() => {
            $('.preloader').fadeOut();
        }, 500);
    });

})(jQuery);
