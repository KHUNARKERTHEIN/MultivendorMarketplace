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
    ], 
    function ($) {
    	setTimeout(function() {
	    	var path = window.location.href;
	    	$('.ks-nav-item a').each(function(){
	            if($(this).attr('href') == path){
	                $(this).parent().addClass('active');
	                if ($(this).hasClass('dropdown-item')) {
	                	$(this).parent().parent().addClass('show');
	                	$(this).parent().parent().parent().addClass('show');	                	
	                }
	            }
	        });
		}, 250);

		/* for hiding when we log in as sellerer*/
		$('.lac-notification-sticky').css('display', 'none');
});