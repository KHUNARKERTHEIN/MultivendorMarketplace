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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\CommissionRule;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * @api
 * @since 100.0.2
 */
class KsCommission extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.ks_commission_value';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $ksLocaleCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;



    /**
     * @param ContextInterface   $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param array              $ksComponents
     * @param array              $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        array $ksComponents = [],
        array $data = []
    ) {
        parent::__construct($ksContext, $ksUiComponentFactory, $ksComponents, $data);
        $this->ksLocaleCurrency = $ksLocaleCurrency;
        $this->ksStoreManager = $ksStoreManager;
    }

    /**
     * Prepare Data Source
     *
     * @param  array $ksDataSource
     * @return array
     */
    public function prepareDataSource(array $ksDataSource)
    {
        if (isset($ksDataSource['data']['items'])) {
            $ksStore = $this->ksStoreManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());

            $ksFieldName = $this->getData('name');
            foreach ($ksDataSource['data']['items'] as & $ksItem) {
                if (isset($ksItem[$ksFieldName])) {
                    if ($ksItem['ks_commission_type'] == 'to_percent') {
                        $ksItem[$ksFieldName] =  number_format($ksItem[$ksFieldName], 2)."%";
                    } else {
                        $ksItem[$ksFieldName] = $ksCurrency->toCurrency(sprintf("%f", $ksItem[$ksFieldName]));
                    }
                }
            }
        }
        return $ksDataSource;
    }
}
