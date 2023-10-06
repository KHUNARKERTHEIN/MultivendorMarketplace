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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ProductAttributes Controller
 */
class ProductAttributes extends \Magento\Backend\App\Action
{

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * Initialize Controller
     *
     * @param Context $ksContext
     * @param DataPersistorInterface $ksDataPersistor
     * @param LayoutFactory $ksResultLayoutFactory
     */
    public function __construct(
        Context $ksContext,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        // get product id
        $ksProductId = $this->getRequest()->getParam('id');
        $this->ksDataPersistor->set('ks_current_product_id', $ksProductId);
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage ;
    }
}
