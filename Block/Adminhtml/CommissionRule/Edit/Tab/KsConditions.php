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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CommissionRule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * KsConditions Block class
 */
class KsConditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $ksRendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $ksConditions;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper
     */
    protected $ksCommissionHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context                   $ksContext
     * @param \Magento\Framework\Registry                               $ksRegistry
     * @param \Magento\Framework\Data\FormFactory                       $ksFormFactory
     * @param \Magento\Rule\Block\Conditions                            $KsConditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset      $ksRendererFieldset
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper $ksCommissionHelper
     * @param array                                                     $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Data\FormFactory $ksFormFactory,
        \Magento\Rule\Block\Conditions $ksConditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $ksRendererFieldset,
        \Ksolves\MultivendorMarketplace\Helper\KsCommissionHelper $ksCommissionHelper,
        array $ksData = []
    ) {
        $this->ksRendererFieldset = $ksRendererFieldset;
        $this->ksConditions = $ksConditions;
        $this->ksCommissionHelper = $ksCommissionHelper;
        parent::__construct($ksContext, $ksRegistry, $ksFormFactory, $ksData);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Products Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Products Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $ksModel = $this->_coreRegistry->registry('commission_rule');
        if ($ksModel->getId() == 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    
    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
        $ksModel = $this->_coreRegistry->registry('commission_rule');

        /**
         * @var \Magento\Framework\Data\Form $form
        */
        $ksForm = $this->_formFactory->create();
        $ksForm->setHtmlIdPrefix('rule_');

        $ksConditionsFieldSetId = $ksModel->getConditionsFieldSetId('ks_marketplace_commission_form');
        $ksRenderer = $this->ksRendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('multivendor/commissionrule/newConditionHtml/form/' . $ksConditionsFieldSetId, ['form_namespace' => 'ks_marketplace_commission_form'])
        )->setFieldSetId(
            $ksConditionsFieldSetId
        );

        $ksFieldset = $ksForm->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __(
                    'Products Conditions'
                )
            ]
        )->setRenderer(
            $ksRenderer
        );

        $ksFieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'data-form-part' => 'ks_marketplace_commission_form']
        )->setRule(
            $ksModel
        )->setRenderer(
            $this->ksConditions
        );

        $ksForm->setValues($ksModel->getData());
        $this->setConditionFormName($ksModel->getConditions(), 'ks_marketplace_commission_form', $ksConditionsFieldSetId);
        $this->setForm($ksForm);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param  \Magento\Rule\Model\Condition\AbstractCondition $ksConditions
     * @param  string                                          $formName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $ksConditions, $ksFormName, $ksJsFormName)
    {
        $ksConditions->setFormName($ksFormName);
        $ksConditions->setJsFormObject($ksJsFormName);

        if ($ksConditions->getConditions() && is_array($ksConditions->getConditions())) {
            foreach ($ksConditions->getConditions() as $ksCondition) {
                $this->setConditionFormName($ksCondition, $ksFormName, $ksJsFormName);
            }
        }
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $ksHtml = parent::getFormHtml();
        $ksId = $this->getRequest()->getParam('id');
        if ($ksId) {
            $ksHtml .= __("<b>Commission Applied: %1 Number of Products</b>", $this->ksCommmissionProductCount($ksId));
        }

        return $ksHtml;
    }

    /**
     * Get Commission applied product count
     *
     * @param  int id
     * @return int
     */
    public function ksCommmissionProductCount($ksId)
    {
        return $this->ksCommissionHelper->ksProductCountByCommissionRule($ksId);
    }
}
