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

namespace Ksolves\MultivendorMarketplace\Model\System\Config\Backend;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;

/**
 * KsMapApiKeySave content block
 */
class KsMapApiKeySave extends \Magento\Framework\App\Config\Value
{
    /**
     * @var ManagerInterface
     */
    protected $ksMessageManager;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param RequestInterface $ksRequest
    * @param ManagerInterface $ksMessageManager
    * @param AbstractResource $resource = null
    * @param AbstractDb $resourceCollection = null
    * @param array $data = []
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ManagerInterface $ksMessageManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksMessageManager = $ksMessageManager;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        try {
            if ($this->isValueChanged()) {
                $ksMapApiKey = $this->getValue();
                $ksUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=India&key=".$ksMapApiKey;
                $ksContent = json_decode(file_get_contents($ksUrl));
                    
                if ($ksContent->status == 'REQUEST_DENIED') {
                    $this->ksMessageManager->addNotice(__('Map API Key not activated.'));
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
        }

        return parent::afterSave();
    }
}
