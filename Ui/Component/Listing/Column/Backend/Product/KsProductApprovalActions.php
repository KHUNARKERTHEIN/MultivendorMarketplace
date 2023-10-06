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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsProductApprovalActions.
 */
class KsProductApprovalActions extends Column
{
    public const KS_APPROVE_PATH = 'multivendor/product/approve';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $ksDataSource
     *
     * @return array
     */
    public function prepareDataSource(array $ksDataSource)
    {
        if (isset($ksDataSource['data']['items'])) {
            foreach ($ksDataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_product_approval_status'])) {
                    $ksName = $this->getData('name');
                    $ksUrlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    switch ($ksItem['ks_product_approval_status']) {
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING_UPDATE:
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING:
                            $ksItem[$ksName] = html_entity_decode('
                            <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                                self::KS_APPROVE_PATH,
                                [
                                        $ksUrlEntityParamName => $ksItem['entity_id'],
                                    ]
                            ) . '">' . __('Approve') . '</a>|<a href="#"
                            class="ks-product-reject" data-id="' . $ksItem['entity_id'] . '">' . __('Reject') . '</a>');
                            break;
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED:
                            $ksItem[$ksName] = html_entity_decode('<a href="#"
                            class="ks-product-reject" data-id="' . $ksItem['entity_id'] . '">' . __('Reject') . '</a>');
                            break;
                        case \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED:
                            $ksItem[$ksName] = html_entity_decode(
                                '<a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                                self::KS_APPROVE_PATH,
                                [
                                            $ksUrlEntityParamName => $ksItem['entity_id'],
                                        ]
                            ) . '">' . __('Approve') . '</a>'
                            );
                            break;
                    }
                }
            }
        }
        return $ksDataSource;
    }
}
