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
 * MassEnable Controller Class
 */
class MassEnable extends MassAction
{

    /**
     * MassEnable for the Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            /** @var \Ksolves\MultivendorMarketPlace\Model\ResourceModel\KsProductType\Collection $ksCollection */
            $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
            $ksMessage = '';
            $ksDisableMessage = '';
            $ksEnabled = 0;
            $ksDisabled = 0;
            if ($ksCollection) {
                foreach ($ksCollection as $ksRecord) {
                    //Store the important value in variable
                    $ksRequestStatus = $ksRecord->getKsRequestStatus();

                    // Check the Product Type is in Pending or Unassigned or Rejected
                    if ($ksRequestStatus == 1 || $ksRequestStatus == 4) {
                        $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                        $ksRecord->save();
                        $ksEnabled++;
                    } else {
                        $ksDisabled++;
                    }
                }
                if ($ksEnabled) {
                    $ksMessage = __('A total of %1 product type status(s) has been enabled successfully.', $ksEnabled);
                    $this->messageManager->addSuccessMessage(
                        __($ksMessage)
                    );
                    if ($ksDisabled) {
                        $ksDisableMessage = __("A total of %1 record(s) of Product Type can't be enabled.", $ksDisabled);
                        $this->messageManager->addErrorMessage(
                            __($ksDisableMessage)
                        );
                        $ksDisabled = 0;
                    }
                } elseif ($ksDisabled && ($ksEnabled == 0)) {
                    $ksDisableMessage = __("A total of %1 record(s) of Product Type can't be enabled.", $ksDisabled);
                    $this->messageManager->addErrorMessage(
                        __($ksDisableMessage)
                    );
                }
            } else {
                $ksMessage = __('Something went wrong');
                $this->messageManager->addErrorMessage(
                    __($ksMessage)
                );
            }
        } catch (NoSuchEntityException $e) {
            $ksMessage = __('There is no such product type to enabled.');
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
            $ksMessage = __('We can\'t mass enable the product type right now.');
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
