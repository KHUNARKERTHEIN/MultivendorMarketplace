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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\CategoryRequests;

/**
 * class KsCategoryName
 */
class KsCategoryName extends \Magento\Ui\Component\Listing\Columns\Column
{
    const KS_CATEGORY_URL = 'catalog/category/edit';
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;
 
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext
     * @param \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $ksContext,
        \Magento\Framework\View\Element\UiComponentFactory $ksUiComponentFactory,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$ksItem) {
                $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksItem['id']);
                $ksCategory=$this->ksCategoryFactory->create()->setStoreId($ksCategoryRequests->getKsStoreId())->load($ksItem['ks_category_id']);
                $ksParents = array_map('intval', explode('/', $ksCategory->getPath()));
                $ksText ='<div class="data-grid-cell-content">';
                $ksCount = 0;
                foreach ($ksParents as $ksParent) {
                    // category id not equal to root category
                    if ($ksParent!= \Magento\Catalog\Model\Category::TREE_ROOT_ID && $ksParent!=$ksCategory->getId()) {
                        $i = 0;
                        while($i<$ksCount){
                            $ksText .= "&nbsp;";
                            $i++;
                        }
                        $ksCat=$this->ksCategoryFactory->create()->setStoreId($ksCategoryRequests->getKsStoreId())->load($ksParent);
                        $ksText .= $ksCat->getName() . '<br>';
                        $ksCount = $ksCount + 3;
                    }
                }
                $i = 0;
                while($i<$ksCount){
                    $ksText .= "&nbsp;";
                    $i++;
                }
                $ksItem[$this->getData('name')] = $ksText;
                $ksItem[$this->getData('name')] .= html_entity_decode('<a href="' . $this->ksUrlBuilder->getUrl(self::KS_CATEGORY_URL, ['id' => $ksItem['ks_category_id'] , 'store' => $ksCategoryRequests->getKsStoreId()]) . '" target="_blank" >' . $ksCategory->getName() . '</a></div>');
            }
        }
        return $dataSource;
    }
}
