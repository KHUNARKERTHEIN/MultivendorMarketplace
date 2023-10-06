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
namespace Ksolves\MultivendorMarketplace\Controller\Product\Ajax;

/**
 * Class KsAssociatedGrid
 * @package Ksolves\MultivendorMarketplace\Controller\Product\Ajax
 */
class KsAssociatedGrid extends \Ksolves\MultivendorMarketplace\Controller\Product\Ajax\KsImportOptions
{
    /**
     * Show configurable associate grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }
}
