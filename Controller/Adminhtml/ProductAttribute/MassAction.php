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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * MassAction Controller abstract For Mass Actions
 */
abstract class MassAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var CollectionFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param CollectionFactory $ksAttributeCollection
     * @param AttributeFactory $ksAttributeFactory
     * @param KsFavouriteSellerHelper $ksSellerHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerHelper $ksHelper
     * @param KsProductHelper $ksProductHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        CollectionFactory $ksAttributeCollection,
        AttributeFactory $ksAttributeFactory,
        KsFavouriteSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        KsSellerHelper $ksHelper,
        KsProductHelper $ksProductHelper
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksAttributeCollection = $ksAttributeCollection;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksHelper = $ksHelper;
        $this->ksProductHelper = $ksProductHelper;
        parent::__construct($ksContext);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_product_attribute');
    }
}
