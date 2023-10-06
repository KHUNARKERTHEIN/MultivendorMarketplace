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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute\Plugin;

use Ksolves\MultivendorMarketplace\Controller\ProductAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput;

/**
 * Plugin for product attribute save controller.
 */
class Save
{
    /**
     * @var ksConvertSwatchAttributeFrontendInput
     */
    private $ksConvertSwatchAttributeFrontendInput;

    /**
     * @param ksConvertSwatchAttributeFrontendInput $ksConvertSwatchAttributeFrontendInput
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
     * @param RequestInterface $ksRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(ProductAttribute\Save $subject, RequestInterface $ksRequest): array
    {
        $ksData = $ksRequest->getPostValue();
        $ksData = $this->ksConvertSwatchAttributeFrontendInput->execute($ksData);
        $ksRequest->setPostValue($ksData);

        return [$ksRequest];
    }
}
