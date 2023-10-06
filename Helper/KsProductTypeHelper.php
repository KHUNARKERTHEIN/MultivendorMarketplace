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

namespace Ksolves\MultivendorMarketplace\Helper;

use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Catalog\Model\Product;

/**
 * KsProductTypeHelper Class
 */
class KsProductTypeHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $ksCustomerCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $ksProductPriceIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $ksProductAction;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProduct;

    /**
     * @var TypeFactory
     */
    protected $ksTypeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResourceConnection;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $ksAttributeRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksProductPriceIndexerProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Catalog\Model\Product\Action $ksProductAction,
     * @param Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param \Magento\Catalog\Model\Product\TypeFactory $ksTypeFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     * @param \Magento\Framework\App\ResourceConnection $ksResourceConnection
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $ksAttributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $ksProductPriceIndexerProcessor,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Catalog\Model\Product\Action $ksProductAction,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Magento\Catalog\Model\Product\TypeFactory $ksTypeFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Magento\Framework\App\ResourceConnection $ksResourceConnection,
        \Magento\Eav\Api\AttributeRepositoryInterface $ksAttributeRepository
    ) {
        $this->ksCustomerCollectionFactory = $ksCustomerCollectionFactory;
        $this->ksProductPriceIndexerProcessor = $ksProductPriceIndexerProcessor;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksProductAction = $ksProductAction;
        $this->ksProduct = $ksProduct;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksTypeFactory     = $ksTypeFactory;
        $this->ksProductHelper   = $ksProductHelper;
        $this->ksResourceConnection = $ksResourceConnection;
        $this->ksAttributeRepository = $ksAttributeRepository;
        parent::__construct($ksContext);
    }

    /**
     * Get Seller details
     *
     * @param  int $ksId
     * @return string array
     */
    public function getKsSellerInfo($ksId)
    {
        $ksCustomerCollection = $this->ksCustomerCollectionFactory->create()->addFieldToFilter('entity_id', $ksId);
        $ksSeller = [];
        foreach ($ksCustomerCollection as $ksCustomer) {
            $ksSeller['email'] = $ksCustomer->getEmail();
            $ksSeller['name'] = $ksCustomer->getName();
        }
        return $ksSeller;
    }

    /**
     * Get Customer DOB
     *
     * @param  int $ksId
     * @return date
     */
    public function getKsCustomerDOB($ksId)
    {
        $ksCustomerCollection = $this->ksCustomerCollectionFactory->create()->addFieldToFilter('entity_id', $ksId);
        foreach ($ksCustomerCollection as $ksCustomer) {
            return $ksCustomer->getDob();
        }
    }

    /**
     * get unassign product type ProductIds
     *
     * @param  int $ksSellerId
     * @param  int $ksProductType
     * @return array $ksProductIds
     */
    public function disableKsUnassignTypeProductIds($ksSellerId, $ksProductTypes)
    {
        $KsProductCollection = $this->ksProduct->create()
                                ->getCollection()
                                ->addFieldToFilter('ks_parent_product_id', 0);
        if (!is_null($ksSellerId)) {
            $KsProductCollection->addFieldToFilter('ks_seller_id', $ksSellerId);
        }

        $ksJoinTable = $KsProductCollection->getTable('catalog_product_entity');
        $KsProductCollection->getSelect()->join(
            $ksJoinTable. ' as cpe',
            'cpe.entity_id = main_table.ks_product_id',
            [
                'type_id'    =>'type_id'
            ]
        )->where('type_id IN (?)', $ksProductTypes);

        $ksProductIds = [];
        foreach ($KsProductCollection as $ksProduct) {
            $ksProductIds[] = $ksProduct->getKsProductId();
        }

        if (!empty($ksProductIds)) {
            $this->disableKsProducts($ksProductIds);
        }
    }

    /**
     * Disable ProductIds
     *
     * @param array $ksProductIds
     */
    public function disableKsProducts($ksProductIds)
    {
        $ksStoreIds = array_keys($this->ksStoreManager->getStores());
        $ksStatus = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        $ksStatusAttributeId = $this->ksAttributeRepository->get(Product::ENTITY, 'status')->getAttributeId();

        try {
            $this->ksProductAction->updateAttributes($ksProductIds, ['status' => $ksStatus], 0);

            $ksConnection = $this->ksResourceConnection->getConnection();
            $ksTableName = $this->ksResourceConnection->getTableName('catalog_product_entity_int');

            //disable other storeviews
            foreach ($ksStoreIds as $ksStoreId) {
                //Select Data from table
                $ksSelect = $ksConnection->select()->from($ksTableName)
                ->where('store_id=?', $ksStoreId)
                ->where('attribute_id=?', $ksStatusAttributeId);

                $ksResult = $ksConnection->fetchAll($ksSelect);

                $ksStoreProductIds = array();
                foreach ($ksResult as $ksValue) {
                    if (in_array($ksValue['entity_id'], $ksProductIds)) {
                        $ksStoreProductIds[] = $ksValue['entity_id'];
                    }
                }

                if (!empty($ksStoreProductIds)) {
                    $this->ksProductAction->updateAttributes($ksStoreProductIds, ['status' => $ksStatus], $ksStoreId);
                }
            }

            $this->ksProductPriceIndexerProcessor->reindexList($ksProductIds);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
        }
    }


    /**
     * Restrict Enable Product By Not Allowed Type
     *
     * @param array $ksProductIds
     */
    public function KsRestrictEnableProductByNotAllowedType($ksProductIds, $ksStatus)
    {
        if ($ksStatus==\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $ksFlag = 0;
            $ksTypes = $this->ksTypeFactory->create()->getTypes();
            $ksProductCol = $this->ksProduct->create()->getCollection()
                        ->addFieldToFilter('ks_product_id', array('in'=>$ksProductIds));

            $ksJoinTable = $ksProductCol->getTable('catalog_product_entity');
            $ksProductCol->getSelect()->join(
                $ksJoinTable. ' as cpe',
                'cpe.entity_id = main_table.ks_product_id',
                [
                    'type_id'    =>'type_id'
                ]
            );

            if ($ksProductCol->getSize()>0) {
                foreach ($ksProductCol as $ksProduct) {
                    $ksSellerId = $ksProduct->getKsSellerId();
                    $ksSellerProductType = $this->ksProductHelper->ksSellerProductList($ksTypes, $ksSellerId);
                    if ((!array_key_exists($ksProduct->getTypeId(), $ksSellerProductType) && $ksProduct->getKsParentProductId()==0) ||
                        $ksProduct->getKsProductApprovalStatus() == KsProduct::KS_STATUS_REJECTED) {
                        if (($key = array_search($ksProduct->getKsProductId(), $ksProductIds)) !== false) {
                            unset($ksProductIds[$key]);
                            $ksFlag++;
                        }
                    }
                }
            }

            if ($ksFlag) {
                $this->ksMessageManager->addErrorMessage(
                    __('We cannot enabled status of rejected product or not allowed type product')
                );
            }
        }
        return $ksProductIds;
    }
}
