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
use Magento\Framework\App\RequestInterface;

/**
 * Class KsFollowersTabDisable.
 */
class KsFollowersTabDisable extends Fieldset implements ComponentVisibilityInterface
{
    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var RequestInterface
     */
    protected $ksRequest;

    /**
     * CustomFieldset constructor.
     *
     * @param ContextInterface $context
     * @param Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData
     * @param RequestInterface $ksRequest
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData,
        RequestInterface $ksRequest,
        array $components = [],
        array $data = []
    ) {
        $this->ksHelperData = $ksHelperData;
        $this->ksRequest = $ksRequest;
        parent::__construct($context, $components, $data);
    }

    /**
     * Show/Hide followers tab.
     *
     * @return boolean
     */
    public function isComponentVisible(): bool
    {
        $ksEnable = $this->isFavSellerModuleEnable();
        
        if ($ksEnable) {
            return (bool)$ksEnable;
        } else {
            return (bool)$ksEnable;
        }
    }

    /**
     * Check favourite seller module status
     * @return boolean
     */
    public function isFavSellerModuleEnable()
    {
        $ksStore = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        return $this->ksHelperData->getKsConfigValue('ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_favourite_seller_enable', $ksStore);
    }
}
