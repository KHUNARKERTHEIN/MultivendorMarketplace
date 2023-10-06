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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsPriceComaparisionProductEditActions Ui Class
 */
class KsPriceComaparisionProductEditActions extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'multivendor/pricecomparison/editproduct';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksksurlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $kscontext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($kscontext, $ksUiComponentFactory, $components, $data);
    }

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
                $ksItem[$this->getData('name')]['edit'] = [
                    'href' => $this->ksUrlBuilder->getUrl(
                        self::KS_URL_PATH_EDIT,
                        [
                            'id' => $ksItem['entity_id'],
                            'parent_id' => $ksItem['ks_parent_product_id']
                        ]
                    ),
                    'label' => __('Edit'),
                    'hidden' => true,
                ];
            }
        }
        return $dataSource;
    }
}
