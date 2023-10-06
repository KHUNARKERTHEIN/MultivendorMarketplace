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
 * KsHideField Block class
 */
class KsHideField extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        array $data = []
    ) {
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext, $data);
    }

    /**
      * Retrieve Element HTML fragment
      *
      * @param \Magento\Framework\Data\Form\Element\AbstractElement $ksElement
      * @return string
      */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $ksElement)
    {
        // Get Login Setting Enable
        $ksEnable = $this->ksDataHelper->getKsConfigLoginAndRegistrationSetting('ks_allow_seller_registration');
        $ksHtml = $ksElement->getElementHtml();
        if (!$ksEnable) {
            $ksHtml .= '<script>
                            document.querySelector("#row_ks_marketplace_promotion_ks_marketplace_promotion_page_ks_start_selling_button_text").style.display = "none";
                        </script>';
        }
        return $ksHtml;
    }
}
