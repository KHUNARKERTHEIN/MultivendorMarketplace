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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\ProductAttribute;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsCustomAttributeAction Ui Class
 */
class KsCustomAttributeAction extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'multivendor/productattribute/edit';
    const KS_URL_PATH_REQUEST = 'multivendor/productattribute/request';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

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
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
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
        if (isset($dataSource['data']['items'])) {
            $ksRequest = $this->ksCheckRequestAllowed();
            foreach ($dataSource['data']['items'] as & $ksItem) {
                if (isset($ksItem['attribute_id'])) {
                    // If Status is not rejected
                    if ($ksItem['ks_attribute_approval_status'] == \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED) {
                        // Check Request is on or off
                        if ($ksRequest) {
                            $ksItem[$this->getData('name')] = html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_REQUEST,
                                ['attribute_id' => $ksItem['attribute_id']]
                            ) . '">' . __('Request for Approval') . '</a> | <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                ['attribute_id' => $ksItem['attribute_id']]
                            ) . '">' . __('Edit') . '</a>');
                        }
                        // If the status are approved
                    } else {
                        if ($ksRequest) {
                            $ksItem[$this->getData('name')] = html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                ['attribute_id' => $ksItem['attribute_id']]
                            ) . '">' . __('Edit') . '</a>');
                        }
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * To Check seller is allowed to request product attribute or not
     * @return int
     */
    public function ksCheckRequestAllowed()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        return $this->ksSellerHelper->getksProductAttributeRequestAllowed($ksSellerId);
    }
}
