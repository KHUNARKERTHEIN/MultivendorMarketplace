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

namespace Ksolves\MultivendorMarketplace\Model\ItemProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemFactory;


/**
* Class KsMarketplace
* @package Ksolves\MultivendorMarketplace\Model\ItemProvider
*/
class KsMarketplace implements ItemProviderInterface
{
	/**
 	* @var KsMarketplaceConfigReader
 	*/
	private $ksConfigReader;

	/**
 	* @var SitemapItemFactory
 	*/
	private $ksItemFactory;

	/**
 	* @var array
 	*/
	protected $ksSitemapItems = [];

	/**
 	* KsMarketplace constructor.
 	* @param KsMarketplaceConfigReader $configReader
 	* @param SitemapItemFactory $itemFactory
 	*/
	public function __construct(
    	KsMarketplaceConfigReader $ksConfigReader,
    	SitemapItemFactory $ksItemFactory
	) {
    	$this->ksConfigReader = $ksConfigReader;
    	$this->ksItemFactory = $ksItemFactory;
	}

	/**
 	* @param int $storeId
 	* @return array
 	* @throws NoSuchEntityException
 	*/
	public function getItems($storeId): array
	{
		if($this->getKsMarketplaceStatus($storeId)==1){
		    $ksObject = new \Magento\Framework\DataObject();
	        $ksObject->setId('id');
	        $ksObject->setUrl('multivendor/sellerprofile/sell/');
	        $ksObject->setUpdatedAt(date('Y-m-d h:i:s'));
	        $ksObject->setPriority($this->getKsPriority($storeId));
	        $ksObject->setChangeFrequency($this->getKsChangeFrequency($storeId));
	        $this->ksSitemapItems['id'] = $ksObject;
	    }
    	return $this->ksSitemapItems;
	}

	/**
 	* @param int $storeId
 	* @return string
 	*/
	private function getKsChangeFrequency(int $ksStoreId): string
	{
    	return $this->ksConfigReader->getChangeFrequency($ksStoreId);
	}

	/**
 	* @param int $storeId
 	* @return string
 	*/
	private function getKsPriority(int $ksStoreId): string
	{
    	return $this->ksConfigReader->getPriority($ksStoreId);
	}

	/**
 	* @param int $storeId
 	* @return string 	*
 	*/
	private function getKsMarketplaceStatus(int $ksStoreId)
	{
		return $this->ksConfigReader->getKsMarketplaceStatus($ksStoreId);
	}
}
