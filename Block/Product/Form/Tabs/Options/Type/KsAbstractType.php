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

use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price;

/**
 * Class KsAbstractType
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type
 */
class KsAbstractType extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $ksOptionPrice;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @param Context $ksContext
     * @param StoreManagerInterface $ksStoreManager
     * @param CurrencyFactory $ksCurrencyFactory
     * @param Price $ksOptionPrice
     * @param array $data
     */
    public function __construct(
        Context $ksContext,
        StoreManagerInterface $ksStoreManager,
        CurrencyFactory $ksCurrencyFactory,
        Price $ksOptionPrice,
        array $data = []
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksOptionPrice = $ksOptionPrice;
        parent::__construct($ksContext, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'option_price_type',
            $this->getLayout()->addBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                $this->getNameInLayout() . '.option_price_type',
                $this->getNameInLayout()
            )->setData(
                [
                    'id' => 'product_option_<%- data.option_id %>_price_type',
                    'class' => 'select product-option-price-type',
                ]
            )
        );

        $this->getChildBlock(
            'option_price_type'
        )->setName(
            'product[options][<%- data.option_id %>][price_type]'
        )->setOptions(
            $this->ksOptionPrice->toOptionArray()
        );

        return parent::_prepareLayout();
    }

    /**
     * Get html of Price Type select element
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        if ($this->getCanEditPrice() === false) {
            $extraParams .= ' disabled="disabled"';
            $this->getChildBlock('option_price_type');
        }
        $this->getChildBlock('option_price_type')->setExtraParams($extraParams);

        return $this->getChildHtml('option_price_type');
    }

    /**
     * Retrieve currency symbol
     * @return string
     */
    public function getKsCurrentCurrency()
    {
        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }
}
