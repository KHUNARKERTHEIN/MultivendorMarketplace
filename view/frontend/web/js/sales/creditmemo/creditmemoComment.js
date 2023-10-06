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
    "jquery/ui",
    ], function ($,ksUrl,confirmation) {
     "use strict";
     function main(config, element) {
        $(document).on('click','#creditmemo-comment',function() {
            var dataForm = $('#creditmemo_history_section');
            event.preventDefault();
            var param = dataForm.serialize();
            $.ajax({
                showLoader: true,
                url: ksUrl.build('multivendor/order_creditmemo/addcomment'),
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#creditmemo_history_block").load(location.href + " #creditmemo_history_block");;
                return true;
            });
        });
        /*update memo qty*/
        $(document).on('click','#update-creditmemo-qty',function(e) {
            e.preventDefault();

            $.ajax({
                url: ksUrl.build('multivendor/order_creditmemo/updateqty'),
                type: 'POST',
                data:$('#ks-sales-new-creditmemo-form').serialize(),
                showLoader: true,
                success: function(response){
                    if (response["error"]) {
                        alert(response["error"]);                            
                    }
                    else{
                        $(".creditmemo-container").replaceWith(response);
                        $('#ks-submit-creditmemo').attr('disabled',false);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    $(".creditmemo-container").load(location.href+ " .creditmemo-container");
                }
            });
        });
        $('#creditmemo_send_email').change(function(){
            //If the checkbox is checked.
            if($(this).is(':checked')){
                //Enable the append comments 
                $('#creditmemo_notify_customer').attr("disabled", false);
            } else{
                //If it is not checked, disable the checkbox of append comments.
                $('#creditmemo_notify_customer').attr("disabled", true);
            }
        });

        $(document).on('change','.ks-creditmemo-item-qty',function(e){
            $('#ks-submit-creditmemo').attr('disabled',true);
            $('#update-creditmemo-qty').attr('disabled',false);
        });

        $(".ks-send-email").on('click', function(e){
            e.preventDefault();
            var url = e.currentTarget.href;
            confirmation({
                  content: $.mage.__('Are you sure you want to send a credit memo email to customer?'),
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