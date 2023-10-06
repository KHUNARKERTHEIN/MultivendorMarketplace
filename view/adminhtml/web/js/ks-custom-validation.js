/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

define([
    'jquery',
    'mage/url',
    'uiRegistry',
    'Magento_Ui/js/lib/validation/utils',
    'moment'
], function($, url, uiRegistry, utils, moment) {
    return function(validator) {
        /**
         * Rule to validate the seller store url
         */
        validator.addRule(
            'ks-validate-store-url',
            function (value) {
                if(value.match(/^[A-Z][A-Z0-9-\/-]*$/i) && value) {
                    return true;
                }
                $(".ks-available").removeClass('success').removeClass('admin__field-error');
                $(".ks-available").html(" ");
            },
            $.mage.__('Only alphanumeric characters and dash(-) symbol are allowed.')
        );

        /**
         * Rule to validate date time string
         */
        validator.addRule(
            'validate-date-time',
            function (value, params, additionalParams) {
                if (value) {
                    return moment(value, 'YYYY-MM-DD hh:mm:ss').isValid(); 
               } else {
                    return true;
               }
                
            },
            $.mage.__('Please enter a valid date.')
        );

        /**
         * rule to verify the email address
         */
        validator.addRule(
            'ks-verify-email',
            function (value) {

                if(value) {
                    var ksReturn = '';
                    var ksCustomerEmail = uiRegistry.get('index= email').value();
                    var ksWebsiteId    = uiRegistry.get('index= website_id').value();

                    var xhr = $.ajax({
                        type: "POST",
                        url: url.build("multivendor/seller_ajax/customeremailverify"),
                        async: false,
                        data: {
                            form_key: window.FORM_KEY,
                            ks_website_id: ksWebsiteId ,
                            ks_customer_email : ksCustomerEmail
                        },
                        success: function (ksResponse) { }
                    }); 

                    var data = JSON.parse(xhr.responseText)
                    if (data.ks_message_type == 'error') {

                        $(".ks-check-email").removeClass('admin__field-error');
                        $(".ks-check-email").html(" ");
                        ksReturn = false;
                    } else {
                        $(".ks-check-email").removeClass('admin__field-error');
                        $(".ks-check-email").html(" ");
                        ksReturn = true;
                    }
                    return ksReturn;   
                }
            },
            $.mage.__('Email address already exists in an associated website.')
        );

        return validator;
    }
});
