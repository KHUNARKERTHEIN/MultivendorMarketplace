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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Benefits;

use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBenefits\CollectionFactory as KsBenefitsCollection;

/**
 * MassDelete Controller Class
 */
class MassDelete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var KsBenefitsCollection
     */
    protected $ksBenefitsCollection;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param KsBenefitsCollection $ksBenefitsCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        KsBenefitsCollection $ksBenefitsCollection
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksBenefitsCollection = $ksBenefitsCollection;
        parent::__construct($ksContext);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksBenefitsCollection->create());
            // get collection size
            $ksCollectionSize = $ksCollection->getSize();
            
            foreach ($ksCollection as $ksData) {
                // delete benefit
                $ksData->delete();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 benefit(s) have been deleted.', $ksCollectionSize)
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
