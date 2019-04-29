<?php

namespace Extait\Articles\Ui\DataProvider\Article\Form\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Related
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class ProductsRelated extends AbstractModifier
{
    const DATA_SCOPE = '';
    const DATA_SCOPE_RELATED = 'related';
    const GROUP_RELATED = 'related';

    private static $previousGroup = 'search_engine_optimization';
    private static $sortOrder = 90;

    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var ProductRepositoryInterface
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * @var ImageHelper
     * @since 101.0.0
     */
    protected $imageHelper;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopeName;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopePrefix;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    public function __construct(
        ProductFactory $productFactory,
        LinkFactory $linkFactory,
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        RequestInterface $request,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->status = $status;
        $this->productFactory = $productFactory;
        $this->linkFactory = $linkFactory;
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->scopeName = 'extait_articles_form.extait_articles_form';
        $this->scopePrefix = '';
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_RELATED => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_RELATED => $this->getRelatedFieldset(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Related'),
                                'collapsible' => false,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$previousGroup,
                                    self::$sortOrder
                                )
                            ],

                        ],
                    ],
                ]
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        $articleId = $this->request->getParam('id');

        $productsIds = $this->linkFactory->create()->getProductsIdsByArticle($articleId);

        $productCollection = $this->productFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', ["in" => $productsIds])
            ->addAttributeToSelect('*')
            ->getItems();

        $data[$articleId]['related_product_listing'][self::DATA_SCOPE_RELATED] = [];
        foreach ($productCollection as $linkItem) {
            $data[$articleId]['related_product_listing'][self::DATA_SCOPE_RELATED][] = $this->fillData($linkItem);
        }

        return $data;
    }

    /**
     * @param $linkedProduct
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function fillData($linkedProduct)
    {
        return [
            'id' => $linkedProduct->getId(),
            'thumbnail' => $this->imageHelper->init($linkedProduct, 'product_listing_thumbnail')->getUrl(),
            'name' => $linkedProduct->getName(),
            'status' => $this->status->getOptionText($linkedProduct->getStatus()),
            'attribute_set' => $this->attributeSetRepository
                ->get($linkedProduct->getAttributeSetId())
                ->getAttributeSetName(),
            'sku' => $linkedProduct->getSku(),
            'price' => $linkedProduct->getPrice(),
        ];
    }

    /**
     * Prepares config for the Related products fieldset
     *
     * @return array
     * @since 101.0.0
     */
    protected function getRelatedFieldset()
    {
        $content = __('Message');

        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Add Related Articles'),
                    $this->scopePrefix . static::DATA_SCOPE_RELATED
                ),
                'modal' => $this->getGenericModal(
                    __('Add Related Products'),
                    $this->scopePrefix . static::DATA_SCOPE_RELATED
                ),
                static::DATA_SCOPE_RELATED => $this->getGrid($this->scopePrefix . static::DATA_SCOPE_RELATED),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Related Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 10,
                    ],
                ],
            ]
        ];
    }

    /**
     * Retrieve grid
     *
     * @param string $scope
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.0
     */
    protected function getGrid($scope)
    {
        $dataProvider = $scope . '_product_listing';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => 'data.related_product_listing',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $dataProvider,
                        'map' => [
                            'id' => 'entity_id',
                            'thumbnail' => 'thumbnail_src',
                            'name' => 'name',
                            'status' => 'status_text',
                            'attribute_set' => 'attribute_set_text',
                            'sku' => 'sku',
                            'price' => 'price',
                            'thumbnail' => 'thumbnail_src',
                        ],
                        'links' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }'
                        ],
                        'sortOrder' => 2,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => $this->fillMeta(),
                ],
            ],
        ];
    }

    /**
     * Retrieve meta column
     *
     * @return array
     * @since 101.0.0
     */
    protected function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', false, __('ID'), 0),
            'thumbnail' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'dataType' => Text::NAME,
                            'dataScope' => 'thumbnail',
                            'fit' => true,
                            'label' => __('Thumbnail'),
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ],
            'name' => $this->getTextColumn('name', false, __('Name'), 20),
            'status' => $this->getTextColumn('status', true, __('Status'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'sku' => $this->getTextColumn('sku', true, __('SKU'), 50),
            'price' => $this->getTextColumn('price', true, __('Price'), 60),
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 70,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve text column structure
     *
     * @param string $dataScope
     * @param bool $fit
     * @param Phrase $label
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getTextColumn($dataScope, $fit, Phrase $label, $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'component' => 'Magento_Ui/js/form/element/text',
                        'dataType' => Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $column;
    }

    /**
     * @param Phrase $content
     * @param Phrase $buttonTitle
     * @param $scope
     * @return array
     */
    protected function getButtonSet(Phrase $content, Phrase $buttonTitle, $scope)
    {
        $modalTarget = $this->scopeName . '.' . static::GROUP_RELATED . '.' . $scope . '.modal';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => $content,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $modalTarget,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modalTarget . '.' . $scope . '_product_listing',
                                        'actionName' => 'render',
                                    ]
                                ],
                                'title' => $buttonTitle,
                                'provider' => null,
                            ],
                        ],
                    ],

                ],
            ],
        ];
    }

    /**
     * @param Phrase $title
     * @param $scope
     * @return array
     */
    protected function getGenericModal(Phrase $title, $scope)
    {
        $listingTarget = $scope . '_product_listing';

        $modal = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'options' => [
                            'title' => $title,
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'actions' => [
                                        'closeModal'
                                    ]
                                ],
                                [
                                    'text' => __('Add Selected Articles'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $listingTarget,
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                $listingTarget => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => $listingTarget,
                                'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
                                'selectionsProvider' => $listingTarget . '.' . $listingTarget . '.product_columns.ids',
                                'ns' => $listingTarget,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $modal;
    }
}
