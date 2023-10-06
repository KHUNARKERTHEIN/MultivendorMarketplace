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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ReportSeller;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Ksolves\MultivendorMarketplace\Model\KsReportSellerReasons;
use Magento\Framework\Exception\LocalizedException;

/**
 * SaveReason Controller class
 *
 * Class SaveReason
 */
class SaveReason extends \Magento\Backend\App\Action
{
    /**
     * @var KsReportSellerReason
     */
    protected $ksReportSellerReason;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * SaveReason Constructor
     *
     * @param Action\Context $ksContext
     * @param KsReportSellerReason $ksReportSellerReason
     * @param Session $ksSession
     */
    public function __construct(
        Action\Context $ksContext,
        KsReportSellerReasons $ksReportSellerReason,
        Session $ksSession
    ) {
        $this->ksReportSellerReason = $ksReportSellerReason;
        $this->ksSession = $ksSession;
        parent::__construct($ksContext);
    }

    /**
     * Save seller reason action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        foreach ($data as $id => $value) {
            if (is_array($value)) {
                $data[$id] = implode(',', $this->getRequest()->getParam($id));
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            try {
                $ksCurrentReason = $this->ksReportSellerReason->load($id);
                if ($id) {
                    $ksReasons = $this->ksReportSellerReason->getCollection()->addFieldToFilter('ks_reason', $ksCurrentReason->getKsReason())->getData();
                    foreach ($ksReasons as $ksReason) {
                        $model = $this->ksReportSellerReason->load($ksReason['id']);
                        $model->setKsReason($data['ks_reason']);
                        $model->setKsReasonStatus($data['ks_reason_status']);
                        $model->save();
                    }
                } else {
                    $model = $this->ksReportSellerReason->setData($data);
                    $model->save();
                }
                $this->messageManager->addSuccess(__('The Reason has been saved.'));
                $this->ksSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'newAction') {
                        return $resultRedirect->setPath('*/*/newAction');
                    } else {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $this->ksReportSellerReason->getId(), '_current' => true]);
                    }
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
