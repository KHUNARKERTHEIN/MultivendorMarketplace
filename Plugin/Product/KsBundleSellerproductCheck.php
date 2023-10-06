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

namespace Ksolves\MultivendorMarketplace\Plugin\Product;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as ksBundleSelection;

/**
 * Class KsBundleSellerproductCheck
 * @package Ksolves\MultivendorMarketplace\Model\Plugin
 */
class KsBundleSellerproductCheck
{
    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param KsCatalogProductHelper constructor.
     */
    public function __construct(
        KsProductHelper $ksProductHelper
    ) {
        $this->ksProductHelper = $ksProductHelper;
    }

    /**
     * @param Collection $ksSubject
     * @param ksBundleSelection $ksBundleSelection
     * @return ksResult
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAppendSelections(Collection $ksSubject, ksBundleSelection $ksBundleSelection)
    {
        if (!$ksBundleSelection->hasFlag('seller_approved_status_filter')) {
            foreach ($ksBundleSelection->getItems() as $key => $selection) {
                if (!$this->ksProductHelper->ksSellerProductFilter($selection->getId())) {
                    $ksBundleSelection->removeItemByKey($key);
                }
            }
        }
    }
}
