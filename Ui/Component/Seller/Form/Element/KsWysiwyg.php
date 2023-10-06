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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Seller\Form\Element;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Helper\Data as KsDataHelper;
use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

/**
 * KsWysiwyg form element
 *
 * @api
 * @since 100.1.0
 */
class KsWysiwyg extends \Magento\Ui\Component\Form\Element\AbstractElement
{
    const KS_NAME = 'wysiwyg';

    /**
     * Wysiwyg status enabled
     */
    const KS_WYSIWYG_ENABLED = 'enabled';

    /**
     * Wysiwyg status configuration path
     */
    const KS_WYSIWYG_STATUS_CONFIG_PATH = 'cms/wysiwyg/enabled';

    /**
     * Wysiwyg status hidden
     */
    const KS_WYSIWYG_HIDDEN = 'hidden';

    /**
     * Wysiwyg status disabled
     */
    const KS_WYSIWYG_DISABLED = 'disabled';

    /**
     * @var Form
     * @since 100.1.0
     */
    protected $ksForm;

    /**
     * @var Editor
     * @since 100.1.0
     */
    protected $ksEditor;

    /**
     * @var DataHelper
     * @since 101.0.0
     */
    protected $ksBackendHelper;

    /**
     * @var LayoutInterface
     * @since 101.0.0
     */
    protected $ksLayout;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $ksBackendUrl;

    /**
     * @var Filesystem
     * @since 101.0.0
     */
    protected $ksFilesystem;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider
     */
    private $ksConfigProvider;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $ksAuthorization;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var array
     */
    protected $ksWindowSize;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $ksAssetRepo;

    /**
     * @param ContextInterface $ksContext
     * @param FormFactory $ksFormFactory
     * @param ConfigInterface $ksWysiwygConfig
     * @param LayoutInterface $ksLayout
     * @param DataHelper $ksBackendHelper
     * @param \Magento\Backend\Model\UrlInterface $ksBackendUrl
     * @param \Magento\Framework\AuthorizationInterface $ksAuthorization
     * @param \Magento\Framework\View\Asset\Repository $ksAssetRepo
     * @param Filesystem $ksFilesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider $ksConfigProvider = null
     * @param \Magento\Framework\App\Request\Http $ksRequest
     * @param array $windowSize = []
     * @param array $ksComponents
     * @param array $ksData
     * @param array $ksConfig
     */
    public function __construct(
        ContextInterface $ksContext,
        FormFactory $ksFormFactory,
        ConfigInterface $ksWysiwygConfig,
        LayoutInterface $ksLayout,
        KsDataHelper $ksBackendHelper,
        \Magento\Backend\Model\UrlInterface $ksBackendUrl,
        \Magento\Framework\AuthorizationInterface $ksAuthorization,
        \Magento\Framework\View\Asset\Repository $ksAssetRepo,
        Filesystem $ksFilesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\App\Request\Http $ksRequest,
        \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider $ksConfigProvider = null,
        array $windowSize = [],
        array $components = [],
        array $data = [],
        array $config = []
    ) {
        $this->ksLayout = $ksLayout;
        $this->ksBackendHelper = $ksBackendHelper;
        $this->ksBackendUrl = $ksBackendUrl;
        $this->ksAssetRepo = $ksAssetRepo;
        $this->ksFilesystem = $ksFilesystem;
        $this->ksAuthorization = $ksAuthorization;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksWindowSize = $windowSize;
        $this->ksRequest = $ksRequest;
        $this->ksConfigProvider = $ksConfigProvider ?: ObjectManager::getInstance()
            ->get(\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider ::class);

        $ksWysiwygConfigData = isset($config['wysiwygConfigData']) ? $config['wysiwygConfigData'] : [];

        $this->ksForm = $ksFormFactory->create();
        $ksWysiwygId = $ksContext->getNamespace() . '_' . $data['name'];
        $this->ksEditor = $this->ksForm->addField(
            $ksWysiwygId,
            \Magento\Framework\Data\Form\Element\Editor::class,
            [
                'force_load' => true,
                'rows' => isset($config['rows']) ? $config['rows'] : 20,
                'name' => $data['name'],
                'config' => $this->getConfig($ksWysiwygConfigData),
                'wysiwyg' => isset($config['wysiwyg']) ? $config['wysiwyg'] : null,
            ]
        );
        $data['config']['content'] = $this->ksEditor->getElementHtml();
        $data['config']['wysiwygId'] = $ksWysiwygId;

        parent::__construct($ksContext, $components, $data);
    }

    /**
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
    }

    /**
     * Get component name
     *
     * @return string
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::KS_NAME;
    }

    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($ksData = [])
    {
        $ksConfig = new \Magento\Framework\DataObject();

        $ksConfig->setData(
            [
                'enabled' => $this->isEnabled(),
                'hidden' => $this->isHidden(),
                'baseStaticUrl' => $this->ksAssetRepo->getStaticViewFileContext()->getBaseUrl(),
                'baseStaticDefaultUrl' => str_replace('index.php/', '', $this->ksBackendUrl->getBaseUrl())
                    . $this->ksFilesystem->getUri(DirectoryList::STATIC_VIEW) . '/',
                'directives_url' => $this->ksBackendUrl->getUrl('cms/wysiwyg/directive'),
                'use_container' => false,
                'add_variables' => true,
                'add_widgets' => true,
                'no_display' => false,
                'add_directives' => true,
                'width' => '100%',
                'height' => '500px',
                'plugins' => [],
            ]
        );

        $ksConfig->setData('directives_url_quoted', preg_quote($ksConfig->getData('directives_url')));

        if (is_array($ksData)) {
            $ksConfig->addData($ksData);
        }

        if ($this->ksAuthorization->isAllowed('Magento_Cms::media_gallery') && $ksConfig->getData('add_images')) {
            $this->ksConfigProvider->processGalleryConfig($ksConfig);
            $ksConfig->addData(
                [
                    'files_browser_window_width' => $this->ksWindowSize['width'],
                    'files_browser_window_height' => $this->ksWindowSize['height'],
                ]
            );
        }
        if ($ksConfig->getData('add_widgets')) {
            $this->ksConfigProvider->processWidgetConfig($ksConfig);
        }

        if ($ksConfig->getData('add_variables')) {
            $this->ksConfigProvider->processVariableConfig($ksConfig);
        }

        return $this->ksConfigProvider->processWysiwygConfig($ksConfig);
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        $ksWysiwygState = $this->ksScopeConfig->getValue(
            self::KS_WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getKsStoreId()
        );
        return in_array($ksWysiwygState, [self::KS_WYSIWYG_ENABLED, self::KS_WYSIWYG_HIDDEN]);
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        $ksStatus = $this->ksScopeConfig->getValue(
            self::KS_WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $ksStatus == self::KS_WYSIWYG_HIDDEN;
    }
}
