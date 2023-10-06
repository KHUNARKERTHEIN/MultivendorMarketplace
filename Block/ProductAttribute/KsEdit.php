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

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsEdit Block Class For Edit Page in Seller Panel
 */
class KsEdit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $ksRegistry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ksRegistry = $ksRegistry;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksJsonHelper = $ksJsonHelper;
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
     * Retrieve frontend labels of attribute for each store
     *
     * @return array
     */
    public function getKsLabelValues()
    {
        $ksValues = (array)$this->getKsAttributeObject()->getFrontend()->getLabel();
        $ksData = $this->getKsAttributeData() ? $this->getKsAttributeData() : [];
        $ksStoreLabels = $this->getKsAttributeObject()->getStoreLabels();
        foreach ($this->getKsStores() as $ksStore) {
            if ($ksStore->getId() != 0) {
                $ksValues[$ksStore->getId()] = isset($ksStoreLabels[$ksStore->getId()]) ? $ksStoreLabels[$ksStore->getId()] : '';
            }
        }
        if ($ksData) {
            $ksValues = [];
        }
        return $ksValues;
    }

    /**
     * Returns stores sorted by Sort Order
     *
     * @return array
     */
    public function getKsStoresSortedBySortOrder()
    {
        $ksStores = $this->getKsStores();
        if (is_array($ksStores)) {
            usort(
                $ksStores,
                function ($ksStoreA, $ksStoreB) {
                    if ($ksStoreA->getSortOrder() == $ksStoreB->getSortOrder()) {
                        return $ksStoreA->getId() < $ksStoreB->getId() ? -1 : 1;
                    }

                    return ($ksStoreA->getSortOrder() < $ksStoreB->getSortOrder()) ? -1 : 1;
                }
            );
        }
        return $ksStores;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getKsAttributeObject()
    {
        return $this->ksRegistry->registry('entity_attribute');
    }

    /**
     * Check Request Allowed
     *
     * @return int
     */
    public function getKsRequestAllowed()
    {
        $ksSellerId = $this->ksRegistry->registry('ks_seller_login_id');
        return $this->ksSellerHelper->getksProductAttributeRequestAllowed($ksSellerId);
    }


    /**
     * Condition for Deleting Product Attribute
     * @return bool
     */
    public function hasKsDeleteCondition()
    {
        $ksData = $this->getKsAttributeObject()->getData();
        if ($ksData['frontend_input'] == null) {
            unset($ksData['entity_type_id']);
            unset($ksData['frontend_input']);
        }
        $ksCondition = false;
        if ($ksData) {
            if ($ksData['ks_seller_id'] != 0) {
                $ksCondition = true;
            }
        }
        return $ksCondition;
    }


    /**
     * Condition for Deleting Product Attribute
     * @return int
     */
    public function getKsAttributeId()
    {
        return $this->getKsAttributeObject()->getAttributeId();
    }

    /**
     * Get Attribute Data if any Error occur
     * @return array
     */
    public function getKsAttributeData()
    {
        $ksData =  $this->ksDataPersistor->get('ks_product_attribute');
        if ($ksData) {
            $ksData['ks_seller_id'] = $this->ksRegistry->registry('ks_seller_login_id');
        }
        return $ksData;
    }

    /**
     * Clear Data Persistor Data
     */
    public function clearDataPersistorAttribute()
    {
        $this->ksDataPersistor->clear('ks_product_attribute');
    }

    /**
     * Get Option Data
     */
    public function getKsOptions()
    {
        $ksData = $this->getKsAttributeData() ? $this->getKsAttributeData() : [];
        $ksDynamicVal = [];
        $ksDefault = [];
        if (array_key_exists('default', $ksData)) {
            $ksDefault = $ksData['default'];
        }
        if (array_key_exists('option', $ksData)) {
            $ksOptions = $ksData['option'];
            $ksValues = $ksOptions['value'];
            $ksInt = 1;
            $ksOp = 0;
            $ksId = 'option_';
            foreach ($ksValues as $ksValue) {
                $ksDyn = [];
                $ksDyn['checked'] = '';
                $ksOptionValue = $ksId.$ksOp;
                
                if (in_array($ksOptionValue, $ksDefault)) {
                    $ksDyn['checked'] = 'checked="checked"';
                }
                $ksDyn['intype'] = $ksData['frontend_input'] == 'multiselect' ? 'checkbox' : 'radio';
                $ksDyn['id'] = $ksId.$ksOp;
                $ksDyn['sort_order'] = $ksInt++;
                foreach ($ksValue as $key => $value) {
                    $ksDyn['store'.$key] = $value;
                }
                $ksDynamicVal[] = $ksDyn;
                $ksOp++;
            }
        }
        return $ksDynamicVal;
    }


    /**
     * Retrieve frontend labels of attribute for each store when error occur
     *
     * @return array
     */
    public function getKsLabelValuesError()
    {
        $ksData = $this->getKsAttributeData() ? $this->getKsAttributeData() : [];
        $ksValues = [];
        if ($ksData) {
            $ksLabel = $ksData['frontend_label'];
            foreach ($this->getKsStores() as $ksStore) {
                if ($ksStore->getId() != 0) {
                    $ksValues[$ksStore->getId()] = isset($ksLabel[$ksStore->getId()]) ? $ksLabel[$ksStore->getId()] : '';
                }
            }
        }
        return $ksValues;
    }

    /**
     * Get Additional Data
     * @return string
     */
    public function ksCheckAdditionalData()
    {
        $ksData = $this->getKsAttributeObject()->getData();
        $ksAdditional = null;
        if ($ksData) {
            if (array_key_exists('frontend_input', $ksData)) {
                if ($ksData['frontend_input'] == 'select') {
                    if ($ksData['additional_data']) {
                        $ksArray = $this->ksJsonHelper->jsonDecode($ksData['additional_data']);
                        if (array_key_exists('swatch_input_type', $ksArray)) {
                            $ksAdditional = $ksArray['swatch_input_type'] == 'visual' ? 'swatch_visual' : 'swatch_text';
                        }
                    }
                }
            }
        }
        return $ksAdditional;
    }

    /**
     * Check Not Submitted State
     * @return bool
     */
    public function ksNotSubmittedState()
    {
        $ksData = $this->getKsAttributeObject()->getData();
        $ksStatus = false;
        if ($ksData) {
            if (array_key_exists('ks_attribute_approval_status', $ksData)) {
                if ($ksData['ks_attribute_approval_status'] == \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED) {
                    $ksStatus = true;
                }
            }
        }
        return $ksStatus;
    }
}
