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

/**
 * Class KsDeleteButton
 */
class KsDeleteButton extends KsGenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry
    ) {
        $this->ksCoreRegistry         = $ksRegistry;
        parent::__construct($ksContext, $ksRegistry);
    }

    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        $ksSellerId = $this->ksCoreRegistry->registry('current_seller_id');
        $ksData = [];
        if ($ksSellerId) {
            $ksData = [
                'label' => __('Delete Seller'),
                'class' => 'delete',
                'id' => 'ks-seller-delete-button',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this seller?<br>Note - All the related details of this seller (Products, Categories, etc.) will be removed. Only Order details will be stored on this seller.'
                ) . '\', \'' . $this->getKsDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $ksData;
    }

    /**
     * Get delete url.
     *
     * @return string
     */
    public function getKsDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->ksCoreRegistry->registry('current_seller_id')]);
    }
}
