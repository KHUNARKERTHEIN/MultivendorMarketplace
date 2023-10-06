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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Banners;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save Controller
 */
class Save extends \Magento\Backend\App\Action
{
    const KS_IMAGE_TMP_PATH = 'ksolves/tmp/multivendor';
 
    const KS_IMAGE_PATH = 'ksolves/multivendor/';

    /**
     * @var ksDataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsBannersFactory
     */
    protected $ksBannersFactory;

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
     * @param \Ksolves\MultivendorMarketplace\Model\KsBannersFactory $ksBannersFactory
     * @param \Magento\Framework\Filesystem $ksFilesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Ksolves\MultivendorMarketplace\Model\KsBannersFactory $ksBannersFactory,
        \Magento\Framework\Filesystem $ksFilesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        DataPersistorInterface $ksDataPersistor
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksBannersFactory = $ksBannersFactory;
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksCoreFileStorageDatabase = $ksCoreFileStorageDatabase;
        $this->ksFileUploaderFactory = $fileUploaderFactory;
        $this->ksDataPersistor = $ksDataPersistor;
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
        //intialize variable
        $ksId = 0;
        // check data
        if ($ksData) {

            try {
                if (array_key_exists('id', $ksData['banners'])) {
                    $ksId = $ksData['banners']['id'];
                }
                //get Banners collection
                $ksBannersModel = $this->ksBannersFactory->create();
                // check id
                if ($ksId) {
                    // load Banners model class
                    $ksBannersModel->load($ksId);
                    if ($ksId != $ksBannersModel->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                //save benefits picture
                if (isset($ksData['banners']['ks_picture'][0]['name']) && isset($ksData['banners']['ks_picture'][0]['tmp_name'])) {
                    $ksBannersPicture = $this->ksMoveFileFromTmp($ksData['banners']['ks_picture'][0]['name']);
                    $ksData['banners']['ks_picture'] = $ksBannersPicture;

                } elseif (isset($ksData['banners']['ks_picture'][0]['name'])) {
                    $ksData['banners']['ks_picture'] = $ksData['banners']['ks_picture'][0]['name'];
                } else {
                    $ksData['banners']['ks_picture'] = '';
                }
                if ($ksId) {
                    $ksBannersModel->setId($ksId);
                    $ksBannersModel->setKsTitle($ksData['banners']['ks_title']);
                    $ksBannersModel->setKsText($ksData['banners']['ks_text']);
                    $ksBannersModel->setKsPicture($ksData['banners']['ks_picture']);
                    $ksBannersModel->save();
                } else {
                    $ksData['banners']['ks_seller_id'] = $this->ksDataPersistor->get('ks_current_seller_id');
                    $ksBannersModel->addData($ksData['banners'])->save();
                }
               
                $this->messageManager->addSuccess(__('You saved the Banners.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Banners.'));
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
