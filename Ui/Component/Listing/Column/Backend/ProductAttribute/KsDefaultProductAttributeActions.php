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
 * KsDefaultProductAttributeActions Ui Class
 */
class KsDefaultProductAttributeActions extends Column
{
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
            foreach ($dataSource['data']['items'] as &$ksitem) {
                if (isset($ksitem['ks_include_in_marketplace'])) {
                    $ksName = $this->getData('name');
                    if ($ksitem['ks_include_in_marketplace']) {
                        $ksitem[$ksName] = html_entity_decode('<label class="ks-switch">
                            <input type="checkbox" class="ks-checkbox" data-id='.$ksitem['attribute_id'].' checked>
                            <span class="ks-slider ks-round"></span>
                            </label>');
                    } else {
                        $ksitem[$ksName] = html_entity_decode('<label class="ks-switch">
                            <input class="ks-checkbox" data-id='.$ksitem['attribute_id'].' type="checkbox">
                            <span class="ks-slider ks-round"></span>
                            </label>');
                    }
                }
            }
        }
        return $dataSource;
    }
}
