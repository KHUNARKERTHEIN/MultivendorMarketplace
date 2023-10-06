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

use Magento\Store\Model\ScopeInterface;

/**
 * Mass Delete Controller Class
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
     */
    protected $ksSellerGroupFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

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
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter,
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksCollectionFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksSellerGroupFactory = $ksCollectionFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksCacheTypeList = $ksCacheTypeList;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksConfigInterface = $ksConfigInterface;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        try {
            $ksFlag=0;
            $ksCollectionSize = 0;
            $ksField = 'ks_marketplace_seller/ks_seller_settings/ks_seller_group';
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerGroupFactory->create());
            //get default seller group config value
            $ksSellerGroupId = $this->ksScopeConfig->getValue(
                $ksField,
                $scopeType = ScopeInterface::SCOPE_STORE,
                $scopeCode = null
            );
            foreach ($ksCollection as $ksRecord) {
                if ($ksRecord->getId()!=$ksSellerGroupId && $ksRecord->getId()!=1) {
                    $ksSellerCollection = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_group_id', $ksRecord->getId());
                    foreach ($ksSellerCollection as $ksItem) {
                        $ksItem->setKsSellerGroupId(\Ksolves\MultivendorMarketplace\Model\KsSellerGroup::KS_DEFAULT_GROUP_ID);
                        $ksItem->save();
                    }
                    $ksRecord->delete();
                    $ksFlag=1;
                    $ksCollectionSize++;
                } else {
                    $this->messageManager->addErrorMessage(
                        __('%1 (default) seller group can\'t be deleted.', $ksRecord->getKsSellerGroupName())
                    );
                }
            }

            //check for flag value
            if ($ksFlag) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 seller group(s) has been deleted.', $ksCollectionSize)
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $ksResultRedirect = $this->resultRedirectFactory->create();
        return $ksResultRedirect->setPath('multivendor/sellergroup/index');
    }
}
