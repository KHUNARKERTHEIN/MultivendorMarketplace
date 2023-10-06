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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product\Ajax;

use Magento\Backend\App\Action\Context;

/**
 * Class ProductReject
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product\Ajax
 */
class ProductReject extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_REJECT_MAIL = 'ks_marketplace_catalog/ks_product_settings/ks_rejection_email';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksCatalogProductFactory;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;


    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductCategoryList
     */
    protected $ksCategoryList;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
    * ProductReject constructor.
    * @param Context $ksContext,
    * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
    * @param \Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory
    * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
    * @param \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository
    * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
    * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
    * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
    * @param \Magento\Catalog\Model\ProductCategoryList $ksCategoryList
    * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
    * @param \Psr\Log\LoggerInterface $ksLogger
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Magento\Catalog\Model\ProductCategoryList $ksCategoryList,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Psr\Log\LoggerInterface $ksLogger
    ) {
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDate = $ksDate;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryList = $ksCategoryList;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksLogger = $ksLogger;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //get id
        $ksId = $this->getRequest()->getPost("ks_id");
        //get rejection reason
        $ksRejecttionReason = $this->getRequest()->getPost("ks_rejection_reason");
        //get rejection status
        $ksRejectStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        //get status
        $ksStatus = 2;
        //get status to notify seller
        $ksNotifySeller = $this->getRequest()->getPost("ks_notify_seller");

        //check id
        if ($ksId) {
            try {
                $ksIds = [];
                $ksProductId = [];
                $ksProductId[] = $ksId;
                //load model
                $ksModel = $this->ksProductFactory->create()->load($ksId, 'ks_product_id');

                $ksProductData = $this->ksCatalogProductFactory->create()->load($ksId);

                if ($ksRejecttionReason) {
                    $ksModel->setKsRejectionReason($ksRejecttionReason);
                } else {
                    $ksModel->setKsRejectionReason("");
                }
                $ksModel->setKsProductApprovalStatus($ksRejectStatus);
                $ksModel->setKsEditMode(1);
                $ksProductData->setStoreId($this->getRequest()->getParam('store', 0));
                $ksProductData->setStatus($ksStatus);
                $ksProductData->setUpdatedAt($this->ksDate->gmtDate());
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksSellerId = $ksModel->getKsSellerId();
                $ksIds[] = $ksSellerId;
                $ksModel->save();
                $ksProductData->save();

                //Send Mail
                $ksProduct = $this->ksProductRepository->getById(
                    $ksId,
                    true,
                    $this->getRequest()->getParam('store', 0)
                );

                //Function sent mail to seller when admin reject product
                if ($ksNotifySeller == 'true') {
                    $this->ksSendEmailProductReject($ksIds, $ksProductId);
                }
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess(__("A product has been rejected successfully. "))
                    ]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->ksLogger->error($e);
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting seller's product."))
                    ]);
            } catch (\Exception $e) {
                $this->ksLogger->error($e);
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting seller's product."))
                    ]);
            }
        } else {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting seller's product."))
                ]);
        }

        return $ksResponse;
    }

    /**
     * Send Mail to Seller when Admin Rejects Product Request
     * @param int $ksSellerId, int $ksProductIds
     */
    private function ksSendEmailProductReject($ksSellerId, $ksProductIds)
    {
        $ksEmailEnabled = $this->ksDataHelper
            ->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_settings/ks_rejection_email',
                $this->ksDataHelper->getKsCurrentStoreView()
            );

        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_settings/ks_email_sender',
                $this->ksDataHelper->getKsCurrentStoreView()
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            //Get Receiver Info
            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);

            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductIds'] = $ksProductIds;
            $ksTemplateVariable['ksReason'] = $this->getRequest()->getPost("ks_rejection_reason");

            // Send Mail
            $this->ksEmailHelper->ksProductApproval(
                self::XML_PATH_PRODUCT_REJECT_MAIL,
                $ksTemplateVariable,
                $ksSenderInfo,
                ucwords($ksReceiverDetails['name']),
                $ksReceiverDetails['email']
            );
        }
    }
}
