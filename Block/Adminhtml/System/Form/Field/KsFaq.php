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
   
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\System\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * KsFaq Block class
 */
class KsFaq extends AbstractFieldArray
{

    /**
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender()
    {
       
        $this->addColumn('question', ['label' => __('Question'), 'class' => 'required-entry']);
        $this->addColumn('answer', ['label' => __('Answer'), 'class' => 'required-entry']);
        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }
}
