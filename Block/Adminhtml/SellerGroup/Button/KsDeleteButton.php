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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\SellerGroup\Button;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use \Magento\Framework\App\RequestInterface;

/**
 * Class KsDeleteButton
 */
class KsDeleteButton extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Buttons\KsGenericButton implements ButtonProviderInterface
{
    /**
    * @var \Magento\Framework\App\RequestInterface
    */
    protected $ksRequest;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Framework\App\RequestInterface
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Framework\App\RequestInterface $ksRequest
    ) {
        $this->ksCoreRegistry         = $ksRegistry;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksRequest      =       $ksRequest;
        parent::__construct($ksContext, $ksRegistry);
    }

    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->ksRequest->getParam('id')) {
            $ksSellerGroupId = $this->ksCoreRegistry->registry('current_seller_group_id');
            $ksData = [];
            $ksSellerGroupDefault = $this->ksDataHelper->getKsConfigSellerSetting('ks_seller_group');
            if ($ksSellerGroupId!=1 && $ksSellerGroupId!=$ksSellerGroupDefault) {
                $ksData = [
                'label' => __('Delete Seller Group'),
                'class' => 'delete',
                'id' => 'ks-seller-group-delete-button',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this seller group?'
                ) . '\', \'' . $this->getKsDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
            }
            return $ksData;
        }
    }

    /**
     * Get delete url.
     *
     * @return string
     */
    public function getKsDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->ksCoreRegistry->registry('current_seller_group_id')]);
    }
}
