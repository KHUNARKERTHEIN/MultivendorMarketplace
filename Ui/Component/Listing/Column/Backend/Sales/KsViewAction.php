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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Sales;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsViewAction.
 */
class KsViewAction extends Column
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
     * Get item url
     * @return string
     */
    public function getViewUrl()
    {
        return $this->ksUrlBuilder->getUrl(
            $this->getData('config/viewUrlPath')
        );
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
                if (isset($ksItem['ks_approval_status'])) {
                    $ksName = $this->getData('name');
                    $ksViewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $ksUrlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    $ksViewUrlDeletePath = $this->getData('config/viewUrlPathDelete') ?: '#';
                    switch ($ksItem['ks_approval_status']) {
                        case 0:
                            $ksItem[$this->getData('name')] = [
                                'edit' => [
                                    'href' => $this->ksUrlBuilder->getUrl(
                                        $ksViewUrlPath,
                                        [
                                            $ksUrlEntityParamName => $ksItem['entity_id']
                                        ]
                                    ),
                                    'label' => 'Edit',
                                ]
                            ];
                            break;
                        case 1:
                            $ksItem[$this->getData('name')] = [
                                'edit' => [
                                    'href' => $this->ksUrlBuilder->getUrl(
                                        $ksViewUrlPath,
                                        [
                                            $ksUrlEntityParamName => $ksItem['entity_id']
                                        ]
                                    ),
                                    'label' => 'View',
                                ]
                            ];
                            break;
                        case 2:
                            $ksItem[$this->getData('name')] = [
                                'edit' => [
                                    'href' => $this->ksUrlBuilder->getUrl(
                                        $ksViewUrlPath,
                                        [
                                            $ksUrlEntityParamName => $ksItem['entity_id']
                                        ]
                                    ),
                                    'label' => 'Edit',
                                ]
                            ];
                            break;
                    }
                }
            }
        }
        return $dataSource;
    }
}
