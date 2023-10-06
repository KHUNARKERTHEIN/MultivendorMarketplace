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

use Magento\Backend\App\Action\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Delete Controller
 */
class Delete extends \Magento\Backend\App\Action
{

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory
     */
    protected $ksSellerGroupFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $ksConfigInterface;
 
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $ksCacheTypeList;

    /**
     * Delete constructor.
     * @param Context $context
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
     */
    public function __construct(
        Context $context,
        \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
    ) {
        $this->ksSellerGroupFactory=$ksSellerGroupFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksCacheTypeList = $ksCacheTypeList;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksConfigInterface = $ksConfigInterface;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksSellerGroupId = $this->getRequest()->getParam('id');
        $ksField = 'ks_marketplace_seller/ks_seller_settings/ks_seller_group';
        //check seller group id
        if ($ksSellerGroupId) {
            try {
                //load seller group model
                $ksSellerGroupModel=$this->ksSellerGroupFactory->create()->load($ksSellerGroupId);
                $ksSellerCollection = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_group_id', $ksSellerGroupId);
                foreach ($ksSellerCollection as $ksItem) {
                    $ksItem->setKsSellerGroupId(\Ksolves\MultivendorMarketplace\Model\KsSellerGroup::KS_DEFAULT_GROUP_ID);
                    $ksItem->save();
                }
                $ksId = $this->ksScopeConfig->getValue(
                    $ksField,
                    $scopeType = ScopeInterface::SCOPE_STORE,
                    $scopeCode = null
                );
                if ($ksId == $ksSellerGroupId) {
                    //save data
                    $this->ksConfigInterface->saveConfig($ksField, \Ksolves\MultivendorMarketplace\Model\KsSellerGroup::KS_DEFAULT_GROUP_ID, 'default', 0);
                    //clean config cache
                    $this->ksCacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                }
                //delete seller group
                $ksSellerGroupModel->delete();
                $this->messageManager->addSuccessMessage(
                    __('A seller group has been deleted successfully.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting seller group.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting seller group.'));
        }
        $ksResultRedirect = $this->resultRedirectFactory->create();
        return $ksResultRedirect->setPath('*/*/');
    }
}
