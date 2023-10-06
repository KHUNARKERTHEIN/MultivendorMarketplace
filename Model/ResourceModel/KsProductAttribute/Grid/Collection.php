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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductAttribute\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection Class
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistryManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $ksEntityFactory
     * @param \Psr\Log\LoggerInterface $ksLogger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $ksFetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $ksEventManager
     * @param \Magento\Framework\Registry $ksRegistryManager
     * @param mixed $ksConnection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $ksResource
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $ksEntityFactory,
        \Psr\Log\LoggerInterface $ksLogger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $ksFetchStrategy,
        \Magento\Framework\Event\ManagerInterface $ksEventManager,
        \Magento\Framework\Registry $ksRegistryManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $ksConnection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $ksResource = null
    ) {
        $this->ksRegistryManager = $ksRegistryManager;
        parent::__construct($ksEntityFactory, $ksLogger, $ksFetchStrategy, $ksEventManager, $ksConnection, $ksResource);
    }

    /**
     *  Add filter by entity type id to collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|$this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setEntityTypeFilter($this->ksRegistryManager->registry('entityType'))->addFieldToFilter('ks_seller_id', 0);
        return $this;
    }
}
