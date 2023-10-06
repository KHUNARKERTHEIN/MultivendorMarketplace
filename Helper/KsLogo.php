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
namespace Ksolves\MultivendorMarketplace\Helper;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * KsLogo class
 * @package Ksolves\SocialLogin\Helper
 */
class KsLogo extends \Magento\Framework\View\Element\AbstractBlock
{
    public function getKsIcon($ksImage)
    {
        $ksLogo = parent::getViewFileUrl($ksImage);
        return $ksLogo;
    }
}
