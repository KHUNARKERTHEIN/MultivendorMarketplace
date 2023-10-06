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

use Magento\Framework\Controller\ResultFactory;

/**
 * NewImageUpload Controller class
 */
class NewImageUpload extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Upload
{
    /**
     * Upload file controller action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $KsImageId = $this->_request->getParam('param_name', 'image');
       
        $ksMediaUrl = $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        try {
            $ksBaseTmpPath = self::KS_IMAGE_TMP_PATH;
            $ksImagesTargetPath = $this->ksMediaDirectory->getAbsolutePath($ksBaseTmpPath);
            
            $ksUploader = $this->ksImageUploader->create(['fileId' => $KsImageId]);
            
            if ($ksUploader) {
                $ksUploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                $ksUploader->setAllowRenameFiles(true);
                $ksUploader->setAllowCreateFolders(true);
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
