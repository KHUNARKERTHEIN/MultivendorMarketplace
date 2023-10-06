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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Benefits;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

/**
 * Class Save Controller
 */
class Save extends \Magento\Backend\App\Action
{
    const KS_IMAGE_TMP_PATH = 'ksolves/tmp/multivendor';
 
    const KS_IMAGE_PATH = 'ksolves/multivendor';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory
     */
    protected $ksBenefitsFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $ksCoreFileStorageDatabase;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $ksFileUploaderFactory;

    protected $ksMediaDirectory;

    /**
     *
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory
     * @param \Magento\Framework\Filesystem $ksFilesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory,
        \Magento\Framework\Filesystem $ksFilesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksBenefitsFactory = $ksBenefitsFactory;
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksCoreFileStorageDatabase = $ksCoreFileStorageDatabase;
        $this->ksFileUploaderFactory = $fileUploaderFactory;
        parent::__construct($ksContext);
    }

    /**
     * Retrieve path
     *
     * @param string $ksPath
     * @param string $ksImageName
     *
     * @return string
     */
    public function getKsFilePath($ksPath, $ksImageName)
    {
        return rtrim($ksPath, '/') . '/' . ltrim($ksImageName, '/');
    }

    /**
     * Checking file for moving and move it
     *
     * @param string $ksImageName
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function ksMoveFileFromTmp($ksImageName)
    {
        $ksBaseTmpPath = self::KS_IMAGE_TMP_PATH;
        $ksBasePath = self::KS_IMAGE_PATH;

        $ksBaseImagePath = $this->getKsFilePath(
            $ksBasePath,
            Uploader::getNewFileName(
                $this->ksMediaDirectory->getAbsolutePath(
                    $this->getKsFilePath($ksBasePath, $ksImageName)
                )
            )
        );
        $ksBaseTmpImagePath = $this->getKsFilePath($ksBaseTmpPath, $ksImageName);
 
        try {
            $this->ksCoreFileStorageDatabase->copyFile(
                $ksBaseTmpImagePath,
                $ksBaseImagePath
            );
            $this->ksMediaDirectory->renameFile(
                $ksBaseTmpImagePath,
                $ksBaseImagePath
            );
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
 
        return str_replace('ksolves/multivendor/', '', $ksBaseImagePath);
    }

    /**
     * execute action
     * Save action
     */
    public function execute()
    {
        $ksData = [];
        // get form data
        $ksData = $this->getRequest()->getPostValue();
        //initialize variable
        $ksId = 0;
        // check data
        if ($ksData) {
            try {
                if (isset($ksData['benefits']['id'])) {
                //get id
                    $ksId = $ksData['benefits']['id'];
                }
                //get Benefits collection
                $ksBenefitsModel = $this->ksBenefitsFactory->create();
                // check id
                if ($ksId) {
                    // load Benefits model class
                    $ksBenefitsModel->load($ksId);
                    if ($ksId != $ksBenefitsModel->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                //save benefits picture
                if (isset($ksData['benefits']['ks_picture'][0]['name']) && isset($ksData['benefits']['ks_picture'][0]['tmp_name'])) {
                    $ksBenefitsPicture = $this->ksMoveFileFromTmp($ksData['benefits']['ks_picture'][0]['name']);
                    $ksData['benefits']['ks_picture'] = $ksBenefitsPicture;

                } elseif (isset($ksData['benefits']['ks_picture'][0]['name'])) {
                    $ksData['benefits']['ks_picture'] = $ksData['benefits']['ks_picture'][0]['name'];
                } else {
                    $ksData['benefits']['ks_picture'] = '';
                }
                if ($ksId) {
                    $ksBenefitsModel->setId($ksId);
                }
                $ksBenefitsModel->setKsTitle($ksData['benefits']['ks_title']);
                $ksBenefitsModel->setKsText($ksData['benefits']['ks_text']);
                $ksBenefitsModel->setKsPicture($ksData['benefits']['ks_picture']);
                $ksBenefitsModel->save();
                $this->messageManager->addSuccess(__('You saved the Benefits.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Benefits.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addException($e, $e->getMessage());
            }
            $ksResultRedirect = $this->resultRedirectFactory->create();
            //check for `back` parameter
            if ($this->getRequest()->getParam('back')) {
                    return $ksResultRedirect->setPath('*/*/edit', ['id' => $ksId]);
            }
            $ksResultRedirect->setPath('*/*/');
            return $ksResultRedirect;
        } else {
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $ksResultRedirect->setPath('*/*/');
            return $ksResultRedirect;
        }
        $ksResultRedirect = $this->resultRedirectFactory->create();
        //check for `back` parameter
        if ($this->getRequest()->getParam('back')) {
            return $ksResultRedirect->setPath('*/*/edit', ['id' => $ksId]);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
         //for redirecting url
        $ksResultRedirect->setPath('*/*/');
        return $ksResultRedirect;
    }
}
