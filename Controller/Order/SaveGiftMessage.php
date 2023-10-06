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

namespace Ksolves\MultivendorMarketplace\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\GiftMessage\Model\Save;

/**
 * SaveGiftMessage Controller
 */
class SaveGiftMessage extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $ksGiftMessageSave;

    /**
     * Initialize dependencies
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\GiftMessage\Model\Save $ksGiftMessageSave
     */
    public function __construct( 
        \Magento\Framework\App\Action\Context $context,      
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\GiftMessage\Model\Save $ksGiftMessageSave
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->ksGiftMessageSave = $ksGiftMessageSave;
        parent::__construct($context);
    } 
             
     /**
     * Gift Message Save.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
     public function execute()
     {
        try {
            $this->ksGiftMessageSave->setGiftmessages($this->getRequest()->getParam('giftmessage'))->saveAllInOrder();
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the gift message.'));
        }

        if ($this->getRequest()->getParam('type') == 'order_item') {
            $this->getResponse()->setBody($this->ksGiftMessageSave->getSaved() ? 'YES' : 'NO');
        } else {
            $this->getResponse()->setBody(__('You saved the gift card message.'));
        }
     }
}