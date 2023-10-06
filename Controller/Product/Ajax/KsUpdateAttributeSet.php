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
namespace Ksolves\MultivendorMarketplace\Controller\Product\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsUpdateAttributeSet Controller class
 */
class KsUpdateAttributeSet extends \Magento\Framework\App\Action\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param Context $ksContext
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        Context $ksContext,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        parent::__construct($ksContext);
    }

    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksProductData = $this->getRequest()->getPostValue();
        $this->ksDataPersistor->set('ks_seller_product', $ksProductData);
    }
}
