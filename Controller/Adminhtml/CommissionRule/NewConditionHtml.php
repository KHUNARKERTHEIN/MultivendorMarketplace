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

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Controller class NewConditionHtml.
 * Returns condition html
 */
class NewConditionHtml extends Quote implements HttpPostActionInterface
{
    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('id');
        $ksFormName = $this->getRequest()->getParam('form_namespace');
        $ksTypeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $ksType = $ksTypeArr[0];

        $ksModel = $this->_objectManager->create(
            $ksType
        )->setId(
            $ksId
        )->setType(
            $ksType
        )->setRule(
            $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class)
        )->setPrefix(
            'conditions'
        );
        if (!empty($ksTypeArr[1])) {
            $ksModel->setAttribute($ksTypeArr[1]);
        }

        if ($ksModel instanceof AbstractCondition) {
            $ksModel->setJsFormObject($this->getRequest()->getParam('form'));
            $ksModel->setFormName($ksFormName);
            $this->setJsFormObject($ksModel);
            $ksHtml = $ksModel->asHtmlRecursive();
        } else {
            $ksHtml = '';
        }
        $this->getResponse()->setBody($ksHtml);
    }

    /**
     * Set jsFormObject for the model object
     *
     * @return void
     * @param AbstractCondition $ksModel
     */
    private function setJsFormObject(AbstractCondition $ksModel): void
    {
        $ksRequestJsFormName = $this->getRequest()->getParam('form');
        $ksActualJsFormName = $this->getJsFormObjectName($ksModel->getFormName());
        if ($ksRequestJsFormName === $ksActualJsFormName) { //new
            $ksModel->setJsFormObject($ksActualJsFormName);
        }
    }

    /**
     * Get jsFormObject name
     *
     * @param string $ksFormName
     * @return string
     */
    private function getJsFormObjectName(string $ksFormName): string
    {
        return $ksFormName . 'rule_conditions_fieldset_';
    }
}
