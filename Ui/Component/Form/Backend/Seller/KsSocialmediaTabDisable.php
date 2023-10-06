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
 * Class KsSocialmediaTabDisable.
 */
class KsSocialmediaTabDisable extends Fieldset implements ComponentVisibilityInterface
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
     * Show/Hide social media tab.
     *
     * @return boolean
     */
    public function isComponentVisible(): bool
    {
        $ksIsSocialmediaEnabled = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_social_media');
        if ($ksIsSocialmediaEnabled) {
            return (bool) $ksIsSocialmediaEnabled;
        } else {
            return (bool) $ksIsSocialmediaEnabled;
        }
    }
}
