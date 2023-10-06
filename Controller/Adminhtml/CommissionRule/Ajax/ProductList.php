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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ProductList Controller
 */
class ProductList extends \Magento\Backend\App\Action
{
    /**
     * @var ksProductFactory
     */
    protected $ksProductFactory;

    /**
     * Initialize Controller
     *
     * @param Context $ksContext
     * @param ProductFactory $ksProductFactory
     */
    public function __construct(
        Context $ksContext,
        ProductFactory $ksProductFactory
    ) {
        $this->ksProductFactory = $ksProductFactory;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        $ksSearchKey = $this->getRequest()->getParam('ks_search_key');
        $ksCatIds = $this->getRequest()->getParam('ks_category');
        // get product collection
        $ksProductList = $this->ksProductFactory->create()
                        ->getCollection()
                            ->addAttributeToFilter(
                                [
                                    ['attribute'=>'name', ['like' => '%'.$ksSearchKey.'%']],
                                ]
                            )
                            ->setOrder('name', 'ASC')
                            ->setPageSize(20)
                            ->setCurPage(1);

        if ($ksCatIds != null) {
            $ksProductList = $ksProductList->addCategoriesFilter(['in' => $ksCatIds]);
        }
        $ksProductList->joinField(
            'ks_seller_id',
            'ks_product_details',
            'ks_seller_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );
        $ksProductList->addFieldToFilter('ks_seller_id', ['nin'=>[null, '']])->addFieldToFilter('type_id', ['in'=>['simple', 'virtual', 'downloadable']]);

        // check size of product collection
        if ($ksProductList->getSize() > 0) {
            foreach ($ksProductList as $ksProduct) {
                $ksOptions[] = [
                    'value' => $ksProduct->getId(),
                    'label' => $ksProduct->getName(),
                ];
            }
        } else {
            $ksOptions[] = [
                'value' => '',
                'label' =>  "No Products available",
            ];
        }

        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksOptions
        ]);

        return $ksResponse;
    }
}
