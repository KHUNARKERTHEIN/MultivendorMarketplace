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
use Magento\Framework\Registry;

/**
 * Class KsCustomOptions
 * @package Ksolves\MultivendorMarketplace\Controller\Product\Ajax
 */
class KsCustomOptions extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var LayoutFactory
     */
    protected $ksResultLayoutFactory;


    /**
     * @param Context $ksContext
     * @param LayoutFactory $ksResultLayoutFactory
     * @param Registry $ksRegistry
     */
    public function __construct(
        Context $ksContext,
        LayoutFactory $ksResultLayoutFactory,
        Registry $ksRegistry
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        $this->ksRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Show custom options in JSON format for specified products
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->ksRegistry->register('import_option_products', $this->getRequest()->getPost('products'));
        return $this->ksResultLayoutFactory->create();
    }
}
