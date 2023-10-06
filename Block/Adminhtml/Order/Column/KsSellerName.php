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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\TruncateFilter\Result;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

/**
 * Sales Order items seller name column renderer
 */
class KsSellerName extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
    * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
    */
    protected $ksProductHelper;

    /**
    * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
    */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $ksPurchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $ksItemsFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeInterface;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param array $data
     * @param CatalogHelper|null $catalogHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        KsProductHelper $ksProductHelper,
        KsSellerHelper  $ksSellerHelper,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface,
        array $data = [],
        ?CatalogHelper $catalogHelper = null,
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksPurchasedFactory = $ksPurchasedFactory;
        $this->ksItemsFactory = $ksItemsFactory;
        $this->ksScopeInterface = $ksScopeInterface;
        $data['catalogHelper'] = $catalogHelper ?? ObjectManager::getInstance()->get(CatalogHelper::class);
        parent::__construct($ksContext, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    /**
     * @var Result
     */
    private $truncateResult = null;

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        $this->truncateResult = $this->filterManager->truncateFilter(
            $value,
            ['length' => $length, 'etc' => $etc, 'breakWords' => $breakWords]
        );
        return $this->truncateResult->getValue();
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $remainder = '';
        $this->truncateString($value, 55, '', $remainder);
        $result = [
            'value' => nl2br($this->truncateResult->getValue()),
            'remainder' => nl2br($this->truncateResult->getRemainder())
        ];

        return $result;
    }

    /**
     * Get Seller Id
     *
     * @param integer $ksProductId
     * @return integer
     */
    public function getKsSellerId($ksProductId)
    {
        return $this->ksProductHelper->getKsSellerId($ksProductId);
    }

    /**
     * Get Seller Name By Seller Id
     *
     * @param integer %$ksSellerId
     * @return string
     */
    public function getKsSellerName($ksSellerId)
    {
        return $this->ksSellerHelper->getKsSellerName($ksSellerId);
    }

    /**
     * Return purchased links.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return Object
     */
    public function getKsDownloadableItemData($ksItem)
    {
        $this->ksPurchased = $this->ksPurchasedFactory->create()->load(
            $ksItem->getId(),
            'order_item_id'
        );
        $ksPurchasedItem = $this->ksItemsFactory->create()->addFieldToFilter('order_item_id', $ksItem->getId());
        $this->ksPurchased->setPurchasedItems($ksPurchasedItem);
        return $this->ksPurchased;
    }

    /**
     * Retunrn links title.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return null|string
     */
    public function getKsDownloadableLinkTitle($ksItem)
    {
        return $this->getKsDownloadableItemData($ksItem)->getLinkSectionTitle() ?: $this->ksScopeInterface->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
