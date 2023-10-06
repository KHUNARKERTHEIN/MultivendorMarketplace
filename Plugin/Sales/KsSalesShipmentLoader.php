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
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;

/**
 * Class KsSalesShipmentLoader
 *
 */
class KsSalesShipmentLoader extends \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
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

    /**
     * @var RedirectFactory
     */
    protected $ksRedirectFactory;

    /**
     * @var KsSalesOrderItemFactory
     */
    protected $ksOrderItemModel;

    /**
     * @var RedirectFactory
     */
    protected $ksRedirect;

    /**
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItemFactory $ksOrderItemModel
     * @param OrderRepositoryInterface $ksOrderRepository
     * @param ItemFactory $ksProductModel
     * @param ManagerInterface $ksMessageManager
     * @param RedirectFactory $ksRedirectFactory
     */
    public function __construct(
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItemFactory $ksOrderItemModel,
        OrderRepositoryInterface $ksOrderRepository,
        ItemFactory $ksProductModel,
        ManagerInterface $ksMessageManager,
        RedirectFactory $ksRedirectFactory,
        \Magento\Framework\App\Response\RedirectInterface $ksRedirect
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksProductModel = $ksProductModel;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksRedirectFactory = $ksRedirectFactory->create();
        $this->ksRedirect = $ksRedirect;
    }


    /**
     * Prepare qty for shipment
     *
     * @param Order $order
     * @return array
     */
    public function beforeLoad(
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $subject
    ) {
        $ksShipment = $subject->getShipment();
        $ksShipmentId = $subject->getShipmentId();
        $ksShipmentItems = isset($ksShipment['items']) ? $ksShipment['items'] : [];
        $ksOrderId = $subject->getOrderId();
        $ksOrder = $ksOrderId ? $this->ksOrderRepository->get($ksOrderId): null;
        $ksSellerItems=[];
        $ksShipmentApproval = isset($ksShipment['ks_approval_flag']);
        $ksTotalQty = 0;
        if (!count($ksShipmentItems) && (!$ksShipmentId)) {
            $ksOrderItems = $ksOrder->getAllItems();
            foreach ($ksOrderItems as $ksItem) {
                if ($this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                    $ksValidQty = $this->ksOrderItemModel->create()->loadByKsOrderItemId($ksItem->getId())->getKsQtyToShip();
                    $ksSellerItems[$ksItem->getId()] = $ksValidQty;
                    $ksTotalQty += $ksValidQty;
                } else {
                    $ksSellerItems[$ksItem->getId()] = $ksItem->getQtyToShip();
                    $ksTotalQty += $ksItem->getQtyToShip();
                }
            }
        } elseif ((!$ksShipmentId) && (!isset($ksShipment['ks_approval_flag']))) {
            foreach ($ksShipmentItems as $itemId => $qty) {
                $ksSalesItem = $this->ksOrderItemModel->create()->loadByKsOrderItemId($itemId);
                if ($ksSalesItem && $this->ksProductHelper->getKsSellerId($ksSalesItem->getKsProductId())) {
                    $ksValidQty = $ksSalesItem->getKsQtyToShip();
                    if ($ksValidQty < $qty) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("We found an invalid quantity to ship  ". $this->ksProductModel->create()->load($itemId)->getName())
                        );
                    } else {
                        $ksSellerItems[$itemId] = $qty;
                        $ksTotalQty += $qty;
                    }
                } else {
                    $ksSellerItems[$itemId] = $qty;
                    $ksTotalQty += $qty;
                }
            }
        } else {
            unset($ksShipment['ks_approval_flag']);
            $ksSellerItems = $ksShipmentItems;
        }
        /*assign Shipment quantity*/
        $ksShipment['items'] = $ksSellerItems;
        $subject->setShipment($ksShipment);
        $subject->setShipmentId($ksShipmentId);
        try {
            if (!$ksTotalQty>0 && (!$ksShipmentId) && !$ksShipmentApproval) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Shipment or Memo request has already created from the seller side")
                );
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
            return $this->ksRedirectFactory->setPath('sales/order/view', ['order_id' => $ksOrderId]);
        }
    }
}
