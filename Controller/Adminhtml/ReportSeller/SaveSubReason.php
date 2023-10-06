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
 * SaveSubReason Controller class
 *
 * Class SaveSubReason
 */
class SaveSubReason extends \Magento\Backend\App\Action
{
    /**
     * @var KsReportSellerReasons
     */
    protected $ksReportSellerSubReason;

    /**
     * @var Session
     */
    protected $ksSession;

    protected $ksSubReasonFlag = 1;

    /**
     * @var KsReportSellerReasons
     */
    protected $ksReportSellerReason;

    /**
     * SaveSubReason Constructor
     *
     * @param Action\Context $ksContext
     * @param KsReportSellerReasons $ksReportSellerReason
     * @param Session $adminsession
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
     * Save seller sub reason action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /*hundfssdf*/

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
                /*edit case*/
                if ($id) {
                    $model = $this->ksReportSellerReason->load($id);
                    $model->setData($data);
                    $model->save();
                } else {
                    /*new sub reason*/
                    $ksCurrentReason = $this->ksReportSellerReason->getCollection()->addFieldToFilter('ks_reason', $data['ks_reason'])->getFirstItem();
                    $data['ks_reason'] = $ksCurrentReason->getKsReason();
                    $data['ks_reason_status'] = $ksCurrentReason->getKsReasonStatus();
                    $ksSubReason = $this->ksReportSellerReason->getCollection()->addFieldToFilter('ks_reason', $ksCurrentReason->getKsReason());
                    /*add sub reason to existing entry*/
                    foreach ($ksSubReason as $subReason) {
                        if ($subReason->getKsSubreason()=="") {
                            $this->ksSubReasonFlag = 0;
                            $model = $this->ksReportSellerReason->load($subReason->getId());
                            $model->setKsSubreason($data['ks_subreason']);
                            $model->setKsSubreasonStatus($data['ks_subreason_status']);
                            $model->save();
                        }
                    }
                    /*Check if the sub reason is sved to some existing empty reason*/
                    if ($this->ksSubReasonFlag) {
                        $model = $this->ksReportSellerReason->setData($data);
                        $model->save();
                    }
                }
                $this->messageManager->addSuccess(__('The Sub-reason has been saved.'));
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
