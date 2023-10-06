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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Control;

use Magento\Ui\Component\Control\Action;

/**
 * Class PdfAction
 */
class PdfAction extends Action
{
    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $context = $this->getContext();
        $config['url'] = $context->getUrl(
            $config['pdfAction'],
            ['ks_order_id' => $context->getRequestParam('ks_order_id')]
        );
        $this->setData('config', (array)$config);
        parent::prepare();
    }
}
