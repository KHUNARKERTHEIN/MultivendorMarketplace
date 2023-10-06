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

use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Framework\Registry;

/**
 * Class KsWebsites
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs
 */
class KsWebsites extends \Magento\Backend\Block\Store\Switcher
{

    /**
     * @var string
     */
    protected $ksStoreFromHtml;

    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/tabs/ks-product-websites.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $ksCoreRegistry = null;

    /**
     *
     * @var WebsiteRepositoryInterface
     */
    protected $ksWebsiteRepository;

    /**
     * @param Context $ksContext
     * @param WebsiteFactory $ksWebsiteFactory
     * @param GroupFactory $ksStoreGroupFactory
     * @param StoreFactory $ksStoreFactory
     * @param WebsiteRepositoryInterface $ksWebsiteRepository
     * @param Registry $ksCoreRegistry
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        WebsiteFactory $ksWebsiteFactory,
        GroupFactory $ksStoreGroupFactory,
        StoreFactory $ksStoreFactory,
        WebsiteRepositoryInterface $ksWebsiteRepository,
        Registry $ksCoreRegistry,
        array $ksData = []
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksWebsiteRepository = $ksWebsiteRepository;
        parent::__construct($ksContext, $ksWebsiteFactory, $ksStoreGroupFactory, $ksStoreFactory, $ksData);
    }
    /**
     * Retrieve edited product model instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getKsProduct()
    {
        return $this->ksCoreRegistry->registry('product');
    }

    /**
     * Returns whether product associated with website with $websiteId
     *
     * @param int $ksWebsiteId
     * @return bool
     */
    public function hasKsWebsite($ksWebsiteId)
    {
        return in_array($ksWebsiteId, $this->getKsProduct()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getKsProduct()->getWebsitesReadonly();
    }

    /**
     * @return string|int
     */
    public function getKsDefaultWebsiteId()
    {
        return $this->ksWebsiteRepository->getDefault()->getId();
    }


    /**
     * Get HTML of store chooser
     *
     * @return string
     */
    public function getKsChooseFromStoreHtml()
    {
        if (!$this->ksStoreFromHtml) {
            $this->ksStoreFromHtml .= '<option value="0">' . __('Default Values') . '</option>';
            foreach ($this->getWebsiteCollection() as $ksWebsite) {
                if (!$this->hasKsWebsite($ksWebsite->getId())) {
                    continue;
                }
                $optGroupLabel = $this->escapeHtml($ksWebsite->getName());
                $this->ksStoreFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($ksWebsite) as $ksGroup) {
                    $optGroupName = $this->escapeHtml($ksGroup->getName());
                    $this->ksStoreFromHtml .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($ksGroup) as $ksStore) {
                        $this->ksStoreFromHtml .= '<option value="' . $ksStore->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->ksStoreFromHtml .= $this->escapeHtml($ksStore->getName()) . '</option>';
                    }
                }
                $this->ksStoreFromHtml .= '</optgroup>';
            }
        }
        return $this->ksStoreFromHtml;
    }
}
