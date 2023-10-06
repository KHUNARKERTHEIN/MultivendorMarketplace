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

use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class KsMarketplaceConfigReader
 * @package Ksolves\MultivendorMarketplace\Model\ItemProvider
 */
class KsMarketplaceConfigReader implements ConfigReaderInterface
{
	const KS_XML_PATH_CHANGE_FREQUENCY ='ks_marketplace_sitemap/ks_sitemap/ks_profile_frequency_marketplace';
	const KS_XML_PATH_PRIORITY = 'ks_marketplace_sitemap/ks_sitemap/ks_profile_priority_marketplace';
	const KS_XML_PATH_MARKETPLACE_STATUS = 'ks_marketplace_sitemap/ks_sitemap/ks_enable_marketplace';
	
	/**
 	* @var ScopeConfigInterface
 	*/
	private $ksScopeConfig;

	/**
 	* @param ScopeConfigInterface $scopeConfig
 	*
 	*/
	public function __construct(ScopeConfigInterface $ksScopeConfig)
	{
    	$this->ksScopeConfig = $ksScopeConfig;
	}

	/**
 	* @param int $storeId
 	* @return string
 	*/
	public function getPriority($storeId): string
	{	
    	$storeId = (int)$storeId;
    	return $this->getKsConfigValue(self::KS_XML_PATH_PRIORITY, $storeId);
	}

	/**
 	* @param int $storeId
 	* @return string
 	*/
	public function getChangeFrequency($storeId): string
	{
    	$storeId = (int)$storeId;
    	return $this->getKsConfigValue(self::KS_XML_PATH_CHANGE_FREQUENCY, $storeId);
	}

	/**
 	* @param int $storeId
 	* @return stringstatus of Marketplace 
 	*/
	public function getKsMarketplaceStatus($ksStoreId)
	{
		$ksStoreId = (int)$ksStoreId;
		return $this->getKsConfigValue(self::KS_XML_PATH_MARKETPLACE_STATUS, $ksStoreId);
	}

	/**
 	* @param string $configPath
 	* @param int $storeId
 	*
 	* @return string
 	*
 	*/
	private function getKsConfigValue(string $ksConfigPath, int $ksStoreId): string
	{
    	$ksConfigValue = $this->ksScopeConfig->getValue(
			$ksConfigPath,
        	ScopeInterface::SCOPE_STORE,
        	$ksStoreId
    	);
    	return (string)$ksConfigValue;
	}
}