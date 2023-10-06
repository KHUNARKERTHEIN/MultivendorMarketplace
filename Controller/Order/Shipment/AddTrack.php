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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Shipping\Model\Order\Track;

/**
 * Add new tracking number to shipment controller.
 */
class AddTrack extends Action implements HttpPostActionInterface
{
    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var Track
     */
    protected $ksTrack;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack $ksSalesShipmentTrack,
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param Track $ksTrack
     * @param ShipmentRepositoryInterface|null $shipmentRepository
     * @param ShipmentTrackInterfaceFactory|null $trackFactory
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack $ksSalesShipmentTrack,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSellerHelper $ksSellerHelper,
        Track $ksTrack,
        ShipmentRepositoryInterface $shipmentRepository = null,
        ShipmentTrackInterfaceFactory $trackFactory = null,
        SerializerInterface $serializer = null
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentRepository = $shipmentRepository ?: ObjectManager::getInstance()
            ->get(ShipmentRepositoryInterface::class);
        $this->trackFactory = $trackFactory ?: ObjectManager::getInstance()
            ->get(ShipmentTrackInterfaceFactory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksTrack = $ksTrack;
        parent::__construct($context);
    }

    /**
     * Add new tracking number action.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number = $this->getRequest()->getPost('number');
            $title = $this->getRequest()->getPost('title');
            $ksParentId = $this->getRequest()->getPost('sales_shipment_id');
            $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
            if (empty($carrier)) {
                throw new LocalizedException(__('Please specify a carrier.'));
            }
            if (empty($number)) {
                throw new LocalizedException(__('Please enter a tracking number.'));
            }

            if ($this->ksOrderHelper->getShipmentApprovalStatus($ksParentId)) {
                $shipmentId = $this->ksOrderHelper->getShipmentId($ksParentId);

                $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                $this->shipmentLoader->setShipmentId($shipmentId);
                $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
                if (!empty($this->getRequest()->getParam('tracking'))) {
                    $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                }
                $shipment = $this->shipmentLoader->load();
                if ($shipment) {
                    $track = $this->trackFactory->create()->setNumber(
                        $number
                    )->setCarrierCode(
                        $carrier
                    )->setTitle(
                        $title
                    );
                    $shipment->addTrack($track);
                    $this->shipmentRepository->save($shipment);
                    $ksTracks = $shipment->getTracksCollection()->getData();
                    if (!empty($ksTracks)) {
                        $ksLastTrack = array_slice($ksTracks, -1)[0];
                        $shipmentData = $this->ksTrack->load($ksLastTrack['entity_id']);
                        $shipmentData->setData('ks_seller_id', $ksSellerId)->save();
                    }

                    $response = [];
                } else {
                    $response = [
                        'error' => true,
                        'message' => __('We can\'t initialize shipment for adding tracking number.'),
                    ];
                }
                $response = $this->serializer->serialize($response);

                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($response);
            } else {
                $ksData =[
                        'ks_parent_id'=> $ksParentId,
                        'ks_order_id'=> $this->ksOrderHelper->getKsOrderId($ksParentId),
                        'ks_track_number' => $number,
                        'ks_title'=>$title,
                        'ks_carrier_code'=>$carrier
                    ];
                $this->ksSalesShipmentTrack->setData($ksData)->save();
            }
            $response =$this->ksResultPageFactory->create()->getLayout()
                        ->createBlock('Ksolves\MultivendorMarketplace\Block\Order\Shipment\View\KsTracking')
                        ->setTemplate('Ksolves_MultivendorMarketplace::order/shipment/view/tracking.phtml')
                        ->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => $e->getTrace()];
        }

        if (\is_array($response)) {
            $response = $this->serializer->serialize($response);

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($response);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setContents($response);
    }
}
