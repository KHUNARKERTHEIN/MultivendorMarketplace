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

namespace Ksolves\MultivendorMarketplace\Plugin\Sales;

use Magento\Framework\Exception\LocalizedException;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class KsSalesCreditmemoLoader
 *
 */
class KsSalesCreditmemoLoader extends \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
{

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var ItemFactory
     */
    protected $ksProductModel;

    /**
     * @var ManagerInterface
     */
    protected $ksMessageManager;

    protected $ksOrderItemFactory;
    protected $ksOrderItemModel;

    /**
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param Magento\Sales\Model\Order\ItemFactory $ksOrderItemFactory
     * @param KsSalesOrderItemFactory $ksOrderItemModel
     * @param OrderRepositoryInterface $ksOrderRepository
     * @param ItemFactory $ksProductModel
     * @param ManagerInterface $ksMessageManager
     */
    public function __construct(
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        \Magento\Sales\Model\Order\ItemFactory $ksOrderItemFactory,
        KsSalesOrderItemFactory $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository,
        ItemFactory $ksProductModel,
        ManagerInterface $ksMessageManager
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksOrderItemFactory = $ksOrderItemFactory->create();
        $this->ksOrderItemModel = $ksOrderItemModel;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksProductModel = $ksProductModel;
        $this->ksMessageManager = $ksMessageManager;
    }


    /**
     * Prepare qty for creditmemo
     *
     * @param Order $order
     * @return array
     */
    public function beforeLoad(
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $subject
    ) {
        $ksCreditmemo = $subject->getCreditmemo();
        $ksCreditmemoId = $subject->getCreditmemoId();
        $ksCreditmemoItems = isset($ksCreditmemo['items']) ? $ksCreditmemo['items'] : [];
        $ksOrderId = $subject->getOrderId();
        $ksOrder = $ksOrderId ? $this->ksOrderRepository->get($ksOrderId): null;
        $ksSellerItems=[];
        $ksTotalQty = 0;
        
        if (!count($ksCreditmemoItems) && (!$ksCreditmemoId)) {
            $ksOrderItems = $ksOrder->getAllItems();
            foreach ($ksOrderItems as $ksItem) {
                if ($this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                    $ksValidQty = $this->ksOrderItemModel->create()->loadByKsOrderItemId($ksItem->getId())->getKsQtyToRefund();
                    $ksSellerItems[$ksItem->getId()]['qty'] = $ksValidQty;
                    if ($ksItem->getProductType()!='bundle') {
                        $ksTotalQty+= $ksValidQty;
                    }
                } else {
                    $ksSellerItems[$ksItem->getId()]['qty'] = $ksItem->getQtyToRefund();
                    if ($ksItem->getProductType()!='bundle') {
                        $ksTotalQty+= $ksItem->getQtyToRefund();
                    }
                }
            }
        } elseif ((!$ksCreditmemoId) && (!isset($ksCreditmemo['ks_approval_flag']))) {
            foreach ($ksCreditmemoItems as $itemId => $itemDetails) {
                $ksSalesItem = $this->ksOrderItemModel->create()->loadByKsOrderItemId($itemId);
                if ($ksSalesItem && $this->ksProductHelper->getKsSellerId($ksSalesItem->getKsProductId())) {
                    $ksValidQty = $ksSalesItem->getKsQtyToRefund();
                    if ($ksValidQty < $itemDetails['qty']) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("We found an invalid quantity to refund item  ". $this->ksProductModel->create()->load($itemId)->getName())
                        );
                    } else {
                        $ksSellerItems[$itemId] = $itemDetails;
                        if ($ksSalesItem->getProductType()!='bundle') {
                            $ksTotalQty+= $ksValidQty;
                        }
                    }
                } else {
                    $ksSellerItems[$itemId] = $itemDetails;
                    if ($ksSalesItem->getProductType()!='bundle') {
                        $ksTotalQty+= $itemDetails['qty'];
                    }
                }
            }
        } else {
            $ksSellerItems = $ksCreditmemoItems;
            $ksTotalQty=1;
        }
    
        if (!$ksTotalQty && (!$ksCreditmemoId) && !isset($ksCreditmemo['ks_approval_flag'])) {
            $this->ksMessageManager->addError(__("Credit Memo request already created from the seller side"));
        }
        /*assign creditmemo quantity*/
        $ksCreditmemo['items'] = $ksSellerItems;
        $subject->setCreditmemo($ksCreditmemo);
        $subject->setCreditmemoId($ksCreditmemoId);
    }
}
