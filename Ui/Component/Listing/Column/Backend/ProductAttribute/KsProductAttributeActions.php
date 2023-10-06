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
 * KsProductAttributeActions Ui Class
 */
class KsProductAttributeActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Urls for actions
     */
    const KS_URL_PATH_APPROVE = 'multivendor/productattribute/approve';

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
     * s
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksitem) {
                if (isset($ksitem['ks_attribute_approval_status'])) {
                    $ksName = $this->getData('name');
                    $ksViewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $ksIndex = $this->getData('config/indexField') ?: '#';
                    if ($ksIndex == 'ks_attribute_id') {
                        $ksArray = [
                            'seller_id' => $ksitem['ks_seller_id'],
                            'attribute_id' => $ksitem['attribute_id']
                        ];
                    } else {
                        $ksArray = [
                            'attribute_id' => $ksitem['attribute_id'],
                        ];
                    }
                    if ($ksitem['ks_attribute_approval_status'] == '0' || $ksitem['ks_attribute_approval_status'] == '3') {
                        $ksitem[$ksName] = html_entity_decode('
                            <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_APPROVE,
                            [
                                    'attribute_id' => $ksitem['attribute_id'],
                                ]
                        ) . '"  data-id="' .$ksitem['attribute_id']. '">' . __('Approve') .'</a> |
                            <a href="#"
                            class="ks-attribute-reject" data-id="'.$ksitem['attribute_id'].'">' . __('Reject') . '</a> | 
                            <a href="' . $this->ksUrlBuilder->getUrl(
                            $ksViewUrlPath,
                            $ksArray
                        ) . '"
                            class="ks-attribute-view" data-id="'.$ksitem['attribute_id'].'">' . __('Edit') . '</a>');
                    } elseif ($ksitem['ks_attribute_approval_status'] == '1') {
                        $ksitem[$ksName] = html_entity_decode('
                            <a href="#"
                            class="ks-attribute-reject" data-id="'.$ksitem['attribute_id'].'">' . __('Reject') . '</a> | 
                            <a href="' . $this->ksUrlBuilder->getUrl(
                            $ksViewUrlPath,
                            $ksArray
                        ) . '"
                            class="ks-attribute-view" data-id="'.$ksitem['attribute_id'].'">' . __('Edit') . '</a>');
                    } else {
                        $ksitem[$ksName] = html_entity_decode('
                            <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_APPROVE,
                            [
                                    'attribute_id' => $ksitem['attribute_id'],
                                ]
                        ) . '"  data-id="' .$ksitem['attribute_id']. '">' . __('Approve') .'</a> |
                            <a href="' . $this->ksUrlBuilder->getUrl(
                            $ksViewUrlPath,
                            $ksArray
                        ) . '"
                            class="ks-attribute-view" data-id="'.$ksitem['attribute_id'].'">' . __('Edit') . '</a>');
                    }
                }
            }
        }
        return $dataSource;
    }
}
