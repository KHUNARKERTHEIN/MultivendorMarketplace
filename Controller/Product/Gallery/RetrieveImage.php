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

namespace Ksolves\MultivendorMarketplace\Controller\Product\Gallery;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;

/**
 * Multivendor Product RetrieveImage controller.
 */
class RetrieveImage extends Action
{
    /**
     * @var RawFactory
     */
    protected $ksResultRawFactory;

    /**
     * @var Config
     */
    protected $ksMediaConfig;

    /**
     * @var Filesystem
     */
    protected $ksFileSystem;

    /**
     * @var AbstractAdapter
     */
    protected $ksImageAdapter;

    /**
     * @var Curl
     */
    protected $ksCurl;

    /**
     * @var File
     */
    protected $ksFileUtility;

    /**
     * @param Context $ksContext
     * @param RawFactory $ksResultRawFactory
     * @param Config $ksMediaConfig
     * @param Filesystem $ksFileSystem
     * @param AdapterFactory $ksImageAdapterFactory
     * @param Curl $ksCurl
     * @param File $ksFileUtility
     */
    public function __construct(
        Context $ksContext,
        RawFactory $ksResultRawFactory,
        Config $ksMediaConfig,
        Filesystem $ksFileSystem,
        AdapterFactory $ksImageAdapterFactory,
        Curl $ksCurl,
        File $ksFileUtility
    ) {
        $this->ksResultRawFactory = $ksResultRawFactory;
        $this->ksMediaConfig = $ksMediaConfig;
        $this->ksFileSystem = $ksFileSystem;
        $this->ksImageAdapter = $ksImageAdapterFactory->create();
        $this->ksCurl = $ksCurl;
        $this->ksFileUtility = $ksFileUtility;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $ksBaseTmpMediaPath = $this->ksMediaConfig->getBaseTmpMediaPath();
        try {
            $ksRemoteImageUrl = $this->getRequest()->getParam('remote_image');
            $ksBaseFileName = basename($ksRemoteImageUrl);
            $ksLocalFileName = Uploader::getCorrectFileName($ksBaseFileName);
            $ksLocalTmpFileName = Uploader::getDispretionPath($ksLocalFileName) . DIRECTORY_SEPARATOR . $ksLocalFileName;
            $ksLocalFileMediaPath = $ksBaseTmpMediaPath . ($ksLocalTmpFileName);
            $ksLocalUniqueFileMediaPath = $this->getNewFileName($ksLocalFileMediaPath);
            $this->ksSaveRemoteImage($ksRemoteImageUrl, $ksLocalUniqueFileMediaPath);
            $ksLocalFileFullPath = $this->getKsDestinationFileAbsolutePath($ksLocalUniqueFileMediaPath);
            $this->ksImageAdapter->validateUploadFile($ksLocalFileFullPath);
            $ksResult = $this->ksAppendResultSaveRemoteImage($ksLocalUniqueFileMediaPath);
        } catch (\Exception $e) {
            $ksResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $ksResponse */
        $ksResponse = $this->ksResultRawFactory->create();
        $ksResponse->setHeader('Content-type', 'text/plain');
        $ksResponse->setContents(json_encode($ksResult));

        return $ksResponse;
    }

    /**
     * @param string $ksFileName
     *
     * @return mixed
     */
    protected function ksAppendResultSaveRemoteImage($ksFileName)
    {
        $ksFileInfo = pathinfo($ksFileName);
        $ksTmpFileName = Uploader::getDispretionPath($ksFileInfo['basename']) . DIRECTORY_SEPARATOR . $ksFileInfo['basename'];
        $ksResult['name'] = $ksFileInfo['basename'];
        $ksResult['type'] = $this->ksImageAdapter->getMimeType();
        $ksResult['error'] = 0;
        $ksResult['size'] = filesize($this->getKsDestinationFileAbsolutePath($ksFileName));
        $ksResult['url'] = $this->ksMediaConfig->getTmpMediaUrl($ksTmpFileName);
        $ksResult['file'] = $ksTmpFileName;

        return $ksResult;
    }

    /**
     * @param string $ksFileUrl
     * @param string $localFilePath
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function ksSaveRemoteImage($ksFileUrl, $ksLocalFilePath)
    {
        $this->ksCurl->setConfig(['header' => false]);
        $this->ksCurl->write('GET', $ksFileUrl);
        $ksImage = $this->ksCurl->read();
        if (empty($ksImage)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not get preview image information. Please check your connection and try again.')
            );
        }
        $this->ksFileUtility->saveFile($ksLocalFilePath, $ksImage);
    }

    /**
     * @param string $ksLocalFilePath
     *
     * @return string
     */
    protected function getNewFileName($ksLocalFilePath)
    {
        $ksDestinationFile = $this->getKsDestinationFileAbsolutePath($ksLocalFilePath);
        $ksFileName = Uploader::getNewFileName($ksDestinationFile);
        $ksFileInfo = pathinfo($ksLocalFilePath);

        return $ksFileInfo['dirname'] . DIRECTORY_SEPARATOR . $ksFileName;
    }

    /**
     * @param string $ksLocalTmpFile
     *
     * @return string
     */
    protected function getKsDestinationFileAbsolutePath($ksLocalTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $ksMediaDirectory = $this->ksFileSystem->getDirectoryRead(FilesystemDirectoryList::MEDIA);
        $ksPathToSave = $ksMediaDirectory->getAbsolutePath();

        return $ksPathToSave . $ksLocalTmpFile;
    }
}
