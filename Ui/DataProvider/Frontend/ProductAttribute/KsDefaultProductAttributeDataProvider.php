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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\ProductAttribute;

use Magento\Catalog\Model\AttributeHandler;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as KsProductCollection;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Catalog\Model\Attribute\Config;

/**
 * KsDefaultProductAttributeDataProvider DataProvider Class For Seller Custom Product Attribute
 */
class KsDefaultProductAttributeDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ksRequestInterface
     */
    protected $ksRequest;

    /**
     * @var ksAttributeConfig
     */
    protected $ksAttributeConfig;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var AttributeManagementInterface
     */
    protected $ksAttributeInterface;

    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $ksRequestFieldName
     * @param AttributeManagementInterface $ksAttributeInterface
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        AttributeManagementInterface $ksAttributeInterface,
        CollectionFactory $ksCollectionFactory,
        RequestInterface $ksRequest,
        Config $ksAttributeConfig,
        KsDataHelper $ksDataHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->ksAttributeInterface = $ksAttributeInterface;
        $this->ksDataHelper = $ksDataHelper;

        // Get Attribute Set If from Configuration
        $ksAttributeSet = $this->ksDataHelper->getKsDefaultAttributes();
        $ksUnassignableAtt = $ksAttributeConfig->getAttributeNames('unassignable');
        // Check Attribute Set is not null
        if (!empty($ksAttributeSet)) {
            // Get Attribute Id
            $ksAssignedAttId = $this->getKsAttributeFromDefaultAttribute($ksAttributeSet);
            $this->collection = $ksCollectionFactory->create()->addFieldToFilter('attribute_code', ['nin' => $ksUnassignableAtt]);
            $this->collection->addFieldToFilter('ks_seller_id', ['eq' => '0'])->addFieldToFilter('attribute_id', ['in' => $ksAssignedAttId])->addFieldToFilter('ks_include_in_marketplace', 1);
        } else {
            $this->collection = $ksCollectionFactory->create()->addFieldToFilter('attribute_code', ['nin' => $ksUnassignableAtt]);
            $this->collection->addFieldToFilter('ks_seller_id', ['eq' => '-1']);
        }
    }

    /**
     * Get Data
     */
    public function getData()
    {
        $ksItems = [];
        foreach ($this->getCollection()->getItems() as $ksAttribute) {
            $ksItems[] = $ksAttribute->toArray();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $ksItems
        ];
    }

    /**
     * Get all the attribute in Attribute Set
     * @param array $ksAttributeSetId
     * @return array $ksAttributeIdArray
     */
    public function getKsAttributeFromDefaultAttribute($ksAttributeSetId)
    {
        // Attribute Id Array
        $ksAttributeIdArray = [];
        // Iterate over Default Given Attribute Id
        foreach ($ksAttributeSetId as $ksValue) {
            // Get Collection of Attribute Set
            $ksArray = $this->ksAttributeInterface->getAttributes(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $ksValue);
            // Iterate to get all the attribute id in attribute set
            foreach ($ksArray as $ksRecord) {
                $ksAttributeIdArray[] = $ksRecord['attribute_id'];
            }
        }
        return array_unique($ksAttributeIdArray);
    }
}
