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

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * KsSellerCreateDataProvider dataprovider class
 */
class KsSellerCreateDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $ksShareConfig;

    /**
     * Data Provider name
     *
     * @var string
     */
    protected $name;

    /**
     * Data Provider Primary Identifier name
     *
     * @var string
     */
    protected $primaryFieldName;

    /**
     * Data Provider Request Parameter Identifier name
     *
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var ReportingInterface
     */
    protected $reporting;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var RequestInterface
     */
    protected $ksRequest;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var Config
     */
    protected $ksEavConfig;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var AttributeMetadataResolver
     */
    private $ksAttributeMetadataResolver;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;
     
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $ksRequest
     * @param FilterBuilder $filterBuilder
     * @param Config $ksEavConfig
     * @param AttributeMetadataResolver $ksAttributeMetadataResolver
     * @param array $meta
     * @param array $data
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $ksRequest,
        FilterBuilder $filterBuilder,
        Config $ksEavConfig,
        AttributeMetadataResolver $ksAttributeMetadataResolver,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->ksRequest = $ksRequest;
        $this->filterBuilder = $filterBuilder;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->reporting = $reporting;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ksEavConfig = $ksEavConfig;
        $this->ksAttributeMetadataResolver = $ksAttributeMetadataResolver;
        $this->ksDataHelper         = $ksDataHelper;
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Get meta function.
     * return mixed
     */
    public function getMeta()
    {
        $ksMeta = parent::getMeta();
        $ksMeta['ks_new_customer_fieldset']['children'] = $this->getKsAttributesMeta(
            $this->ksEavConfig->getEntityType('customer')
        );
        if ($this->getKsShareConfig()->isGlobalScope()) {
            $ksMeta['base_fieldset']['children']['website_id']['arguments']['data']['config']['disabled'] = true;
        } else {
            $ksMeta['base_fieldset']['children']['website_id']['arguments']['data']['config']['disabled'] = false;
        }
        return $ksMeta;
    }

    /**
     * Get attributes meta
     *
     * @param Type $ksEntityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getKsAttributesMeta(Type $ksEntityType)
    {
        $ksMeta = [];
       
        $ksAttributes = $ksEntityType->getAttributeCollection();
        $ksDefaultAttr = ['prefix', 'firstname','middlename', 'lastname', 'suffix','email', 'taxvat' ,
            'disable_auto_group_change','group_id','gender','sendemail_store_id', 'dob'];
           
        foreach ($ksAttributes as $ksAttribute) {
            $ksCode = $ksAttribute->getAttributeCode();

            if (in_array($ksCode, $ksDefaultAttr)) {
                $ksCode = $ksAttribute->getAttributeCode();
                $ksMeta[$ksCode] = $this->ksAttributeMetadataResolver->getAttributesMeta(
                    $ksAttribute,
                    $ksEntityType,
                    true
                );

                if ($ksAttribute->getIsVisible()==1) {
                    if ($this->getKsShareConfig()->isGlobalScope() && $ksCode=='website_id') {
                        continue;
                    }
                    $ksMeta[$ksCode]['arguments']['data']['config']['additionalClasses']['ks-customer-fields'] = true;
                }
                $ksMeta[$ksCode]['arguments']['data']['config']['dataScope'] = 'customer['.$ksCode.']';
            }
        }
        return $ksMeta;
    }

    /**
     * Retrieve Customer Config Share
     *
     * @return \Magento\Customer\Model\Config\Share
     * @deprecated 100.1.3
     */
    private function getKsShareConfig()
    {
        if (!$this->ksShareConfig) {
            $this->ksShareConfig = ObjectManager::getInstance()->get(\Magento\Customer\Model\Config\Share::class);
        }
        return $this->ksShareConfig;
    }
}
