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

/**
 * Class KsGenericButton
 */
class KsGenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Registry
     *
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
        $this->ksUrlBuilder   = $ksContext->getUrlBuilder();
        $this->ksCoreRegistry = $ksRegistry;
    }

    /**
     * Return the seller Id.
     *
     * @return int|null
     */
    public function getKsSellerId()
    {
        return $this->ksCoreRegistry->registry('current_seller_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->ksUrlBuilder->getUrl($route, $params);
    }
}
