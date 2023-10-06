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

namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Class KsAfterProductSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsAfterProductSave implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @var KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * @var ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var SerializerInterface
     */
    protected $ksSerialize;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavSellerHelper;

    /**
     * @param  Http $ksRequest
     * @param  DateTime $ksDate
     * @param  KsProductFactory $ksProductFactory
     * @param  ManagerInterface $ksMessageManager
     * @param  KsSellerFactory $ksSellerFactory
     * @param  SerializerInterface $ksSerialize
     * @param  KsProductHelper $ksProductHelper
     * @param  \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper
     */
    public function __construct(
        Http $ksRequest,
        DateTime $ksDate,
        KsProductFactory $ksProductFactory,
        ManagerInterface $ksMessageManager,
        KsSellerFactory $ksSellerFactory,
        SerializerInterface $ksSerialize,
        KsProductHelper $ksProductHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper
    ) {
        $this->ksRequest                  = $ksRequest;
        $this->ksDate                     = $ksDate;
        $this->ksProductFactory           = $ksProductFactory;
        $this->ksMessageManager           = $ksMessageManager;
        $this->ksSellerFactory            = $ksSellerFactory;
        $this->ksSerialize                = $ksSerialize;
        $this->ksProductHelper            = $ksProductHelper;
        $this->ksFavSellerHelper          = $ksFavSellerHelper;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        //get Data
        $ksData = $this->ksRequest->getParams();

        try {
            $ksProduct = $ksObserver->getProduct();

            //check product
            if ((!empty($ksProduct))) {
                $ksProductId = $ksProduct->getId();
                $ksPastType = $ksProduct->getOrigData('type_id');
                $ksPresentType = $ksProduct->getData('type_id');

                if ($ksPastType != $ksPresentType) {
                    $this->ksProductHelper->ksChangeProductOwnerSetting($ksProductId);
                }


                if (isset($ksData['product']['ks_seller_id'])) {
                    $ksSellerId = $ksData['product']['ks_seller_id'];
                    $ksOldSpecialPrice = $ksProduct->getOrigData('special_price');

                    $ksProductAutoApproval = $this->ksSellerFactory->create()
                        ->load($ksSellerId, 'ks_seller_id')
                        ->getKsProductAutoApproval();

                    //get product collection
                    $ksSellerProductCollection = $this->ksProductFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('ks_product_id', $ksProductId);
                    //check size
                    if ($ksSellerProductCollection->getSize()) {
                        foreach ($ksSellerProductCollection as $ksProduct) {
                            $ksId = $ksProduct->getId();
                        }
                        $this->ksAssignProductUpdate($ksId, $ksSellerId);
                    //get seller id
                    } elseif (isset($ksSellerId) && $ksSellerId != '') {
                        $this->ksSellerProductAssign($ksProductId, $ksSellerId);
                    }

                    //Email functionality for followers
                    if (isset($ksData['product']['special_price'])) {
                        $ksNewPrice = $ksData['product']['special_price'];

                        if ($ksNewPrice != 0 && $ksNewPrice != $ksOldSpecialPrice) {
                            $ksEditState = \Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmail::KS_EDIT_PRODUCT;
                            $this->ksFavSellerHelper->ksSaveFavSellerEmailData($ksSellerId, $ksData['id'], $ksOldSpecialPrice, $ksData['type'], $ksEditState);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $ksId
     * @param $ksSellerId
     * @param $ksProductAutoApproval
     * @throws \Exception
     */
    private function ksAssignProductUpdate($ksId, $ksSellerId)
    {
        $ksCollection = $this->ksProductFactory->create()->load($ksId);
        $ksCollection->setKsSellerId($ksSellerId);
        $ksCollection->setKsUpdatedAt($this->ksDate->gmtDate());
        $ksCollection->save();
    }

    /**
     * @param $ksProductId
     * @param $ksSellerId
     * @param $ksProductAutoApproval
     * @throws \Exception
     */
    private function ksSellerProductAssign($ksProductId, $ksSellerId)
    {
        $ksCollection = $this->ksProductFactory->create();
        $ksCollection->setKsProductId($ksProductId);
        $ksCollection->setKsSellerId($ksSellerId);
        $ksCollection->setKsProductApprovalStatus(KsProduct::KS_STATUS_APPROVED);
        $ksCollection->setKsRejectionReason("");
        $ksCollection->setKsCreatedAt($this->ksDate->gmtDate());
        $ksCollection->setKsUpdatedAt($this->ksDate->gmtDate());
        $ksCollection->save();
    }
}
