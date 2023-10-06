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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Reject Controller Class
 */
class Reject extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_rejection_email_template';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksHelper;

    /**
     * Reject constructor.
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsFavouriteSellerHelper $ksSellerHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerHelper $ksHelper
     * @param KsProductHelper $ksProductHelper
     * @param AttributeFactory $ksAttributeCollection
     */
    public function __construct(
        Context $ksContext,
        KsFavouriteSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        KsSellerHelper $ksHelper,
        KsProductHelper $ksProductHelper,
        AttributeFactory $ksAttributeCollection
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksHelper = $ksHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksAttributeCollection = $ksAttributeCollection;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
            $ksId = $this->getRequest()->getParam('attribute_id');
            $ksRejectionReason = $this->getRequest()->getParam('ks_rejection_reason');
            $ksRejectionReason = $ksRejectionReason ? $ksRejectionReason : "";
            //check Id
            if ($ksId) {
                //get model data
                $ksModel = $this->ksAttributeCollection->create()->load($ksId);
                $ksSellerDetails = $this->ksSellerHelper->getKsCustomerAccountInfo($ksModel->getKsSellerId());
                $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email');

                $ksTemplateVariable = [];
                $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
                $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
                $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
                $ksTemplateVariable['ks-required'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsRequired());
                $ksTemplateVariable['ks-system'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsUserDefined());
                $ksTemplateVariable['ks-visible'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsVisible());
                $ksTemplateVariable['ks-scope'] = $this->ksHelper->getKsStoreValues($ksModel->getIsGlobal());
                $ksTemplateVariable['ks-searchable'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsSearchable());
                $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksHelper->getKsFilterableValues($ksModel->getIsFilterable());
                $ksTemplateVariable['ks-comparable'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsComparable());
                $ksTemplateVariable['ks-rejection-reason'] = $ksRejectionReason;

                //check model data
                if ($ksModel) {
                    if ($this->ksProductHelper->ksUnassignAttributeFromAttributeSet($ksId)) {
                        $ksModel->setKsAttributeRejectionReason($ksRejectionReason);
                        $ksModel->setKsAttributeApprovalStatus($ksAttributeStatus);
                        $ksModel->save();
                        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_rejection_email_template');

                        if ($this->getRequest()->getParam('ks_notify') == "true") {
                            if ($ksEmailDisable != 'disable') {
                                $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails);
                            }
                        }
                        $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess(__('A product attribute has been rejected successfully.'))
                        ]);
                    } else {
                        $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage(__('This attribute is used in configurable products.'))
                        ]);
                    }
                } else {
                    $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage(__('There is no such product attribute exists'))
                        ]);
                }
            } else {
                $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__('Something went wrong while rejecting product attribute.'))
                ]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting product attribute."))
                ]);
        } catch (\Exception $e) {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while rejecting product attribute."))
                ]);
        }
        return $ksResponse;
    }
}
