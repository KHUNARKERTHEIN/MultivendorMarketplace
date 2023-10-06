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
 * KsAssignAction Ui Class
 */
class KsAssignAction extends Column
{
    /**
     * Get Assign Url
     */
    const KS_URL_PATH_ASSIGN = 'multivendor/assignproduct/assign';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$ksitem) {
                if (isset($ksitem['entity_id'])) {
                    $ksName = $this->getData('name');
                    $ksitem[$ksName] = html_entity_decode('
                                <button class="action-primary ks_assign_product" data-seller_id="'.$ksitem['ks_seller_id'].'" data-id="'.$ksitem['entity_id'].'" type="button">Assign</button>');
                }
            }
        }
        return $dataSource;
    }
}
