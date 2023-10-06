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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Store\Model\Store;

/**
 * Class KsOption
 */
class KsOption extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option
{
    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/tabs/options/ks-option.phtml';

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $canEditPrice = $this->getCanEditPrice();
        $canReadPrice = $this->getCanReadPrice();

        $ksTemplates = $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type\KsText')
                ->setCanReadPrice($canReadPrice)
                ->setCanEditPrice($canEditPrice)
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/options/type/ks-text.phtml')
                ->toHtml();

        $ksTemplates .= $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type\KsDate')
                ->setCanReadPrice($canReadPrice)
                ->setCanEditPrice($canEditPrice)
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/options/type/ks-date.phtml')
                ->toHtml();

        $ksTemplates .= $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type\KsFile')
                ->setCanReadPrice($canReadPrice)
                ->setCanEditPrice($canEditPrice)
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/options/type/ks-file.phtml')
                ->toHtml();

        $ksTemplates .= $this->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\Type\KsSelect')
                ->setCanReadPrice($canReadPrice)
                ->setCanEditPrice($canEditPrice)
                ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/options/type/ks-select.phtml')
                ->toHtml();

        return $ksTemplates;
    }

    /**
     * Get Option Values
     *
     * @return \Magento\Framework\DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptionValues()
    {
        $ksOptionsArr = $this->getProduct()->getOptions();
       

        if ($ksOptionsArr == null) {
            $ksOptionsArr = [];
        }

        if (!$this->_values || $this->getIgnoreCaching()) {
            $ksShowPrice = $this->getCanReadPrice();
            $values = [];
            $scope = (int)$this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        
            foreach ($ksOptionsArr as $option) {
                /* @var $option \Magento\Catalog\Model\Product\Option */

                $this->setItemCount($option->getOptionId());
               

                $value = [];

                $value['id'] = $option->getOptionId();
                $value['item_count'] = $this->getItemCount();
                $value['option_id'] = $option->getOptionId();
                $value['title'] = $option->getTitle();
                $value['type'] = $option->getType();
                $value['is_require'] = $option->getIsRequire();
                $value['sort_order'] = $option->getSortOrder();
                $value['can_edit_price'] = $this->getCanEditPrice();
                $value['default_title'] = $option->getDefaultTitle();

                if ($this->getProduct()->getStoreId() != Store::DEFAULT_STORE_ID) {
                    $value['scopeTitleDisabled'] = $option->getStoreTitle() === null ? 'disabled' : null;
                    $value['scopeTitleUseDefaultChecked'] = $option->getStoreTitle() === null ? 'checked' : null;
                    ;
                }

                if ($option->getGroupByType() == ProductCustomOptionInterface::OPTION_GROUP_SELECT) {
                    $value = $this->getOptionValueOfGroupSelect($value, $option, $ksShowPrice, $scope);
                } else {
                    $value['price'] = $ksShowPrice ? $this->getPriceValue(
                        $option->getPrice(),
                        $option->getPriceType()
                    ) : '';
                    $value['price_type'] = $option->getPriceType();
                    $value['sku'] = $option->getSku();
                    $value['max_characters'] = $option->getMaxCharacters();
                    $value['file_extension'] = $option->getFileExtension();
                    $value['image_size_x'] = $option->getImageSizeX();
                    $value['image_size_y'] = $option->getImageSizeY();
                }
                $values[] = new \Magento\Framework\DataObject($value);
            }
            $this->_values = $values;
        }

        return $this->_values;
    }


    /**
     * Get Option Value Of Group Select
     *
     * @param array $value
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param boolean $ksShowPrice
     * @param int $scope
     * @return array
     */
    private function getOptionValueOfGroupSelect($value, $option, $ksShowPrice, $scope)
    {
        $i = 0;
        $ksItemCount = 0;
        foreach ($option->getValues() as $_value) {
            /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
            $value['optionValues'][$i] = [
                'item_count' => max($ksItemCount, $_value->getOptionTypeId()),
                'option_id' => $_value->getOptionId(),
                'option_type_id' => $_value->getOptionTypeId(),
                'title' => $_value->getTitle(),
                'price' => $ksShowPrice ? $this->getPriceValue(
                    $_value->getPrice(),
                    $_value->getPriceType()
                ) : '',
                'price_type' => $ksShowPrice ? $_value->getPriceType() : 0,
                'sku' => $_value->getSku(),
                'sort_order' => $_value->getSortOrder(),
            ];

            $value['optionValues'][$i]['defaultTitle'] = $_value->getDefaultTitle();

            if ($this->getProduct()->getStoreId() != Store::DEFAULT_STORE_ID) {
                $value['optionValues'][$i]['scopeTitleDisabled'] = $_value->getStoreTitle() === null
                    ? 'disabled'
                    : null;
                $value['optionValues'][$i]['scopeTitleUseDefaultChecked'] = $_value->getStoreTitle() === null
                    ? 'checked'
                    : null;
            }
            $i++;
        }
        return $value;
    }
}
