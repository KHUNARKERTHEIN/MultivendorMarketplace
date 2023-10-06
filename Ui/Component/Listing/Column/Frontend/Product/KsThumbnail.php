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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class KsThumbnail
 * @package Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product
 */
class KsThumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const NAME = 'thumbnail';

    public const ALT_FIELD = 'name';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @param ContextInterface                      $context
     * @param UiComponentFactory                    $uiComponentFactory
     * @param \Magento\Framework\UrlInterface       $urlBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image         $imageHelper
     * @param array                                 $components
     * @param array                                 $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->_productFactory = $productFactory;
        $this->_imageHelper = $imageHelper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = $this->_productFactory->create()->load($item['entity_id']);

                $ksProductThumbnailImage= $this->_imageHelper->init($product, 'product_thumbnail_image');
                $ksProductBaseImage = $this->_imageHelper->init($product, 'product_base_image');

                $item[$fieldName . '_src']      = $ksProductThumbnailImage->getUrl();
                $item[$fieldName . '_alt']      = $this->getAlt($item) ?: $ksProductThumbnailImage->getLabel();
                $item[$fieldName . '_orig_src'] = $ksProductBaseImage->getUrl();
                $item[$fieldName . '_link']     = $this->_urlBuilder->getUrl(
                    'multivendor/product/edit',
                    ['id' => $product->getEntityId()]
                );
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
