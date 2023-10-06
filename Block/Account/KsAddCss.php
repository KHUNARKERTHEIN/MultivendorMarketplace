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
 
namespace Ksolves\MultivendorMarketplace\Block\Account;

use Magento\Framework\View\Element\Template;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsAddCss Block class
 */
class KsAddCss extends Template
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $ksAssetRepository;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $ksDataHelper;
 
    /**
     * KsAddCss constructor.
     * @param Template\Context $ksContext
     * @param KsDataHelper $ksDataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $ksContext,
        KsDataHelper $ksDataHelper,
        array $data = []
    ) {
        parent::__construct($ksContext, $data);
        $this->ksAssetRepository = $ksContext->getAssetRepository();
        $this->ksDataHelper    = $ksDataHelper;
    }
 
    /**
     * To Get Css For RTL Theme
     * @return string
     */
    public function getKsRTLThemeCss()
    {
        $ks_asset_repository = $this->ksAssetRepository;
        $ksasset  = $ks_asset_repository->createAsset('Ksolves_MultivendorMarketplace::css/rtl-view.css');
        $ksUrl = "";
        // Check RTL is allowed or not
        if ($this->ksDataHelper->getKsConfigSellerPanelSetting('ks_rtl_theme')) {
            $ksUrl    = $ksasset->getUrl();
        }
        return $ksUrl;
    }
}
