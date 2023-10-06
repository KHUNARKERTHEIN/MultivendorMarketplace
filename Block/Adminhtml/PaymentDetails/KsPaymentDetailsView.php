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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\PaymentDetails;

/**
 * KsPaymentDetailsView block class
 */
class KsPaymentDetailsView extends \Magento\Backend\Block\Template
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory
     */
    protected $ksSellerPaymentDetailsCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeInterface;

    /**
     * @var \Magento\Framework\Json\Helper\Data $ksJsonHelper
     */
    protected $ksJsonHelper;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory $ksSellerPaymentDetailsCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerPaymentDetails\CollectionFactory $ksSellerPaymentDetailsCollection,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        array $data = []
    ) {
        $this->ksSellerPaymentDetailsCollection = $ksSellerPaymentDetailsCollection;
        $this->ksScopeInterface = $ksScopeInterface;
        $this->ksRegistry = $ksRegistry;
        $this->ksJsonHelper = $ksJsonHelper;

        parent::__construct($ksContext, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Ksolves_MultivendorMarketplace::payment-details/ks_payment_details_view.phtml');
    }
    
    /**
     * Get Seller Id
     * @return int
     */
    public function getKsSellerId()
    {
        //return seller id
        if ($this->getRequest()->getParam('ks_seller_id')) {
            return $this->getRequest()->getParam('ks_seller_id');
        } else {
            return $this->ksRegistry->registry('ks_seller_id');
        }
    }
  
    /**
     * Get Store Id
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->getRequest()->getParam('store') ? $this->getRequest()->getParam('store') : 0;
    }

    /**
     * Get Base Url
     * @return string
     */
    public function getKsBaseUrl()
    {
        return $this->ksScopeInterface->getValue("web/secure/base_url");
    }

    /**
     * Get payment Details Data
     * @param int $sellerid
     * @return array
     */
    public function getKsPaymentData($sellerid)
    {
        return $this->ksSellerPaymentDetailsCollection->create()->addFieldToFilter('ks_seller_id', $sellerid)->getFirstItem()->getData();
    }

    /**
     * Get Account Details
     * @return array
     */
    public function getKsDecodeData($ksJson)
    {
        return  $this->ksJsonHelper->jsonDecode($ksJson);
    }
}
