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

namespace Ksolves\MultivendorMarketplace\Controller\Product\Attribute;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\ConfigurableProduct\Model\AttributesList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonData;

/**
 * GetAttributes.
 */
class GetAttributes extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var AttributesList
     */
    protected $ksConfigurableAttributesList;

    /**
     * @var JsonData
     */
    protected $ksJsonData;

    /**
     * @param Context $context
     * @param KsSellerHelper $ksSellerHelper
     * @param AttributesList $ksConfigurableAttributesList
     * @param JsonData $ksJsonData
     */
    public function __construct(
        Context $ksContext,
        KsSellerHelper $ksSellerHelper,
        AttributesList $ksConfigurableAttributesList,
        JsonData $ksJsonData
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksConfigurableAttributesList = $ksConfigurableAttributesList;
        $this->ksJsonData = $ksJsonData;
        parent::__construct($ksContext);
    }

    /**
     * Get Eav Attributes action.
     */
    public function execute()
    {
        $ksAttributesArray = $this->ksConfigurableAttributesList
            ->getAttributes($this->getRequest()->getParam('attributes'));
        $this->getResponse()->representJson(
            $this->ksJsonData->jsonEncode($ksAttributesArray)
        );
    }
}
