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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Gallery;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductVideo\Helper\Media;

/**
 * KsContent block Class.
 */
class KsContent extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $ksProduct;

    /**
     * @var Config
     */
    protected $ksMediaConfig;

    /**
     * @var EncoderInterface
     */
    protected $ksJsonEncoderInterface;

    /**
     * @var Media
     */
    protected $ksProductVideoMediaHelper;

    /**
     * @var Random
     */
    protected $ksMathRandom;

    /**
     * @var Filesystem
     */
    protected $ksFilesystem;

    /**
     * @var Size
     */
    protected $ksFileSizeService;

    /**
     * @param Context $context
     * @param Config $ksMediaConfig
     * @param EncoderInterface $ksJsonEncoderInterface
     * @param Registry $ksRegistry
     * @param Product $ksProduct
     * @param Size $ksFileSizeService
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $data
     */
    public function __construct(
        Context $ksContext,
        Config $ksMediaConfig,
        EncoderInterface $ksJsonEncoderInterface,
        Registry $ksRegistry,
        Product $ksProduct,
        Size $ksFileSizeService,
        Media $ksProductVideoMediaHelper,
        Random $ksMathRandom,
        Filesystem $ksFilesystem,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksMediaConfig = $ksMediaConfig;
        $this->ksJsonEncoderInterface = $ksJsonEncoderInterface;
        $this->ksFileSizeService = $ksFileSizeService;
        $this->ksProductVideoMediaHelper = $ksProductVideoMediaHelper;
        $this->ksMathRandom = $ksMathRandom;
        $this->ksFilesystem = $ksFilesystem;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Get product image data.
     *
     * @return array
     */
    public function getKsProductImagesJson()
    {
        $ksProduct = $this->getKsProduct();
        if (isset($ksProduct)) {
            $ksMediaGalleryImages = $ksProduct->getMediaGalleryImages();
        } else {
            return '[]';
        }
        $ksProductImages = [];
        $ksMediaDir = $this->ksFilesystem->getDirectoryRead(DirectoryList::MEDIA);
        if (count($ksMediaGalleryImages) > 0) {
            foreach ($ksMediaGalleryImages as &$ksMediaGalleryImage) {
                $ksMediaGalleryImage['url'] = $this->ksMediaConfig->getMediaUrl(
                    $ksMediaGalleryImage['file']
                );

                $ksFileHandler = $ksMediaDir->stat($this->ksMediaConfig->getMediaPath($ksMediaGalleryImage['file']));
                $ksMediaGalleryImage['size'] = $ksFileHandler['size'];
                array_push($ksProductImages, $ksMediaGalleryImage->getData());
            }
            return $this->ksJsonEncoderInterface->encode($ksProductImages);
        }

        return '[]';
    }

    /**
     * @return array
     */
    public function getKsProductImageTypes()
    {
        $ksProductImageTypes = [];
        $ksProduct = $this->getKsProduct();
        foreach ($this->getKsProductMediaAttributes() as $ksAttribute) {
            $ksProductImageTypes[$ksAttribute->getAttributeCode()] = [
                'code' => $ksAttribute->getAttributeCode(),
                'value' => $ksProduct[$ksAttribute->getAttributeCode()],
                'label' => $ksAttribute->getFrontend()->getLabel(),
                'name' => 'product[' . $ksAttribute->getAttributeCode() . ']',
            ];
        }

        return $ksProductImageTypes;
    }

    /**
     * @return array
     */
    public function getKsProductMediaAttributes()
    {
        return $this->getKsProduct()->getMediaAttributes();
    }

    /**
     * @return \Magento\Framework\File\Size
     */
    public function getKsFileSizeService()
    {
        return $this->ksFileSizeService;
    }

    /**
     * Get html id
     *
     * @return mixed
     */
    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->ksMathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get widget options
     *
     * @return string
     */
    public function getKsVideoEncodedOptions()
    {
        return $this->ksJsonEncoderInterface->encode(
            [
                'saveVideoUrl' => $this->getUrl(
                    'multivendor/product_gallery/upload',
                    [
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                ),
                'saveRemoteVideoUrl' => $this->getUrl(
                    'multivendor/product_gallery/retrieveimage',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'htmlId' => $this->getHtmlId(),
                'youTubeApiKey' => $this->ksProductVideoMediaHelper->getYouTubeApiKey()
            ]
        );
    }

    /**
     * Get note for video url
     *
     * @return \Magento\Framework\Phrase
     */
    public function getKsNoteVideoUrl()
    {
        $ksResult = __('YouTube and Vimeo supported.');
        if ($this->ksProductVideoMediaHelper->getYouTubeApiKey() === null) {
            $ksResult = __(
                'Vimeo supported.<br />'
                . 'To add YouTube video, please ask admin to set YouTube API Key first.'
            );
        }
        return $ksResult;
    }
}
