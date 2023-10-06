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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\PriceComparison\Tabs;

/**
 * KsDownloadableProduct block
 */
class KsDownloadableProduct extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ksolves_MultivendorMarketplace::product/ks_downloadable.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        array $data = []
    ) {
        parent::__construct($ksContext, $data);
        $this->ksRegistry = $ksRegistry;
    }

    /**
     * get seller product
     *
     * @return object
     */
    public function getKsSellerProduct()
    {
        return $this->ksRegistry->registry('ks_product_modal');
    }

    /**
     * Check is readonly block
     *
     * @return boolean
     */
    public function KsIsReadonly()
    {
        return $this->getKsSellerProduct()->getDownloadableReadonly();
    }
}
