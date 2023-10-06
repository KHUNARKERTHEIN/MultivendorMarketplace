<?php

declare(strict_types=1);
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory as KsCommissionCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsCommissionFormDataProvider dataprovider class
 */
class KsCommissionFormDataProvider extends AbstractDataProvider
{
    /**
     * @var Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var ksDataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var array
     */
    protected $ksLoadedData;

    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $ksRequest,
        KsCommissionCollectionFactory $ksCommissionCollectionFactory,
        DataPersistorInterface $ksDataPersistor,
        StoreManagerInterface $ksStoreManager,
        array $meta = [],
        array $data = []
    ) {
        $ksId                       = $ksRequest->getParam('id');
        $this->collection = $ksCommissionCollectionFactory->create()->addFieldToFilter('id', $ksId);
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        if (count($this->collection->getData())>0) {
            if ($this->collection->getData()[0]['id'] != 1) {
                $meta['commission_details']['children']['commission_fieldset']['children']['ks_commission_value']['arguments']['data']['config']['notice'] = 'In case of Fixed commission type, the minimum price is a mandatory field, as it is advisible that the minimum price of the product should be more than the commission value';
            }
        } else {
            $meta['commission_details']['children']['commission_fieldset']['children']['ks_commission_value']['arguments']['data']['config']['notice'] = 'In case of Fixed commission type, the minimum price is a mandatory field, as it is advisible that the minimum price of the product should be more than the commission value';
        }

        return $meta;
    }

    public function getData()
    {
        if (isset($this->ksLoadedData)) {
            return $this->ksLoadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $rule) {
            $this->ksLoadedData[$rule->getId()]['commission_details']['rule_information'] = $this->getRuleInformation($rule);
            $this->ksLoadedData[$rule->getId()]['commission_details']['extra_details'] = $this->getExtraDetails($rule);
            $this->ksLoadedData[$rule->getId()]['commission_details']['commission_fieldset'] = $this->getCommissionDetails($rule);
            $this->ksLoadedData[$rule->getId()]['rule'] = $this->getFiterDetails($rule);
            $this->ksLoadedData[$rule->getId()]['id'] = $rule->getId();
            if ($rule->getId() == 1) {
                $this->ksLoadedData[$rule->getId()]['do_we_show_it'] = false;
                $this->ksLoadedData[$rule->getId()]['do_we_disable_it'] = true;
            } else {
                $this->ksLoadedData[$rule->getId()]['do_we_show_it'] = true;
                $this->ksLoadedData[$rule->getId()]['do_we_disable_it'] = false;
            }
        }

        $data = $this->ksDataPersistor->get('commission_form_data');

        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->ksLoadedData[$rule->getId()] = $rule->getData();
            if ($rule->getId() == 1) {
                $this->ksLoadedData[$rule->getId()]['do_we_show_it'] = false;
                $this->ksLoadedData[$rule->getId()]['do_we_disable_it'] = true;
            } else {
                $this->ksLoadedData[$rule->getId()]['do_we_show_it'] = true;
                $this->ksLoadedData[$rule->getId()]['do_we_disable_it'] = false;
            }
            $this->ksDataPersistor->clear('commission_form_data');
        }

        return $this->ksLoadedData;
    }

    public function getRuleInformation($ksItem)
    {
        $ksData = [];
        $ksData= [
            'id' => $ksItem->getId(),
            'ks_rule_name' => $ksItem->getKsRuleName(),
            'ks_rule_desc' => $ksItem->getKsRuleDesc(),
            'ks_status' => $ksItem->getKsStatus(),
            'ks_rule_type' => $ksItem->getKsRuleType(),
            'ks_seller_id' => $ksItem->getKsSellerId(),
            'ks_priority' => $ksItem->getKsPriority()
        ];
        return $ksData;
    }

    public function getExtraDetails($ksItem)
    {
        $ksData = [];
        $ksData= [
            'ks_website' => $ksItem->getKsWebsite(),
            'ks_seller_group' => $ksItem->getKsSellerGroup(),
            'ks_product_type' => $ksItem->getKsProductType(),
            'ks_start_date' => $ksItem->getKsStartDate(),
            'ks_end_date' => $ksItem->getKsEndDate(),
            'ks_min_price' => $ksItem->getKsMinPrice(),
            'ks_max_price' => $ksItem->getKsMaxPrice(),
            'ks_price_roundoff' => $ksItem->getKsPriceRoundoff()

        ];
        return $ksData;
    }

    public function getCommissionDetails($ksItem)
    {
        $ksData = [];
        $ksData= [
            'ks_calculation_baseon' =>  $ksItem->getKsCalculationBaseon(),
            'ks_commission_type' => $ksItem->getKsCommissionType(),
            'ks_commission_value' => $ksItem->getKsCommissionValue()
        ];
        return $ksData;
    }

    public function getFiterDetails($ksItem)
    {
        $ksData = [];
        $ksData= [
            'conditions' => $ksItem->getConditionsSerialized()
        ];
        return $ksData;
    }
}
