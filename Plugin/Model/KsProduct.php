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

namespace Ksolves\MultivendorMarketplace\Plugin\Model;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;

/**
   class KsProduct 
 */
class KsProduct   
{
	/**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;
    
    /**
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
    ) {
    	$this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
    	$this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
        $this->ksProductFactory = $ksProductFactory;
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
	public function afterGetCategoryIds(\Magento\Catalog\Model\Product $subject, $result)
	{
		$ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$subject->getId());
		//iterate collection
		foreach ($ksProductCategoryCollection as $ksItem) {
			$ksIsSellerProduct = $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_product_id',$ksItem->getKsProductId())->getFirstItem();
			if(!empty($ksIsSellerProduct->getData())){
	            $ksCollection = $this->ksSellerCategoryCollection->create()
	            ->addFieldToFilter('ks_seller_id', $ksIsSellerProduct->getKsSellerId())
	            ->addFieldToFilter('ks_category_id',$ksItem->getKsCategoryId());
	            if (!in_array($ksItem->getKsCategoryId(),$result) && $ksCollection->getSize() > 0) {
	                $result[] = $ksItem->getKsCategoryId();
	            }
        	}
        }
		return $result;
	}

}