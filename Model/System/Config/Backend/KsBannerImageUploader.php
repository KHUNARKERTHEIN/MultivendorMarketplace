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

namespace Ksolves\MultivendorMarketplace\Model\System\Config\Backend;

/**
 * Class KsBannerImageUploader.
 */
class KsBannerImageUploader extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * Upload max file size in kilobytes
     *
     * @var int
     */
    protected $_minFileWidth = 1000;

    /**
     * Upload min file size in kilobytes
     *
     * @var int
     */
    protected $_maxFileWidth = 2400;

    /**
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg','jpeg','png','gif'];
    }

    /**
     * Validation callback for checking max file height and width
     *
     * @param  string $filePath Path to temporary uploaded file
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateImageSize($filePath)
    {
        list($width, $height, $type, $attr) = getimagesize($filePath);
        
        if ($width > $this->_maxFileWidth || $width < $this->_minFileWidth) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The file you\'re uploading not in mentioned size range.')
            );
        }
    }


    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $file = $this->getFileData();
        if (!empty($file)) {
            $uploadDir = $this->_getUploadDir();
            try {
                /** @var Uploader $uploader */
                $uploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->addValidateCallback('width', $this, 'validateImageSize');
                $result = $uploader->save($uploadDir);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
            }

            $filename = $result['file'];
            if ($filename) {
                if ($this->_addWhetherScopeInfo()) {
                    $filename = $this->_prependScopeInfo($filename);
                }
                $this->setValue($filename);
            }
        } else {
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } elseif (is_array($value) && !empty($value['value'])) {
                $this->setValue($value['value']);
            } else {
                $this->unsValue();
            }
        }

        return $this;
    }
}
