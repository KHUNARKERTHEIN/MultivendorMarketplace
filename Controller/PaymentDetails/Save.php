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

namespace Ksolves\MultivendorMarketplace\Controller\PaymentDetails;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Save Controller class
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerPaymentDetailsFactory
     */
    protected $ksSellerPaymentDetails;
 
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerPaymentDetails $ksSellerPaymentDetails
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsSellerPaymentDetailsFactory $ksSellerPaymentDetails,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerPaymentDetails = $ksSellerPaymentDetails;
        $this->messageManager = $messageManager;
        parent::__construct($ksContext);
    }

    /**
     * Save payment details of seller
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get data
                $ksModel = $this->ksSellerPaymentDetails->create();
                $ksPostData = $this->getRequest()->getPostValue();
                if (isset($ksPostData['id'])) {
                    $ksModel->load($ksPostData['id']);
                }
                $ksModel->setKsCheckMoneyStatus($ksPostData['ks_check_money_status']);
                $ksModel->setKsPayeeName($ksPostData['ks_payee_name']);
                $ksModel->setKsPaypalStatus($ksPostData['ks_paypal_status']);
                $ksModel->setKsPaypalAssociatedEmail($ksPostData['ks_paypal_associated_email']);
                $ksModel->setKsSellerId($ksPostData['ks_seller_id']);
                $ksModel->setKsStoreId($ksPostData['ks_store_id']);
                $ksModel->setKsBankTransferStatus($ksPostData['ks_bank_transfer_status']);
                
                //Saving Primary Account Details
                $ksPriData = [
                    'ks_pri_acc_holder_name' => $ksPostData['ks_pri_acc_holder_name'],
                    'ks_pri_bank_acc_no' => $ksPostData['ks_pri_bank_acc_no'],
                    'ks_pri_bank_name' => $ksPostData['ks_pri_bank_name'],
                    'ks_pri_bank_address' => $ksPostData['ks_pri_bank_address'],
                    'ks_pri_additional_details' => $ksPostData['ks_pri_additional_details']
                ];
                $ksPriValues = $this->ksJsonHelper->jsonEncode($ksPriData);
                $ksModel->setKsPrimaryAccountDetails($ksPriValues);
                //Saving Secondary Account Details
                $ksSecData = [
                    'ks_sec_acc_holder_name' => $ksPostData['ks_sec_acc_holder_name'],
                    'ks_sec_bank_acc_no' => $ksPostData['ks_sec_bank_acc_no'],
                    'ks_sec_bank_name' => $ksPostData['ks_sec_bank_name'],
                    'ks_sec_bank_address' => $ksPostData['ks_sec_bank_address'],
                    'ks_sec_additional_details' => $ksPostData['ks_sec_additional_details']
                ];
                $ksSecValues = $this->ksJsonHelper->jsonEncode($ksSecData);
                $ksModel->setKsSecondaryAccountDetails($ksSecValues);

                // Save Additional Payment Method 1
                $ksModel->setKsAdditionalPaymentMethodOneStatus($ksPostData['ks_additional_payment_method_one_status']);
                $ksAddPayOneData = [
                    'ks_additional_payment_method_one_name' => $ksPostData['ks_additional_payment_method_one_name'],
                    'ks_additional_payment_method_one_details' => $ksPostData['ks_additional_payment_method_one_details']
                ];
                $ksAddPayOneValues = $this->ksJsonHelper->jsonEncode($ksAddPayOneData);
                $ksModel->setKsAdditionalPaymentMethodOneDetails($ksAddPayOneValues);

                // Save Additional Payment Method 2
                $ksModel->setKsAdditionalPaymentMethodTwoStatus($ksPostData['ks_additional_payment_method_two_status']);
                $ksAddPayTwoData = [
                    'ks_additional_payment_method_two_name' => $ksPostData['ks_additional_payment_method_two_name'],
                    'ks_additional_payment_method_two_details' => $ksPostData['ks_additional_payment_method_two_details']
                ];
                $ksAddPayTwoValues = $this->ksJsonHelper->jsonEncode($ksAddPayTwoData);
                $ksModel->setKsAdditionalPaymentMethodTwoDetails($ksAddPayTwoValues);

                // Save data
                $ksModel->save();
                $this->messageManager->addSuccess(__('The payment details has been saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__('An error occured while saving your data.'));
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
