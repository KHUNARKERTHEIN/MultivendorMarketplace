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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute\Plugin;

use Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput;

/**
 * Plugin for product attribute save controller.
 */
class Save
{
    /**
     * @var ConvertSwatchAttributeFrontendInput
     */
    private $ksConvertSwatchAttributeFrontendInput;

    /**
     * @param ConvertSwatchAttributeFrontendInput $ksConvertSwatchAttributeFrontendInput
     */
    public function __construct(
        ConvertSwatchAttributeFrontendInput $ksConvertSwatchAttributeFrontendInput
    ) {
        $this->ksConvertSwatchAttributeFrontendInput = $ksConvertSwatchAttributeFrontendInput;
    }

    /**
     * Performs the conversion of the frontend input value.
     *
     * @param Attribute\Save $subject
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(ProductAttribute\Save $subject, RequestInterface $request): array
    {
        $ksPostData = $request->getPostValue();
        $ksPostData = $this->ksConvertSwatchAttributeFrontendInput->execute($ksPostData);
        $request->setPostValue($ksPostData);

        return [$request];
    }
}
