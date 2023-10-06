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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\SellerReportReason;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsSellerReportReason Ui Class
 */
class KsSellerReportReason extends Column
{
    const KS_URL_PATH_EDIT = 'multivendor/reportseller/new';

    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param UrlInterface $ksUrlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $components, $data);
    }

    /**
     * Get item url
     * @return string
     */
    public function getViewUrl()
    {
        return $this->ksUrlBuilder->getUrl(
            $this->getData('config/viewUrlPath')
        );
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$ksitem) {
                if (isset($ksitem['ks_reason'])) {
                    $ksName = $this->getData('name');
                    $ksitem[$ksName] = html_entity_decode("<a href='".$this->ksUrlBuilder->getUrl(self::KS_URL_PATH_EDIT, ['id' => $ksitem['id']])."' target='blank' title='".__('Edit Reason')."'>".$ksitem['ks_reason'].'</a>');
                }
            }
        }
        return $dataSource;
    }
}
