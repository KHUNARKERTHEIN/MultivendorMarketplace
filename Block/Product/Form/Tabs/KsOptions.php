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
namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class KsOptions
 */
class KsOptions extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * @return Widget
     */
    protected function _prepareLayout()
    {
        $this->addChild('options_box', \Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Options\KsOption::class);

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getKsOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }
}
