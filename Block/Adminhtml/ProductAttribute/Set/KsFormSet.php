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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\ProductAttribute\Set;

use Magento\Backend\Block\Widget\Form;

/**
 * KsFormSet Class for hiding seller attribute set
 *
 */
class KsFormSet extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $ksSetFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\Data\FormFactory $ksFormFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $ksSetFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Data\FormFactory $ksFormFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $ksSetFactory,
        array $ksData = []
    ) {
        $this->ksSetFactory = $ksSetFactory;
        parent::__construct($ksContext, $ksRegistry, $ksFormFactory, $ksData);
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        $ksData = $this->ksSetFactory->create()->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Framework\Data\Form $form */
        $ksForm = $this->_formFactory->create();
        $ksFieldset = $ksForm->addFieldset('set_name', ['legend' => $this->getAttributeSetLabel()]);
        $ksFieldset->addField(
            'attribute_set_name',
            'text',
            [
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry validate-no-html-tags',
                'value' => $ksData->getAttributeSetName()
            ]
        );

        if (!$this->getRequest()->getParam('id', false)) {
            $ksFieldset->addField('gotoEdit', 'hidden', ['name' => 'gotoEdit', 'value' => '1']);

            $ksSets = $this->ksSetFactory->create()->getResourceCollection()->addFieldToFilter('ks_seller_id', 0)->setEntityTypeFilter(
                $this->_coreRegistry->registry('entityType')
            )->load()->toOptionArray();

            $ksFieldset->addField(
                'skeleton_set',
                'select',
                [
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $ksSets
                ]
            );
        }

        $ksForm->setMethod('post');
        $ksForm->setUseContainer(true);
        $ksForm->setId('set-prop-form');
        $ksForm->setAction($this->getUrl('catalog/*/save'));
        $ksForm->setOnsubmit('return false;');
        $this->setForm($ksForm);
    }

    /**
     * Get Attribute Set Label
     *
     * @return \Magento\Framework\Phrase
     */
    private function getAttributeSetLabel()
    {
        if ($this->getRequest()->getParam('id', false)) {
            return __('Edit Attribute Set Name');
        }

        return __('Attribute Set Information');
    }
}
