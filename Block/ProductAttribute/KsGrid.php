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

use Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid;

/**
 * KsGrid Block Class For Overriding Catalog Grid
 */
class KsGrid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $_module = 'catalog';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksCollectionFactory,
        array $data = []
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare product attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $ksCollection = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', ['eq' => 0])->addVisibleFilter();
        $this->setCollection($ksCollection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare product attributes grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'is_visible',
            [
                'header' => __('Visible'),
                'sortable' => true,
                'index' => 'is_visible_on_front',
                'type' => 'options',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'align' => 'center'
            ],
            'frontend_label'
        );

        $this->addColumnAfter(
            'is_global',
            [
                'header' => __('Scope'),
                'sortable' => true,
                'index' => 'is_global',
                'type' => 'options',
                'options' => [
                    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE => __('Store View'),
                    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE => __('Web Site'),
                    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL => __('Global'),
                ],
                'align' => 'center'
            ],
            'is_visible'
        );

        $this->addColumn(
            'is_searchable',
            [
                'header' => __('Searchable'),
                'sortable' => true,
                'index' => 'is_searchable',
                'type' => 'options',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'align' => 'center'
            ]
        );

        $this->_eventManager->dispatch('product_attribute_grid_build', ['grid' => $this]);

        $this->addColumnAfter(
            'is_comparable',
            [
                'header' => __('Comparable'),
                'sortable' => true,
                'index' => 'is_comparable',
                'type' => 'options',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'align' => 'center'
            ],
            'is_filterable'
        );
        return $this;
    }
}
