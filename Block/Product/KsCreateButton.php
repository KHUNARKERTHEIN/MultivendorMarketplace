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

namespace Ksolves\MultivendorMarketplace\Block\Product;

/**
 * KsCreateButton block class
 */
class KsCreateButton extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $ksTypeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $ksSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Catalog\Model\Product\TypeFactory $ksTypeFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Catalog\Model\Product\TypeFactory $ksTypeFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Magento\Customer\Model\SessionFactory $ksSession,
        array $ksData = []
    ) {
        $this->ksRegistry        = $ksRegistry;
        $this->ksTypeFactory     = $ksTypeFactory;
        $this->ksProductFactory  = $ksProductFactory;
        $this->ksProductHelper   = $ksProductHelper;
        $this->ksSession         = $ksSession;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Return current Product
     *
     * @return Product
     */
    public function getKsProduct()
    {
        return $this->ksRegistry->registry('product');
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    public function KsAddProductButtonOptions()
    {
        $ksTypes = $this->ksTypeFactory->create()->getTypes();

        $ksSellerProductType = $this->ksProductHelper->ksSellerProductList($ksTypes, $this->ksSession->create()->getId());

        uasort(
            $ksSellerProductType,
            function ($ksElementOne, $ksElementTwo) {
                return ($ksElementOne['sort_order'] < $ksElementTwo['sort_order']) ? -1 : 1;
            }
        );

        return $ksSellerProductType;
    }

    /**
     * @return int
     */
    public function ksDefaultAttributeSetId()
    {
        $ksAttributeData = $this->ksProductHelper
            ->getKsAttributeSet(
                $this->ksSession->create()->getId(),
                4
            );
        foreach ($ksAttributeData as $ksData) {
            if ($ksData == 4) {
                return $ksData;
            }
        }
        return $ksAttributeData[0];
    }
}
