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
 * KsDeleteSubReason Ui Class
 */
class KsDeleteSubReason extends Column
{
    const KS_URL_PATH_DELETE = 'multivendor/reportseller/deletesubreason';

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
                $ksName = $this->getData('name');
                $ksitem[$ksName]['delete'] = [
                    'href' => $this->ksUrlBuilder->getUrl(
                        self::KS_URL_PATH_DELETE,
                        ['id' => $ksitem['id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'message' => __(
                            'Are you sure you want to delete a record?'
                        )
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
