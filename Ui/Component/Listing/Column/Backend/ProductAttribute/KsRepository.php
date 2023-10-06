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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\ProductAttribute;

use Magento\Catalog\Ui\Component\Listing\Attribute\Repository;

/**
 * KsRepository Class to Remove Seller Attribute from Product Listing Columns
 */
class KsRepository extends Repository
{

    /**
     * To Remove Seller Attribute From Listing
     */
    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter('additional_table.is_used_in_grid', 1)->addFilter('ks_seller_id', 0)->create();
    }
}
