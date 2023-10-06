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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;

/**
 * AddToCart Controller Class
 */
class AddToCart extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $ksCart;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $ksProduct;

    /**
     * Initialize constructor.
     * @param Context $ksContext
     * @param JsonFactory $ksResultJsonFactory
     * @param Cart $ksCart
     * @param Product $ksProduct
     */
    public function __construct(
        Context $ksContext,
        JsonFactory $ksResultJsonFactory,
        Cart $ksCart,
        Product $ksProduct
    ) {
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksCart = $ksCart;
        $this->ksProduct = $ksProduct;
        parent::__construct($ksContext);
    }


    /**
     * Add To Cart Product page.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ksParams = array(
            'form_key' => $this->getRequest()->getParam('form_key'),
            'product'  => $this->getRequest()->getParam('product'),
            'qty'      => $this->getRequest()->getParam('qty'),
        );

        $ksProduct = $this->ksProduct->load($this->getRequest()->getParam('product'));
        $this->ksCart->addProduct($ksProduct, $ksParams);
        $this->ksCart->save();

        $ksResult = $this->ksResultJsonFactory->create();
        return $ksResult->setData($ksProduct);
    }
}
