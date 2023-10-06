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

namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsSellerGroup
 */
class KsSellerGroup implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
     */
    protected $ksSellerGroupFactory;

    /**
     * KsSellerGroup constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupFactory
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupFactory
    ) {
        $this->ksSellerGroupFactory = $ksSellerGroupFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $ksOptions = [];
        //get collection
        $ksData=$this->ksSellerGroupFactory->create()->addFieldToFilter('ks_status', 1);
        foreach ($ksData as $ksItem) {
            $ksOptions[] = ['value' => $ksItem->getId(), 'label' => $ksItem->getKsSellerGroupName()];
        }
        return $ksOptions;
    }
}
