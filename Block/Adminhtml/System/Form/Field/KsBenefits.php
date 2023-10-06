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
 
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\System\Form\Field;

/**
 * KsBenefits Block class
 */
class KsBenefits extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * NewWidget constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $ksContext
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksData);
    }
    
    /**
     * render element
     *
     * @return void
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $ksElement)
    {
        $ksElement->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($ksElement);
    }
    
    /**
     * get element html
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $ksElement)
    {
        return sprintf(
            '<a href ="%s">%s</a>',
            rtrim($this->_urlBuilder->getUrl('multivendor/benefits/index'), '/'),
            __('Add Benefits')
        );
    }
}
