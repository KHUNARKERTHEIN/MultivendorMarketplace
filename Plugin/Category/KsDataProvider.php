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

namespace Ksolves\MultivendorMarketplace\Plugin\Category;

/**
  * class KsDataProvider
 */
class KsDataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;
    
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $ksVersion;
    
    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $ksVersion
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Magento\Framework\App\ProductMetadataInterface $ksVersion
    ) {
        $this->eavConfig = $eavConfig;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksVersion = $ksVersion;
    }

    /**
     * Prepare meta data
     *
     * @param array $result
     * @return array
     * @since 101.0.0
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        if(version_compare($this->ksVersion->getVersion(), '2.4.1', '>')){
            $meta = array_replace_recursive($result, $this->_prepareFieldsMeta(
                $subject->getFieldsMap(),
                $subject->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
            ));
        } else {
            $meta = array_replace_recursive($result, $this->_prepareFieldsMeta(
                $this->_getFieldsMap(),
                $subject->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
            ));
        }
        //check config allowed categories
        if ($this->ksCategoryHelper->getKsGeneralSettingConfig('ks_allowed_categories') == 0) {
            $meta['general']['children']['ks_include_in_marketplace']['arguments']['data']['config']['disabled'] = true;
            $meta['general']['children']['ks_include_in_marketplace']['arguments']['data']['config']['notice'] = 'To update this setting, please select "manual section" option in allowed categories of marketplace.';
        } else {
            $meta['general']['children']['ks_include_in_marketplace']['arguments']['data']['config']['disabled'] = false;
            $meta['general']['children']['ks_include_in_marketplace']['arguments']['data']['config']['notice'] = '';
        }
        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    public function _prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }

    /**
     * List of fields groups and fields.
     *
     * @return array
     * @since 101.0.0
     */
    public function _getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['general'][] = 'ks_include_in_marketplace';
        return $fields;
    }
}
