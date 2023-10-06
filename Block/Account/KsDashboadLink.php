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

namespace Ksolves\MultivendorMarketplace\Block\Account;

use Magento\Framework\Session\SessionManagerInterface;

/**
 * KsDashboadLink block class
 */
class KsDashboadLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $ksSession;

    /**
     * @var SessionManagerInterface
     */
    protected $ksCoreSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param SessionManagerInterface $ksCoreSession
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Customer\Model\SessionFactory $ksCustomerSession,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        SessionManagerInterface $ksCoreSession,
        array $ksData = []
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSession = $ksCustomerSession;
        $this->ksCoreSession = $ksCoreSession;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $ksSellerId = $this->ksSession->create()->getCustomer()->getId();
        $ksIsSeller = $this->ksSellerHelper->ksIsSellerApproved($ksSellerId);

        // Check Seller Login is Enable or Not
        $ksEnable = $this->ksDataHelper->getKsConfigLoginAndRegistrationSetting('ks_enable_seller_login');

        // get value from session to check the login from backend
        $ksIsSellerLoginFromAdmin = $this->ksCoreSession->getKsIsLoginFromAdmin();

        if ($ksIsSeller && $ksEnable || $ksIsSellerLoginFromAdmin) {
            $this->ksSellerHelper->ksFlushCache();
            return parent::_toHtml();
        }

        return '';
    }
}
