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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsProductSetAction Ui Class
 */
class KsProductSetAction extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'multivendor/productattribute_set/edit';

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
        UiComponentFactory $ksuiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($kscontext, $ksuiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $ksItem) {
                if (isset($ksItem['attribute_set_id'])) {
                    $ksItem[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                [
                                    'id' => $ksItem['attribute_set_id']
                                ]
                            ),
                            'label' => 'Edit'
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
