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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup;

/* use required classes */
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Collection of KsSellerGroup
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param EntityFactoryInterface $ksEntityFactory,
     * @param LoggerInterface        $ksLogger,
     * @param FetchStrategyInterface $ksFetchStrategy,
     * @param ManagerInterface       $ksEventManager,
     * @param StoreManagerInterface  $ksStoreManager,
     * @param AdapterInterface       $ksConnection,
     * @param AbstractDb             $ksResource
     */
    public function __construct(
        EntityFactoryInterface $ksEntityFactory,
        LoggerInterface $ksLogger,
        FetchStrategyInterface $ksFetchStrategy,
        ManagerInterface $ksEventManager,
        StoreManagerInterface $ksStoreManager,
        AdapterInterface $ksConnection = null,
        AbstractDb $ksResource = null
    ) {
        $this->_init(
            'Ksolves\MultivendorMarketplace\Model\KsSellerGroup',
            'Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup'
        );
        parent::__construct($ksEntityFactory, $ksLogger, $ksFetchStrategy, $ksEventManager, $ksConnection, $ksResource);
        $this->storeManager = $ksStoreManager;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('id', 'ks_seller_group_name');
    }

    /**
     * Retrieve option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('id', 'ks_seller_group_name');
    }
}
