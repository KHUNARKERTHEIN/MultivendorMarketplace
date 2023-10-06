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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\FavouriteSeller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollectionFactory;

/**
 * Class KsFollowersCount
 */
class KsFollowersCount extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var KsFavouriteSellerCollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;
    
    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $ksItem) {
                if (isset($ksItem['ks_seller_id'])) {
                    $ksName = $this->getData('name');
                    $ksItem[$ksName] = $this->getKsSellerFollowersSize($ksItem['ks_seller_id']);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Check module enable/disable.
     *
     * @return boolean
     */
    public function prepare()
    {
        parent::prepare();
        $ksEnable = $this->ksFavouriteSellerHelper->isKsEnabled();
        if ($ksEnable) {
            $this->_data['config']['componentDisabled'] = false;
        }
    }

    /**
     * Get followers count
     *
     * @param  int $ksSellerId
     * @return int
     */
    public function getKsSellerFollowersSize($ksSellerId)
    {
        return $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getSize();
    }
}
