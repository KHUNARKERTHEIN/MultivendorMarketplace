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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Downloadable;

/**
 * KsLinks
 */
class KsLinks extends \Magento\Framework\View\Element\Template
{
    /**
     * Block config data
     *
     * @var \Magento\Framework\DataObject
     */
    protected $ksConfig;

    /**
     * Purchased Separately Attribute cache
     *
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $ksPurchasedSeparatelyAttribute = null;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $ksDownloadableFile = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $ksCoreFileStorageDb = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $ksLink;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase
     * @param \Magento\Downloadable\Helper\File $ksDownloadableFile
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Downloadable\Model\Link $link
     * @param \Magento\Eav\Model\Entity\AttributeFactory $ksAttributeFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase,
        \Magento\Downloadable\Helper\File $ksDownloadableFile,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Downloadable\Model\Link $ksLink,
        \Magento\Eav\Model\Entity\AttributeFactory $ksAttributeFactory,
        array $data = []
    ) {
        $this->ksJsonEncoder = $ksJsonEncoder;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksCoreFileStorageDb = $ksCoreFileStorageDatabase;
        $this->ksDownloadableFile = $ksDownloadableFile;
        $this->ksLink = $ksLink;
        $this->ksAttributeFactory = $ksAttributeFactory;
        parent::__construct($ksContext, $data);
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getKsProduct()
    {
        return $this->ksCoreRegistry->registry('product');
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function ksIsProductLinksCanBePurchasedSeparately()
    {
        return (bool) $this->getKsProduct()->getData('links_purchased_separately');
    }

    /**
     * Retrieve default links title
     *
     * @return string
     */
    public function getKsLinksTitle()
    {
        return $this->getKsProduct()->getId() &&
            $this->getKsProduct()->getTypeId() ==
            'downloadable' ? $this->getKsProduct()->getLinksTitle() : $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Check exists defined links title
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getKsUsedDefault()
    {
        return $this->getKsProduct()->getAttributeDefaultValue('links_title') === false;
    }

    /**
     * Return true if price in website scope
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getKsIsPriceWebsiteScope()
    {
        $ksScope = (int)$this->_scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($ksScope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
            return true;
        }
        return false;
    }

    /**
     * Return array of links
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getKsLinkData()
    {
        $ksLinkArr = [];
        if ($this->getKsProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $ksLinkArr;
        }
        $ksLinks = $this->getKsProduct()->getTypeInstance()->getLinks($this->getKsProduct());
        $ksPriceWebsiteScope = $this->getKsIsPriceWebsiteScope();
        $ksFileHelper = $this->ksDownloadableFile;
        foreach ($ksLinks as $ksItem) {
            $ksTmpLinkItem = [
                'link_id' => $ksItem->getId(),
                'title' => $this->escapeHtml($ksItem->getTitle()),
                'price' => $this->getKsPriceValue($ksItem->getPrice()),
                'number_of_downloads' => $ksItem->getNumberOfDownloads(),
                'is_shareable' => $ksItem->getIsShareable(),
                'link_url' => $ksItem->getLinkUrl(),
                'link_type' => $ksItem->getLinkType(),
                'sample_file' => $ksItem->getSampleFile(),
                'sample_url' => $ksItem->getSampleUrl(),
                'sample_type' => $ksItem->getSampleType(),
                'sort_order' => $ksItem->getSortOrder(),
            ];

            $ksLinkFile = $ksItem->getLinkFile();
            if ($ksLinkFile) {
                $ksFile = $ksFileHelper->getFilePath($this->ksLink->getBasePath(), $ksLinkFile);

                $ksFileExist = $ksFileHelper->ensureFileInFilesystem($ksFile);

                if ($ksFileExist) {
                    $ksName = '<a href="' . $this->getUrl(
                        'multivendor/product_downloadable_edit/link',
                        ['id' => $ksItem->getId(), 'type' => 'link', '_secure' => true]
                    ) . '" target="_blank">' . $ksFileHelper->getFileFromPathFile(
                        $ksLinkFile
                    ) . '</a>';
                    $ksTmpLinkItem['file_save'] = [
                        [
                            'file' => $ksLinkFile,
                            'name' => $ksName,
                            'size' => $ksFileHelper->getFileSize($ksFile),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            $ksSampleFile = $ksItem->getSampleFile();
            if ($ksSampleFile) {
                $ksFile = $ksFileHelper->getFilePath($this->ksLink->getBaseSamplePath(), $ksSampleFile);

                $ksFileExist = $ksFileHelper->ensureFileInFilesystem($ksFile);

                if ($ksFileExist) {
                    $ksName = '<a href="' . $this->getUrl(
                        'multivendor/product_downloadable_edit/link',
                        ['id' => $ksItem->getId(), 'type' => 'sample', '_secure' => true]
                    ) . '" target="_blank">' . $ksFileHelper->getFileFromPathFile(
                        $ksSampleFile
                    ) . '</a>';
                    $ksTmpLinkItem['sample_file_save'] = [
                        [
                            'file' => $ksItem->getSampleFile(),
                            'name' => $ksName,
                            'size' => $ksFileHelper->getFileSize($ksFile),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($ksItem->getNumberOfDownloads() == '0') {
                $ksTmpLinkItem['is_unlimited'] = ' checked="checked"';
            }
            if ($this->getKsProduct()->getStoreId() && $ksItem->getStoreTitle()) {
                $ksTmpLinkItem['store_title'] = $ksItem->getStoreTitle();
            }
            if ($this->getKsProduct()->getStoreId() && $ksPriceWebsiteScope) {
                $ksTmpLinkItem['website_price'] = $ksItem->getWebsitePrice();
            }
            $ksLinkArr[] = new \Magento\Framework\DataObject($ksTmpLinkItem);
        }
        return $ksLinkArr;
    }

    /**
     * Return formatted price with two digits after decimal point
     *
     * @param float $ksValue
     * @return string
     */
    public function getKsPriceValue($ksValue)
    {
        return number_format($ksValue, 2, null, '');
    }

    /**
     * Retrieve max downloads value from config
     *
     * @return int
     */
    public function getKsConfigMaxDownloads()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link::XML_PATH_DEFAULT_DOWNLOADS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve File Field Name
     *
     * @param string $ksType
     * @return string
     */
    public function getKsFileFieldName($ksType)
    {
        return $ksType;
    }

    /**
     * Retrieve config json
     *
     * @param string $ksType
     * @return string
     */
    public function getKsConfigJson($ksType = 'links')
    {
        $this->getKsConfig()->setUrl($this->getUploadUrl($ksType));
        $this->getKsConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getKsConfig()->setFileField($this->getKsFileFieldName($ksType));
        $this->getKsConfig()->setFilters(['all' => ['label' => __('All Files'), 'files' => ['*.*']]]);
        $this->getKsConfig()->setReplaceBrowseWithRemove(true);
        $this->getKsConfig()->setWidth('32');
        $this->getKsConfig()->setHideUploadButton(true);
        return $this->ksJsonEncoder->encode($this->getKsConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getKsConfig()
    {
        if ($this->ksConfig === null) {
            $this->ksConfig = new \Magento\Framework\DataObject();
        }

        return $this->ksConfig;
    }

    /**
     * Is single store mode
     *
     * @return bool
     */
    public function ksIsSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Get base currency symbol
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getKsBaseCurrencyCode($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseCurrency()->getCurrencySymbol();
    }
}
