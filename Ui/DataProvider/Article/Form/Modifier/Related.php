<?php

namespace Extait\Articles\Ui\DataProvider\Article\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Extait\Articles\Model\ResourceModel\Article\CollectionFactory as ArticleCollection;
use Magento\Ui\Component\Modal;

class Related extends AbstractModifier
{
    const DATA_SCOPE = '';
    const DATA_SCOPE_RELATED = 'article';
    const GROUP_RELATED = 'related';

    /**
     * @var LocatorInterface
     */
    protected $locator;

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
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var ArticleCollection
     */
    protected $articleCollection;

    /**
     * Related constructor.
     * @param ArticleCollection $articleCollection
     * @param UrlInterface $urlBuilder
     * @param LocatorInterface $locator
     * @param string $scopeName
     * @param string $scopePrefix
     */
    public function __construct(
        ArticleCollection $articleCollection,
        UrlInterface $urlBuilder,
        LocatorInterface $locator,
        $scopeName = "product_form.product_form",
        $scopePrefix = ''
    ) {
        $this->articleCollection = $articleCollection;
        $this->locator = $locator;
        $this->scopeName = $scopeName;
        $this->scopePrefix = $scopePrefix;
        $this->urlBuilder = $urlBuilder;
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
                                'label' => __('Related Products, Related Articles,
                                Related Products, Up-Sells, and Cross-Sells'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE
                            ],
                        ],
                    ],
                ],
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
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if (!$productId) {
            return $data;
        }

        $linkedArticles = $this->articleCollection
            ->create()
            ->getRelatedArticles($productId)
            ->getItems();

        foreach ($this->getDataScopes() as $dataScope) {
            $data[$productId]['links'][$dataScope] = [];
            foreach ($linkedArticles as $linkedArticle) {
                $data[$productId]['links'][$dataScope][] = $this->fillData($linkedArticle);
            }
        }

        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_product_id'] = $productId;
        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_store_id'] = $this->locator->getStore()->getId();

        return $data;
    }

    /**
     * @param Article $linkedArticle
     * @return array
     */
    protected function fillData($linkedArticle)
    {
        return [
            'id' => $linkedArticle->getArticleId(),
            'title' => $linkedArticle->getTitle(),
        ];
    }

    /**
     * Retrieve all data scopes
     *
     * @return array
     * @since 101.0.0
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_RELATED,
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
        $content = __(
            'Related products are shown to customers in addition to the item the customer is looking at.'
        );

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
                        'label' => __('Related Articles'),
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
        $dataProvider = $scope . '_listing';

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
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $dataProvider,
                        'map' => [
                            'id' => 'id',
                            'title' => 'title',
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
            'title' => $this->getTextColumn('title', false, __('Title'), 20),
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
                                        'targetName' => $modalTarget . '.' . $scope . '_listing',
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
     * Prepares config for modal slide-out panel
     *
     * @param Phrase $title
     * @param string $scope
     * @return array
     * @since 101.0.0
     */
    protected function getGenericModal(Phrase $title, $scope)
    {
        $listingTarget = $scope . '_listing';

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
                                'selectionsProvider' => $listingTarget . '.' . $listingTarget . '.article_columns.ids',
                                'ns' => $listingTarget,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'article_id' => '${ $.provider }:data.article.article_id',
                                ],
                                'exports' => [
                                    'article_id' => '${ $.externalProvider }:params.article_id',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $modal;
    }
}
