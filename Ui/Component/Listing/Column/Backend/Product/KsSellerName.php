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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class KsSellerName
 * @package Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product
 */
class KsSellerName extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * KsSellerName constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksSellerFactory   = $ksSellerFactory;
        $this->ksUrlBuilder      = $ksUrlBuilder;
        $this->ksCustomerFactory = $ksCustomerFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $ksDataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $ksDataSource)
    {
        if (isset($ksDataSource['data']['items'])) {
            foreach ($ksDataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_seller_id'])) {
                    $ksCustomer = $this->ksCustomerFactory->create()->load($ksItem['ks_seller_id']);
                    $ksItem[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->ksUrlBuilder->getUrl(
                                    'multivendor/seller/edit',
                                    [
                                        'id' => $this->ksSellerFactory->create()
                                        ->load($ksItem['ks_seller_id'], 'ks_seller_id')->getId(),
                                        'seller_id' => $ksItem['ks_seller_id'],
                                    ]
                                ),
                                'label' =>$ksCustomer->getName(),
                            ],
                        ];
                }
            }
        }
        return $ksDataSource;
    }
}
