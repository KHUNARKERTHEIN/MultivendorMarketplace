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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Form\Backend\Seller;

use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class KsStorepolicyTabDisable.
 */
class KsStorepolicyTabDisable extends Fieldset implements ComponentVisibilityInterface
{

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper
     */
    protected $KsSellerDashboardMyProfileHelper;

    /**
     * CustomFieldset constructor.
     *
     * @param ContextInterface $context
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper
     * @param array $components
     * @param array $data
     */

    public function __construct(
        ContextInterface $context,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->KsSellerDashboardMyProfileHelper = $KsSellerDashboardMyProfileHelper;
    }

    /**
     * Show/Hide store policies tab.
     *
     * @return boolean
     */
    public function isComponentVisible(): bool
    {
        $ksIsRefundpolicyEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_refund_policy');
        $ksIsPrivacypolicyEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_privacy_policy');
        $ksIsShippingpolicyEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_shipping_policy');
        $ksIsLegalpolicyEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_legal_notice');
        $ksIsTermsOfServiceEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_terms_of_service');
        if ($ksIsRefundpolicyEnabled ||  $ksIsPrivacypolicyEnabled ||   $ksIsShippingpolicyEnabled ||   $ksIsLegalpolicyEnabled ||  $ksIsTermsOfServiceEnabled) {
            return (bool) true;
        } else {
            return (bool) false;
        }
    }
}
