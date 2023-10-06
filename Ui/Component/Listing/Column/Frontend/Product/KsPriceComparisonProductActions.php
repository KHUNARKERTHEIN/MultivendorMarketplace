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

/**
 * KsPriceComparisonProductActions Ui Class
 */
class KsPriceComparisonProductActions extends Column
{
    /**
     * Urls for actions
     */
    public const KS_URL_PATH_EDIT = 'multivendor/pricecomparison/editproduct';
    public const KS_URL_PATH_REQUEST_FOR_APPROVAL = 'multivendor/pricecomparison/productrequest';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

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
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($kscontext, $ksUiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_product_approval_status'])) {
                    switch ($ksItem['ks_product_approval_status']) {
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED:
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED:
                            $ksUrl = html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_REQUEST_FOR_APPROVAL,
                                ['entity_id' => $ksItem['entity_id']]
                            ) . '" class="ks-reload">' . __('Request for Approval') . '</a> || <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                [
                                    'id' => $ksItem['entity_id'],
                                    'parent_id' => $ksItem['ks_parent_product_id']
                                ]
                            ) . '">' . __('Edit') . '</a>');
                            break;

                        default:
                            $ksUrl = html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                [
                                    'id' => $ksItem['entity_id'],
                                    'parent_id' => $ksItem['ks_parent_product_id']
                                ]
                            ) . '">' . __('Edit') . '</a>');
                            break;
                    }
                    $ksItem[$this->getData('name')]= $ksUrl;
                }
            }
        }
        return $dataSource;
    }
}
