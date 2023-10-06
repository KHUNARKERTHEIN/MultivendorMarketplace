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

namespace Ksolves\MultivendorMarketplace\Model\Source;

/**
 * Source for KsEmailTemplateOptions
 */
class KsEmailTemplateOptions extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $ksCoreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $ksEmailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $ksTemplatesFactory;

    /**
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $ksTemplatesFactory
     * @param \Magento\Email\Model\Template\Config $ksEmailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $ksTemplatesFactory,
        \Magento\Email\Model\Template\Config $ksEmailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksTemplatesFactory = $ksTemplatesFactory;
        $this->ksEmailConfig = $ksEmailConfig;
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $ksCollection \Magento\Email\Model\ResourceModel\Template\Collection */
        if (!($ksCollection = $this->ksCoreRegistry->registry('config_system_email_template'))) {
            $ksCollection = $this->ksTemplatesFactory->create();
            $ksCollection->load();
            $this->ksCoreRegistry->register('config_system_email_template', $ksCollection);
        }
        $ksOptions = $ksCollection->toOptionArray();
        array_unshift($ksOptions, ['value'=> 'disable', 'label' => 'Disable']);
        $ksTemplateId = str_replace('/', '_', $this->getPath());
        $ksTemplateLabel = $this->ksEmailConfig->getTemplateLabel($ksTemplateId);
        $ksTemplateLabel = __('%1 (Default)', $ksTemplateLabel);
        array_unshift($ksOptions, ['value' => $ksTemplateId, 'label' => $ksTemplateLabel]);
        return $ksOptions;
    }
}
