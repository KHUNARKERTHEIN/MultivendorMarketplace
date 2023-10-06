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

namespace Ksolves\MultivendorMarketplace\Controller\ProductType;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Request\Http;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;

/**
 * MassAction Controller Class
 */
abstract class MassAction extends Action
{
    /**
     * @var \Magento\Framework\App\Request\Http $ksRequest
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $ksFormKey;

    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var CollectionFactory
     */
    protected $ksProductTypeCollectionFactory;

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
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksSellerFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param Context $ksContext
     * @param FormKey $ksFormKey
     * @param Http $ksRequest
     * @param CollectionFactory $ksProductTypeCollectionFactory
     * @param Filter $ksFilter
     * @param KsSellerHelper $ksSellerHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param KsSellerFactory $ksSellerFactory
     */
    public function __construct(
        Context $ksContext,
        FormKey $ksFormKey,
        Http $ksRequest,
        CollectionFactory $ksProductTypeCollectionFactory,
        Filter $ksFilter,
        KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        KsSellerFactory $ksSellerFactory
    ) {
        $this->ksFormKey = $ksFormKey;
        $this->ksRequest = $ksRequest;
        $this->ksRequest->setParam('form_key', $this->ksFormKey->getFormKey());
        $this->ksProductTypeCollectionFactory = $ksProductTypeCollectionFactory;
        $this->ksFilter = $ksFilter;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksSellerFactory = $ksSellerFactory;
        parent::__construct($ksContext);
    }
}
