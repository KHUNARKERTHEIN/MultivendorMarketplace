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

namespace Ksolves\MultivendorMarketplace\Controller\Product\Downloadable\File;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * *
 * downloadable Product KsUpload controller.
 */
class KsUpload extends Action
{
    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $ksLink;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $ksSample;

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $ksFileHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $ksUploaderFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $ksStorageDatabase;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Downloadable\Model\Link $ksLink
     * @param \Magento\Downloadable\Model\Sample $ksSample
     * @param \Magento\Downloadable\Helper\File $ksFileHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $ksUploaderFactory
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksStorageDatabase
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Downloadable\Model\Link $ksLink,
        \Magento\Downloadable\Model\Sample $ksSample,
        \Magento\Downloadable\Helper\File $ksFileHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $ksUploaderFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $ksStorageDatabase
    ) {
        $this->ksLink = $ksLink;
        $this->ksSample = $ksSample;
        $this->ksFileHelper = $ksFileHelper;
        $this->ksUploaderFactory = $ksUploaderFactory;
        $this->ksStorageDatabase = $ksStorageDatabase;
        parent::__construct($ksContext);
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $ksType = $this->getRequest()->getParam('type');
            $ksTmpPath = '';
            if ($ksType === 'samples') {
                $ksTmpPath = $this->ksSample->getBaseTmpPath();
            } elseif ($ksType === 'links') {
                $ksTmpPath = $this->ksLink->getBaseTmpPath();
            } elseif ($ksType === 'link_samples') {
                $ksTmpPath = $this->ksLink->getBaseSampleTmpPath();
            } else {
                throw new LocalizedException(__('Upload type can not be determined.'));
            }

            $ksUploader = $this->ksUploaderFactory->create(['fileId' => $ksType]);

            $ksResult = $this->ksFileHelper->uploadFromTmp($ksTmpPath, $ksUploader);

            if (!$ksResult) {
                throw new FileSystemException(
                    __('File can not be moved from temporary folder to the destination folder.')
                );
            }

            unset($ksResult['tmp_name'], $ksResult['path']);

            if (isset($ksResult['file'])) {
                $ksRelativePath = rtrim($ksTmpPath, '/') . '/' . ltrim($ksResult['file'], '/');
                $this->ksStorageDatabase->saveFile($ksRelativePath);
            }
        } catch (\Throwable $e) {
            $ksResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($ksResult);
    }
}
