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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\CategoryRequests;

/**
 * class KsCategoryRequestsActions
 */
class KsCategoryRequestsActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const KS_URL_PATH_APPROVE = 'multivendor/categoryrequests/approve';
    const KS_URL_PATH_REJECT = 'multivendor/categoryrequests/reject';
    const KS_URL_PATH_ASSIGN = 'multivendor/categoryrequests/assign';
    const KS_URL_PATH_UNASSIGN = 'multivendor/categoryrequests/unassign';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext
     * @param \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext,
        \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_request_status'])) {
                    $ksCategory = $this->ksCategoryRequestsFactory->create()->load($ksItem['id']);
                    if ($this->ksCategoryHelper->getKsCategoryExist($ksCategory->getKsCategoryId(), $ksCategory->getKsStoreId())) {
                        $ksUrl = html_entity_decode('
                        <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_APPROVE,
                            [
                                    'id' => $ksItem['id'],
                            ]
                        ) . '">' . __('Approve') . '</a> | <a href="#"
                        class="ks-category-reject" data-id="'.$ksItem['id'].'">' . __('Reject') . '</a>');
                        $ksItem[$this->getData('name')]=  $ksUrl;
                    } else {
                        $ksItem[$this->getData('name')]= "";
                    }
                }
            }
        }
        return $dataSource;
    }
}
