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

namespace Ksolves\MultivendorMarketplace\Model\Source\CommissionRule;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\OptionSourceInterface;
use \Ksolves\MultivendorMarketplace\Model\KsSellerFactory;

/**
 * KsWebsites Model class
 */
class KsWebsites implements OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $ksWebsiteFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @param \Magento\Store\Model\WebsiteFactory                                                   $ksWebsiteFactory,
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $ksWebsiteFactory,
        \Magento\Framework\App\ResourceConnection $ksResource
    ) {
        $this->ksWebsiteFactory = $ksWebsiteFactory;
        $this->ksResource = $ksResource;
    }
   
    /**
     * Get Website
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksOptions = [];
        $ksOptions[] =  ['value'=>'null', 'label'=>'Select Website'];
        $ksOptions = array_merge($ksOptions, $this->ksWebsiteFactory->create()->getCollection()->toOptionArray());
        return $ksOptions;
    }
}
