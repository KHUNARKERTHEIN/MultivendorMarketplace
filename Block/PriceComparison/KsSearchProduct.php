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
namespace Ksolves\MultivendorMarketplace\Block\PriceComparison;

use Magento\Framework\View\Element\Template;

/**
 * KsSearchProduct Block Class
 */
class KsSearchProduct extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Magento\Framework\Registry $registry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
    ) {
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksProductHelper = $ksProductHelper;
        parent::__construct($context);
    }

    /**
     * get searched product collection
     * @return mixed
     */
    public function getKsSearchedProductCollection()
    {
        // Get Collection from the registry
        $ksData = $this->ksCoreRegistry->registry('ks_product_collection');

        // Check Data
        if ($ksData) {
            // If data is not empty
            if ($ksData->getData()) {
                return $ksData;
            } else {
                return $ksData->getData();
            }
            // If Data is null then pass false
        } else {
            return false;
        }
    }

    /**
     * get current product
     * @return arrar
     */
    public function getKsCurrentCurrency()
    {
        return $this->ksProductHelper->getKsCurrentCurrencySymbol();
    }
}
