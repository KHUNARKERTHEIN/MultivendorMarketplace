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
 * MassStatus Controller Class
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
     */
    protected $ksSellerGroupCollectionFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * MassChangeStatus constructor.
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupCollectionFactory
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupCollectionFactory,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
    ) {
        $this->ksSellerGroupCollectionFactory = $ksSellerGroupCollectionFactory;
        $this->ksFilter = $ksFilter;
        $this->ksDate = $ksDate;
        parent::__construct($ksContext);
    }

    /**
     * Check Change Seller Group Status
     * Execute Action
     */
    public function execute()
    {
        //for check Seller Group Status
        if ($this->getRequest()->getParam('ks_status') == 1) {
            $ksSellerGroupStatus = 'enabled';
        } else {
            $ksSellerGroupStatus = 'disabled';
        }

        try {
            $ksFlag=0;
            $ksCollectionSize = 0;
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerGroupCollectionFactory->create());

            foreach ($ksCollection as $ksData) {
                if ($ksData->getId() == 1 && $this->getRequest()->getParam('ks_status') == 0) {
                    $this->messageManager->addErrorMessage(
                        __('%1 (default) seller group can\'t be disabled.', $ksData->getKsSellerGroupName())
                    );
                } else {
                    $ksData->setKsStatus($this->getRequest()->getParam('ks_status'));
                    $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksData->save();
                    $ksFlag=1;
                    $ksCollectionSize++;
                }
            }

            //check for flag value
            if ($ksFlag) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 seller group(s) status has been %2 successfully.', $ksCollectionSize, $ksSellerGroupStatus)
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
