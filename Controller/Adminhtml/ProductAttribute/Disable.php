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

/**
 * Disable Controller Class for Disabling Attributes
 */
class Disable extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_UNASSIGNED_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_unassign_email_template';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
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
     * Disable constructor.
     * @param  Context $ksContext,
     * @param  KsFavouriteSellerHelper $ksHelper,
     * @param  ksSellerHelper $ksSellerHelper,
     * @param  KsEmailHelper $ksEmailHelper,
     * @param  KsDataHelper $ksDataHelper,
     * @param  AttributeFactory $ksAttributeCollection
     */
    public function __construct(
        Context $ksContext,
        KsFavouriteSellerHelper $ksHelper,
        ksSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        AttributeFactory $ksAttributeCollection
    ) {
        $this->ksHelper = $ksHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksAttributeCollection = $ksAttributeCollection;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
            $ksId = $this->getRequest()->getParam('attribute_id');
            //check Id
            if ($ksId) {
                //get model data
                $ksModel = $this->ksAttributeCollection->create()->load($ksId);

                $ksSellerArray = $this->ksSellerHelper->getKsSellerList();
                //check model data
                if ($ksModel) {
                    $ksModel->setKsIncludeInMarketplace($ksAttributeStatus);
                    $ksModel->save();
                    $this->KsSendUnAssignedAttributeMail($ksSellerArray, $ksModel);
                    $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess(__('A product attribute status has been disabled successfully.'))
                    ]);
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
                    'message' => $this->messageManager->addErrorMessage(__('Something went wrong while disabling product attribute.'))
                ]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while disabling product attribute."))
                ]);
        } catch (\Exception $e) {
            $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage(__("Something went wrong while disabling product attribute."))
                ]);
        }
        return $ksResponse;
    }

    /**
     * Send Mail When Unassigned the Attribute
     * @param array $ksSellerList
     * @param collection $ksModel
     */
    public function KsSendUnAssignedAttributeMail($ksSellerList, $ksModel)
    {
        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_unassign_email_template');
        if ($ksEmailDisable != 'disable') {
            foreach ($ksSellerList as $ksSellerId) {
                $ksSellerDetails = $this->ksHelper->getKsCustomerAccountInfo($ksSellerId);
                $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email');
                $ksTemplateVariable = [];
                $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails['name'];
                $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
                $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
                $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsRequired());
                $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsUserDefined());
                $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsVisible());
                $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksModel->getIsGlobal());
                $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsSearchable());
                $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksModel->getIsFilterable());
                $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsComparable());
                $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_UNASSIGNED_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
            }
        }
    }
}
