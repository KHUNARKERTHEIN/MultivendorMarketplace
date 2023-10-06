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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollection;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory;

/**
 * Seller Controller Class
 */
abstract class Seller extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $ksFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var KsSellerCollection
     */
    protected $ksSellerCollection;

    /**
     * @var KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
    * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
    */
    protected $ksSellerGroupCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Ui\Component\MassAction\Filter $ksFilter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param KsSellerCollection $ksSellerCollection
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Ui\Component\MassAction\Filter $ksFilter,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        KsSellerCollection $ksSellerCollection,
        KsSellerStoreFactory $ksSellerStoreFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksSellerGroupCollectionFactory
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksDate = $ksDate;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksSellerGroupCollectionFactory = $ksSellerGroupCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_sellers');
    }
}
