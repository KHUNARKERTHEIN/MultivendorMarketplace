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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\ProductAttribute;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsProductAttributeEditActions Ui Class
 */
class KsProductAttributeEditActions extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_EDIT = 'catalog/product_attribute/edit';
    
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $kscontext,
        UiComponentFactory $ksuiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
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
                                'label' => __('Edit'),
                                'hidden' => true
                            ]
                        ];
                }
            }
        }
        return $dataSource;
    }
}
