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

/**
 * KsProducts Model class
 */
class KsProducts implements OptionSourceInterface
{
    /**
     * @var product collection
     */
    protected $ksProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @param \Magento\Store\Model\WebsiteFactory $ksWebsiteFactory,
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $ksResource
    ) {
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksResource = $ksResource;
    }
    
    /**
     * Get gender options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksProducctsOption=[];
        $ksCollection = $this->ksProductCollectionFactory->addAttributeToSelect('*');
        $ksCount = 0;
        foreach ($ksCollection as $ksItem) {
            if ($ksCount <= 20) {
                $ksProducctsOption[] = ['value' => $ksItem->getData('entity_id'), 'label' => $ksItem->getData('name')];
                $ksCount++;
            }
        }
        return $ksProducctsOption;
    }
}
