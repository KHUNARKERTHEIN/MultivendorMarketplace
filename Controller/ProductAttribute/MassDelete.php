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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * MassDelete Controller Class
 */
class MassDelete extends Action
{
    /**
     * @var \Magento\Framework\App\Request\Http $ksRequest
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var CollectionFactory
     */
    protected $ksAttributeCollectionFactory;

    /**
     * @var AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @param Context $ksContext
     * @param FormKey $ksFormKey
     * @param Http $ksRequest
     * @param CollectionFactory $ksAttributeCollectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param Filter $ksFilter
     * @param KsSellerHelper $ksSellerHelper
     * @param AttributeFactory $ksAttributeFactory
     */
    public function __construct(
        Context $ksContext,
        FormKey $ksFormKey,
        Http $ksRequest,
        CollectionFactory $ksAttributeCollectionFactory,
        Filter $ksFilter,
        KsSellerHelper $ksSellerHelper,
        AttributeFactory $ksAttributeFactory
    ) {
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest = $ksRequest;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        $this->ksAttributeCollectionFactory = $ksAttributeCollectionFactory;
        $this->ksFilter = $ksFilter;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksAttributeFactory = $ksAttributeFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute Mass Enable action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //Get Selected Data
                $ksAttribute = 0;
                $ksCollection = $this->ksFilter->getCollection($this->ksAttributeCollectionFactory->create());
                // Check Collection has data
                if ($ksCollection->getSize()) {
                    foreach ($ksCollection as $ksValue) {
                        $ksAttributeId = $ksValue->getAttributeId();
                        // Create the Attribute Factory
                        $ksModel = $this->ksAttributeFactory->create();
                        // Load Model
                        $ksModel->load($ksAttributeId);
                        try {
                            $ksModel->delete();
                            $ksAttribute++;
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__('The " %1" attribute is used in configurable products.', $ksModel->getFrontendLabel()));
                        }
                    }
                    $this->messageManager->addSuccess(__('A total of %1 product attribute(s) has been deleted.', $ksAttribute));
                } else {
                    $this->messageManager->addErrorMessage(
                        __('There are no product attribute available')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
