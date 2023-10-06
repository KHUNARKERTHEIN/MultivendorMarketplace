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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Seller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsSellerEdit.
 */
class KsSellerEdit extends Column
{
    /**
     * url path to create session to login seller at frontend
     */
    const URL_PATH_LOGIN_AS_SELLER = 'multivendor/seller/loginasseller';
    
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface       $ksUrlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['ks_seller_id'])) {
                    $ksViewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $ksUrlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    $ksSellerStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_APPROVED;
                    // check seller status
                    if ($ksItem['ks_seller_status'] == $ksSellerStatus) {
                        $ksItem[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->ksUrlBuilder->getUrl(
                                    $ksViewUrlPath,
                                    [
                                        $ksUrlEntityParamName => $ksItem['id'],
                                        'seller_id' => $ksItem['ks_seller_id'],
                                    ]
                                ),
                                'label' => 'Edit',
                            ],
                            'login_as_seller' => [
                                'href' => $this->ksUrlBuilder->getUrl(
                                    static::URL_PATH_LOGIN_AS_SELLER,
                                    [
                                        'seller_id' => $ksItem['ks_seller_id'],
                                    ]
                                ),
                                'target' =>'_blank',
                                'label' => 'Login as Seller',
                                'post' => true,
                            ]
                        ];
                    } else {
                        $ksItem[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->ksUrlBuilder->getUrl(
                                    $ksViewUrlPath,
                                    [
                                        $ksUrlEntityParamName => $ksItem['id'],
                                        'seller_id' => $ksItem['ks_seller_id'],
                                    ]
                                ),
                                'label' => 'Edit',
                            ]
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
