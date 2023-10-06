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
 * KsProductTypes Model class
 */
class KsProductTypes implements OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $ksProductType;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @param \Magento\Store\Model\WebsiteFactory                                                   $ksWebsiteFactory,
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $ksProductType,
        \Magento\Framework\App\ResourceConnection $ksResource
    ) {
        $this->ksProductType = $ksProductType;
        $this->ksResource = $ksResource;
    }


    /**
     * Get Website
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksOptions = $this->ksProductType->toOptionArray();
        $ksData = array_column($ksOptions, 'value');
        $ksIndex = array_search('bundle', $ksData);
        unset($ksOptions[$ksIndex]);
        $ksIndex = array_search('grouped', $ksData);
        unset($ksOptions[$ksIndex]);
        return $ksOptions;
    }
}
