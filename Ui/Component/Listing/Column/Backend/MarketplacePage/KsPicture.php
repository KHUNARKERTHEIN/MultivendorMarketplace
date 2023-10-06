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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\MarketplacePage;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KsPicture
 */
class KsPicture extends Column
{
    const ALT_FIELD = 'title';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $ksImageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @param ContextInterface              $context
     * @param UiComponentFactory            $uiComponentFactory
     * @param StoreManagerInterface         $ksStoreManager
     * @param \Magento\Catalog\Helper\Image $ksImageHelper
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param array                         $components
     * @param array                         $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $ksStoreManager,
        \Magento\Catalog\Helper\Image $ksImageHelper,
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksImageHelper = $ksImageHelper;
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$item) {
                $url = '';
                if (isset($item[$fieldName])) {
                    if ($item[$fieldName] != '') {
                        $url = $this->ksStoreManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . "ksolves/multivendor/" . $item[$fieldName];
                    } else {
                        $url = $this->ksImageHelper->getDefaultPlaceholderUrl('small_image');
                    }
                } else {
                    $url = $this->ksImageHelper->getDefaultPlaceholderUrl('small_image');
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $ksUrl = $this->ksUrlBuilder->getCurrentUrl();
                if (str_contains($ksUrl, 'benefits')) {
                    $item[$fieldName . '_link'] = $this->ksUrlBuilder->getUrl(
                        'multivendor/benefits/edit',
                        ['id' => $item['id']]
                    );
                } else {
                    $item[$fieldName . '_link'] = $this->ksUrlBuilder->getUrl(
                        'multivendor/howitworks/edit',
                        ['id' => $item['id']]
                    );
                }
                $item[$fieldName . '_orig_src'] = $url;
            }
        }
        return $dataSource;
    }

    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
