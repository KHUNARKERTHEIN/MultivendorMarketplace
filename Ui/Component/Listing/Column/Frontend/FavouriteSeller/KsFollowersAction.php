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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Frontend\FavouriteSeller;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * KsFollowersAction Class
 */
class KsFollowersAction extends Column
{
    const KS_URL_PATH = 'multivendor/favouriteseller_seller/delete';

    /**
     * @var UrlInterface
     */
    protected $ksurlBuilder;

    /**
     * Constructor
     * @param ContextInterface $ksContext
     * @param UiComponentFactory $ksUiComponentFactory
     * @param array $ksComponents
     * @param UrlInterface $ksksurlBuilder
     * @param array $data
     */
    public function __construct(
        ContextInterface $ksContext,
        UiComponentFactory $ksUiComponentFactory,
        UrlInterface $ksurlBuilder,
        array $ksComponents = [],
        array $data = []
    ) {
        $this->ksurlBuilder = $ksurlBuilder;
        parent::__construct($ksContext, $ksUiComponentFactory, $ksComponents, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $ksitem) {
                if (isset($ksitem['id'])) {
                    $ksName = $this->getData('name');
                    $ksitem[$ksName] = html_entity_decode('
                        <a href="#" class="ks-fav-sendmail" id="' .$ksitem['email'].'"data-id="' .$ksitem['id']. '">' . __('Email') .'</a> |
                        <a href="'. $this->ksurlBuilder->getUrl(self::KS_URL_PATH, ['id' =>$ksitem['id']]).'"
                        class="ks-delete" data-id="'.$ksitem['id'].'">' . __('Delete') . '</a>');
                }
            }
        }
        return $dataSource;
    }
}
