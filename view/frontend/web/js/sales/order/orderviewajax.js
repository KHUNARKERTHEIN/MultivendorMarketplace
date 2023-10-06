/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
/*jshint browser:true jquery:true*/

define([
    "jquery",
    "mage/url",
    "Magento_Ui/js/modal/modal",
    "Magento_Ui/js/modal/confirm",
    "jquery/ui" 
], function ($,ksUrl,modal,confirm) {
 "use strict";
function main(config, element) {

        /*Gift message*/
        var AjaxUrl = config.AjaxUrl;
        var formId = config.FormId;
        var dataForm = $('#'+formId+'_form');
        $(document).on('click','#giftmessage-btn',function() {
            event.preventDefault();
            var param = dataForm.serialize();
            $.ajax({
                showLoader: true,
                url  : AjaxUrl,
                data : param,
                type : "POST"
            }).done(function (data) {
                return true;
            });
        });

        /*modal for gift option*/
        var options = {
            type: 'popup', // popup or slide
            responsive: true, // true = on smaller screens the modal slides in from the right
            title: '<span>Gift Options for </span><span class="item-name"></span>',
            buttons: [{ // Add array of buttons within the modal if you need.
                text: $.mage.__('Ok'),
                class: 'modal-close close-popup ks-action-btn ks-primary gift_options_cancel_button',
                click: function () {
                        var item_id = $('input[name="item_id"]').val();
                        console.log(config);
                        if(item_id && config.EditAllowed){
                            var action = $('#giftmessage_'+item_id+'_form').attr('action');
                            $('#giftmessage_'+item_id+'_sender').val($('#current_item_giftmessage_sender').val())
                            $('#giftmessage_'+item_id+'_recipient').val($('#current_item_giftmessage_recipient').val())
                            $('#giftmessage_'+item_id+'_message').val($('#current_item_giftmessage_message').val())
                            var dataForm = $('#giftmessage_'+item_id+'_form');
                            var param = dataForm.serialize();
                            $.ajax({
                                showLoader: true,
                                url: action,
                                data: param,
                                type: "POST",                                          
                            }).done(function (data) {
                                $("#gift_options_data_"+item_id).load(location.href + " #gift-option-content_"+item_id);
                            }); 
                        }
                        this.closeModal(); // This button closes the modal
                }
            }] 
        }; 
        var popup = modal(options, $('.gift-option-item-popup'));
        $(".ks-gift-box-modal").on('click',function(){ 
            $('.gift-option-item-popup').modal('openModal');
        });
        

        /*Order comment*/
        var AjaxCommentUrl = config.AjaxCommentUrl;
        $(document).on('click','#comment-button',function() {
            var dataForm = $('#order_history_section');
            event.preventDefault();
            var param = dataForm.serialize();
            $.ajax({
                showLoader: true,
                url: AjaxCommentUrl,
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#order_history_block").load(location.href + " #order_history_block");
                return true;
            });
        });


        $('.action-link').mouseenter(function(){
            var item_id = $(this).attr('data-value');
            $(".gift-options-tooltip-content_"+item_id).show();
        }).mouseleave(function(){
            var item_id = $(this).attr('data-value');
            $(".gift-options-tooltip-content_"+item_id).hide();
        });

        $(document).on('click','.action-link',function(e) {
            $('.gift-option-item-popup').show();
            var item_id = $(this).attr('data-value');
            var sender = $('#giftmessage_'+item_id+'_sender').val();
            var recipient = $('#giftmessage_'+item_id+'_recipient').val();
            var message = $('#giftmessage_'+item_id+'_message').val();
            var item_name = $('#giftmessage_'+item_id+'_name').val();

            $('span.item-name').html(item_name);
            $('input[name="item_id"]').val(item_id);
            $('input[name="current_item_giftmessage_sender"]').val(sender);
            $('input[name="current_item_giftmessage_recipient"]').val(recipient);
            $('#current_item_giftmessage_message').val(message);
        });

        $(document).on('click','#ks-send-order-email',function(){
            event.preventDefault();
            var href = $(this).attr('href');
            confirm({
                content: $.mage.__('Are you sure you want to send an order email to customer?'),
                modalClass:"ks-sales-send-mail-modal",
                actions: {
                    confirm: function(){
                        window.location = href;
                    },
                    cancel: function(){},
                    always: function(){}
                }
            });
        });
      
    };
    return main;
});