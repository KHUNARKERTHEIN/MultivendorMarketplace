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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\PriceComparison\Tabs;

/**
 * KsDownloadableLinks block
 */
class KsDownloadableLinks extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ksolves_MultivendorMarketplace::product/ks_downloadable_links.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $ksDownloadableFile;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $ksLink;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    protected $ksUrlFactory;

    protected $ksConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Magento\Downloadable\Helper\File $ksDownloadableFile
     * @param \Magento\Downloadable\Model\Link $ksLink
     * @param \Magento\Eav\Model\Entity\AttributeFactory $ksAttributeFactory
     * @param \Magento\Backend\Model\UrlFactory $ksUrlFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\Downloadable\Helper\File $ksDownloadableFile,
        \Magento\Downloadable\Model\Link $ksLink,
        \Magento\Eav\Model\Entity\AttributeFactory $ksAttributeFactory,
        \Magento\Backend\Model\UrlFactory $ksUrlFactory,
        array $data = []
    ) {
        $this->ksRegistry         = $ksRegistry;
        $this->ksJsonEncoder      = $ksJsonEncoder;
        $this->ksDownloadableFile = $ksDownloadableFile;
        $this->ksLink             = $ksLink;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksUrlFactory       = $ksUrlFactory;
        parent::__construct($ksContext, $data);
    }

    /**
     * get seller product
     *
     * @return object
     */
    public function getKsSellerProduct()
    {
        return $this->ksRegistry->registry('ks_product_modal');
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function KsisProductLinksCanBePurchasedSeparately()
    {
        return (bool) $this->getKsSellerProduct()->getData('links_purchased_separately');
    }

    /**
     * Retrieve Add button HTML
     *
     * @return string
     */
    public function getKsAddButtonHtml()
    {
        $ksAddButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Add New Link'),
                'id' => 'add_link_item',
                'class' => 'action-add',
                'data_attribute' => ['action' => 'add-link'],
            ]
        );
        return $ksAddButton->toHtml();
    }

    /**
     * Retrieve default links title
     *
     * @return string
     */
    public function getKsLinksTitle()
    {
        return $this->getKsSellerProduct()->getId() &&
            $this->getKsSellerProduct()->getTypeId() ==
            'downloadable' ? $this->getKsSellerProduct()->getLinksTitle() : $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Check exists defined links title
     *
     * @return bool
     */
    public function getKsUsedDefault()
    {
        return $this->getKsSellerProduct()->getAttributeDefaultValue('links_title') === false;
    }

    /**
     * Return true if price in website scope
     *
     * @return bool
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
     */
    public function getKsLinkData()
    {
        $ksLinkArr = [];
        if ($this->getKsSellerProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $ksLinkArr;
        }
        $ksLinks = $this->getKsSellerProduct()->getTypeInstance()->getLinks($this->getKsSellerProduct());
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
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $ksItem->getId(), 'type' => 'link', '_secure' => true]
                    ) . '">' . $ksFileHelper->getFileFromPathFile(
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
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $ksItem->getId(), 'type' => 'sample', '_secure' => true]
                    ) . '">' . $ksFileHelper->getFileFromPathFile(
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
            if ($this->getKsSellerProduct()->getStoreId() && $ksItem->getStoreTitle()) {
                $ksTmpLinkItem['store_title'] = $ksItem->getStoreTitle();
            }
            if ($this->getKsSellerProduct()->getStoreId() && $ksPriceWebsiteScope) {
                $ksTmpLinkItem['website_price'] = $ksItem->getWebsitePrice();
            }
            $ksLinkArr[] = new \Magento\Framework\DataObject($ksTmpLinkItem);
        }
        return $ksLinkArr;
    }

    /**
     * Return formatted price with two digits after decimal point
     *
     * @param float $value
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
     * @param string $type
     * @return string
     */
    public function getKsFileFieldName($ksType)
    {
        return $ksType;
    }

    /**
     * Retrieve Upload URL
     *
     * @param string $type
     * @return string
     */
    public function getKsUploadUrl($ksType)
    {
        return $this->ksUrlFactory->create()->getUrl(
            'adminhtml/downloadable_file/upload',
            ['type' => $ksType, '_secure' => false]
        );
    }

    /**
     * Retrieve config json
     *
     * @param string $KsType
     * @return string
     */
    public function getKsConfigJson($KsType = 'links')
    {
        $this->getKsConfig()->setUrl($this->getKsUploadUrl($KsType));
        $this->getKsConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getKsConfig()->setFileField($this->getFileFieldName($KsType));
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
    public function KsisSingleStoreMode()
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
    public function getKsBaseCurrencySymbol($ksStoreId)
    {
        return $this->_storeManager->getStore($ksStoreId)->getBaseCurrency()->getCurrencySymbol();
    }
}
