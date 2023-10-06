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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsConfigurable block class
 */
class KsConfigurable extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var Configurable
     */
    protected $ksConfigurableProductType;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param Configurable $ksConfigurableProductType
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        Configurable  $ksConfigurableProductType,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksConfigurableProductType = $ksConfigurableProductType;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * get variations
     *
     * @return bool
     */
    public function getKsVariationsMatrix()
    {
        return $this->getChildBlock('ks.product.matrix')->toHtml();
    }

    /**
     * @return bool
     */
    public function isHasVariations()
    {
        $ksAssociatedProductCollection = $this->ksConfigurableProductType
                                        ->getUsedProductCollection($this->getKsProduct())
                                        ->setFlag('has_stock_status_filter', true);

        $ksAssociateproducts = [];
        foreach ($ksAssociatedProductCollection as $ksAssociatedProduct) {
            if ($ksAssociatedProduct->getData('has_options')) {
                continue;
            }
            $ksAssociateproducts[] = $ksAssociatedProduct->getEntityId();
        }

        return $this->getKsProduct()->getTypeId() === Configurable::TYPE_CODE
             && count($ksAssociateproducts);
    }
}
