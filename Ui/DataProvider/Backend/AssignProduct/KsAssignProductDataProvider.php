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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\AssignProduct;

use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

class KsAssignProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var array
     */
    protected $ksLoadedData;

    protected $meta;
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $ksDataPersistor,
        KsProductHelper $ksProductHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksProductHelper = $ksProductHelper;
        $this->meta = $this->prepareMeta($this->meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Prepares Meta
     *
     * @param  array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }


    /**
     * Get Meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->prepareMeta($meta);
        $ksProductId = $this->ksDataPersistor->get('ks_assign_product_details');
        $ksProductType = $this->ksProductHelper->ksCheckProductType($ksProductId);
        if ($ksProductType == 'simple' || $ksProductType == 'virtual' || $ksProductType == 'downloadable') {
            $meta['ks_marketplace_child_assign_product_tab']['arguments']['data']['config']['visible'] = 0;
        }
        return $meta;
    }
}
