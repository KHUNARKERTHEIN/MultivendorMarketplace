/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
var config = {

	map: {
	    '*': {            
	        ksConfigProductTab: 'Ksolves_MultivendorMarketplace/js/product/ks-configurable-product',
            'Magento_ConfigurableProduct/js/variations/variations' : 'Ksolves_MultivendorMarketplace/js/product/ks-variations'
	    }
	 },

    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Ksolves_MultivendorMarketplace/js/ks-custom-validation': true
            }
        }
    }
};