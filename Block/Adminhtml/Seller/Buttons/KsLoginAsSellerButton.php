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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;

/**
 * Class KsLoginAsSellerButton
 */
class KsLoginAsSellerButton extends KsGenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param KsSellerFactory $ksSellerFactory
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        KsSellerFactory $ksSellerFactory
    ) {
        $this->ksCoreRegistry     = $ksRegistry;
        $this->ksSellerFactory    = $ksSellerFactory;
        parent::__construct($ksContext, $ksRegistry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $ksSellerId = $this->ksCoreRegistry->registry('current_seller_id');
        $ksSellerModel = $this->ksSellerFactory->create()->load($ksSellerId, 'ks_seller_id');
        $ksData = [];
        if ($ksSellerModel->getKsSellerStatus() == 1) {
            $ksData = [
                'label' => __('Login As Seller'),
                'on_click' => sprintf("window.open('%s', '_blank');", $this->getKsLoginAsSellerUrl()),
                'class' => 'login-as-seller',
                'sort_order' => 15,
                'target' => '_blank'
            ];
        }
        return $ksData;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getKsLoginAsSellerUrl()
    {
        $ksSellerId = $this->getKsSellerId();
        return $this->getUrl('multivendor/seller/loginasseller', ['seller_id' => $ksSellerId]);
    }
}
