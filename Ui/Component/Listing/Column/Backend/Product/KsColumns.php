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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product;

use Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product\KsColumns as KsFrontendColumns;

/**
 * KsColumn Class for Adding Columns in the Product Listing Grid
 */
class KsColumns extends KsFrontendColumns
{
    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $ksColumnSortOrder = self::KS_DEFAULT_COLUMNS_MAX_ORDER;
        foreach ($this->ksAttributeRepository->getKsAdminAttributeList() as $ksAttribute) {
            if ($this->ksCheckExistence($ksAttribute->getAttributeId())) {
                $ksConfig = [];
                if (!isset($this->components[$ksAttribute->getAttributeCode()])) {
                    $ksConfig['sortOrder'] = ++$ksColumnSortOrder;
                    if ($ksAttribute->getIsFilterableInGrid()) {
                        $ksConfig['filter'] = $this->getFilterType($ksAttribute->getFrontendInput());
                    }
                    $ksColumn = $this->ksColumnFactory->create($ksAttribute, $this->getContext(), $ksConfig);
                    $ksColumn->prepare();
                    $this->addComponent($ksAttribute->getAttributeCode(), $ksColumn);
                }
            }
        }
        parent::prepare();
    }
}
