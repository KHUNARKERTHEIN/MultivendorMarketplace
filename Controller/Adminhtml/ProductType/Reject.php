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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductType;

use Magento\Backend\App\Action;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * Reject Controller Class for Product Type
 */
class Reject extends Action
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_TYPE_REJECT_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_rejection_email';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ksProductTypeFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * ProductType Reject constructor
     * @param Action\Context $ksContext
     * @param KsProductTypeFactory $ksProductTypeFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        Action\Context $ksContext,
        KsProductTypeFactory $ksProductTypeFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * Reject Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $ksRejectStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_REJECTED;
            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
            $ksData = $this->getRequest()->getParam('ks_id');
            $ksReason = trim($this->getRequest()->getParam('ks_rejection_reason'));
            $ksShotMail = trim($this->getRequest()->getParam('ks_notify'));
            $ksMessage = '';
            $ksError = false;
            //check data
            if ($ksData) {
                //get model data
                $ksModel=$this->ksProductTypeFactory->create()->load($ksData);
                //check model data
                $ksModelData = $ksModel->getData();
                if ($ksModelData) {
                    $ksSellerId = $ksModel->getKsSellerId();
                    $ksProductType = $ksModel->getKsProductType();
                    $ksModel->setKsRequestStatus($ksRejectStatus);
                    $ksModel->setKsProductTypeStatus($ksProductTypeStatus);
                    if ($ksReason != "") {
                        $ksModel->setKsProductTypeRejectionReason($ksReason);
                    } else {
                        $ksModel->setKsProductTypeRejectionReason("");
                    }
                    $ksModel->save();

                    $ksStoreId = $this->getRequest()->getParam('store', 0);
                    
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        self::XML_PATH_PRODUCT_TYPE_REJECT_MAIL,
                        $ksStoreId
                    );
                    if ($ksShotMail == 'true') {
                        if ($ksEmailEnabled != "disable") {
                            //Get Sender Info
                            $ksSender = $this->ksDataHelper->getKsConfigValue(
                                'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                                $this->ksDataHelper->getKsCurrentStoreView()
                            );
                            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
                            $ksTemplateVariable = [];
                            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                            $ksTemplateVariable['ksProductType'] = ucwords($ksProductType);
                            $ksTemplateVariable['ksReason'] = $ksModel->getKsProductTypeRejectionReason();
                            // Send Mail
                            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                        }
                    }
                    $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess(__('A product type has been rejected successfully'))
                    ]);
                } else {
                    $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage(__('There is no such product type to rejected.'))
                        ]);
                }
            } else {
                $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__('Something went wrong'))
                ]);
            }
        } catch (Exception $e) {
            $ksResponse = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData(['error' => true,
                'message' => $this->messageManager->addErrorMessage(__($e->getMessage()))
            ]);
        } catch (\Exception $e) {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting product type."))
                ]);
        }
        return  $ksResponse;
    }
}
