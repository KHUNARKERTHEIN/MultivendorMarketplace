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
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsProductReview
 * @package Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product
 */
class KsProductReview extends Column
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $ksProductReview;

    /**
     * KsProductReview constructor.
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $ksProductReview
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $ksProductReview,
        array $components = [],
        array $data = []
    ) {
        $this->ksProductReview = $ksProductReview;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $ksDataSource
     *
     * @return array
     */
    public function prepareDataSource(array $ksDataSource)
    {
        if (isset($ksDataSource['data']['items'])) {
            foreach ($ksDataSource['data']['items'] as &$ksItem) {
                $ksProductReview = $this->ksProductReview->create();
                if (isset($ksItem['entity_id'])) {
                    $ksName = $this->getData('name');
                    $ksItem[$ksName] = $ksProductReview->addFieldToFilter('entity_id', $ksItem['entity_id'])->getSize();
                }
            }
        }
        return $ksDataSource;
    }
}
