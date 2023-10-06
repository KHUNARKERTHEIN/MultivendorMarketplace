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

namespace Ksolves\MultivendorMarketplace\Helper;
 
class KsSubmitQueryEmail extends \Magento\Framework\App\Helper\AbstractHelper
{
     
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'contact/email/email_template';
   /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $ksScopeConfig;
 
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;
 
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $ksInlineTranslation;
 
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $ksTransportBuilder;
     
    /**
     * @var string
     */
    protected $ksTemp_id;
 
    /**
     * @param Magento\Framework\App\Helper\Context $ksContext
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param Magento\Framework\Translate\Inline\StateInterface $ksInlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder $ksTransportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Translate\Inline\StateInterface $ksInlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $ksTransportBuilder
    ) {
         $this->ksScopeConfig = $ksContext;
         parent::__construct($ksContext);
         $this->ksStoreManager = $ksStoreManager;
         $this->ksInlineTranslation = $ksInlineTranslation;
         $this->ksTransportBuilder = $ksTransportBuilder;
    }
 
    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
 
    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->ksStoreManager->getStore();
    }
 
    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }
 
    /**
     * [generateTemplate description]  with template file and tempaltes variables values
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
          
        $template = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemp_id)
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->ksStoreManager->getStore()->getId(),
                            ]
                        )
                        ->setTemplateVars($emailTemplateVariables)
                        ->setFrom($senderInfo)
                        ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
 
    /**
     * [sendInvoicedOrderEmail description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    /* your send mail method*/
    public function sendAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
 
        $this->ksTemp_id = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);
        $this->ksInlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->ksTransportBuilder->getTransport();
        $transport->sendMessage();
        $this->ksInlineTranslation->resume();
    }
}
