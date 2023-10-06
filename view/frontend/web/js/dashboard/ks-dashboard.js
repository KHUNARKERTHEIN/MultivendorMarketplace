
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

require([
    'jquery'
    ], function ($) { 

        $(document).ready(function(){ 

            var ksSliderOption = {
                loop:true,
                nav:true,
                responsiveClass: true,
                center:false,
                items:1,
                autoplay:true,
                autoplayHoverPause: true,
                autoplayTimeout:3000,
                responsive : {
                //breakpoint from 0 and up
                0 : {
                    items : 1,
                },
                // add as many breakpoints as desired , breakpoint from 480 up
                480 : {
                    items:1,
                },
                // breakpoint from 768 up
                768 : {
                    items:1,
                },
                992 :{
                    items:1,
                },
                }
            } 

            var ksSliderProductOption = {
                loop:true,
                nav:true,
                responsiveClass: true,
                center:false,
                items:4,
                autoplay:true,
                autoplayHoverPause: true,
                autoplayTimeout:3000,
                responsive : {
                    //breakpoint from 0 and up
                    0 : {
                       items : 2,
                    },
                    // add as many breakpoints as desired , breakpoint from 480 up
                    480 : {
                       items:2,
                    },
                    // breakpoint from 768 up
                    768 : {
                        items:3,
                    },
                    992 :{
                        items:4,
                    },
                }
            }
        
            function ksOwlResize($ksOwl) {
                $ksOwl.trigger('destroy.owl.carousel');
                $ksOwl.html($ksOwl.find('.owl-stage-outer').html()).removeClass('owl-loaded');
                $ksOwl.owlCarousel(ksSliderOption);
            } 

            function ksOwlProductResize($ksOwl) {
                $ksOwl.trigger('destroy.owl.carousel');
                $ksOwl.html($ksOwl.find('.owl-stage-outer').html()).removeClass('owl-loaded');
                $ksOwl.owlCarousel(ksSliderProductOption);
            } 

            // Function for Toggle button
            $("#sidve-nav-toggler").click(function(){
            $(".columns").toggleClass("side-nav-collaped");
                if ($('.ks-sidebar-title').is(':visible')) {
                    $('.ks-sidebar-title').hide();
                } 
                else {
                    $('.ks-sidebar-title').show();
                }
                if(parseInt($('div.ks-banner-slider .ks-banner-div').length)>1){
                    var $ksOwl = $(".ks-banner-slider").owlCarousel(ksSliderOption);
                    setTimeout(function(){ksOwlResize($ksOwl);}, 500);
                }
                if(parseInt($('ol.ks-discount-products li').length)>=4){
                    var $ksDiscountOwl = $(".ks-discount-products").owlCarousel(ksSliderProductOption);
                    setTimeout(function(){ksOwlProductResize($ksDiscountOwl);}, 500);
                }
                if(parseInt($('ol.ks-recently-products li').length)>=4){
                    var $ksRecentlyOwl = $(".ks-recently-products").owlCarousel(ksSliderProductOption);
                    setTimeout(function(){ksOwlProductResize($ksRecentlyOwl);}, 500);
                }
                if(parseInt($('ol.ks-best-products li').length)>=4){
                    var $ksBestOwl = $(".ks-best-products").owlCarousel(ksSliderProductOption);
                    setTimeout(function(){ksOwlProductResize($ksBestOwl);}, 500);
                }
            });

           // Mobile View Sidebar Menu
            $("#sidve-nav-mobile-toggler").click(function(){
                $(".sidebar-main").addClass("expand");
            });
            $("#close-menu").click(function(){
                $(".sidebar-main").removeClass("expand");
            }); 

            //  Make Button for Mobile View
            $('body').on('click','.ks-mobile-toolbar-grid',function(){
                var checkForGridHeader = $('.admin__data-grid-header').css('display');
                if (checkForGridHeader === 'none'){
                    $('.admin__data-grid-header').show();
                } else {
                    $('.admin__data-grid-header').hide();
                }
            });

            //add condition to add loader
            $('body').on('click','.ks-reload',function(){
                $("body").trigger('processStart');
                $(".loader").css('user-select','none');
            });

            $('.ks-form-reload').submit(function() {
                $("body").trigger('processStart');
            });

        });

});
