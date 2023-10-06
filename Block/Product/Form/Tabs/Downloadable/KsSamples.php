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
 * KsSamples block
 */
class KsSamples extends \Magento\Framework\View\Element\Template
{
    /**
     * Block config data
     *
     * @var \Magento\Framework\DataObject
     */
    protected $ksConfig;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $ksDownloadableFile = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $ksSampleModel;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    protected $ksUrlFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Magento\Downloadable\Helper\File $ksDownloadableFile
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Downloadable\Model\Sample $ksSampleModel
     * @param \Magento\Backend\Model\UrlFactory $ksUrlFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\Downloadable\Helper\File $ksDownloadableFile,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Downloadable\Model\Sample $ksSampleModel,
        \Magento\Framework\UrlFactory $ksUrlFactory,
        array $data = []
    ) {
        $this->ksJsonEncoder = $ksJsonEncoder;
        $this->ksDownloadableFile = $ksDownloadableFile;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSampleModel = $ksSampleModel;
        $this->ksUrlFactory = $ksUrlFactory;
        parent::__construct($ksContext, $data);
    }

    /**
     * Get model of the product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getKsProduct()
    {
        return $this->ksCoreRegistry->registry('product');
    }

    /**
     * Retrieve samples array
     *
     * @return array
     */
    public function getKsSampleData()
    {
        $ksSamplesArr = [];
        if ($this->getKsProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $ksSamplesArr;
        }
        $ksSamples = $this->getKsProduct()->getTypeInstance()->getSamples($this->getKsProduct());
        $ksFileHelper = $this->ksDownloadableFile;
        foreach ($ksSamples as $ksItem) {
            $ksTmpSampleItem = [
                'sample_id' => $ksItem->getId(),
                'title' => $this->escapeHtml($ksItem->getTitle()),
                'sample_url' => $ksItem->getSampleUrl(),
                'sample_type' => $ksItem->getSampleType(),
                'sort_order' => $ksItem->getSortOrder(),
            ];

            $ksSampleFile = $ksItem->getSampleFile();
            if ($ksSampleFile) {
                $ksFile = $ksFileHelper->getFilePath($this->ksSampleModel->getBasePath(), $ksSampleFile);

                $ksFileExist = $ksFileHelper->ensureFileInFilesystem($ksFile);

                if ($ksFileExist) {
                    $ksName = '<a href="' . $this->getUrl(
                        'multivendor/product_downloadable_edit/sample',
                        ['id' => $ksItem->getId(), '_secure' => true]
                    ) . '" target="_blank">' . $ksFileHelper->getFileFromPathFile(
                        $ksSampleFile
                    ) . '</a>';
                    $ksTmpSampleItem['file_save'] = [
                        [
                            'file' => $ksSampleFile,
                            'name' => $ksName,
                            'size' => $ksFileHelper->getFileSize($ksFile),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($this->getKsProduct() && $ksItem->getStoreTitle()) {
                $ksTmpSampleItem['store_title'] = $ksItem->getStoreTitle();
            }
            $ksSamplesArr[] = new \Magento\Framework\DataObject($ksTmpSampleItem);
        }

        return $ksSamplesArr;
    }

    /**
     * Check exists defined samples title
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getKsUsedDefault()
    {
        return $this->getKsProduct()->getAttributeDefaultValue('samples_title') === false;
    }

    /**
     * Retrieve Default samples title
     *
     * @return string
     */
    public function getKsSamplesTitle()
    {
        return $this->getKsProduct()->getId()
        && $this->getKsProduct()->getTypeId() == 'downloadable' ? $this->getKsProduct()->getSamplesTitle() :
            $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Sample::XML_PATH_SAMPLES_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Retrieve config json
     *
     * @return string
     */
    public function getKsConfigJson()
    {
        $this->getKsConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getKsConfig()->setFileField('samples');
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
     * Get is single store mode
     *
     * @return bool
     */
    public function ksIsSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
