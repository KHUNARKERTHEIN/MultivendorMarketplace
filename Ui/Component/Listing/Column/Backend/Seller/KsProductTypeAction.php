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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Seller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsProductTypeAction Ui Class
 */
class KsProductTypeAction extends Column
{

    /**
     * Urls for actions
     */
    const KS_URL_PATH_ASSIGN = 'multivendor/producttype/assign';
    const KS_URL_PATH_UNASSIGN = 'multivendor/producttype/unassign';
    
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
        ContextInterface $kscontext,
        UiComponentFactory $ksuiComponentFactory,
        UrlInterface $ksUrlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($kscontext, $ksuiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $ksitem) {
                if (isset($ksitem['id'])) {
                    // If The Product Type is Approved and Assigned
                    if ($ksitem['ks_request_status'] == '1' || $ksitem['ks_request_status'] == '4') {
                        $ksitem[$this->getData('name')] = html_entity_decode('
                                <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_UNASSIGN,
                            [
                                        'id' => $ksitem['id'],
                                    ]
                        ) . '">' . __('Unassign') . '</a>');
                    // ElseIf The Product Type is in Unassigned or Rejected
                    } elseif ($ksitem['ks_request_status'] == '0' || $ksitem['ks_request_status'] == '3') {
                        $ksitem[$this->getData('name')] = html_entity_decode('
                                <a class="ks-reload" href="' . $this->ksUrlBuilder->getUrl(
                            self::KS_URL_PATH_ASSIGN,
                            [
                                        'id' => $ksitem['id'],
                                    ]
                        ) . '">' . __('Assign') . '</a>');
                    }
                }
            }
        }
        return $dataSource;
    }
}
