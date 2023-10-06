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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Seller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsProductTypeApprovalAction Ui Class
 */
class KsProductTypeApprovalAction extends Column
{
    const KS_URL_PATH_APPROVE = 'multivendor/producttype/approve';

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
     * Get item url
     * @return string
     */
    public function getViewUrl()
    {
        return $this->ksUrlBuilder->getUrl(
            $this->getData('config/viewUrlPath')
        );
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
                if (isset($ksitem['ks_request_status'])) {
                    $ksName = $this->getData('name');
                    if ($ksitem['ks_request_status'] == '2') {
                        $ksitem[$ksName] = html_entity_decode('
                            <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_APPROVE,
                            [
                                        'ks_id' => $ksitem['id'],
                                    ]
                        ) . '">' . __('Approve') . '</a> |
                            <a href="#"
                            class="ks-producttype-reject" data-id="'.$ksitem['id'].'">' . __('Reject') . '</a>');
                    }
                }
            }
        }
        return $dataSource;
    }
}
