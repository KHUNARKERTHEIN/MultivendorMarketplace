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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Sales;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsApprovalActions.
 */
class KsApprovalActions extends Column
{

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
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_approval_status'])) {
                    $ksName = $this->getData('name');
                    $ksApproveUrlPath = $this->getData('config/approveUrlPath') ?: '#';
                    $ksRejectUrlPath = $this->getData('config/rejectUrlPath') ?: '#';
                    $ksUrlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    switch ($ksItem['ks_approval_status']) {
                        case 0 :
                            $ksItem[$ksName] = html_entity_decode('
                            <a href="' . $this->ksUrlBuilder->getUrl(
                                $ksApproveUrlPath,
                                [
                                        $ksUrlEntityParamName => $ksItem['entity_id'],
                                    ]
                            ) . '">' . __('Approve') . '</a> | <a href="#"
                            class="ks-reject" data-url = "'.$this->ksUrlBuilder->getUrl($ksRejectUrlPath).'" data-id="'.$ksItem['entity_id'].'">' . __('Reject') . '</a>');
                            break;
                        case 2 :
                            $ksItem[$ksName] = html_entity_decode('
                                <a href="' . $this->ksUrlBuilder->getUrl(
                                    $ksApproveUrlPath,
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
        return $dataSource;
    }
}
