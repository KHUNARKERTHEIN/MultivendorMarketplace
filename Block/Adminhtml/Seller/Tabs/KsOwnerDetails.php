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
 * KsOwnerDetails block
 */
class KsOwnerDetails extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $ksAddressRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $ksGroupRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Customer\Model\Options
     */
    protected $ksCustomerOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $ksAddressRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $ksGroupRepository
     * @param \Magento\Customer\Model\Options $ksCustomerOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $ksAddressRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $ksGroupRepository,
        \Magento\Customer\Model\Options $ksCustomerOptions,
        array $data = []
    ) {
        $this->ksCoreRegistry         = $ksRegistry;
        $this->ksSellerHelper         = $ksSellerHelper;
        $this->ksDataHelper           = $ksDataHelper;
        $this->ksCustomerFactory      = $ksCustomerFactory;
        $this->ksAddressRepository    = $ksAddressRepository;
        $this->ksGroupRepository      = $ksGroupRepository;
        $this->ksCustomerOptions      = $ksCustomerOptions;
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
        return __('Customer/Owner Details');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Customer/Owner Details');
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
     * get Seller Data
     * @return object
     */
    public function getKsOwnerData()
    {
        return $this->ksCustomerFactory->create()->load($this->getKsCurrentSellerId());
    }

    /**
     * get contact number
     * @param $ksBillingAddressId
     * @return string
     */
    public function getKsOwnerContactNumber($ksBillingAddressId)
    {
        $ksBillingAddress = $this->ksAddressRepository->getById($ksBillingAddressId);
        return $ksBillingAddress->getTelephone();
    }

    /**
     * get contact number
     * @param $ksBillingAddressId
     * @return string
     */
    public function getKsOwnerCompany($ksBillingAddressId)
    {
        $ksBillingAddress = $this->ksAddressRepository->getById($ksBillingAddressId);
        return $ksBillingAddress->getCompany();
    }

    /**
     * get contact number
     * @param $ksBillingAddressId
     * @return string
     */
    public function getKsOwnerFax($ksBillingAddressId)
    {
        $ksBillingAddress = $this->ksAddressRepository->getById($ksBillingAddressId);
        return $ksBillingAddress->getFax();
    }

    /**
     * Retrieve customer group by groupid
     *
     * @param int $ksGroupId
     * @return string
     */
    public function getKsOwnerGroup($ksGroupId)
    {
        $group = $this->ksGroupRepository->getById($ksGroupId);
        return $group->getCode();
    }

    /**
     * get customer field config filed value
     *
     * @param string $ksCode
     * @param int $ksWebsiteId
     * @return string
     */
    public function getKsCustomerConfig($ksCode, $ksWebsiteId)
    {
        return $this->ksDataHelper->getKsConfigCustomerField($ksWebsiteId, $ksCode);
    }
}
