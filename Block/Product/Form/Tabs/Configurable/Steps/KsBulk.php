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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Configurable\Steps;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\Image;

/**
 * KsBulk Block.
 */
class KsBulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @var VariationMediaAttributes
     */
    protected $ksMediaConfig;

    /**
     * @var ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var Image
     */
    protected $ksHelperImage;

    /**
     * @param Context $ksContext
     * @param MediaConfig $ksMediaConfig
     * @param ProductFactory $ksProductFactory
     * @param Image   $ksHelperImage
     */
    public function __construct(
        Context $ksContext,
        MediaConfig $ksMediaConfig,
        ProductFactory $ksProductFactory,
        Image $ksHelperImage
    ) {
        parent::__construct($ksContext);
        $this->ksMediaConfig = $ksMediaConfig;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksHelperImage = $ksHelperImage;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * @return array
     */
    public function getConfMediaAttributes()
    {
        static $ksSimple;
        if (empty($ksSimple)) {
            $ksSimple = $this->ksProductFactory->create()->setTypeId(
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            )->getMediaAttributes();
        }

        return $ksSimple;
    }

    /**
     * @return string
     */
    public function getPlaceholderImageUrl()
    {
        return $this->ksHelperImage->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * @return array
     */
    public function getAttributeImageTypes()
    {
        $ksAttrImageTypes = [];
        foreach ($this->ksMediaConfig->getMediaAttributeCodes() as $ksAttributeCode) {
            $ksAttrImageTypes[$ksAttributeCode] = [
                'code' => $ksAttributeCode,
                'value' => '',
                'label' => $ksAttributeCode,
                'scope' => '',
                'name' => $ksAttributeCode,
            ];
        }

        return $ksAttrImageTypes;
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getKsImageTypes()
    {
        $ksImageTypes = [];
        foreach ($this->ksMediaConfig->getMediaAttributeCodes() as $ksAttributeCode) {
            /* @var $attribute Attribute */
            $ksImageTypes[$ksAttributeCode] = [
                'code' => $ksAttributeCode,
                'value' => '',
                'label' => $ksAttributeCode,
                'scope' => '',
                'name' => $ksAttributeCode,
            ];
        }
        return $ksImageTypes;
    }
}
