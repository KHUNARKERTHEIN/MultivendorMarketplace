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

namespace Ksolves\MultivendorMarketplace\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed;

/**
 * Backend for serialized array data
 */
class KsCategoryRequiredapprovalSave extends \Magento\Framework\App\Config\Value
{
    /**
      * @var CollectionFactory
      */
    protected $ksCategoryRequestsAllowedCollection;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param CollectionFactory $ksCategoryRequestsAllowedCollection
    * @param AbstractResource $resource = null
    * @param AbstractDb $resourceCollection = null
    * @param array $data = []
    */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CollectionFactory $ksCategoryRequestsAllowedCollection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksCategoryRequestsAllowedCollection = $ksCategoryRequestsAllowedCollection;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $ksCategoryAutoApproval = ((int) $this->getValue() ==1) ? KsCategoryRequestsAllowed::KS_STATUS_DISABLED : KsCategoryRequestsAllowed::KS_STATUS_ENABLED;

            $ksCollection = $this->ksCategoryRequestsAllowedCollection->create();
            //iterate collection
            foreach ($ksCollection as $ksItem) {
                $ksItem->setKsIsAutoApproved($ksCategoryAutoApproval);
                $ksItem->save();
            }
        }
        return parent::afterSave();
    }
}
