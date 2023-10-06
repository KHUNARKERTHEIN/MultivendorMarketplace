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
 	"mage/translate"
 	], function($, url ,$t) { 
 		$('body').on('click','.ks-favourite',function() {
            $('body').trigger('processStart');
        });
    });