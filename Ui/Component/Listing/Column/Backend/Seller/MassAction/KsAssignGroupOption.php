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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Seller\MassAction;

use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory;

/**
 * Class KsAssignGroupOption for Mass Action Group
 *
 */
class KsAssignGroupOption implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $ksOptions;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $ksData;

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $ksUrlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $ksParamName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $ksAdditionalData = [];

    /**
     * Constructor
     *
     * @param CollectionFactory $ksCollectionFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $data
     */
    public function __construct(
        CollectionFactory $ksCollectionFactory,
        UrlInterface $ksUrlBuilder,
        array $data = []
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksData = $data;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize() : mixed
    {
        if ($this->ksOptions === null) {
            $ksOptions = $this->ksCollectionFactory->create()->toOptionArray();
            $this->prepareData();
            foreach ($ksOptions as $optionCode) {
                $this->ksOptions[$optionCode['value']] = [
                    'type' => 'seller_group_' . $optionCode['value'],
                    'label' => __($optionCode['label']),
                    '__disableTmpl' => true
                ];

                if ($this->ksUrlPath && $this->ksParamName) {
                    $this->ksOptions[$optionCode['value']]['url'] = $this->ksUrlBuilder->getUrl(
                        $this->ksUrlPath,
                        [$this->ksParamName => $optionCode['value']]
                    );
                }

                $this->ksOptions[$optionCode['value']] = array_merge_recursive(
                    $this->ksOptions[$optionCode['value']],
                    $this->ksAdditionalData
                );
            }
            $this->ksOptions = $this->ksOptions ? array_values($this->ksOptions) : [];
        }

        return $this->ksOptions;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->ksData as $ksKey => $ksValue) {
            switch ($ksKey) {
                case 'urlPath':
                    $this->ksUrlPath = $ksValue;
                    break;
                case 'paramName':
                    $this->ksParamName = $ksValue;
                    break;
                case 'confirm':
                    foreach ($ksValue as $ksMessageName => $ksMessage) {
                        $this->ksAdditionalData[$ksKey][$ksMessageName] = (string) new Phrase($ksMessage);
                    }
                    break;
                default:
                    $this->ksAdditionalData[$ksKey] = $ksValue;
                    break;
            }
        }
    }
}
