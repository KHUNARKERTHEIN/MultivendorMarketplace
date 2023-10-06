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
    'jquery', "mage/url", 'owl_carousel'
    ], function ($,url) {
        
        $(document).ready(function() {

            function ksInitSliders(){
                
                if(parseInt($('ol.ks-discount-products li').length)>=4){
                    $('.ks-discount-products').owlCarousel({
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
                    });
                }
                
                if(parseInt($('ol.ks-recently-products li').length)>=4){
                    $('.ks-recently-products').owlCarousel({
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
                    });
                }
                
                if(parseInt($('ol.ks-best-products li').length)>=4){
                    $('.ks-best-products').owlCarousel({
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
                    });
                }
                
                if(parseInt($('div.ks-banner-slider .ks-banner-div').length)>1){
                    $('.ks-banner-slider').owlCarousel({
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
                    });
                }
            }

            var tab_ids = $('#ks-tab-id').val();
            var ks_link = window.location.href;
            if (ks_link.indexOf('?')>-1) {
                if(!ks_link.includes('?tab_id')){
                    tab_ids = 'tab-2';
                }
            }
    
            $('ul.ks_tabs li').removeClass('current');
            $('.tab-content').removeClass('current');
    
            $("#ks" + tab_ids).addClass('current');
            $("#" + tab_ids).addClass('current');

            $('ul.ks_policy_tabs li').each(function() {
                ksShowTab('ul.ks_policy_tabs li','.tab-content_policy',this);
                return false;
            });

            
            ksInitSliders();

            $('.ks_flags i').click(function() {
                $(this).removeClass('fa-flag-o');
                $(this).addClass('fa-flag');
            });
        
            $('ul.ks_tabs li').click(function() {
                ksShowTab('ul.ks_tabs li','.tab-content',this);
                var ksTabValue = $(this).attr('data-tab');
                var ksUrl = $('#ks-get-url').val();   
                ksUrl += '?tab_id='+ksTabValue;
                window.history.pushState({ path: ksUrl }, '', ksUrl);
            });

            $('ul.ks_policy_tabs li').click(function() {
                ksShowTab('ul.ks_policy_tabs li','.tab-content_policy',this);
            });

            function ksShowTab(ksLiClass, ksContentClass , ksElement){
                var ksTabId = $(ksElement).attr('data-tab');

                if (ksTabId == 'tab-1'){
                    var $ksBannerOwl = $('.ks-banner-slider');
                    if ($ksBannerOwl.data('owlCarousel')) {
                        $ksBannerOwl.data('owlCarousel').onThrottledResize();
                    }
                    var $ksWhishlistsOwl = $('.ks-discount-products');
                    if ($ksWhishlistsOwl.data('owlCarousel')) {
                        $ksWhishlistsOwl.data('owlCarousel').onThrottledResize();
                    }
                    var $ksRecentlyProductOwl = $('.ks-recently-products');
                    if ($ksRecentlyProductOwl.data('owlCarousel')) {
                        $ksRecentlyProductOwl.data('owlCarousel').onThrottledResize();
                    }
                    var $ksBestProductOwl = $('.ks-best-products');
                    if ($ksBestProductOwl.data('owlCarousel')) {
                        $ksBestProductOwl.data('owlCarousel').onThrottledResize();
                    }
                }else{
                    ksInitSliders();
                }
    
                $(ksLiClass).removeClass('current');
                $(ksContentClass).removeClass('current');
    
                $(ksElement).addClass('current');
                $("#" + ksTabId).addClass('current');
            }
            
            ksCheckCheckbox(".ks-banner-checkbox",".ks-banner-content");
            ksCheckCheckbox(".ks-recently-checkbox",".ks-recently-content");
            ksCheckCheckbox(".ks-best-checkbox",".ks-best-content");
            ksCheckCheckbox(".ks-discount-checkbox",".ks-discount-content");

            ksOnChange(".ks-banner-checkbox",".ks-banner-content");
            ksOnChange(".ks-recently-checkbox",".ks-recently-content");
            ksOnChange(".ks-best-checkbox",".ks-best-content");
            ksOnChange(".ks-discount-checkbox",".ks-discount-content");

            function ksOnChange($ksCheckBoxClass,$ksContentClass){

                $($ksCheckBoxClass).change(function(){
                    ksCheckCheckbox($ksCheckBoxClass,$ksContentClass);
                });
            }

            function ksCheckCheckbox($ksCheckBoxClass,$ksContentClass){

                if($($ksCheckBoxClass).prop('checked') == true){
                    $($ksContentClass).css("display","block");
                } else {
                     $($ksContentClass).css("display","none");
                }
            }

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-add-banner-btn').on('click', function(e) {
                e.preventDefault();
                $('.ks-upload-banner-popup').css("display","block");
                $('.ks-add-banner-form').trigger("reset");
                $('#ks-banner-upload').attr("required","required");
                $('.ks-file-select-name').html("No file chosen...");
                $('.ks-banner-id').val("");
            });

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-banner-modal-close').on('click', function(e) {
                e.preventDefault();
                $('.ks-upload-banner-popup').css("display","none");
            });

            // script used for banner upload file in home page right sidebar
            $('#ks-banner-upload').bind('change', function () {
            var filename = $("#ks-banner-upload").val();
            if (/^\s*$/.test(filename)) {
                $(".ks-file-upload-parent").removeClass('active');
                $("#ks-selected-file").text("No file chosen..."); 
            }
            else {
                $(".ks-file-upload-parent").addClass('active');
                $("#ks-selected-file").text(filename.replace("C:\\fakepath\\", "")); 
            }
            });

            $('div.ks-seller-config-data .ks-homepage-disabled').each(function() {
                $(this).prop("readonly", true);
            });

            $('div.ks-seller-config-data .ks-homepage-disabled').on('click', function(){ 
                $(this).prop("readonly", false);
                $('.ks-profile-action').css("display","block");
            });
            
            $('.ks-banner-edit').on('click', function(){ 
                //get is
                ksId = parseInt($(this).attr('data-tab'));
                var customurl = url.build('multivendor/sellerprofile/bannerdata');
                $.ajax({
                    url: customurl,
                    type: 'POST',
                    data:{id: ksId },
                    success: function(response) {
                        $('.ks-upload-banner-popup').css("display","block");
                        $('.ks-add-banner-form').trigger("reset");
                        $('#ks-banner-upload').removeAttr("required");
                        $('.ks-file-select-name').html(response.ks_picture);
                        $('.ks-title').val(response.ks_title);
                        $('.ks-text').val(response.ks_text);
                        $('.ks-banner-id').val(ksId);  
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            });

            $('#ks-contact-seller-form').submit(function(){
                $('#ks-contact-seller-submit').attr("disabled",true);
            })
        })
});


