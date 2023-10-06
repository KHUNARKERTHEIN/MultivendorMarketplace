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
class KsSellerGroupOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
     */
    protected $ksSellerGroupFactory;

    /**
    * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
    */
    protected $ksDataHelper;

    /**
     * KsSellerGroup constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupFactory
     *  @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
    ) {
        $this->ksSellerGroupFactory = $ksSellerGroupFactory;
        $this->ksDataHelper         = $ksDataHelper;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $ksOptions = [];
        //get default seller group id from configuration
        $ksSellerGroupId= $this->ksDataHelper->getKsConfigSellerSetting('ks_seller_group');
        //get collection
        $ksData=$this->ksSellerGroupFactory->create()->addFieldToFilter('ks_status', 1);
        $ksDefaultData=$this->ksSellerGroupFactory->create()->addFieldToFilter('ks_status', 1)->addFieldToFilter('id', $ksSellerGroupId);
        $ksOptions[] = ['value' => $ksSellerGroupId, 'label' => $ksDefaultData->getFirstItem()->getKsSellerGroupName()];
        foreach ($ksData as $ksItem) {
            if ($ksItem->getId() != $ksSellerGroupId) {
                $ksOptions[] = ['value' => $ksItem->getId(), 'label' => $ksItem->getKsSellerGroupName()];
            }
        }
        return $ksOptions;
    }
}
