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

use Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductType\MassAction;
use Magento\Framework\Controller\ResultFactory;

/**
 * MassDisable Controller Class
 */
class MassDisable extends MassAction
{
    /**
     * MassDisable for the Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            /** @var \Ksolves\MultivendorMarketPlace\Model\ResourceModel\KsProductType\Collection $ksCollection */
            $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());

            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
            $ksDisabled = $ksCollection->getSize();
            $ksProductType = [];

            if ($ksCollection) {
                //Loop for Disabling the product type
                foreach ($ksCollection as $ksRecord) {
                    $ksSellerId = $ksRecord->getKsSellerId();
                    $ksProductType[] = $ksRecord->getKsProductType();
                    $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                    $ksRecord->save();
                }
                //disabled product with unassign product types
                $this->ksProductTypeHelper->disableKsUnassignTypeProductIds($ksSellerId, $ksProductType);

                $ksMessage = __('A total of %1 product type status(s) has been disabled successfully.', $ksDisabled);
                $this->messageManager->addSuccessMessage(
                    __($ksMessage)
                );
            } else {
                $ksMessage = __('Something went wrong');
                $this->messageManager->addErrorMessage(
                    __($ksMessage)
                );
            }
        } catch (NoSuchEntityException $e) {
            $ksMessage = __('There is no such product type to disabled.');
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        } catch (LocalizedException $e) {
            $ksMessage = __($e->getMessage());
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        } catch (\Exception $e) {
            $ksMessage = __('We can\'t mass disable the product type right now.');
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
