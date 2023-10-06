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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;

/**
 * MassAssignGroup Controller Class
 */
class MassAssignGroup extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller
{
    /**
     * Execute Action
     */
    public function execute()
    {
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerCollection->create());
            //get collection size
            $ksCollectionSize = $ksCollection->getSize();
            foreach ($ksCollection as $ksData) {
                $ksData->setKsSellerGroupId($this->getRequest()->getParam('group'));
                $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksData->save();
            }
            
            $ksSellerGroupCollection = $this->ksSellerGroupCollectionFactory->create()->addFieldToFilter('id', $this->getRequest()->getParam('group'));
            $ksGroupName='';

            foreach ($ksSellerGroupCollection as $ksCollection) {
                $ksGroupName = $ksCollection->getKsSellerGroupName();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 seller(s) has been assigned a "%2" group successfully.', $ksCollectionSize, $ksGroupName)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
