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

declare(strict_types=1);

namespace Ksolves\MultivendorMarketplace\Plugin\Sales;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Service\InvoiceService;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory;
use Magento\Sales\Model\Order\ItemFactory;

/**
 * Class KsSalesInvoiceService
 *
 */
class KsSalesInvoiceService extends InvoiceService
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
     * @var KsSalesOrderItem
     */
    protected $ksOrderItemModel;

    /**
     * @var ItemFactory
     */
    protected $ksProductModel;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @param KsProductHelper $ksProductHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItemFactory $ksOrderItemModel
     * @param ItemFactory $ksProductModel
     */
    public function __construct(
        KsProductHelper $ksProductHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItemFactory $ksOrderItemModel,
        ItemFactory $ksProductModel,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksOrderItemModel = $ksOrderItemModel;
        $this->ksProductModel = $ksProductModel;
        $this->orderRepository = $orderRepository;
    }


    /**
     * Prepare qty to invoice and eliminate quantities for which invoice request has being created by sel
     *
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    public function beforePrepareInvoice(
        \Magento\Sales\Model\Service\InvoiceService $subject,
        $order,
        $items
    ) {
        $ksSellerItems =[];
        
        if (!$order->getId()) {
            return [$order,$items];
        }
        if (!count($items)) {
            $ksOrderItems = $order->getAllItems();
            foreach ($ksOrderItems as $ksItem) {
                if ($this->ksProductHelper->getKsSellerId($ksItem->getProductId())) {
                    $ksValidQty = $this->ksOrderItemModel->create()->loadByKsOrderItemId($ksItem->getId())->getKsQtyToInvoice();
                    if ($ksItem->isDummy() && $ksItem->canInvoice()) {
                        $ksSellerItems[$ksItem->getId()] = $ksItem->getQtyOrdered()?$ksItem->getQtyOrdered():1;
                    } elseif ($ksValidQty > 0) {
                        $ksSellerItems[$ksItem->getId()] = $ksValidQty;
                    }
                } else {
                    if ($ksItem->isDummy()) {
                        $ksSellerItems[$ksItem->getId()] = $ksItem->getQtyOrdered()?$ksItem->getQtyOrdered():1;
                    } else {
                        $ksSellerItems[$ksItem->getId()] = $ksItem->getQtyToInvoice();
                    }
                }
            }
            if (empty($ksSellerItems)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Invoice request already created from the seller side")
                );
            }
        } elseif (isset($items['ks_approval_flag'])) {
            unset($items['ks_approval_flag']);
            $ksSellerItems = $items;
        } else {
            foreach ($items as $itemId => $itemQty) {
                $ksSalesItem = $this->ksOrderItemModel->create()->loadByKsOrderItemId($itemId);
                if ($ksSalesItem && $this->ksProductHelper->getKsSellerId($ksSalesItem->getKsProductId())) {
                    $ksValidQty = $ksSalesItem->getKsQtyToInvoice();
                    if ($ksValidQty < $itemQty) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("We found an invalid quantity to invoice item  ". $this->ksProductModel->create()->load($itemId)->getName())
                        );
                    } else {
                        $ksSellerItems[$itemId] = $itemQty;
                    }
                } else {
                    $ksSellerItems[$itemId] = $itemQty;
                }
            }
        }
        return [$order,$ksSellerItems];
    }
}
