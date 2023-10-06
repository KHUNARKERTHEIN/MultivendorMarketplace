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
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollection;

/**
 * Class KsSellerLinkup.
 */
class KsSellerLinkup extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var KsSellerCollection
     */
    protected $ksSellerCollection;

    /**
     * Constructor.
     *
     * @param ContextInterface   $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface       $ksUrlBuilder
     * @param KsSellerCollection $ksSellerCollection
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        KsSellerCollection $ksSellerCollection,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksSellerCollection = $ksSellerCollection;
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
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_seller_id'])) {
                    $ksSellerData = $this->ksSellerCollection->create()->addFieldToFilter('ks_seller_id', $ksItem['ks_seller_id'])->getFirstItem();
                    $ksUrlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    $ksItem['ks_seller_name'] = "<a href='".$this->ksUrlBuilder->getUrl('multivendor/seller/edit', [$ksUrlEntityParamName => $ksSellerData->getId(),'seller_id' => $ksItem['ks_seller_id']])."' target='blank' title='".__('View Seller')."'>".$ksItem['ks_seller_name'].'</a>';
                }
            }
        }
        return $dataSource;
    }
}
