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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;

/**
 * class for listing products of a rule.
 */
class ProductsList extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ksolves_MultivendorMarketplace::commissionruleproduct';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param Action\Context $ksContext
     * @param \\Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory,
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        parent::__construct($ksContext);
    }

    /**
     * Init actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function ksInitAction()
    {
        $ksId = $this->getRequest()->getParam('ksRuleId');
        $this->ksDataPersistor->set('ks_commission_rule_id', $ksId);
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }


    /**
     * Execute action
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksResultPage = $this->ksInitAction();
        return $ksResultPage;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::commissionruleproduct');
    }
}
