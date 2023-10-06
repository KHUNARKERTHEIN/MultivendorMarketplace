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

namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\ColorPicker;

/**
 * Class KsColorPicker
 * @package Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\ColorPicker
 */
class KsColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * KsColorPicker constructor.
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksData);
    }

    /**
     * add color picker in admin configuration fields
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $ksElement)
    {
        $ksHtml = $ksElement->getElementHtml();
        $ksValue = $ksElement->getData('value');

        $ksHtml .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $ksElement = $("#' . $ksElement->getHtmlId() . '");
                    $ksElement.css("backgroundColor", "' . $ksValue . '");

                    // Attach the color picker
                    $ksElement.ColorPicker({
                        color: "' . $ksValue . '",
                        onChange: function (hsb, hex, rgb) {
                            $ksElement.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';

        return $ksHtml;
    }
}
