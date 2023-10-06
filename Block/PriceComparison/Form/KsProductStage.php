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

namespace Ksolves\MultivendorMarketplace\Block\PriceComparison\Form;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsSellerProductCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsProductStage block class
 */
class KsProductStage extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{

    /**
     * Set collection factory
     *
     * @var KsSellerProductCollectionFactory
     * @since 101.0.0
     */
    protected $ksSellerProductCollectionFactory;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsSellerProductCollectionFactory $ksSellerProductCollectionFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsSellerProductCollectionFactory $ksSellerProductCollectionFactory,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksSellerProductCollectionFactory = $ksSellerProductCollectionFactory;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    public function getKsProductStage()
    {
        $ksId = $this->getRequest()->getParam('id');
        $ksProductCollection = $this->ksSellerProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksId);

        foreach ($ksProductCollection as $key => $ksData) {
            return $ksData->getKsProductStage();
        }
        return 1;
    }
}
