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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\ProductAttribute;

/**
 * KsProductEditActions Ui Class
 */
class KsProductAttributeEditActions extends KsCustomAttributeAction
{

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['attribute_id'])) {
                    if ($this->ksCheckRequestAllowed()) {
                        $ksItem[$this->getData('name')]['edit'] = [
                            'href' => $this->ksUrlBuilder->getUrl(
                                self::KS_URL_PATH_EDIT,
                                ['attribute_id' => $ksItem['attribute_id']]
                            ),
                            'label' => __('Edit'),
                            'hidden' => true,
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }
}
