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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\AssignProduct;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;

/**
 * AssignProductDetails Controller Class
 */
class AssignProductList extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\View\Result\LayoutFactory $ksResultFactory,
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\LayoutFactory $ksResultFactory,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksResultFactory = $ksResultFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute action
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Get Seller Id from Param
        $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
        // Save it to DataPersistor
        $this->ksDataPersistor->set('ks_assign_product_seller_id', $ksSellerId);
        $ksResultPage = $this->ksResultFactory->create();
        return $ksResultPage;
    }
}
