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

namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\ProductReason;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class KsProductReasons
 */
class KsProductReasons implements ArrayInterface
{

 
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $ksResourceConnection;

    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory $ksReasonFactory,
        ResourceConnection $ksResourceConnection
    ) {
        $this->ksReasonFactory =$ksReasonFactory;
        $this->ksResourceConnection = $ksResourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $option) {
            $result[] = [
                 'value' => $option['ks_reason'],
                 'label' => $option['ks_reason'],
             ];
        }
        return $result;
    }

    /**
     * Return options
     *
     * @return array
     */
    public function getOptions()
    {
        $ksQuery = $this->ksReasonFactory->create()->getCollection()->getSelect()->group('ks_reason');
        return $this->ksResourceConnection->getConnection()->query($ksQuery->__toString())->fetchAll();
    }
}
