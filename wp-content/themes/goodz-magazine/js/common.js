(function($) { 'use strict';

    $(document).ready(function($){

    	// Calculate clients viewport

        function viewport() {
            var e = window, a = 'inner';
            if(!('innerWidth' in window )) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
        }

        var w=window,d=document,
        e=d.documentElement,
        g=d.getElementsByTagName('body')[0],
        x=w.innerWidth||e.clientWidth||g.clientWidth, // Viewport Width
        y=w.innerHeight||e.clientHeight||g.clientHeight; // Viewport Height

        // Global Vars

        var $window = $(window);
        var body = $('body');
        var wScrollTop = $window.scrollTop();
        var sidebar = $('#secondary');

		// Outline none on mousedown for focused elements

        body.on('mousedown', '*', function(e) {
            if(($(this).is(':focus') || $(this).is(e.target)) && $(this).css('outline-style') == 'none') {
                $(this).css('outline', 'none').on('blur', function() {
                    $(this).off('blur').css('outline', '');
                });
            }
        });

        // Main Header

        // sticky header

        var mainHeader = $('#masthead');

        if(body.hasClass('sticky-header') && !body.hasClass('featured-slider-fullwidth')){
            var mainContent = $('#content');
            var headerHeight;
            var logoImg = $('img.site-logo');
            if(logoImg.length){
                logoImg.on('load', function(){
                    headerHeight = mainHeader.outerHeight();
                });
            }
            headerHeight = mainHeader.outerHeight();
            mainContent.css({paddingTop: headerHeight + 40});

            var stickyHeader = function(){
                if(wScrollTop < 50){
                    mainHeader.addClass('transparent').removeClass('shrink');
                }
                else {
                    mainHeader.removeClass('transparent').addClass('shrink');
                }
            };
            stickyHeader();

            $(window).scroll(function(){
                wScrollTop = $(window).scrollTop();
                stickyHeader();
            });
        }

        // dropdown button

        var mainMenuDropdownLink = $('.nav-menu .menu-item-has-children > a, .nav-menu .page_item_has_children > a');
        var dropDownArrow = $('<button class="dropdown-toggle"><span class="screen-reader-text">toggle child menu</span>+</button>');

        mainMenuDropdownLink.after(dropDownArrow);

        // dropdown open on click

        var dropDownButton = mainMenuDropdownLink.next('button.dropdown-toggle');

        dropDownButton.on('click', function(e){
            e.preventDefault();
            var $this = $(this);
            $this.parent('li').toggleClass('toggle-on').siblings().removeClass('toggle-on').find('.toggle-on').removeClass('toggle-on');
        });

        // big search field

        var bigSearchWrap = $('div.search-wrap');
        var bigSearchField = bigSearchWrap.find('.search-field');
        var bigSearchTrigger = $('#big-search-trigger');
        var bigSearchClose = bigSearchWrap.add('#big-search-close');

        bigSearchClose.on('touchend click', function(){
            var $this = $(this);
            if(body.hasClass('big-search')){
                body.removeClass('big-search');
                setTimeout(function(){
                    $this.siblings('.search-wrap').find('.search-field').blur();
                }, 100);
            }
        });

        bigSearchTrigger.on('touchend click', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $this = $(this);
            body.addClass('big-search');
            setTimeout(function(){
                $this.siblings('.search-wrap').find('.search-field').focus();
            }, 100);
        });

        bigSearchField.on('touchend click', function(e){
            e.stopPropagation();
        });


        bigSearchField.attr({placeholder: js_vars['Enter Keywords'], autocomplete: 'off'});

        // Checkbox and Radio buttons

        //if buttons are inside label
        function radio_checkbox_animation() {
            var checkBtn = $('label').find('input[type="checkbox"]');
            var checkLabel = checkBtn.parent('label');
            var radioBtn = $('label').find('input[type="radio"]');

            checkLabel.click(function(){
                var $this = $(this);
                if($this.find('input').is(':checked')){
                    $this.addClass('checked');
                }
                else{
                    $this.removeClass('checked');
                }
            });

            radioBtn.change(function(){
                var $this = $(this);
                if($this.is(':checked')){
                    $this.parent('label').siblings().removeClass('checked');
                    $this.parent('label').addClass('checked');
                }
                else{
                    $this.parent('label').removeClass('checked');
                }
            });
        }
        radio_checkbox_animation();

        // Featured slider

        var featuredSlider = $('div.featured-slider');

        if(featuredSlider.length){

            var featuredSliderItem = featuredSlider.find('article');
            var sliderPreloader = $('.featured-slider-wrap + div.slider-preloader');
            var featuredSliderHeader = featuredSlider.find('.entry-header');

            featuredSliderHeader.hover(
                function(){
                    $(this).closest('.featured-slider').addClass('opaque');
                },
                function() {
                    $(this).closest('.featured-slider').removeClass('opaque');
            });

            if(featuredSliderItem.length <= 1){
                sliderPreloader.hide();
            }

            var initializeFeaturedSlider = function(){

                sliderPreloader.fadeOut(300);

                featuredSlider.addClass('loaded');

                featuredSlider.slick({
                    slide: 'article',
                    autoplaySpeed: 5000,
                    autoplay: true,
                    infinite: true,
                    fade: true,
                    speed: 600,
                    centerMode: false,
                    centerPadding: 0,
                    draggable: false,
                    dots: false,
                    touchThreshold: 20,
                    slidesToShow: 1,
                    cssEase: 'cubic-bezier(0.28, 0.12, 0.22, 1)',
                    responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            dots: true,
                            draggable: true
                        }
                    }
                  ]
                });
            };

            var msieversion = function() {

                var ua = window.navigator.userAgent;
                var msie = ua.indexOf("MSIE ");

                if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
                    initializeFeaturedSlider();
                }

               return false;
            };

            msieversion();

            var sliderFunctions = function() {

                var notIE = function() {

                    var ua = window.navigator.userAgent;
                    var msie = ua.indexOf("MSIE ");

                    if(!(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))){
                        initializeFeaturedSlider();
                    }

                   return false;
                };

                notIE();

                if(body.hasClass('featured-slider-fullwidth')){
                    var htmlOffsetTop = parseInt($('html').css('margin-top'));
                    var featuredSliderHeight = featuredSlider.outerHeight();
                    var mainHeader = $('#masthead');
                    var headerHeight = mainHeader.outerHeight();

                    $('.site-content > .container').wrap('<div class="main-content-wrap"></div>');

                    var wScrollTop = $(window).scrollTop();

                    if(x > 1024){

                        $('.main-content-wrap').css({marginTop: featuredSliderHeight});

                        if(body.hasClass('sticky-header')){
                            featuredSlider.parent().css({top: htmlOffsetTop, position: 'fixed'});

                            var featuredSliderStickyHeader = function(){
                                if(wScrollTop < featuredSliderHeight - headerHeight + 40){
                                    featuredSlider.css({opacity: ( featuredSliderHeight - wScrollTop ) / featuredSliderHeight});
                                    mainHeader.addClass('transparent').removeClass('shrink');
                                }
                                else {
                                    mainHeader.removeClass('transparent').addClass('shrink');
                                }
                            };
                            featuredSliderStickyHeader();

                            $(window).scroll(function(){
                                wScrollTop = $(window).scrollTop();
                                featuredSliderStickyHeader();
                            });
                        }

                        else{
                            featuredSlider.parent().css({top: headerHeight + htmlOffsetTop});
                            var featuredSliderDefaultHeader = function() {
                                if(wScrollTop > headerHeight - 4){
                                    featuredSlider.css({opacity: ( featuredSliderHeight - wScrollTop + headerHeight ) / featuredSliderHeight}).parent().css({top: htmlOffsetTop, position: 'fixed'});
                                }
                                else{
                                    featuredSlider.css({opacity: 1}).parent().css({top: headerHeight + htmlOffsetTop, position: 'absolute'});
                                }
                            };
                            featuredSliderDefaultHeader();

                            $(window).scroll(function(){
                                wScrollTop = $(window).scrollTop();
                                featuredSliderDefaultHeader();
                            });
                        }
                    }

                    else{
                        if(body.hasClass('sticky-header')){

                            mainHeader.next('#content').css({marginTop: headerHeight});

                            var featuredSliderStickyHeaderTouch = function(){
                                if(wScrollTop < featuredSliderHeight - headerHeight + 40){
                                    mainHeader.addClass('transparent').removeClass('shrink');
                                }
                                else {
                                    mainHeader.removeClass('transparent').addClass('shrink');
                                }
                            };
                            featuredSliderStickyHeaderTouch();

                            $(window).scroll(function(){
                                wScrollTop = $(window).scrollTop();
                                featuredSliderStickyHeaderTouch();
                            });
                        }
                    }
                }

            };

            var featuredImagesLoaded = function () {

               counter--;
               if( counter === 0 ) {
                    sliderFunctions();
                }
            };

            var featuredSliderImg = featuredSlider.find('img');
            var counter = featuredSliderImg.length;

            featuredSliderImg.each(function() {
                if( this.complete ) {
                    featuredImagesLoaded.call( this );
                } else{
                    $(this).one('load', featuredImagesLoaded);
                }
            });

            if(!featuredSliderImg.length){
                sliderFunctions();
            }
        }

        // Gallery slider

        var gallerySlider = $('.entry-gallery .gallery-size-full');

        if(gallerySlider.length){

            var initializeGallerySlider = function(){

                gallerySlider.each(function(){
                    var $this = $(this);
                    var gallerySliderPreloader = $('.entry-gallery + div.slider-preloader');
                    $this.addClass('loaded');

                    gallerySliderPreloader.fadeOut(300);

                    $this.slick({
                        infinite: true,
                        speed: 400,
                        centerMode: false,
                        centerPadding: 0,
                        draggable: false,
                        slidesToShow: 1,
                        cssEase: 'ease-out',
                        responsive: [
                        {
                          breakpoint: 1025,
                          settings: {
                            draggable: true
                          }
                        },
                        {
                        breakpoint: 768,
                        settings: {
                            dots: true
                        }
                    }
                      ]
                    });
                });

            };

            var msieversion = function() {

                var ua = window.navigator.userAgent;
                var msie = ua.indexOf("MSIE ");

                if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
                    initializeGallerySlider();
                }

               return false;
            };

            msieversion();

            var galleryImagesLoaded = function () {

               galleryCounter--;
               if( galleryCounter === 0 ) {

                    var notIE = function() {

                        var ua = window.navigator.userAgent;
                        var msie = ua.indexOf("MSIE ");

                        if(!(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))){
                            initializeGallerySlider();
                        }

                       return false;
                    };

                    notIE();

                }
            };
            var galleryImg = gallerySlider.find('img');
            var galleryCounter = galleryImg.length;

            galleryImg.each(function() {
                if( this.complete ) {
                    galleryImagesLoaded.call( this );
                } else{
                    $(this).one('load', galleryImagesLoaded);
                }
            });

        }

        // On Infinite Scroll Load

        var infiniteHandle = $('#infinite-handle');
        infiniteHandle.find('button').text(js_vars.load_more_text);

        if(infiniteHandle.length){
            if(x > 1024){
                infiniteHandle.parent().css('margin-bottom', 154);
            }
            else{
                infiniteHandle.parent().css('margin-bottom', 60);
            }
        }

        // Animate grid items

        var gridItem = $('.grid-wrapper article.post, .grid-wrapper article.page');

        gridItem.each(function(i){
            setTimeout(function(){
                gridItem.eq(i).addClass('post-loaded animate');
            }, 200 * (i+1));
        });

        var $container = $('div.grid-wrapper');

        $(document.body).on('post-load', function(){

            radio_checkbox_animation();

            // Reactivate masonry on post load

            var newEl = $container.children().not('article.post-loaded, span.infinite-loader').addClass('post-loaded');


            newEl.each(function(){
                var wScrollTop = $(window).scrollTop();
                var $this = $(this);
                if(x >= 992){
                    if(wScrollTop > $this.offset().top - ($(window).height() / 1.1)){
                        $this.addClass('animate');
                    }
                }
                else{
                    if(wScrollTop > $this.offset().top - ($(window).height() / 1.2)){
                        $this.addClass('animate');
                    }
                }
                if($this.has('.entry-gallery .gallery-size-full')){
                    $this.find('.gallery-size-full').addClass('loaded').slick();
                }
            });

            $(window).scroll(function(){
                var wScrollTop = $(window).scrollTop();
                newEl.each(function(){
                    var $this = $(this);
                    if(x >= 992){
                        if(wScrollTop > $this.offset().top - ($(window).height() / 1.1)){
                            $this.addClass('animate');
                        }
                    }
                    else{
                        if(wScrollTop > $this.offset().top - ($(window).height() / 1.2)){
                            $this.addClass('animate');
                        }
                    }
                });
            });

        });

        // Contact Form

        var contactForm = $('form.contact-form');

        if(contactForm.length){
            var contactSubmit = contactForm.find('input[type="submit"]');
            var halfWidthInputs = contactForm.find('input[type=text], input[type=email]').parent();
            var halfWidthInputsOdd =  halfWidthInputs.filter(function(index) {
                return (index + 1) % 2 === 0;
            });
            // halfWidthInputs.addClass('half-width');
            // halfWidthInputsOdd.addClass('last');
        }

	}); // End Document Ready

	$(window).load(function(){

        // Calculate clients viewport

        function viewport() {
            var e = window, a = 'inner';
            if(!('innerWidth' in window )) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
        }

        var w=window,d=document,
        e=d.documentElement,
        g=d.getElementsByTagName('body')[0],
        x=w.innerWidth||e.clientWidth||g.clientWidth, // Viewport Width
        y=w.innerHeight||e.clientHeight||g.clientHeight; // Viewport Height

        // Global Vars

        var $window = $(window);
        var body = $('body');

        body.addClass('page-loaded');

	}); // End Window Load

    $(window).resize(function(){

        // Calculate clients viewport

        function viewport() {
            var e = window, a = 'inner';
            if(!('innerWidth' in window )) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
        }

        var w=window,d=document,
        e=d.documentElement,
        g=d.getElementsByTagName('body')[0],
        x=w.innerWidth||e.clientWidth||g.clientWidth, // Viewport Width
        y=w.innerHeight||e.clientHeight||g.clientHeight; // Viewport Height

        var body = $('body');

        // Main Header
        var mainHeader = $('#masthead');

        if(body.hasClass('sticky-header') && !body.hasClass('featured-slider-fullwidth')){
            var mainContent = $('#content');
            var headerHeight = mainHeader.outerHeight();
            mainContent.css({paddingTop: headerHeight + 40});

            var stickyHeader = function(){
                if(wScrollTop < 50){
                    mainHeader.addClass('transparent').removeClass('shrink');
                }
                else {
                    mainHeader.removeClass('transparent').addClass('shrink');
                }
            };
            stickyHeader();

            $(window).scroll(function(){
                wScrollTop = $(window).scrollTop();
                stickyHeader();
            });
        }

        // Featured Slider

        if(body.hasClass('featured-slider-fullwidth')){
            var htmlOffsetTop = parseInt($('html').css('margin-top'));
            var featuredSlider = $('div.featured-slider');
            var featuredSliderHeight = featuredSlider.outerHeight();
            var headerHeight = mainHeader.outerHeight();
            var wScrollTop = $(window).scrollTop();

            if(x > 1024){

                $('.main-content-wrap').css({marginTop: (featuredSlider.outerHeight())});

                if(body.hasClass('sticky-header')){
                    featuredSlider.parent().css({top: htmlOffsetTop, position: 'fixed'});
                    mainHeader.next('#content').css({marginTop: ''});

                    var featuredSliderStickyHeader = function(){
                        if(wScrollTop < featuredSliderHeight - headerHeight + 40){
                            featuredSlider.css({opacity: ( featuredSliderHeight - wScrollTop ) / featuredSliderHeight});
                            mainHeader.addClass('transparent').removeClass('shrink');
                        }
                        else {
                            mainHeader.removeClass('transparent').addClass('shrink');
                        }
                    };
                    featuredSliderStickyHeader();

                    $(window).scroll(function(){
                        wScrollTop = $(window).scrollTop();
                        featuredSliderStickyHeader();
                    });
                }

                else{
                    featuredSlider.parent().css({top: headerHeight + htmlOffsetTop});
                    var featuredSliderDefaultHeader = function() {
                        if(wScrollTop > headerHeight - 4){
                            featuredSlider.css({opacity: ( featuredSliderHeight - wScrollTop + headerHeight ) / featuredSliderHeight}).parent().css({top: htmlOffsetTop, position: 'fixed'});
                        }
                        else{
                            featuredSlider.css({opacity: 1}).parent().css({top: headerHeight + htmlOffsetTop, position: 'absolute'});
                        }
                    };
                    featuredSliderDefaultHeader();

                    $(window).scroll(function(){
                        wScrollTop = $(window).scrollTop();
                        featuredSliderDefaultHeader();
                    });
                }
            }

            else{
                $('.main-content-wrap').css({marginTop: ''});

                if(body.hasClass('sticky-header')){

                    featuredSlider.parent().css({top: '', position: ''});

                    mainHeader.next('#content').css({marginTop: headerHeight});

                    var featuredSliderStickyHeaderTouch = function(){
                        if(wScrollTop < featuredSliderHeight - headerHeight + 40){
                            mainHeader.addClass('transparent').removeClass('shrink');
                        }
                        else {
                            mainHeader.removeClass('transparent').addClass('shrink');
                        }
                    };
                    featuredSliderStickyHeaderTouch();

                    $(window).scroll(function(){
                        wScrollTop = $(window).scrollTop();
                        featuredSliderStickyHeaderTouch();
                    });
                }
            }
        }

	}); // End Window Resize

})(jQuery);
