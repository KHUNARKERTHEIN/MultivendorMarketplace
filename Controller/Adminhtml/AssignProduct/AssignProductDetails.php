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
class AssignProductDetails extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory,
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute action
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('ks_assign_product_id');
        $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
        $this->ksDataPersistor->set('ks_assign_product_details', $ksId);
        $this->ksDataPersistor->set('ks_assign_product_seller_details', $ksSellerId);
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }
}
