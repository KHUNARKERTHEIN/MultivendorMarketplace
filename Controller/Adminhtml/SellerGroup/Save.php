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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\SellerGroup;

/**
 * Save Controller Class
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory
     */
    protected $ksSellerGroupFactory;

    /**
     * @var  \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime
    ) {
        $this->ksSellerGroupFactory = $ksSellerGroupFactory->create();
        $this->ksDate = $ksDateTime;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        //get post data
        $ksPostValue = $this->getRequest()->getPostValue();
        //get data
        $ksData = $ksPostValue['ks_seller_group'];
        //check data
        if ($ksData) {
            try {
                //check id is set or not
                if (isset($ksData['id']) && $ksData['id']) {
                    $ksModel = $this->ksSellerGroupFactory->load($ksData['id']);
                    //check model
                    if ($ksModel) {
                        $ksData['ks_updated_at'] = $this->ksDate->gmtDate();
                        $ksModel->setData($ksData);
                        $ksModel->save();
                        $this->messageManager->addSuccessMessage(__('You saved the seller group.'));
                    } else {
                        $this->messageManager->addErrorMessage(__('Something went wrong while saving seller group.'));
                    }
                } else {
                    $ksData['ks_created_at'] = $this->ksDate->gmtDate();
                    $this->ksSellerGroupFactory->addData($ksData)->save();
                    $this->messageManager->addSuccessMessage(__('You saved the seller group.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Seller Group Already Exists.');
            }
        }
        return $this->_redirect('multivendor/sellergroup/index');
    }
}
