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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Save class
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var ksDataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\commissionRuleFactory
     */
    protected $ksCommissionRuleModel;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param DataPersistorInterface $ksDataPersistor
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $KsDate
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $KsCommissionRuleModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Framework\Stdlib\DateTime\DateTime $KsDate,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $KsCommissionRuleModel
    ) {
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksDate = $KsDate;
        $this->ksCommissionRuleModel = $KsCommissionRuleModel;
        parent::__construct($ksContext);
    }

    /**
     * Save action
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $ksData;
            try {
                $ksModel = $this->ksCommissionRuleModel->create();
                $ksData = $this->prepareData();
                if ($ksData['ks_priority']!= '') {
                    if (!preg_match('/^\d+(\.\d{1})?$/', $ksData['ks_priority'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Priority input is allowed up to 1 decimal.'));
                    }
                }
                $ksId = $this->getRequest()->getParam('id');
                if ($ksId) {
                    $ksModel->load($ksId);
                    if ($ksId != $ksModel->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                if (!$ksModel->getId()) {
                    $ksModel->setKsCreatedAt($this->ksDate->gmtDate());
                }
                $ksData = $this->prepareData($ksData);

                if ($ksData['ks_rule_type']==1) {
                    $ksData['ks_seller_id']= 0;
                } else {
                    $ksData['ks_seller_group'] = null;
                }
                if (!isset($ksData['id']) || $ksData['id'] != 1) {
                    $ksDates = ['from_date' => $ksData['ks_start_date'], 'to_date' => $ksData['ks_end_date']];
                    $ksValidateResult = $ksModel->validateData(new \Magento\Framework\DataObject($ksDates));
                    if ($ksValidateResult !== true) {
                        foreach ($ksValidateResult as $ksErrorMessage) {
                            throw new \Magento\Framework\Exception\LocalizedException(__($ksErrorMessage));
                        }
                        return;
                    }
                    if ($ksData['ks_min_price'] > 0 && $ksData['ks_max_price'] > 0) {
                        if ($ksData['ks_max_price'] < $ksData['ks_min_price']) {
                            throw new \Magento\Framework\Exception\LocalizedException(__('Max Price can not be less than min price.'));
                        }
                    }
                    if ($ksData['ks_commission_type'] == 'to_fixed' && $ksData['ks_commission_value'] > $ksData['ks_min_price']) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Please enter the minimum price bracket amount which is equal to or greater than the fixed commission value.'));
                    }
                }
                $ksModel->loadPost($ksData);
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->save();
                $this->messageManager->addSuccess(__('You saved the commission rule.'));
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('multivendor/*/edit', ['id' => $ksModel->getId()]);
                    return;
                }
                $this->_redirect('multivendor/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $ksId = (int)$this->getRequest()->getParam('id');
                $this->ksDataPersistor->set('commission_form_data', $this->getRequest()->getPostValue());
                if (!empty($ksId)) {
                    $this->_redirect('multivendor/*/edit', ['_query'=>['id' => $this->getRequest()->getParam('id')]]);
                } else {
                    $this->_redirect('multivendor/*/new', ['_query'=>['_current' => true]]);
                }
                return;
            } catch (\Exception $e) {
                $this->ksDataPersistor->set('commission_form_data', $this->getRequest()->getPostValue());
                $this->messageManager->addError(
                    __(('Something went wrong while saving the item data. Please review the error log.'))
                );
                $this->_redirect('multivendor/*/edit', ['_query'=>['id' => $this->getRequest()->getParam('id')]]);
                return;
            }
        }
        $this->_redirect('multivendor/*/');
    }

    /**
     * Prepares specific data
     *
     * @param  array $data
     * @return array
     */
    protected function prepareData()
    {
        $ksData  = $this->getRequest()->getPostValue()['commission_details']['rule_information'];
        $ksData = array_merge($ksData, $this->getRequest()->getPostValue()['commission_details']['extra_details']);
        $ksData = array_merge($ksData, $this->getRequest()->getPostValue()['commission_details']['commission_fieldset']);
        if (isset($this->getRequest()->getPostValue()['rule'])) {
            $ksData = array_merge($ksData, $this->getRequest()->getPostValue()['rule']);
        }

        if (isset($ksData['ks_seller_group']) && $ksData['ks_seller_group']!= null) {
            $ksData['ks_seller_group']=implode(',', $ksData['ks_seller_group']);
        } else {
            $ksData['ks_seller_group']= null;
        }

        if (isset($ksData['ks_website']) && $ksData['ks_website']!= null) {
            $ksData['ks_website']=implode(',', $ksData['ks_website']);
        } else {
            $ksData['ks_website']= null;
        }

        if (isset($ksData['ks_product_type']) && $ksData['ks_product_type']!= null) {
            $ksData['ks_product_type']=implode(',', $ksData['ks_product_type']);
        } else {
            $ksData['ks_product_type']= null;
        }
        
        $ksDataObject = new \Magento\Framework\DataObject($ksData);
        $ksData = $ksDataObject->getData();
        unset($ksData['form_key']);
        return $ksData;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_commission');
    }
}
