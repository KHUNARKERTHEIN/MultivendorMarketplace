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

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Product Image Upload controller.
 */
class Upload extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $ksMediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var UploaderFactory
     */
    protected $ksFileUploaderFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var MediaConfig
     */
    protected $ksMediaConfig;

    /**
     * @param Context $ksContext
     * @param Filesystem $ksFilesystem
     * @param UploaderFactory $ksFileUploaderFactory
     * @param MediaConfig $ksMediaConfig
     * @param KsSellerHelper $ksSellerHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $ksContext,
        Filesystem $ksFilesystem,
        UploaderFactory $ksFileUploaderFactory,
        MediaConfig $ksMediaConfig,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksFileUploaderFactory = $ksFileUploaderFactory;
        $this->ksMediaConfig = $ksMediaConfig;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller) {
            try {
                $ksTarget = $this->ksMediaDirectory->getAbsolutePath(
                    $this->ksMediaConfig->getBaseTmpMediaPath()
                );
                $ksFileUploader = $this->ksFileUploaderFactory->create(
                    ['fileId' => 'image']
                );
                $ksFileUploader->setAllowedExtensions(
                    ['gif', 'jpg', 'png', 'jpeg']
                );
                $ksFileUploader->setFilesDispersion(true);
                $ksFileUploader->setAllowRenameFiles(true);
                $ksResultData = $ksFileUploader->save($ksTarget);

                unset($ksResultData['tmp_name']);
                unset($ksResultData['path']);

                $ksResultData['url'] = $this->ksMediaConfig->getTmpMediaUrl($ksResultData['file']);
                $ksResultData['file'] = $ksResultData['file'] . '.tmp';

                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode($ksResultData)
                );
            } catch (\Exception $e) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode(
                        [
                            'error' => $e->getMessage(),
                            'errorcode' => $e->getCode(),
                        ]
                    )
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
