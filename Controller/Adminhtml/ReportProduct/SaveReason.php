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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ReportProduct;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Ksolves\MultivendorMarketplace\Model\KsReportProductReasons;
use Magento\Framework\Exception\LocalizedException;

/**
 * SaveReason Controller class
 *
 * Class SaveReason
 */
class SaveReason extends \Magento\Backend\App\Action
{
    /**
     * @var KsReportProductReasonsFactory
     */
    protected $ksReportProductReasons;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * SaveReason Constructor
     *
     * @param Action\Context $ksContext
     * @param KsReportProductReasonsFactory $ksReportProductReasons
     * @param Session $ksSession
     */
    public function __construct(
        Action\Context $ksContext,
        KsReportProductReasons $ksReportProductReasons,
        Session $ksSession
    ) {
        $this->ksReportProductReasons = $ksReportProductReasons;
        $this->ksSession = $ksSession;
        parent::__construct($ksContext);
    }

    /**
     * Save product reason action
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
                $ksCurrentReason = $this->ksReportProductReasons->load($id);
                if ($id) {
                    $ksReasons = $this->ksReportProductReasons->getCollection()->addFieldToFilter('ks_reason', $ksCurrentReason->getKsReason())->getData();
                    foreach ($ksReasons as $ksReason) {
                        $model = $this->ksReportProductReasons->load($ksReason['id']);
                        $model->setKsReason($data['ks_reason']);
                        $model->setKsReasonStatus($data['ks_reason_status']);
                        $model->save();
                    }
                } else {
                    $model = $this->ksReportProductReasons->setData($data);
                    $model->save();
                }
                $this->messageManager->addSuccess(__('The Reason has been saved.'));
                $this->ksSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'newAction') {
                        return $resultRedirect->setPath('*/*/newAction');
                    } else {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $this->ksReportProductReasons->getId(), '_current' => true]);
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
