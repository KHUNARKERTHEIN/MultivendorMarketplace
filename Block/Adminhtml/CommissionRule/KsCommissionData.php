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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CommissionRule;

use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsCommissionData Block class
 */
class KsCommissionData extends \Magento\Framework\View\Element\Template
{
    /**
    * @var \Magento\Framework\Locale\CurrencyInterface
    */
    protected $ksLocaleCurrency;

    /**
    * @var \Magento\Framework\Locale\CurrencyInterface
    */
    protected $ksContext;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $ksCurrency;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     *
     * @param Context $ksContext
     * @param \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $ksCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param KsDataHelper $ksDataHelper
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency,
        \Magento\Directory\Model\CurrencyFactory $ksCurrency,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        KsDataHelper $ksDataHelper,
        array $ksData = []
    ) {
        $this->ksLocaleCurrency = $ksLocaleCurrency;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCurrency = $ksCurrency;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext, $ksData);
    }

    public function ksGetCurrency()
    {
        // Get Base Currency Code
        $ksBaseCurrecyCode = $this->ksDataHelper->getKsBaseCurrenyCode();
        // Create the Model
        $ksCurrencyModel = $this->ksCurrency->create()->load($ksBaseCurrecyCode);
        $ksCurrencySymbol = $ksCurrencyModel->getCurrencySymbol();
        $ksCurrencySymbol = $ksCurrencySymbol ? $ksCurrencySymbol : $ksBaseCurrecyCode;
        return $ksCurrencySymbol;
    }
}
