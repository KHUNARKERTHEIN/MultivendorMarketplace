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

            $('div.ks-sell-tabs input').each(function() {
                var tab_id = $(this).attr('data-tab');

                $(this).attr('checked',true);

                return false;
            });

            $('#ks-marketplace-contact-form').submit(function(){
                $('#ks-marketplace-contact-submit').attr("disabled",true);
            });
        });
        
});


