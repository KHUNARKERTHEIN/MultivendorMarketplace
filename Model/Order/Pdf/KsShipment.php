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

namespace Ksolves\MultivendorMarketplace\Model\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\RtlTextHandler;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper;

/**
 * Sales Order Shipment PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KsShipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $ksAppEmulation;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $ksProductMetadata;

    /**
     * @var RtlTextHandler
     */
    private $ksRtlTextHandler;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerDashboardHelper;

    /**
     * @param \Magento\Payment\Helper\Data $ksPaymentData
     * @param \Magento\Framework\Stdlib\StringUtils $ksString
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Framework\Filesystem $ksFilesystem
     * @param Config $ksPdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $ksPdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksLocaleDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $ksAddressRenderer
     * @param RtlTextHandler|null $ksRtlTextHandler
     * @param KsSellerDashboardMyProfileHelper $ksSellerDashboardHelper
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Store\Model\App\Emulation $ksAppEmulation
     * @param KsOrderHelper $ksOrderHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $ksProductMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Framework\Locale\ResolverInterface $ksLocaleResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $ksPaymentData,
        \Magento\Framework\Stdlib\StringUtils $ksString,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Framework\Filesystem $ksFilesystem,
        Config $ksPdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $ksPdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksLocaleDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $ksAddressRenderer,
        RtlTextHandler $ksRtlTextHandler,
        KsSellerDashboardMyProfileHelper $ksSellerDashboardHelper,
        \Magento\Store\Model\App\Emulation $ksAppEmulation,
        KsOrderHelper $ksOrderHelper,
        \Magento\Framework\App\ProductMetadataInterface $ksProductMetadata,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Locale\ResolverInterface $ksLocaleResolver,
        array $data = []
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksAppEmulation = $ksAppEmulation;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksProductMetadata = $ksProductMetadata;
        $this->ksRtlTextHandler = $ksRtlTextHandler;
        $this->ksSellerDashboardHelper = $ksSellerDashboardHelper;
        if($this->ksProductMetadata->getVersion()>='2.4.1') {
            $new = $ksAppEmulation;
        } else {
            $new = $ksLocaleResolver;
        }
        parent::__construct(
            $ksPaymentData,
            $ksString,
            $ksScopeConfig,
            $ksFilesystem,
            $ksPdfConfig,
            $ksPdfTotalFactory,
            $pdfItemsFactory,
            $ksLocaleDate,
            $inlineTranslation,
            $ksAddressRenderer,
            $ksStoreManager,
            $new,
            $data
        );
    }

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 100];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Insert order to pdf page.
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function ksInsertOrder(&$page, $obj, $ksSellerId, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 206, $top - 25);
        $page->drawRectangle(206, $top, 387, $top - 25);
        $page->drawRectangle(387, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold by:'), 35, $top - 15, 'UTF-8');
        $page->drawText(__('Sold to:'), 216, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 397, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 397, $top - 15, 'UTF-8');
        }

        $ksCompanyDetails = $this->ksSellerDashboardHelper->getKsCompanyDetails($ksSellerId);
        $ksCompanyDetailsHeight = $this->ksCalcAddressHeight($ksCompanyDetails);
        $addressesHeight = $this->ksCalcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->ksCalcAddressHeight($shippingAddress));
        }

        $addressesHeight = max($addressesHeight,$ksCompanyDetailsHeight);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($ksCompanyDetails as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 38, true, true) as $_value) {
                    $text[] = $this->ksRtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }
        $ksCompanyDetailsEnd = $this->y;
        $this->y = $addressesStartY;
        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 38, true, true) as $_value) {
                    $text[] = $this->ksRtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 216, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }
        $billingAddressEnd = $this->y;

        $addressesEndY = min($ksCompanyDetailsEnd,$billingAddressEnd);

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 38, true, true) as $_value) {
                        $text[] = $this->ksRtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 397, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Calculate address height
     *
     * @param  array $address
     * @return int Height
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function ksCalcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 38, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->ksAppEmulation->startEnvironmentEmulation(
                    $shipment->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->ksStoreManager->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            $ksShipmentIncId = $shipment->getIncrementId();
            $ksSellerId = $this->ksOrderHelper->getKsIsSellerShipment($ksShipmentIncId);
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            if ($ksSellerId) {
                /* Add head */
                $this->ksInsertOrder(
                    $page,
                    $shipment,
                    $ksSellerId,
                    $this->_scopeConfig->isSetFlag(
                        self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $order->getStoreId()
                    )
                );
            } else{
                /* Add head */
                $this->insertOrder(
                    $page,
                    $shipment,
                    $this->_scopeConfig->isSetFlag(
                        self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $order->getStoreId()
                    )
                );
            }
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            if ($shipment->getStoreId()) {
                $this->ksAppEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
