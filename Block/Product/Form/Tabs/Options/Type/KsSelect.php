<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type;

/**
 * Class KsSelect
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type
 */
class KsSelect extends \Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type\KsAbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/tabs/options/type/ks-select.phtml';

    /**
     * Return select input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        $this->getChildBlock(
            'option_price_type'
        )->setData(
            'id',
            'product_option_<%- data.id %>_select_<%- data.select_id %>_price_type'
        )->setName(
            'product[options][<%- data.id %>][values][<%- data.select_id %>][price_type]'
        );

        return parent::getPriceTypeSelectHtml($extraParams);
    }
}
