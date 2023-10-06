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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product;

/**
 * KsColumn Class for Adding Columns in the Product Listing Grid
 */
class KsColumns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order value
     */
    const KS_DEFAULT_COLUMNS_MAX_ORDER = 150;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface
     */
    protected $ksAttributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var array
     */
    protected $ksFilterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
        'datetime' => 'datetimeRange',
    ];

    /**
     * @var \Magento\Catalog\Ui\Component\ColumnFactory
     */
    protected $ksColumnFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product\Attribute $ksAttributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $ksColumnFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $ksAttributeFactory,
        \Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product\Attribute\KsAttributeRepository $ksAttributeRepository,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->ksColumnFactory = $ksColumnFactory;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksAttributeRepository = $ksAttributeRepository;
        $this->ksDataHelper = $ksDataHelper;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $ksColumnSortOrder = self::KS_DEFAULT_COLUMNS_MAX_ORDER;
        foreach ($this->ksAttributeRepository->getKsSellerAttributeList() as $ksAttribute) {
            $ksConfig = [];
            if (!isset($this->components[$ksAttribute->getAttributeCode()])) {
                $ksConfig['sortOrder'] = ++$ksColumnSortOrder;
                if ($ksAttribute->getIsFilterableInGrid()) {
                    $ksConfig['filter'] = $this->getFilterType($ksAttribute->getFrontendInput());
                }
                $ksColumn = $this->ksColumnFactory->create($ksAttribute, $this->getContext(), $ksConfig);
                $ksColumn->prepare();
                $this->addComponent($ksAttribute->getAttributeCode(), $ksColumn);
            }
        }

        foreach ($this->ksAttributeRepository->getKsAdminAttributeList() as $ksAttribute) {
            if ($this->ksCheckExistence($ksAttribute->getAttributeId())) {
                $ksConfig = [];
                if (!isset($this->components[$ksAttribute->getAttributeCode()])) {
                    $ksConfig['sortOrder'] = ++$ksColumnSortOrder;
                    if ($ksAttribute->getIsFilterableInGrid()) {
                        $ksConfig['filter'] = $this->getFilterType($ksAttribute->getFrontendInput());
                    }
                    $ksColumn = $this->ksColumnFactory->create($ksAttribute, $this->getContext(), $ksConfig);
                    $ksColumn->prepare();
                    $this->addComponent($ksAttribute->getAttributeCode(), $ksColumn);
                }
            }
        }
        parent::prepare();
    }

    /**
     * Retrieve filter type by $ksFrontendInput
     *
     * @param string $ksFrontendInput
     * @return string
     */
    protected function getFilterType($ksFrontendInput)
    {
        return $this->ksFilterMap[$ksFrontendInput] ?? $this->ksFilterMap['default'];
    }

    /**
     * Check Existence of Attribute in Attribute Set.
     * @param  $ksAttributeId
     * @return bool
     */
    public function ksCheckExistence($ksAttributeId)
    {
        $ksAttributeSet = $this->ksDataHelper->getKsDefaultAttributes();
        $ksCondition = false;
        foreach ($ksAttributeSet as $ksSet) {
            $ksCollection = $this->ksAttributeFactory->create()->setAttributeSetFilter($ksSet)->addFieldToFilter('main_table.attribute_id', $ksAttributeId)->addFieldToFilter('ks_include_in_marketplace', 1);
            if ($ksCollection->getSize()) {
                $ksCondition = true;
            }
        }
        return $ksCondition;
    }
}
