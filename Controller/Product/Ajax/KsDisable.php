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

class KsDisable extends \Ksolves\MultivendorMarketplace\Controller\Product\MassDelete
{
    //Product disable
    public function execute()
    {
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $ksId =$this->getRequest()->getPost("entity_id");
        $ksStoreId =$this->getRequest()->getPost("store_id");
    
        if ($ksId) {
            //get status
            $ksStatus = 2;
            $ksProductIds[] = $ksId;
            try {
                if (!empty($ksProductIds)) {
                    $this->ksProductAction->updateAttributes($ksProductIds, ['status' => $ksStatus], (int) $ksStoreId);
                    $this->ksPriceIndexerProcessor->reindexList($ksProductIds);
                    $ksResponse->setData(['success' => true,
                        'message' => $this->messageManager->addSuccess("A product status has been disabled successfully.")
                    ]);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $ksResponse->setData(['error' => true,
                    'message' => $this->messageManager->addErrorMessage($e->getMessage())
                ]);
            }
        }
        return $ksResponse;
    }
}
