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
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsAddBundleProduct Controller class
 */
class KsAddBundleProduct extends \Magento\Framework\App\Action\Action
{
    /**
      * @var DataPersistorInterface
      */
    protected $ksDataPersistor;

    /**
     * @var LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @param Context $ksContext
     * @param LayoutFactory $ksResultLayoutFactory
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        Context $ksContext,
        LayoutFactory $ksResultLayoutFactory,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
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
        $ksExcludeProductIds = json_decode(stripslashes($this->getRequest()->getPost('ksExcludeProductIds')));
        $this->ksDataPersistor->set('ks_selected_bundle_productids', $ksExcludeProductIds);

        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }
}
