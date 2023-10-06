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
    "Magento_Ui/js/modal/confirm",
    "jquery/ui" 
    ], function ($,ksUrl,confirmation) {
       "use strict";
       function main(config, element) {

        $(document).on('click','#comment-invoice',function(e) {
            var dataForm = $('#invoice_history_section');
            e.preventDefault();
            var param = dataForm.serialize();
            $.ajax({
                showLoader: true,
                url: ksUrl.build('multivendor/order_invoice/addcomment'),
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#invoice_history_block").load(location.href + " #invoice_history_block");
                return true;
            });
        });

        $(document).on('click','#update-invoice-qty',function(e) {
            e.preventDefault();
            var qty = [];
            var itemIds = [];
            
            $(".ks-invoice-item-qty").each(function(){
                itemIds.push($(this).attr('item-id'));
                qty.push($(this).val());
            })

            $.ajax({
                url: ksUrl.build('multivendor/order_invoice/updateqty'),
                type: 'POST',
                data:{qtys:qty,itemIds:itemIds,order_id:config.ks_order_id},
                showLoader: true,
                success: function(response){
                    if (response["error"]) {
                        alert(response["error"]);                            
                    } else{
                        $('.invoice-container').replaceWith(response);
                        $('#ks-submit-invoice').attr('disabled',false);
                    }       
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    $(".invoice-container").load(location.href+ " .invoice-container");
                }
            });
        });

        $(document).on('change','.ks-invoice-item-qty',function(e){
            $('#ks-submit-invoice').attr('disabled',true);
            $('#update-invoice-qty').attr('disabled',false);
        });
        
        $('#send_email').change(function(){
            //If the checkbox is checked.
            if($(this).is(':checked')){
                //Enable the append comments 
                $('#notify_customer').attr("disabled", false);
            } else{
                //If it is not checked, disable the checkbox of append comments.
                $('#notify_customer').attr("disabled", true);
            }
        });

        $(".ks-send-email").on('click', function(e){
            e.preventDefault();
            var url = e.currentTarget.href;
            confirmation({
                  content: $.mage.__('Are you sure you want to send an invoice email to customer?'),
                  actions: {
                      confirm: function(){
                        window.location.href = url;
                    },
                    cancel: function(){
                  },
                }
            });
        });
    };
    return main;
});