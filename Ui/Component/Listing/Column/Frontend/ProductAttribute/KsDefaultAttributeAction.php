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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\ProductAttribute;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsDefaultAttributeAction Ui Class
 */
class KsDefaultAttributeAction extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'multivendor/productattribute/edit';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksksurlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $kscontext,
        UiComponentFactory $ksuiComponentFactory,
        KsSellerHelper $ksSellerHelper,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($kscontext, $ksuiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $ksitem) {
                if (isset($ksitem['attribute_id'])) {
                    $ksitem[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->ksUrlBuilder->getUrl(
                                static::KS_URL_PATH_EDIT,
                                [
                                    'attribute_id' => $ksitem['attribute_id']
                                ]
                            ),
                            'label' => __('View'),
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
