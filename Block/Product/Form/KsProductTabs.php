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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsProductTabs block class
 */
class KsProductTabs extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $ksSearchCriteriaBuilder;

    /**
     * @var ProductAttributeGroupRepositoryInterface
     */
    protected $ksAttributeGroupRepository;

    /**
     * @var AttributeGroupInterface[]
     */
    private $ksAttributeGroups = [];

    /**
     * @var AttributeCollectionFactory
     */
    private $ksAttributeCollectionFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $ksAttributeRepository;

    /**
     * @var KsSortOrderBuilder
     * @since 101.0.0
     */
    protected $ksSortOrderBuilder;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param SearchCriteriaBuilder $ksSearchCriteriaBuilder
     * @param ProductAttributeGroupRepositoryInterface $ksAttributeGroupRepository
     * @param AttributeCollectionFactory $ksAttributeCollectionFactory = null
     * @param ProductAttributeRepositoryInterface $ksAttributeRepository
     * @param SortOrderBuilder $ksSortOrderBuilder
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        SearchCriteriaBuilder $ksSearchCriteriaBuilder,
        ProductAttributeGroupRepositoryInterface $ksAttributeGroupRepository,
        AttributeCollectionFactory $ksAttributeCollectionFactory = null,
        ProductAttributeRepositoryInterface $ksAttributeRepository,
        SortOrderBuilder $ksSortOrderBuilder,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksSearchCriteriaBuilder = $ksSearchCriteriaBuilder;
        $this->ksAttributeGroupRepository = $ksAttributeGroupRepository;
        $this->ksAttributeCollectionFactory = $ksAttributeCollectionFactory
            ?: ObjectManager::getInstance()->get(AttributeCollectionFactory::class);
        $this->ksAttributeRepository = $ksAttributeRepository;
        $this->ksSortOrderBuilder = $ksSortOrderBuilder;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $ksTabAttributesBlock = $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsAttribute')
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/ks-eav-tabs.phtml');

        foreach ($this->getKsAttributeGroups() as $ksGroupCode => $ksGroup) {
            $ksGroupCode = $ksGroup->getAttributeGroupCode();
            $ksExcludeArray = ['design','schedule-design-update','advanced-pricing'];

            if (in_array($ksGroupCode, $ksExcludeArray)) {
                continue;
            }

            $ksAttributes = !empty($this->getKsAttributes($ksGroup)) ? $this->getKsAttributes($ksGroup) : [];
            if ($ksAttributes) {
                if ($ksGroupCode =='image-management') {
                    $ksTabImagesBlock = $this->getLayout()
                            ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Gallery\KsContent')
                            ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/gallery/ks-gallery.phtml');

                    $tabData = [
                            'label' => __('Images And Videos'),
                            'content' => $ksTabImagesBlock->setProductMediaAttributes($ksAttributes)->toHtml(),
                            'group_code' => $ksGroupCode,
                        ];
                    $this->addKsTab($ksGroupCode, $tabData);
                } else {
                    $tabData = [
                                'label' => __($ksGroup->getAttributeGroupName()),
                                'content' => $ksTabAttributesBlock->setGroup($ksGroup)->setGroupAttributes($ksAttributes)->toHtml(),
                                'group_code' => $ksGroupCode,
                            ];
                    $this->addKsTab($ksGroupCode, $tabData);
                }
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve groups
     *
     * @return AttributeGroupInterface[]
     */
    protected function getKsAttributeGroups()
    {
        if (!$this->ksAttributeGroups) {
            $ksSearchCriteria = $this->ksPrepareGroupSearchCriteria()->create();
            $ksAttributeGroupSearchResult = $this->ksAttributeGroupRepository->getList($ksSearchCriteria);
            foreach ($ksAttributeGroupSearchResult->getItems() as $ksGroup) {
                $this->ksAttributeGroups[$ksGroup->getAttributeGroupCode()] = $ksGroup;
            }
        }

        return $this->ksAttributeGroups;
    }

    /**
     * Initialize attribute group search criteria with filters.
     *
     * @return SearchCriteriaBuilder
     */
    protected function ksPrepareGroupSearchCriteria()
    {
        return $this->ksSearchCriteriaBuilder->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getKsAttributeSetId()
        );
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    protected function getKsAttributeSetId()
    {
        $ksAttributeSetId = (int) $this->getRequest()->getParam('set');
        if (!$ksAttributeSetId) {
            $ksAttributeSetId = $this->getKsProduct()->getAttributeSetId();
        }
        return $ksAttributeSetId;
    }

    /**
     * Loads attributes for specified groups
     *
     * @param AttributeGroupInterface[] $groups
     * @return ProductAttributeInterface[]
     */
    public function getKsAttributes($ksGroup)
    {
        $ksAttributes = [];

        $ksSortOrder = $this->ksSortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();

        $ksSearchCriteria = $this->ksSearchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $ksGroup->getAttributeGroupId())
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($ksSortOrder)
            ->create();

        $ksGroupAttributes = $this->ksAttributeRepository->getList($ksSearchCriteria)->getItems();

        $ksProductType = $this->getKsProduct()->getTypeId();

        foreach ($ksGroupAttributes as $ksAttribute) {
            // Check this Attribute is included in Marketplace or not
            if ($ksAttribute->getKsIncludeInMarketplace()) {
                $ksApplyTo = $ksAttribute->getApplyTo();
                $ksIsRelated = !$ksApplyTo || in_array($ksProductType, $ksApplyTo);
                if ($ksIsRelated) {
                    $ksAttributes[] = $ksAttribute;
                }
            }
        }

        return $ksAttributes;
    }
}
