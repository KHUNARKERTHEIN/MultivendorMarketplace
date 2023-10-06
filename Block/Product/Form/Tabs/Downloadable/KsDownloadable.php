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
namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Downloadable;

/**
 * Class KsDownloadable
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Downloadable
 */
class KsDownloadable extends \Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\KsAttribute
{
    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getKsProduct()
    {
        return $this->ksRegistry->registry('product');
    }

    /**
     * Check is readonly block
     *
     * @return boolean
     */
    public function ksIsReadonly()
    {
        return $this->getKsProduct()->getDownloadableReadonly();
    }

    /**
     * Is downloadable
     *
     * @return bool
     */
    public function ksIsDownloadable()
    {
        return $this->getKsProduct()->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }
}
