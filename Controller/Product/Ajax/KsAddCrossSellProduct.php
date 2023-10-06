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

/**
 * KsAddCrossSellProduct Controller class
 */
class KsAddCrossSellProduct extends \Magento\Framework\App\Action\Action
{

    /**
     * @var LayoutFactory
     */
    protected $ksResultLayoutFactory;


    /**
     * @param Context $ksContext
     * @param LayoutFactory $ksResultLayoutFactory
     */
    public function __construct(
        Context $ksContext,
        LayoutFactory $ksResultLayoutFactory
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        parent::__construct($ksContext);
    }

    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }
}
