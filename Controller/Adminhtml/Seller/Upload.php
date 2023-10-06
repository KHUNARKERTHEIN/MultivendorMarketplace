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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory as KsImageUploader;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Image\AdapterFactory;

/**
 * Upload Controller class
 */
class Upload extends \Magento\Backend\App\Action
{
    const KS_IMAGE_TMP_PATH = 'ksolves/tmp/multivendor';

    /**
     * @var KsImageUploader
     */
    protected $ksImageUploader;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var Filesystem
     */
    protected $ksFilesystem;

    /**
     * @var AdapterFactory
     */
    protected $ksAdapterFactory;

    protected $ksMediaDirectory;
 
    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param KsImageUploader $ksImageUploader
     * @param Filesystem $ksFilesystem
     * @param StoreManagerInterface $ksStoreManager
     * @param AdapterFactory $ksAdapterFactory
     */
    public function __construct(
        Context $ksContext,
        KsImageUploader $ksImageUploader,
        Filesystem $ksFilesystem,
        StoreManagerInterface $ksStoreManager,
        AdapterFactory $ksAdapterFactory
    ) {
        $this->ksImageUploader = $ksImageUploader;
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksStoreManager = $ksStoreManager;
        $this->ksAdapterFactory = $ksAdapterFactory;
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
     * Upload file controller action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $KsImageId = $this->_request->getParam('param_name', 'image');
        $ksImageArr = explode('[', $KsImageId);
        $ksLastWord = array_pop($ksImageArr);
        $ksImageField = rtrim($ksLastWord, ']');

        $ksFiles = $this->_request->getFiles();
        $ksMediaUrl = $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $ksImagesFiles = $ksFiles['ks_seller_account_details_tab']['ks_public_profile_section'];
        
        try {
            $ksBaseTmpPath = self::KS_IMAGE_TMP_PATH;
            $ksImagesTargetPath = $this->ksMediaDirectory->getAbsolutePath($ksBaseTmpPath);
            
            $ksUploader = $this->ksImageUploader->create(['fileId' => $ksImagesFiles[$ksImageField]]);
            
            if ($ksUploader) {
                $ksUploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $ksUploader->setAllowRenameFiles(true);
                $ksUploader->setAllowCreateFolders(true);
                $ksImageAdapter = $this->ksAdapterFactory->create();
                $ksUploader->addValidateCallback('custom_image_upload', $ksImageAdapter, 'validateUploadFile');
                $ksResult = $ksUploader->save($ksImagesTargetPath);
                
                if (!$ksResult) {
                    throw new LocalizedException(
                        __('File can not be saved to the destination folder.')
                    );
                }
                unset($ksResult['path']);
                $ksResult['tmp_name'] = str_replace('\\', '/', $ksResult['tmp_name']);
                
                $ksResult['url'] = $ksMediaUrl . $this->getKsFilePath($ksBaseTmpPath, $ksResult['file']);
                $ksResult['name'] = $ksResult['file'];
            }
        } catch (\Exception $e) {
            $ksResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($ksResult);
    }
}
