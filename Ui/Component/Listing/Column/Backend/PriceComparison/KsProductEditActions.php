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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\PriceComparison;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsProductEditActions Ui Class
 */
class KsProductEditActions extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'multivendor/pricecomparison/edit';

    /**
     * @var UrlInterface
     */
    protected $ksurlBuilder;

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
        UrlInterface $ksurlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksurlBuilder = $ksurlBuilder;
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
            foreach ($dataSource['data']['items'] as & $ksitem) {
                if (isset($ksitem['id'])) {
                    $ksitem[$this->getData('name')] = [
                        'edit' => [
                        'href' => $this->ksurlBuilder->getUrl(
                            static::KS_URL_PATH_EDIT,
                            [
                                        'id' => $ksitem['id']
                                    ]
                        ),
                        'label' => __('Edit')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
