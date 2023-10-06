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
namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Bundle;

/**
 * Class KsSelection
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Bundle
 */
class KsSelection extends \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection
{
    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/tabs/bundle/ks-selection.phtml';

    /**
     * Initialize bundle option selection block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPriceTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.index %>_price_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            'bundle_options[bundle_options][<%- data.parentIndex %>][' . $this->getFieldName() . '][<%- data.index %>][selection_price_type]'
        )->setOptions(
            $this->_priceType->toOptionArray()
        );
        if ($this->getCanEditPrice() === false) {
            $select->setExtraParams('disabled="disabled"');
        }
        return $select->getHtml();
    }

    /**
     * Retrieve price scope checkbox html
     *
     * @return string
     */
    public function getCheckboxScopeHtml()
    {
        $checkboxHtml = '';
        if ($this->isUsedWebsitePrice()) {
            $fieldsId = $this->getFieldId() . '_<%- data.index %>_price_scope';
            $name = 'bundle_options[bundle_options][<%- data.parentIndex %>][' . $this->getFieldName() . '][<%- data.index %>][default_price_scope]';
            $class = 'bundle-option-price-scope-checkbox';
            $label = __('Use Default Value');
            $disabled = $this->getCanEditPrice() === false ? ' disabled="disabled"' : '';
            $checkboxHtml = '<input type="checkbox" id="' .
                $fieldsId .
                '" class="' .
                $class .
                '" name="' .
                $name .
                '"' .
                $disabled .
                ' value="1" />';
            $checkboxHtml .= '<label class="normal" for="' . $fieldsId . '">' . $label . '</label>';
        }
        return $checkboxHtml;
    }

    /**
     * Retrieve qty type select html
     *
     * @return string
     */
    public function getQtyTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => $this->getFieldId() . '_<%- data.index %>_can_change_qty', 'class' => 'select']
        )->setName(
            'bundle_options[bundle_options][<%- data.parentIndex %>][' . $this->getFieldName() . '][<%- data.index %>][selection_can_change_qty]'
        )->setOptions(
            $this->_yesno->toOptionArray()
        );

        return $select->getHtml();
    }
}
