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

/**
 * NewAction Controller Class
 */
class NewAction extends \Magento\Backend\App\Action
{

    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ksolves_MultivendorMarketplace::commissionrule';

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $ksResultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $KsContext
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $KsContext,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
    ) {
        $this->ksResultForwardFactory = $ksResultForwardFactory;
        parent::__construct($KsContext);
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $ksResultForward */
        $ksResultForward = $this->ksResultForwardFactory->create();
        return $ksResultForward->forward('edit');
    }
}
