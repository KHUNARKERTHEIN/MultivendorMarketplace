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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Product;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurableAttributeSetHandler;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;

/**
 * Data provider for Attribute Set handler in the Configurable products
 */
class KsConfigurableAttributeSetHandler extends ConfigurableAttributeSetHandler
{
    /**
     * @var UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $ksLocator;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * Constructor
     * @param UrlInterface       $ksUrlBuilder
     * @param LocatorInterface $ksUrlBuilder
     * @param KsProductFactory $ksProductFactory
     */
    public function __construct(
        UrlInterface $ksUrlBuilder,
        LocatorInterface $ksLocator,
        KsProductFactory $ksProductFactory
    ) {
        $this->ksLocator = $ksLocator;
        $this->ksProductFactory = $ksProductFactory;
        parent::__construct($ksUrlBuilder);
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_merge_recursive(
            $meta,
            [
                self::ATTRIBUTE_SET_HANDLER_MODAL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Modal::NAME,
                                'dataScope' => '',
                                'options' => [
                                    'title' => __('Choose Affected Attribute Set'),
                                    'type' => 'popup',
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'affectedAttributeSetError' => $this->getAttributeSetErrorContainer(),
                        'affectedAttributeSetCurrent' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __('Add configurable attributes to the current Attribute Set'),
                                        'dataScope' => 'configurableAffectedAttributeSet',
                                        'valueMap' => [
                                            'true' => 'current',
                                            'false' => '0',
                                        ],
                                        'value' => 'current',
                                        'sortOrder' => 20,
                                    ],
                                ],
                            ],
                        ],
                        'affectedAttributeSetNew' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __(
                                            'Add configurable attributes to the new Attribute Set based on current'
                                        ),
                                        'dataScope' => 'configurableAffectedAttributeSet',
                                        'valueMap' => [
                                            'true' => 'new',
                                            'false' => '0',
                                        ],
                                        'value' => '0',
                                        'sortOrder' => 30,
                                    ],
                                ],
                            ],
                        ],
                        'configurableNewAttributeSetName' => $this->getNewAttributeSet(),
                        'ksSellerIdOfCurrentProduct' => $this->getKsSellerIdOfProduct(),
                        'affectedAttributeSetExisting' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __(
                                            'Add configurable attributes to the existing Attribute Set'
                                        ),
                                        'dataScope' => 'configurableAffectedAttributeSet',
                                        'valueMap' => [
                                            'true' => 'existing',
                                            'false' => '0',
                                        ],
                                        'value' => '0',
                                        'sortOrder' => 50,
                                    ],
                                ],
                            ],
                        ],
                        'configurableExistingAttributeSetId' => $this->getExistingAttributeSet($meta),
                        'confirmButtonContainer' => $this->getConfirmButton(),
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Returns new attribute set input configuration
     *
     * @return array
     */
    protected function getKsSellerIdOfProduct()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => Form\Element\DataType\NUMBER::NAME,
                        'formElement' => Form\Element\Hidden::NAME,
                        'componentType' => Form\Field::NAME,
                        'default' => $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId() ? $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId() : 0,
                        'sortOrder' => 40,
                        'validation' => ['required-entry' => true],
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = affectedAttributeSetNew:checked',
                            'disabled' =>
                                '!ns = ${ $.ns }, index = affectedAttributeSetNew:checked',
                            '__disableTmpl' => ['disabled' => false, 'visible' => false],
                        ]
                    ],
                ],
            ],
        ];
    }
}
