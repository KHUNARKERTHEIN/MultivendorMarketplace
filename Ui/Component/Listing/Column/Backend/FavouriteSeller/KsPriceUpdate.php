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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\FavouriteSeller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsPriceUpdate Ui Class
 */
class KsPriceUpdate extends Column
{

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
        array $components = [],
        array $data = []
    ) {
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
                if (isset($ksitem['ks_seller_price_alert'])) {
                    $ksName = $this->getData('name');
                    if ($ksitem['ks_seller_price_alert']) {
                        $ksitem[$ksName] = html_entity_decode('<label class="ks-switch">
                            <input type="checkbox" class="ks-checkbox" data-id='.$ksitem['id'].' checked disabled>
                            <span class="ks-slider ks-round ks-disabled"></span>
                            </label>');
                    } else {
                        $ksitem[$ksName] = html_entity_decode('<label class="ks-switch">
                            <input class="ks-checkbox" data-id='.$ksitem['id'].' type="checkbox" disabled>
                            <span class="ks-slider ks-round ks-cursor-na" ></span>
                            </label>');
                    }
                }
            }
        }
        return $dataSource;
    }
}
