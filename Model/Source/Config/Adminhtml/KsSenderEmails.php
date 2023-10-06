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
namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsSenderEmails
 * @package Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml
 */
class KsSenderEmails implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * KsSenderEmails constructor.
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
    ) {
        $this->ksDataHelper = $ksDataHelper;
    }

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $ksOptions=[
            [
                'label'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_general/name') . " ( " . $this->ksDataHelper->getKsConfigSenderSetting('ident_general/email') . " )",
                'value'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_general/email')
            ],
            [
                'label'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_sales/name') . " ( " . $this->ksDataHelper->getKsConfigSenderSetting('ident_sales/email') . " )",
                'value'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_sales/email')
            ],
            [
                'label'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_support/name') . " ( " . $this->ksDataHelper->getKsConfigSenderSetting('ident_support/email') . " )",
                'value'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_support/email')
            ],
            [
                'label'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_custom1/name') . " ( " . $this->ksDataHelper->getKsConfigSenderSetting('ident_custom1/email') . " )",
                'value'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_custom1/email')
            ],
            [
                'label'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_custom2/name') . " ( " . $this->ksDataHelper->getKsConfigSenderSetting('ident_custom2/email') . " )",
                'value'=>$this->ksDataHelper->getKsConfigSenderSetting('ident_custom2/email')
            ]
        ];
        return $ksOptions;
    }
}
