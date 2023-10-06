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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\SellerGroup;

/**
 * Class KsSellerGroupActions
 */
class KsSellerGroupActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const KS_URL_PATH_EDIT = 'multivendor/sellergroup/edit';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext
     * @param \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory
     * @param array $ksComponents
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext,
        \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $ksDataSource
     * @return array
     */
    public function prepareDataSource(array $ksDataSource)
    {
        if (isset($ksDataSource['data']['items'])) {
            foreach ($ksDataSource['data']['items'] as & $ksItem) {
                $ksItem[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->ksUrlBuilder->getUrl(
                            static::KS_URL_PATH_EDIT,
                            [
                                'id' => $ksItem['id']
                            ]
                        ),
                        'label' => __('Edit')
                    ],
                ];
            }
        }
        return $ksDataSource;
    }
}
