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
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * KsChildProductAction Ui Class
 */
class KsChildProductAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param DataPersistorInterface $ksDataPersistor,
     * @param KsProductHelper $ksProductHelper,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        DataPersistorInterface $ksDataPersistor,
        KsProductHelper $ksProductHelper,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksProductHelper = $ksProductHelper;
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
                    if ($this->ksCheckProduct($ksitem['entity_id'])) {
                        $ksitem[$ksName] = html_entity_decode('
                                <button class="action-secondary ks_link_delink_button" data-id="'.$ksitem['entity_id'].'" type="button">Delink</button>');
                    } else {
                        $ksitem[$ksName] = html_entity_decode('
                                <button class="action-secondary ks_delinked_products" data-id="'.$ksitem['entity_id'].'" type="button" title="This product will not be linked with the assigned product as either the product type, attribute set or website is not allowed to the Seller.">Link</button>');
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * check product conditions
     * @param $ksProductId
     * @return bool
     */
    public function ksCheckProduct($ksProductId)
    {
        $ksSellerId = $this->ksDataPersistor->get('ks_assign_product_seller_details');
        return $this->ksProductHelper->ksCheckProductCondition($ksProductId, $ksSellerId);
    }
}
