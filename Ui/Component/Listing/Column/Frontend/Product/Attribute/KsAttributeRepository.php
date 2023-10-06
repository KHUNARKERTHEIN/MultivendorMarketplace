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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\Product\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsAttributeRepository Class for getting attribute of seller
 */
class KsAttributeRepository
{
    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected $ksSellerAttributes;

    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected $ksAdminAttributes;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $ksProductAttributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $ksSearchCriteriaBuilder;
    
    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param ProductAttributeRepositoryInterface $ksProductAttributeRepository
     * @param SearchCriteriaBuilder $ksSearchCriteriaBuilder
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        ProductAttributeRepositoryInterface $ksProductAttributeRepository,
        SearchCriteriaBuilder $ksSearchCriteriaBuilder,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksProductAttributeRepository = $ksProductAttributeRepository;
        $this->ksSearchCriteriaBuilder = $ksSearchCriteriaBuilder;
        $this->ksSellerHelper = $ksSellerHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function ksBuildSearchCriteria()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        return $this->ksSearchCriteriaBuilder->addFilter('additional_table.is_used_in_grid', 1)
            ->addFilter('ks_seller_id', $ksSellerId)
            ->addFilter('ks_attribute_approval_status', 1)
            ->create();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getKsSellerAttributeList()
    {
        if (null == $this->ksSellerAttributes) {
            $this->ksSellerAttributes = $this->ksProductAttributeRepository
                ->getList($this->ksBuildSearchCriteria())
                ->getItems();
        }
        return $this->ksSellerAttributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function ksAdminBuildSearchCriteria()
    {
        return $this->ksSearchCriteriaBuilder->addFilter('additional_table.is_used_in_grid', 1)
            ->addFilter('ks_seller_id', 0)
            ->addFilter('ks_include_in_marketplace', 1)
            ->create();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getKsAdminAttributeList()
    {
        if (null == $this->ksAdminAttributes) {
            $this->ksAdminAttributes = $this->ksProductAttributeRepository
                ->getList($this->ksAdminBuildSearchCriteria())
                ->getItems();
        }
        return $this->ksAdminAttributes;
    }
}
