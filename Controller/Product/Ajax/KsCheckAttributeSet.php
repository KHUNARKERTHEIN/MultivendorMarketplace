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

namespace Ksolves\MultivendorMarketplace\Controller\Product\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * KsCheckAttributeSet Class to Check the Attribute Set belongs to the seller or not
 */
class KsCheckAttributeSet extends \Ksolves\MultivendorMarketplace\Controller\Product\MassDelete
{
    public function execute()
    {
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        try {
            $ksId =$this->getRequest()->getPost("set_id");
            if ($ksId) {
                // Get Seller id
                $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
                $ksSetCollection = $this->ksSetFactory->create()->addFieldToFilter('attribute_set_id', $ksId)->addFieldToFilter('ks_seller_id', $ksSellerId);
                if ($ksSetCollection->getSize()) {
                    $ksResponse->setData(['success' => true,
                                          'seller_attribute_set' => true
                    ]);
                } else {
                    $ksResponse->setData(['success' => true,
                                          'seller_attribute_set' => false
                    ]);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $ksResponse->setData(['error' => true,
                                  'message' => $e->getMessage()
                ]);
        }

        return $ksResponse;
    }
}
