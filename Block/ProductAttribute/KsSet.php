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

namespace Ksolves\MultivendorMarketplace\Block\ProductAttribute;

use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;

/**
 * KsSet Class Form Attribute Set Form
 */
class KsSet extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $ksSetFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $ksTypeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    protected $ksGroupFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface
     */
    protected $ksAttributeMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext,
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $ksSetFactory,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
     * @param \Magento\Customer\Model\Session $ksCustomerSession,,
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder,
     * @param \Magento\Eav\Model\Entity\TypeFactory $ksTypeFactory,
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $ksGroupFactory,
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksCollectionFactory,
     * @param \Magento\Framework\Registry $ksRegistry,
     * @param AttributeMapperInterface $ksAttributeMapper,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $ksSetFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\TypeFactory $ksTypeFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $ksGroupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksCollectionFactory,
        \Magento\Framework\Registry $ksRegistry,
        AttributeMapperInterface $ksAttributeMapper,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksData);
        $this->ksSetFactory = $ksSetFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksJsonEncoder = $jsonEncoder;
        $this->ksTypeFactory = $ksTypeFactory;
        $this->ksGroupFactory = $ksGroupFactory;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksAttributeMapper = $ksAttributeMapper;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getKsStores()
    {
        return $this->ksStoreManager->getStores();
    }

    /**
     * Return Seller id for Set Attribute Edit.
     *
     * @return int
     */
    public function getKsCustomerId()
    {
        return $this->ksCoreRegistry->registry('ks_current_seller_login');
    }

    /**
     * Return Seller id for Set.
     *
     * @return int
     */
    public function getKsSellerId()
    {
        return $this->ksCoreRegistry->registry('ks_current_seller_id');
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getKsAllowedAttributeSet()
    {
        $ksDefaultSet = $this->ksDataHelper->getKsDefaultAttributes();
        $ksSellerId = $this->getKsSellerId();
        $ksSellerSetData = $this->ksSetFactory->create()->getResourceCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->getData();
        $ksSetCollection = $this->ksSetFactory->create()->getResourceCollection()->setEntityTypeFilter(4)->load()->toOptionArray();
        foreach ($ksSetCollection as $ksKey => $ksValue) {
            if (!in_array($ksValue['value'], $ksDefaultSet)) {
                unset($ksSetCollection[$ksKey]);
            } else {
                $ksSetCollection[$ksKey]['label'] = $ksSetCollection[$ksKey]['label']. "[Admin]";
            }
        }
        foreach ($ksSellerSetData as $ksValue) {
            $ksArray = [
                            'value' => $ksValue['attribute_set_id'],
                            'label' => $ksValue['attribute_set_name']
                        ];
            $ksSetCollection[] = $ksArray;
        }
        return $ksSetCollection;
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getKsGroupTreeJson()
    {
        $ksItems = [];
        $ksSetId = $this->getKsSetId();
        $ksSellerId = $this->getKsCustomerId();

        /* @var $groups \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection */
        $ksGroups = $this->ksGroupFactory->create()->getResourceCollection()->setAttributeSetFilter(
            $ksSetId
        )->setSortOrder()->load();

        /* @var $node \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($ksGroups as $ksNode) {
            $ksItem = [];
            $ksItem['text'] = $this->escapeHtml($ksNode->getAttributeGroupName());
            $ksItem['id'] = $ksNode->getAttributeGroupId();
            $ksItem['cls'] = 'folder';
            $ksItem['allowDrop'] = true;
            $ksItem['allowDrag'] = true;
            $ksNodeChildren = $this->ksCollectionFactory->create()->setAttributeGroupFilter(
                $ksNode->getId()
            )->addVisibleFilter()->addFieldToFilter('ks_include_in_marketplace', 1)->load();

            $ksNotAllowedAttribute = [];
            $ksAttributeChildren = $this->ksCollectionFactory->create()->setAttributeGroupFilter(
                $ksNode->getId()
            )->addVisibleFilter()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_attribute_approval_status', ['neq' => '1'])->load();
            foreach ($ksAttributeChildren as $ksValue) {
                $ksNotAllowedAttribute[] = $ksValue->getAttributeId();
            }

            if ($ksNodeChildren->getSize() > 0) {
                $ksItem['children'] = [];
                foreach ($ksNodeChildren->getItems() as $ksChild) {
                    $ksAttributeId = $ksChild->getAttributeId();
                    if (in_array($ksAttributeId, $ksNotAllowedAttribute)) {
                        continue;
                    }
                    $ksItem['children'][] = $this->ksAttributeMapper->map($ksChild);
                }
            }
            $ksItems[] = $ksItem;
        }

        return $this->ksJsonEncoder->encode($ksItems);
    }

    /**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getKsAttributeTreeJson()
    {
        $ksItems = [];
        $ksSellerId = $this->getKsCustomerId();
        $ksSetId = $this->getKsSetId();

        $ksCollection = $this->ksCollectionFactory->create()->setAttributeSetFilter($ksSetId)->load();

        $ksAttributesIds = ['0'];
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($ksCollection->getItems() as $ksItem) {
            $ksAttributesIds[] = $ksItem->getAttributeId();
        }

        $ksAttibuteArr = $this->ksProductHelper->ksGetAttribute($ksSellerId);
        $ksAttributes = $this->ksCollectionFactory->create()->setAttributesExcludeFilter(
            $ksAttributesIds
        )->addVisibleFilter()->addFieldToFilter('main_table.attribute_id', ['in' => $ksAttibuteArr])->load();

        foreach ($ksAttributes as $ksChild) {
            $ksAttr = [
                'text' => $this->escapeHtml($ksChild->getAttributeCode()),
                'id' => $ksChild->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $ksChild->getIsUserDefined(),
                'entity_id' => $ksChild->getEntityId(),
            ];

            $ksItems[] = $ksAttr;
        }

        if (count($ksItems) == 0) {
            $ksItems[] = [
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            ];
        }

        return $this->ksJsonEncoder->encode($ksItems);
    }

    /**
     * Retrieve Attribute Set Save URL
     *
     * @return string
     */
    public function getKsSaveUrl()
    {
        return $this->getUrl('multivendor/productattribute_set/save', ['id' => $this->getKsSetId()]);
    }

    /**
     * Retrieve current Attribute Set object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    public function getKsAttributeSet()
    {
        return $this->ksCoreRegistry->registry('current_attribute_set');
    }

    /**
     * Retrieve current attribute set Id
     *
     * @return int
     */
    public function getKsSetId()
    {
        return $this->getKsAttributeSet()->getId();
    }
}
