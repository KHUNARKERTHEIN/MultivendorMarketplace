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
        $(document).ready(function() {
        	ksCheckRequired();
            $("#ks_check_money_status").on('change', function() {
            	if (this.value == 1) {
            		$('#ks-payee_name').addClass('required-entry');
            		$('#ks-payee_name').parents('.ks-form-field').addClass('required');
            	} else {
            		$('#ks-payee_name').removeClass('required-entry');
            		$('#ks-payee_name').parents('.ks-form-field').removeClass('required');
            		$('#ks-payee_name-error').hide();
            	}
    		});

    		$("#ks_paypal_status").on('change', function() {
            	if (this.value == 1) {
            		$('#ks_paypal_associated_email').addClass('required-entry');
            		$('#ks_paypal_associated_email').parents('.ks-form-field').addClass('required');
            	} else {
            		$('#ks_paypal_associated_email').removeClass('required-entry');
            		$('#ks_paypal_associated_email').parents('.ks-form-field').removeClass('required');
            		$('#ks_paypal_associated_email-error').hide();
            	}
    		});

    		$("#ks_bank_transfer_status").on('change', function() {
            	if (this.value == 1) {
            		$('#ks_pri_acc_holder_name').addClass('required-entry');
            		$('#ks_pri_acc_holder_name').parents('.ks-form-field').addClass('required');
            		$('#ks_pri_bank_acc_no').addClass('required-entry');
            		$('#ks_pri_bank_acc_no').parents('.ks-form-field').addClass('required');
            	} else {
            		$('#ks_pri_acc_holder_name').removeClass('required-entry');
            		$('#ks_pri_acc_holder_name').parents('.ks-form-field').removeClass('required');
            		$('#ks_pri_bank_acc_no').removeClass('required-entry');
            		$('#ks_pri_bank_acc_no').parents('.ks-form-field').removeClass('required');
            		$('#ks_pri_acc_holder_name-error').hide();
            		$('#ks_pri_bank_acc_no-error').hide();
            	}
    		});

    		$("#ks_additional_payment_method_one_status").on('change', function() {
            	if (this.value == 1) {
            		$('#ks_additional_payment_method_one_name').addClass('required-entry');
            		$('#ks_additional_payment_method_one_name').parents('.ks-form-field').addClass('required');
            	} else {
            		$('#ks_additional_payment_method_one_name').removeClass('required-entry');
            		$('#ks_additional_payment_method_one_name').parents('.ks-form-field').removeClass('required');
            		$('#ks_additional_payment_method_one_name-error').hide();
            	}
    		});

    		$("#ks_additional_payment_method_two_status").on('change', function() {
            	if (this.value == 1) {
            		$('#ks_additional_payment_method_two_name').addClass('required-entry');
            		$('#ks_additional_payment_method_two_name').parents('.ks-form-field').addClass('required');
            	} else {
            		$('#ks_additional_payment_method_two_name').removeClass('required-entry');
            		$('#ks_additional_payment_method_two_name').parents('.ks-form-field').removeClass('required');
            		$('#ks_additional_payment_method_two_name-error').hide();
            	}
    		});
    		// Remove Make Field Collapse
    		$('form').submit(function(e) {
        		$('.ks-tab-heading').each(function(e) {
                    if ($(this).hasClass('collapsed')) {
                        $(this).click();
                    }
        		});
    		});
        }); 
});

function ksCheckRequired() 
{
	require([
		'jquery'
		], function ($) {
			$(document).ready(function() {
				if ($("#ks_check_money_status").val() == 1) {
					$('#ks-payee_name').addClass('required-entry');
					$('#ks-payee_name').parents('.ks-form-field').addClass('required');
				}

				if ($("#ks_paypal_status").val() == 1) {
					$('#ks_paypal_associated_email').addClass('required-entry');
					$('#ks_paypal_associated_email').parents('.ks-form-field').addClass('required');
				}

				if ($("#ks_bank_transfer_status").val() == 1) {
					$('#ks_pri_acc_holder_name').addClass('required-entry');
					$('#ks_pri_acc_holder_name').parents('.ks-form-field').addClass('required');
					$('#ks_pri_bank_acc_no').addClass('required-entry');
					$('#ks_pri_bank_acc_no').parents('.ks-form-field').addClass('required');
				}


				if ($("#ks_additional_payment_method_one_status").val() == 1) {
					$('#ks_additional_payment_method_one_name').addClass('required-entry');
					$('#ks_additional_payment_method_one_name').parents('.ks-form-field').addClass('required');
				}

				if ($("#ks_additional_payment_method_two_status").val() == 1) {
					$('#ks_additional_payment_method_two_name').addClass('required-entry');
					$('#ks_additional_payment_method_two_name').parents('.ks-form-field').addClass('required');
				}
			});
		});

}
