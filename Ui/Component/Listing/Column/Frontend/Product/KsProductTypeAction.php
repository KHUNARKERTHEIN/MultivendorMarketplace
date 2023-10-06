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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsProductTypeAction Ui Class
 */
class KsProductTypeAction extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_REQUEST= 'multivendor/producttype/request';

    /**
     * @var UrlInterface
     */
    protected $ksurlBuilder;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksksurlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $kscontext,
        UiComponentFactory $ksuiComponentFactory,
        KsSellerHelper $ksSellerHelper,
        UrlInterface $ksurlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksurlBuilder = $ksurlBuilder;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($kscontext, $ksuiComponentFactory, $components, $data);
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
        $ksRequestAllowed = $this->ksCheckRequestAllowed();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $ksitem) {
                if (isset($ksitem['id'])) {
                    // If The Product Type is Approved and Assigned
                    if ($ksRequestAllowed) {
                        if ($ksitem['ks_request_status'] == '0' || $ksitem['ks_request_status'] == '3') {
                            $ksitem[$this->getData('name')] = [
                                'unassign' => [
                                    'href' => $this->ksurlBuilder->getUrl(
                                        static::KS_URL_PATH_REQUEST,
                                        [
                                            'id' => $ksitem['id']
                                        ]
                                    ),
                                    'label' => __('Request for Approval'),
                                     'confirm' => [
                                        'title' => __('Request'),
                                        'message' => __('Are you sure you want to request for the product type ?')
                                    ]
                                ]
                            ];
                        }
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * To Check seller is allowed to request product type or not
     * @return int
     */
    public function ksCheckRequestAllowed()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        return $this->ksSellerHelper->getksProductRequestAllowed($ksSellerId);
    }
}
