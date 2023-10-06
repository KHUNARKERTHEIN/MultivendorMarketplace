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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\SellerGroup;

use Magento\Framework\Controller\ResultFactory;

/**
 * Edit Controller Class
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory
     */
    protected $ksSellerGroupFactory;

    protected $ksCoreRegistry;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerGroup $ksSellerGroup
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory,
        \Magento\Framework\Registry $ksRegistry
    ) {
        $this->ksSellerGroupFactory = $ksSellerGroupFactory;
        $this->ksCoreRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksSellerGroupId = $this->getRequest()->getParam('id');
        $ksModel = $this->ksSellerGroupFactory->create();

        //check seller group id
        if ($ksSellerGroupId) {
            $this->ksCoreRegistry->register('current_seller_group_id', $ksSellerGroupId);
            $ksModel->load($ksSellerGroupId);
            //check model
            if (!$ksModel->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('multivendor/sellergroup/index');
                return;
            }
        }

        $ksResultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        //check seller group id
        if ($ksSellerGroupId) {
            $ksResultPage->getConfig()->getTitle()->prepend($ksModel->getKsSellerGroupName());
        } else {
            $ksResultPage->getConfig()->getTitle()->prepend(__('Add New Seller Group'));
        }
        return $ksResultPage;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::seller_group');
    }
}
