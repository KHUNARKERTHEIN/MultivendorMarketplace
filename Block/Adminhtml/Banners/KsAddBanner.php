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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Banners;

use Magento\Framework\App\Request\Http;

/**
 * KsAddBanner block class
 */
class KsAddBanner extends \Magento\Backend\Block\Template
{
    /**
     * @var Http
     */
    protected $ksRequest;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param Http $ksRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        Http $ksRequest,
        array $data = []
    ) {
        $this->ksRequest = $ksRequest;
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        parent::__construct($ksContext, $data);
    }

    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::seller/ks_add_banner.phtml';

    /**
     * get seller id
     *
     * @return int
     */
    public function getKsSellerId()
    {
        $ksSellerId = $this->ksRequest->getParam('seller_id');
        return $ksSellerId;
    }
    

    /**
     * show seller banner images or not
     *
     * @return int
     */
    public function getKsShowBannerImagesText()
    {
        $ksCollection = $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
        return $ksCollection->getKsShowBanner();
    }
}
