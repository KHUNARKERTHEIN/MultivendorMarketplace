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
 * Class CommissionRulesList Controller
 */
class CommissionRulesList extends \Magento\Backend\App\Action
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
        if (array_key_exists('ksAttributesData', $this->getRequest()->getParams())) {
            parse_str($this->getRequest()->getParam('ksAttributesData'), $ksAttributesData);
            $ksAttributes = $ksAttributesData;
            $this->ksDataPersistor->set('ks_attributes', $ksAttributes);
        } else {
            $this->ksDataPersistor->set('ks_attributes', null);
        }
        $ksProductId = $this->getRequest()->getParam('ksProductId');
        $ksSellerId = $this->getRequest()->getParam('ksSellerId');
        $ksProductPrice =  $this->getRequest()->getParam('ksPrice');
        $ksDiscount =  $this->getRequest()->getParam('ksDiscount');
        $ksQuantity =  $this->getRequest()->getParam('ksQuantity');
        $ksTaxRate = $this->getRequest()->getParam('ksTax');

        $this->ksDataPersistor->set('ks_current_product_id', $ksProductId);
        $this->ksDataPersistor->set('ks_price', $ksProductPrice);
        $this->ksDataPersistor->set('ks_tax_rate', $ksTaxRate);
        $this->ksDataPersistor->set('ks_discount', $ksDiscount);
        $this->ksDataPersistor->set('ks_quantity', $ksQuantity);
        $this->ksDataPersistor->set('ks_current_seller_id', $ksSellerId);
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage ;
    }
}
