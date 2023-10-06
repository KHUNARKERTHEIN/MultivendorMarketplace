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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\ProductAttribute;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsAttributeSetName Class Name
 */
class KsAttributeSetName extends Column
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var AttributeManagementInterface
     */
    protected $ksAttributeManagementInterface;

    /**
     * @var ksAttributeSetRepositoryInterface
     */
    protected $ksAttributeSetRepositoryInterface;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param AttributeManagementInterface $ksAttributeManagementInterface
     * @param AttributeSetRepositoryInterface $ksAttributeSetRepositoryInterface,
     * @param KsDataHelper $ksDataHelper,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        AttributeManagementInterface $ksAttributeManagementInterface,
        AttributeSetRepositoryInterface $ksAttributeSetRepositoryInterface,
        KsDataHelper $ksDataHelper,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksAttributeManagementInterface = $ksAttributeManagementInterface;
        $this->ksAttributeSetRepositoryInterface = $ksAttributeSetRepositoryInterface;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksItem) {
                if (isset($ksItem['attribute_id'])) {
                    $ksName = $this->getData('name');
                    $ksItem[$ksName] = "<p>".$this->getKsAttributeSetNameFromAttributeId($ksItem['attribute_id']).'</p>';
                }
            }
        }
        return $dataSource;
    }


    /**
     * Get all the attribute in Attribute Set
     * @param array $ksAttributeSetId
     * @return array $ksAttributeIdArray
     */
    public function getKsAttributeSetNameFromAttributeId($ksAttributeId)
    {
        // Attribute Set Name Array
        $ksAttributeSetName = [];
        // Get Attribute Set from Configuration
        $ksAttributeSet = $this->ksDataHelper->getKsDefaultAttributes();
        // Iterate over Default Given Attribute Id
        foreach ($ksAttributeSet as $ksValue) {
            // Get Collection of Attribute Set
            $ksArray = $this->ksAttributeManagementInterface->getAttributes(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $ksValue);
            // Iterate to get all the attribute id in attribute set
            foreach ($ksArray as $ksRecord) {
                // Check the Attribute Id
                if ($ksRecord['attribute_id'] == $ksAttributeId) {
                    $ksAttributeSetName[] = $this->ksAttributeSetRepositoryInterface->get($ksRecord['attribute_set_id'])->getAttributeSetName();
                }
            }
        }
        // Convert it into String
        $ksAttributeSetName = implode(', ', $ksAttributeSetName);
        return $ksAttributeSetName;
    }
}
