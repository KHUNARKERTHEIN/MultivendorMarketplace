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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\AssignProduct;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsAssignProductAction Ui Class
 */
class KsAssignProductAction extends Column
{
    /**
      * Urls for actions
      */
    public const KS_URL_PATH_EDIT = 'catalog/product/edit';
    public const KS_URL_PATH_REMOVE = 'multivendor/assignproduct/remove';

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
                if (isset($ksItem['id'])) {
                    $ksItem[$this->getData('name')] = html_entity_decode('
                                <a class="ks-assign-product-remove" href="' . $this->ksUrlBuilder->getUrl(
                        self::KS_URL_PATH_REMOVE,
                        [
                                        'entity_id' => $ksItem['id'],
                                    ]
                    ) . '">' . __('Remove') . '</a> | <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                        self::KS_URL_PATH_EDIT,
                        [
                                        'id' => $ksItem['entity_id'],
                                    ]
                    ) . '">' . __('Edit') . '</a> ');
                }
            }
        }
        return $dataSource;
    }
}
