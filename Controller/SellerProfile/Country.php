<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited  (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

/**
 * Country Controller class
 */
class Country extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $ksRegionCollectionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory
     * @param \Magento\Directory\Model\RegionFactory $ksRegionCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Magento\Directory\Model\RegionFactory $ksRegionCollectionFactory
    ) {
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksRegionCollectionFactory = $ksRegionCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Region details
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
        //get result json factory
        $ksResult = $this->ksResultJsonFactory->create();
        //get mdoel data
        $ksRegions = $this->ksRegionCollectionFactory->create()->getCollection()->addFieldToFilter('country_id', $this->getRequest()->getParam('country'));
        //intialize variable
        $html = '';
        //check count
        if (count($ksRegions) > 0) {
            foreach ($ksRegions as $state) {
                $html.=    '<option  value="'.$state->getId().'">'.$state->getName().'</option>';
            }
        }
        return $ksResult->setData(['success' => true,'value'=>$html]);
    }
}
