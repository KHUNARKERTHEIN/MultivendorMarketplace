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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipmentTrack;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Collection Model Class
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @param EntityFactoryInterface $ksEntityFactory,
     * @param LoggerInterface        $ksLogger,
     * @param FetchStrategyInterface $ksFetchStrategy,
     * @param ManagerInterface       $ksEventManager,
     * @param AdapterInterface       $ksConnection,
     * @param AbstractDb             $ksResource
     */
    public function __construct(
        EntityFactoryInterface $ksEntityFactory,
        LoggerInterface $ksLogger,
        FetchStrategyInterface $ksFetchStrategy,
        ManagerInterface $ksEventManager,
        AdapterInterface $ksConnection = null,
        AbstractDb $ksResource = null
    ) {
        $this->_init(
            'Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack',
            'Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipmentTrack'
        );
        parent::__construct(
            $ksEntityFactory,
            $ksLogger,
            $ksFetchStrategy,
            $ksEventManager,
            $ksConnection,
            $ksResource
        );
    }
}
