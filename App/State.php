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

namespace Ksolves\MultivendorMarketplace\App;

class State extends \Magento\Framework\App\State
{
    public function validateAreaCode()
    {
        if (!isset($this->_areaCode)) {
            return false;
        }
        return true;
    }
}
