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
 * KsDownloadableSamples block
 */
class KsDownloadableSamples extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ksolves_MultivendorMarketplace::product/ks_downloadable_samples.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $ksDownloadableFile;

    /**
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

    protected $ksConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase
     * @param \Magento\Downloadable\Helper\File $ksDownloadableFile
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Downloadable\Model\Sample $ksSampleModel
     * @param \Magento\Backend\Model\UrlFactory $ksUrlFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\Downloadable\Helper\File $ksDownloadableFile,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Downloadable\Model\Sample $ksSampleModel,
        \Magento\Backend\Model\UrlFactory $ksUrlFactory,
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
    public function getKsSellerProduct()
    {
        return $this->ksCoreRegistry->registry('ks_product_modal');
    }

    /**
     * Retrieve Add Button HTML
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
                'id' => 'add_sample_item',
                'class' => 'action-add',
                'data_attribute' => ['action' => 'add-sample'],
            ]
        );
        return $ksAddButton->toHtml();
    }

    /**
     * Retrieve samples array
     *
     * @return array
     */
    public function getKsSampleData()
    {
        $ksSamplesArr = [];
        if ($this->getKsSellerProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $ksSamplesArr;
        }
        $ksSamples = $this->getKsSellerProduct()->getTypeInstance()->getSamples($this->getKsSellerProduct());
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
                        'adminhtml/downloadable_product_edit/sample',
                        ['id' => $ksItem->getId(), '_secure' => true]
                    ) . '">' . $ksFileHelper->getFileFromPathFile(
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

            if ($this->getKsSellerProduct() && $ksItem->getStoreTitle()) {
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
        return $this->getKsSellerProduct()->getAttributeDefaultValue('samples_title') === false;
    }

    /**
     * Retrieve Default samples title
     *
     * @return string
     */
    public function getKsSamplesTitle()
    {
        return $this->getKsSellerProduct()->getId()
        && $this->getKsSellerProduct()->getTypeId() == 'downloadable' ? $this->getKsSellerProduct()->getSamplesTitle() :
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
        $ksUrl = $this->ksUrlFactory->create()->getUrl(
            'adminhtml/downloadable_file/upload',
            ['type' => 'samples', '_secure' => true]
        );
        $this->getKsConfig()->setUrl($ksUrl);
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
