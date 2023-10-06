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
    "jquery",
    "mage/url", 
    "mage/translate", 
    "mage/adminhtml/events", 
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
    ], function($,url){

        $(document).ready(function() {

            ksAddEditor("ks-overview-textarea");
            ksAddEditor("ks-return-policy-textarea");
            ksAddEditor("ks-privacy-policy-textarea");
            ksAddEditor("ks-shipping-policy-textarea");
            ksAddEditor("ks-legal-policy-textarea");
            ksAddEditor("ks-terms-of-service-textarea");

            function ksAddEditor($ksClassId) {
                ks_wysiwyg_terms_of_service = new wysiwygSetup($ksClassId, {
                    "width":"100%",  // defined width of editor
                    "height":"300px", // height of editor
                    "tinymce":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });
                ks_wysiwyg_terms_of_service.setup("exact");
            }

            $('.ks-shop-status').change(function() {
                $("body").trigger('processStart');
                var ksStatus = 0;
                if($(this).is(':checked')){
                    ksStatus = 1;
                } else {
                    ksStatus = 0;
                }
                $ksSellerId = $('.ks-seller-id').val();
                var customurl = url.build('multivendor/sellerprofile/shopstatussave');
                $.ajax({
                    url: customurl,
                    type: 'POST',
                    data:{ks_status: ksStatus,ks_seller_id: $ksSellerId},
                    success: function(response) {
                        if(ksStatus == 1){
                            $('.ks-success').css("display","block");
                            $('.ks-danger').css("display","none");
                        } else {
                            $('.ks-danger').css("display","block");
                            $('.ks-success').css("display","none");
                        }
                        location.reload();
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            });

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-edit-seller-logo').on('click', function(e) {
                e.preventDefault();
                $('.ks-edit-logo-content').css("display","block");
            });

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-edit-logo-close').on('click', function(e) {
                e.preventDefault();
                $('.ks-edit-logo-content').css("display","none");
            });

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-edit-seller-banner').on('click', function(e) {
                e.preventDefault();
                $('.ks-edit-banner-content').css("display","block");
            });

            // Quick & dirty toggle to demonstrate modal toggle behavior
            $('.ks-edit-banner-close').on('click', function(e) {
                e.preventDefault();
                $('.ks-edit-banner-content').css("display","none");
            });

            // script used for banner upload file in profile edit page
            $('#ks-profile-banner-upload').bind('change', function () {
                var filename = $("#ks-profile-banner-upload").val();
                if (/^\s*$/.test(filename)) {
                    $(".ks-file-upload-parent").removeClass('active');
                    $("#ks-banner-selected-file").text("Choose Banner Image"); 
                }
                else {
                    $(".ks-file-upload-parent").addClass('active');
                    $("#ks-banner-selected-file").text(filename.replace("C:\\fakepath\\", "")); 
                }
            });

            // script used for logo upload file in profile edit page
            $('#ks-profile-logo-upload').bind('change', function () {
                var filename = $("#ks-profile-logo-upload").val();
                if (/^\s*$/.test(filename)) {
                    $(".ks-file-upload-parent").removeClass('active');
                    $("#ks-logo-selected-file").text("Choose Logo Image"); 
                }
                else {
                    $(".ks-file-upload-parent").addClass('active');
                    $("#ks-logo-selected-file").text(filename.replace("C:\\fakepath\\", "")); 
                }
            });

            if($('#ks-store-id').val() == 0){
                $('.ks-profile-checkbox-container').each(function(){
                    $(this).find('input[type=checkbox]').prop('disabled', true);
                    $(this).hide();
                });
            } else {
                if($('#ks-count').val() == 0){
                    $('.ks-profile-checkbox-container').each(function(){
                        $(this).find('input[type=checkbox]').prop('disabled', true);
                        $(this).hide();
                    });
                } else {
                    $('.ks-profile-checkbox-container').each(function(){
                        $(this).find('input[type=checkbox]').prop('disabled', false);
                        $(this).show();
                    });
                }
            }

            $('.ks-profile-checkbox').each(function() {
                var ksInputId = $(this).attr('data-tab');
                if($('#ks-store-id').val() != 0  && $('#ks-count').val() != 0){
                    if ($(this).prop("checked")) {
                        $(ksInputId).prop("disabled",true);
                        $(ksInputId).css({"cursor": "not-allowed", "background-color": "#e9e9e9"});
                    } else {
                        $(ksInputId).prop("disabled",false);
                        $(ksInputId).css({"cursor": "default", "background-color": "#fff"});
                    }
                }
            });

            $('.ks-textarea-checkbox').each(function() {
                var ksInputId = $(this).attr('data-tab');
                if($('#ks-store-id').val() != 0  && $('#ks-count').val() != 0){
                    if ($(this).prop("checked")) {
                        tinyMCE.get(ksInputId).setMode("readonly");
                    } else {
                        tinyMCE.get(ksInputId).setMode("design");
                    }
                }
            });

            $('.ks-textarea-checkbox').change(function() {
                var ksInputId = $(this).attr('data-tab');
                if($('#ks-store-id').val() != 0  && $('#ks-count').val() != 0){
                    if ($(this).prop("checked")) {
                        tinyMCE.get(ksInputId).setMode("readonly");
                    } else {
                        tinyMCE.get(ksInputId).setMode("design");
                    }
                }
            });
            
            $('.ks-profile-checkbox').change(function() {
                var ksInputId = $(this).attr('data-tab');
                if($('#ks-store-id').val() != 0 && $('#ks-count').val() != 0){
                    if ($(this).prop("checked")) {
                        $(ksInputId).prop("disabled",true);
                        $(ksInputId).css({"cursor": "not-allowed", "background-color": "#e9e9e9"});
                    } else {
                        $(ksInputId).prop("disabled",false);
                        $(ksInputId).css({"cursor": "default", "background-color": "#fff"});
                    }
                }
            });

            $('div.ks-tabs input[type=radio]').prop("checked", false);

            $('div.ks-tabs input[type=radio]').each(function() {
                $(this).prop("checked", true);
                return false;
            });
        });
});