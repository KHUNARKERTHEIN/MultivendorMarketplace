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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CommissionRule\Edit\Analyzer;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsAttributesFields Block class
 */
class KsAttributesFields extends \Magento\Framework\View\Element\Template
{
    /**
     * @var productId
     */
    protected $ksProductId;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Framework\Stdlib\ksArrayUtils
     */
    protected $ksArrayUtils;

    /**
     * @var ProductModel
     */
    protected $ksProductModel;

    /**
     *
     * @param Context $ksContext
     * @param DataPersistorInterface $ksDataPersistor
     * @param ProductFactory $ksProductFactory
     * @param ArrayUtils $ksArrayUtils
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Framework\Stdlib\ArrayUtils $ksArrayUtils,
        array $ksData = []
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksArrayUtils = $ksArrayUtils;
        $this->ksProductModel = $ksProductFactory->create();
        parent::__construct($ksContext, $ksData);
    }

    /**
     * @return  product id
     */
    public function getProductsId()
    {
        $this->ksProductId = $this->ksDataPersistor->get('ks_current_product_id');
        return $this->ksProductId;
    }
    
    /**
     * @return  \Magento\Catalog\Model
     */
    public function getProduct()
    {
        $this->getProductsId();
        $ksProduct = $this->ksProductModel->load($this->ksProductId);
        return $ksProduct;
    }

    /**
     * @return array
     */
    public function getAllowAttributes()
    {
        if ($this->getProduct()->getTypeId() == 'configurable') {
            return $this->getProduct()->getTypeInstance()->getConfigurableAttributes($this->getProduct());
        } else {
            return [];
        }
    }

    /**
     * Decorate a plain array of arrays or objects
     *
     * @param array $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return array
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        return $this->ksArrayUtils->decorateArray($array, $prefix, $forceSetAll);
    }
}
