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

namespace Ksolves\MultivendorMarketplace\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

use Magento\Framework\Filesystem\Driver\File;

/**
 * Class AddDefaultData data patch.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddDefaultData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     *
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerGroup
     */
    protected $ksSellerGroupFactory;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDateTime;

    /**
     * @var EavSetupFactory
     */
    private $ksEavSetupFactory;

    /**
     *
     * @var \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory
     */
    protected $ksCommissionRuleFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory
     */
    protected $ksBenefitsFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory
     */
    protected $ksHowItWorksFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $ksConfigFactory;
    
    /**
     * @var \Magento\Framework\App\State
     */
    private $ksState;
    
    /**
     * @var  File
     */
    protected $ksDriver;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory $ksHowItWorksFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime
     * @param EavSetupFactory $ksEavSetupFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRuleFactory
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigFactory
     * @param \Magento\Framework\App\State $ksState
     * @param File $ksDriver
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Ksolves\MultivendorMarketplace\Model\KsSellerGroupFactory $ksSellerGroupFactory,
        \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory,
        \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory $ksHowItWorksFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory $ksProductTypeFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime,
        EavSetupFactory $ksEavSetupFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRuleFactory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigFactory,
        \Magento\Framework\App\State $ksState,
        File $ksDriver
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->ksDateTime           = $ksDateTime;
        $this->ksSellerGroupFactory = $ksSellerGroupFactory->create();
        $this->ksBenefitsFactory    = $ksBenefitsFactory;
        $this->ksHowItWorksFactory  = $ksHowItWorksFactory;
        $this->ksEavSetupFactory    = $ksEavSetupFactory;
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksCommissionRuleFactory = $ksCommissionRuleFactory->create();
        $this->ksConfigFactory = $ksConfigFactory;
        $this->ksState = $ksState;
        $this->ksDriver = $ksDriver;
    }

    public function apply()
    {
        try {
            if (!$this->ksState->validateAreaCode()) {
                $this->ksState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
        
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->startSetup();
        /**
         * Add Default Data in ks_seller_group table.
         */
        $ksData = [
            'ks_seller_group_name'=> "General",
            'ks_status'=> 1,
            'ks_created_at'=>$this->ksDateTime->gmtDate()
        ];

        $this->ksSellerGroupFactory->addData($ksData)->save();

        // add eav attribute for category
        $eavSetup = $this->ksEavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'ks_include_in_marketplace', [
            'type'     => 'int',
            'label'    => 'Include in Marketplace',
            'input'    => 'boolean',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'default'  => '1',
            'required' => false,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'    => 'General'
        ]);
        
        /**
         * Add Default Data in ks_commission_rule table.
         */
        $ksCommissionData = [
            'ks_rule_name'=> "Default Rule",
            'ks_status'=> 1,
            'ks_rule_type'=> 1,
            'ks_priority'=> 99,
            'ks_commission_type' => 'to_percent',
            'ks_commission_value' => 0,
            'ks_calculation_baseon' => 1
        ];

        $this->ksCommissionRuleFactory->addData($ksCommissionData)->save();

        /**
         * Save Product Type data
         */
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'simple',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'virtual',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'downloadable',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'configurable',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'grouped',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();
        $this->ksProductTypeFactory->create()->addData($ksProductTypeData = [
            'ks_seller_id'    => 0,
            'ks_product_type'  => 'bundle',
            'ks_product_type_status' => 1,
            'ks_request_status' => 4
        ])->save();

        /**
         * Add Default Data in ks_marketplace_benefits and ks_marketplace_howitworks table.
         */
        $this->ksMoveDirToMediaDir('benefit-1.png');
        $this->ksMoveDirToMediaDir('benefit-2.png');
        $this->ksMoveDirToMediaDir('benefit-3.png');
        $this->ksMoveDirToMediaDir('benefit-4.png');
        $this->ksMoveDirToMediaDir('benefit-5.png');
        $this->ksMoveDirToMediaDir('benefit-6.png');
        $this->ksMoveDirToMediaDir('benefit-7.png');

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-1.png',
            'ks_title'=> "Customize The Seller Profile As You Like It",
            'ks_text'=> "Your seller profile should reflect the appropriate information that you want your buyers to witness! Exactly, that’s what you’ll get with Marketplace Module. You can make unlimited customizations on ‘My Profile Page’ and ‘Home Page’ to attract diverse buyers."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-2.png',
            'ks_title'=> "Operate Store Anywhere",
            'ks_text'=> "With absolutely no geographical restrictions, you can efficiently operate your store from anywhere in the world. Additionally, with the Seller Locator Feature, you can provide your location to access nearby buyers."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-3.png',
            'ks_title'=> "Scale Your Product Range",
            'ks_text'=> "Feel free to scale the product range at any instance of your selling. Once you receive essential insights on your customers’ requirements, you can target the required features and swiftly add them in your product."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-4.png',
            'ks_title'=> "Easily List Your Products",
            'ks_text'=> "In order to attract the quality audience to your product, it should be listed appropriately. In the Seller Dashboard, you have an option to select the desired category, type, and attribute for your product to reach the target audience."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-5.png',
            'ks_title'=> "Multiple Payment Methods",
            'ks_text'=> "You can start your Shop from anywhere around the world, we offer multiple payment methods. You will receive your earnings within the prescribed period through check/money order , wire transfer, and paypal, whichever suits you the best."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-6.png',
            'ks_title'=> "Track The Entire Order Fulfillment Process",
            'ks_text'=> "As a seller, you’ll have an absolute control over the order process of your product. The Sales feature will allow you to manage order, shipment, credit memo, and invoice to behold the insights into your earnings."
        ])->save();

        $this->ksBenefitsFactory->create()->addData($ksData=[
            'ks_picture'=> 'benefit-7.png',
            'ks_title'=> "Compare The Price Of The Product",
            'ks_text'=> "Listing your products and their respective prices is much more tricky that you can imagine. The Price Comparison feature enables you to compare the price with a similar product. So, you can increase or decrease the prices accordingly to create a better chance of sale and overshadow your competition at the same time."
        ])->save();

        $this->ksMoveDirToMediaDir('howitworks-1.png');
        $this->ksMoveDirToMediaDir('howitworks-2.png');
        $this->ksMoveDirToMediaDir('howitworks-3.png');
        $this->ksMoveDirToMediaDir('howitworks-4.png');
        
        //save default logo in core_config_data
        $this->ksMoveImgToMediaDir('KMM_Logo.png');

        $this->ksHowItWorksFactory->create()->addData($ksData=[
            'ks_picture'=> 'howitworks-1.png',
            'ks_title'=> "Open Your Marketplace",
            'ks_text'=> "Create your seller account by just adding your unique store name & url and start selling. You won’t require anything else apart from an active bank account details."
        ])->save();

        $this->ksHowItWorksFactory->create()->addData($ksData=[
            'ks_picture'=> 'howitworks-2.png',
            'ks_title'=> "Add Unlimited Products",
            'ks_text'=> "Fill the inventory with your products and select the desired configuration to attract a diverse customer base. "
        ])->save();

        $this->ksHowItWorksFactory->create()->addData($ksData=[
            'ks_picture'=> 'howitworks-3.png',
            'ks_title'=> "Manage Order Fulfillment",
            'ks_text'=> "You will receive orders including the bulk orders through a diverse customer base from all geographic locations, where you can manage order, invoice, shipment, and credit memo on your own."
        ])->save();

        $this->ksHowItWorksFactory->create()->addData($ksData=[
            'ks_picture'=> 'howitworks-4.png',
            'ks_title'=> "Payment",
            'ks_text'=> "Once the order is delivered, you will receive your earnings within the given payment cycle. The process will remain the same for digital as well as ‘Cash On Delivery’ payments modes."
        ])->save();
        
        $ksIndex = time();

        $ksDefaultLogo = 'ks_marketplace_seller_portal_profile/ks_marketplace_sellerpanel/ks_sellerpanel_logo';
        $ksWhySellField = 'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_why_to_sell';
        $ksWhySellValue = [
            '_'.$ksIndex.'_0' => ["whysell" => "Sell customers globally"],
            '_'.$ksIndex.'_1' => ["whysell" => "Regular Payments"],
            '_'.$ksIndex.'_2' => ["whysell" => "Compare products with peers"],
            '_'.$ksIndex.'_3' => ["whysell" => "Increases Brand visibility"],
            '_'.$ksIndex.'_4' => ["whysell" => "Ease in configuring seller profile"],
        ];
        //save why to sell data
        $this->ksConfigFactory->saveConfig($ksWhySellField, json_encode($ksWhySellValue), 'default', 0);
        $this->ksConfigFactory->saveConfig($ksDefaultLogo, 'default/KMM_Logo.png', 'default', 0);

        $ksFaqField = 'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_faq';
        $ksFaqValue = [
            '_'.$ksIndex.'_5' => ["question" => " Will I have my own profile to market my product?","answer"=> "Indeed, you’ll have a Seller Profile which can be customized based on your requirements. Above that, you can also highlight the products that are recently added and those which are best selling. You can also add another section inspired by the wishlist of the customers."],
            '_'.$ksIndex.'_6' => ["question" => "Will I get options to add SEO for my seller shop?","answer"=> "Ofcourse, you’ll have an option to add SEO details in order to place the desired keywords in the URL of your Shop, apart from the description content of your product."],
            '_'.$ksIndex.'_7' => ["question" => " What would be the criteria for the commission?","answer"=> "The Commission fee will differ depending on the category of your product, final price, and service you choose to avail from Marketplace. Additionally, you can use the earning calculator to analyse the commission on the product."],
            '_'.$ksIndex.'_8' => ["question" => "Can I sell my product without having a website of my own?","answer"=> " You don’t require a website to sell your products. After you’ve registered your Shop, you can add unlimited products to the inventory and attract the target customers."],
            '_'.$ksIndex.'_9' => ["question" => "I want to connect with the global audience to sell my product. Do I have the option of selling my products outside the country?","answer"=> "Yes, you can sell your products in any country around the globe. Your geographic location won’t be a hindrance to attract global audiences and elevate your reach, you can also highlight your location and let your customers know more about you."],
            '_'.$ksIndex.'_10' => ["question" => "Once I add my product, how will the customer get to know that it’s my product?","answer"=> "The Seller information will be available on the product page, and the invoice sent to the customer will highlight your name, as well. So, the customer will get to know that it’s your product."],
        ];
        //save faq data
        $this->ksConfigFactory->saveConfig($ksFaqField, json_encode($ksFaqValue), 'default', 0);

        //save faq data
        $ksProductTypeField = 'ks_marketplace_catalog/ks_product_type_settings/ks_product_type';
        $ksProductType = 'simple,virtual,bundle,downloadable,configurable,grouped';
        $this->ksConfigFactory->saveConfig($ksProductTypeField, $ksProductType, 'default', 0);
        $this->moduleDataSetup->getConnection()->endSetup();
        $this->moduleDataSetup->endSetup();
    }

    /**
     * ksMoveDirToMediaDir move directories to media
     * @return void
     */
    private function ksMoveDirToMediaDir($ksImage)
    {
        try {
            $ksObjManager = \Magento\Framework\App\ObjectManager::getInstance();
            $ksReader = $ksObjManager->get('Magento\Framework\Module\Dir\Reader');
            $ksFilesystem = $ksObjManager->get('Magento\Framework\Filesystem');
            $ksType = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $ksSmpleFilePath = $ksFilesystem->getDirectoryRead($ksType)
                ->getAbsolutePath().'ksolves/multivendor/';
            
            $ksModulePath = $ksReader->getModuleDir('', 'Ksolves_MultivendorMarketplace');
            $ksMediaFile = $ksModulePath.'/view/frontend/web/images/marketplace/'.$ksImage;
            if (!file_exists($ksSmpleFilePath)) {
                $this->ksDriver->createDirectory($ksSmpleFilePath, 0777, true);
            }
 
            $ksFilePath = $ksSmpleFilePath.$ksImage;
            if (!file_exists($ksFilePath)) {
                if (file_exists($ksMediaFile)) {
                    copy($ksMediaFile, $ksFilePath);
                }
            }
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
    }

    /**
     * Transfer default logo to media directory
     *
     * @param  [string] $ksImage
     */
    public function ksMoveImgToMediaDir($ksImage)
    {
        try {
            $ksObjManager = \Magento\Framework\App\ObjectManager::getInstance();
            $ksReader = $ksObjManager->get('Magento\Framework\Module\Dir\Reader');
            $ksFilesystem = $ksObjManager->get('Magento\Framework\Filesystem');
            $ksType = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $ksSmpleFilePath = $ksFilesystem->getDirectoryRead($ksType)
                ->getAbsolutePath().'marketplace/sellerpanel/default/';
            
            $ksModulePath = $ksReader->getModuleDir('', 'Ksolves_MultivendorMarketplace');
            $ksMediaFile = $ksModulePath.'/view/frontend/web/images/'.$ksImage;
            if (!file_exists($ksSmpleFilePath)) {
                $this->ksDriver->createDirectory($ksSmpleFilePath, 0777, true);
            }
 
            $ksFilePath = $ksSmpleFilePath.$ksImage;
            if (!file_exists($ksFilePath)) {
                if (file_exists($ksMediaFile)) {
                    copy($ksMediaFile, $ksFilePath);
                }
            }
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
