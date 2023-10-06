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

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\ProductAlert\Model\StockFactory;

/**
 * KsProductAlert block class
 */
class KsProductAlert extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{

    /**
     *
     * @var StockFactory
     */
    protected $ksStockFactory;

    /**
     *
     * @var PriceFactory
     */
    protected $ksPriceFactory;

    /**
     * @var StockResolverInterface
     */
    protected $ksStockResolver;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param StockFactory $ksStockFactory
     * @param PriceFactory $ksPriceFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param StockResolverInterface $ksStockResolver
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        StockFactory $ksStockFactory,
        PriceFactory $ksPriceFactory,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        StockResolverInterface $ksStockResolver,
        array $ksData = []
    ) {
        $this->ksStockFactory  = $ksStockFactory;
        $this->ksPriceFactory  = $ksPriceFactory;
        $this->ksStockResolver = $ksStockResolver;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Return alert stock
     * @return \Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection object
     */
    public function getKsStockCollection()
    {
        $ksProductId = $this->getKsProduct()->getId();
        $ksWebsiteId = 0;
        if ($ksStore = $this->getRequest()->getParam('store')) {
            $ksWebsiteId = $this->_storeManager->getStore($ksStore)->getWebsiteId();
        }

        $ksStockCollection = $this->ksStockFactory->create()->getCustomerCollection()->join($ksProductId, $ksWebsiteId);

        foreach ($ksStockCollection->getItems() as $ksItem) {
            $ksWebsite = $this->_storeManager->getWebsite($ksItem->getWebsiteId());
            $ksStock = $this->ksStockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $ksWebsite->getCode());

            $ksItem->setStockName($ksStock->getName());
        }
        return $ksStockCollection;
    }

    /**
     * Return alert price
     * @return collection object
     */
    public function getKsPriceCollection()
    {
        $ksProductId = $this->getKsProduct()->getId();
        $ksWebsiteId = 0;
        if ($ksStore = $this->getRequest()->getParam('store')) {
            $ksWebsiteId = $this->_storeManager->getStore($ksStore)->getWebsiteId();
        }

        return $this->ksPriceFactory->create()->getCustomerCollection()->join($ksProductId, $ksWebsiteId);
    }
}
