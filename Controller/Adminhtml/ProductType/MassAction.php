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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductType;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory;
use Psr\Log\LoggerInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * MassAction Controller Class
 */
abstract class MassAction extends Action
{
    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var JsonFactory
     */
    protected $ksResultJsonFactory;

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
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param CollectionFactory $ksCollectionFactory
     * @param LoggerInterface $ksLogger
     * @param JsonFactory $ksResultJsonFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        CollectionFactory $ksCollectionFactory,
        LoggerInterface $ksLogger,
        JsonFactory $ksResultJsonFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksLogger = $ksLogger;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        parent::__construct($ksContext);
    }
}
