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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Tabs;

/**
 * KsCategoryView Block class
 */
class KsCategoryView extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
     /**
     * @var \Magento\Framework\Registry 
     */
    protected $ksCoreRegistry;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\App\Request\Http $ksRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\App\Request\Http $ksRequest,
        array $ksData = []
    ) {
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksRequest = $ksRequest;
        parent::__construct($ksContext, $ksData);
    }
 
    /**
     * @return int
     */
    public function getKsCurrentSellerId()
    {
        return $this->ksCoreRegistry->registry('current_seller_id');
    }
    
    /**
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product Categories');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Categories');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('multivendor/categoryrequests/categorytree', [
                                    'ks_seller_id' => $this->getKsCurrentSellerId(),
                                    'store' => $this->getKsStoreId(),
                                ]);
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}
