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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Show Class to Save the Visual Swatch Image
 */
class Show extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Helper to move image from tmp to catalog
     *
     * @var \Magento\Swatches\Helper\Media
     */
    protected $ksSwatchHelper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $ksAdapterFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $ksConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $ksFilesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $ksUploaderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Swatches\Helper\Media $ksSwatchHelper
     * @param \Magento\Framework\Image\AdapterFactory $ksAdapterFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $ksConfig
     * @param \Magento\Framework\Filesystem $ksFilesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $ksUploaderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Swatches\Helper\Media $ksSwatchHelper,
        \Magento\Framework\Image\AdapterFactory $ksAdapterFactory,
        \Magento\Catalog\Model\Product\Media\Config $ksConfig,
        \Magento\Framework\Filesystem $ksFilesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $ksUploaderFactory
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSwatchHelper = $ksSwatchHelper;
        $this->ksAdapterFactory = $ksAdapterFactory;
        $this->ksConfig = $ksConfig;
        $this->ksFilesystem = $ksFilesystem;
        $this->ksUploaderFactory = $ksUploaderFactory;
        parent::__construct($context);
    }

    /**
     * Image upload action in iframe
     *
     * @return string
     */
    public function execute()
    {
        try {
            $ksConfig = $this->ksConfig;
            $ksUploader = $this->ksUploaderFactory->create(['fileId' => 'datafile']);
            $ksUploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $ksImageAdapter = $this->ksAdapterFactory->create();
            $ksUploader->addValidateCallback('catalog_product_image', $ksImageAdapter, 'validateUploadFile');
            $ksUploader->setAllowRenameFiles(true);
            $ksUploader->setFilesDispersion(true);
            /** @var \Magento\Framework\Filesystem\Directory\Read $ksMediaDirectory */
            $ksMediaDirectory = $this->ksFilesystem->getDirectoryRead(DirectoryList::MEDIA);
            
            $ksResult = $ksUploader->save($ksMediaDirectory->getAbsolutePath($ksConfig->getBaseTmpMediaPath()));
            unset($ksResult['path']);

            $this->_eventManager->dispatch(
                'swatch_gallery_upload_image_after',
                ['result' => $ksResult, 'action' => $this]
            );

            unset($ksResult['tmp_name']);

            $ksResult['url'] = $this->ksConfig->getTmpMediaUrl($ksResult['file']);
            $ksResult['file'] = $ksResult['file'] . '.tmp';

            $ksNewFile = $this->ksSwatchHelper->moveImageFromTmp($ksResult['file']);
            $this->ksSwatchHelper->generateSwatchVariations($ksNewFile);
            $ksFileData = ['swatch_path' => $this->ksSwatchHelper->getSwatchMediaUrl(), 'file_path' => $ksNewFile];
            $this->getResponse()->setBody(json_encode($ksFileData));
        } catch (\Exception $e) {
            $ksResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody(json_encode($ksResult));
        }
    }
}
