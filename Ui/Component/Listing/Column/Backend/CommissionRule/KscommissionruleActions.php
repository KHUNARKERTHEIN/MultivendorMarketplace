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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\CommissionRule;

/**
 * class KscommissionruleActions
 */
class KscommissionruleActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const KS_URL_PATH_EDIT = 'multivendor/commissionrule/edit';
    const KS_URL_PATH_LIST = 'multivendor/commissionrule/productslist';
    
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface                              $ksUrlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext
     * @param \Magento\Framework\View\Element\UiComponentFactory           $ksUiComponentFactory
     * @param array                                                        $ksComponents
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext,
        \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory,
        array $ksComponents = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $ksComponents, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param  array $ksDataSource
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
                        'data'=>$ksItem['id'],
                        'label' => __('Edit'),
                        'class' => 'ks-edit'
                    ],
                    'view_products' => [
                        'href' =>"#",
                        'label' => __('View Products'),
                        'data'=>$ksItem['id'],
                        'class' => 'ks-commission-action'
                    ],
                ];
            }
        }
        return $ksDataSource;
    }
}
