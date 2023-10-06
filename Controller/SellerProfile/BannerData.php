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

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

use Magento\Framework\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsBannersFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * class BannerData
 */
class BannerData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsBannersFactory
     */
    protected $ksBannersFactory;

    /**
     * @var JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @param Context $ksContext
     * @param KsBannersFactory $ksBannersFactory
     * @param JsonFactory $ksResultJsonFactory
     */
    public function __construct(
        Context $ksContext,
        KsBannersFactory $ksBannersFactory,
        JsonFactory $ksResultJsonFactory
    ) {
        $this->ksBannersFactory = $ksBannersFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        parent::__construct($ksContext);
    }
   
    /**
     * Banner Details
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        //get id
        $ksId = $this->getRequest()->getPost('id');
        //get model data
        $ksBannerCollection = $this->ksBannersFactory->create()->load($ksId);
        //init array
        $ksBannerDetails=[
          "ks_picture" => $ksBannerCollection->getKsPicture(),  
          "ks_title" => $ksBannerCollection->getKsTitle(),
          "ks_text"   => $ksBannerCollection->getKsText()
        ];
        //create resultjson factory
        $ksResult = $this->ksResultJsonFactory->create();
        $ksResult->setData($ksBannerDetails);
        //return json data
        return $ksResult;
    }
}
