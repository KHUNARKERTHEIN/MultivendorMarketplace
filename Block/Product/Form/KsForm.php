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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form;

/**
 * KsForm block class
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Product\KsCreateButton
{
    /**
     * Product Form Submit action
     *
     * @return string
     */
    public function ksProductFormAction()
    {
        return $this->getUrl('multivendor/product/save', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Product Form back action
     *
     * @return string
     */
    public function ksProductBackAction()
    {
        return $this->getUrl('multivendor/product/index', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
