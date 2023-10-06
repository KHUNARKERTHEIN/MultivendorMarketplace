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

use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * KsPaymentDetails block
 */
class KsPaymentDetails extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory
     */
    protected $ksSellerPaymentDetailsCollection;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;


    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory $ksSellerPaymentDetailsCollection
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory $ksSellerPaymentDetailsCollection,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        array $data = []
    ) {
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksSellerPaymentDetailsCollection  = $ksSellerPaymentDetailsCollection;
        parent::__construct($ksContext, $data);
    }

    /**
     * @return int
     */
    public function getKsCurrentSellerId()
    {
        return $this->ksCoreRegistry->registry('current_seller_id');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Payment Methods');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Payment Methods');
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
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * get Seller Payment Deatils Data
     * @return object
     */
    public function getKsPaymentDetailsData()
    {
        //get model data
        $ksSellerPaymentDeatils = "";
        $ksSellerPaymentCollection = $this->ksSellerPaymentDetailsCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsCurrentSellerId());
        foreach ($ksSellerPaymentCollection as $_ksSellerPaymentDeatils) {
            $ksSellerPaymentDeatils = $_ksSellerPaymentDeatils;
        }
        return $ksSellerPaymentDeatils;
    }

    /**
     * @param array $dataToDecode
     * @return string
     */
    public function getKsDecodeData($dataToDecode)
    {
        $ksDecodedData = $this->ksJsonHelper->jsonDecode($dataToDecode);
        return $ksDecodedData;
    }
}
