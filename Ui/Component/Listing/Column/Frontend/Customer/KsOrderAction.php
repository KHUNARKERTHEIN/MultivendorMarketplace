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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Customer;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
 
/**
 * Class KsOrderAction
 */
class KsOrderAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $ksOrderFactory;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $ksSalesOrder;

    protected $ksOrders;

    protected $ksSellerHelper;
 
    /**
     * constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param string[] $components
     * @param string[] $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $ksOrderFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSalesOrder $ksSalesOrder,
        KsSellerHelper $ksSellerHelper,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksOrderFactory = $ksOrderFactory;
        $this->ksSalesOrder = $ksSalesOrder;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksitem) {
                if (isset($ksitem['entity_id'])) {
                    $this->ksOrders = $this->ksOrderFactory->create()->addFieldToFilter('customer_id', $ksitem['entity_id'])->setOrder('created_at', 'asc');
                    $ksOrderIdList=[];
                    foreach ($this->ksOrders as $order) {
                        array_push($ksOrderIdList, $order->getEntityId());
                    }
                    $ksSalesOrderList=$this->ksSalesOrder->getCollection()->addFieldToFilter('ks_order_id', ["in"=>$ksOrderIdList])->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
                    $ksName = $this->getData('name');
                    $ksitem[$ksName] =
                    html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                        "multivendor/order/listing",
                        [
                            'customer_id' => $ksitem['entity_id'],
                                        ]
                    ) . '"><span>' . $ksSalesOrderList->getSize() . '</span></a>');
                }
            }
        }
        return $dataSource;
    }
}
