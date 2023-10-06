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

namespace Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule;

use Magento\Framework\Indexer\AbstractProcessor;

class KsCommissionRuleProcessor extends AbstractProcessor
{
    /**
     * Indexer id
     */
    public const INDEXER_ID = 'commissionrule_product';
}
