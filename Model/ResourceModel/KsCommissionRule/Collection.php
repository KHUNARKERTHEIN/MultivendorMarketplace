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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule;

/* Use Required Classes */
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Collection Model Class
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    /**
     * List of fields to fulltext search
     */
    private const KS_FIELDS_TO_FULLTEXT_SEARCH = [
        'ks_rule_name'
    ];

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

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
            'Ksolves\MultivendorMarketplace\Model\KsCommissionRule',
            'Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule'
        );
        parent::__construct(
            $ksEntityFactory,
            $ksLogger,
            $ksFetchStrategy,
            $ksEventManager,
            $ksConnection,
            $ksResource
        );
        $this->ksStoreManager = $ksStoreManager;
    }

    /**
     * Add fulltext filter
     *
     * @param  string $ksValue
     * @return $this
     */
    public function addFullTextFilter(string $ksValue)
    {
        $ksFields = self::KS_FIELDS_TO_FULLTEXT_SEARCH;
        $ksWhereCondition = '';
        foreach ($ksFields as $ksKey => $ksField) {
            $ksField = $ksField === 'region'
                ? $this->getRegionNameExpression()
                : 'main_table.' . $ksField;
            $ksCondition = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier($ksField),
                ['like' => "%".$ksValue."%"]
            );
            $ksWhereCondition .= ($ksKey === 0 ? '' : ' OR ') . $ksCondition;
        }
        if ($ksWhereCondition) {
            $this->getSelect()->where($ksWhereCondition);
        }

        return $this;
    }
}
