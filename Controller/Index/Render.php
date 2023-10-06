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

namespace Ksolves\MultivendorMarketplace\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UicomponentFactory;
use Magento\Framework\View\Element\UicomponentInterface;

/**
 * Class Render
 */
class Render extends AbstractAction
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');
            return;
        }
        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->ksPrepareMultivendorUIcomponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * executeAjaxRequest Action for AJAX request.
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }


    /**
     * Call prepare method in the component UI
     *
     * @param UicomponentInterface $component
     * @return void
     */
    protected function ksPrepareMultivendorUIcomponent(UicomponentInterface $component)
    {
        foreach ($component->getChildcomponents() as $child) {
            $this->ksPrepareMultivendorUIcomponent($child);
        }
        $component->prepare();
    }
}
