<?php
/**
 * Copyright (c) MageBootcamp 2020.
 *
 * Created by MageBootcamp: The Ultimate Online Magento Course.
 * We are here to help you become a Magento PRO.
 * Watch and learn at https://magebootcamp.com.
 *
 * @author Daniel Donselaar
 */
namespace MageBootcamp\SizeChart\Model\Layer\Filter;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider\DecimalRange as DataProvider;
use MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider\DecimalRangeFactory;
use Zend_Db_Select_Exception;

/**
 * This class is responsible for range filter in the frontend.
 * If you add the '_from_number' param in the url this will filter the products.
 * Make sure you have the '_from_number' attribute filterable otherwise nothing will happen.
 */
class DecimalRange extends AbstractFilter
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider\DecimalRange
     */
    protected $dataProvider;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface                    $attributeRepository
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory                             $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface                                  $storeManager
     * @param \Magento\Catalog\Model\Layer                                                $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder                        $itemDataBuilder
     * @param \MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider\DecimalRangeFactory $dataProviderFactory
     * @param array                                                                       $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        DecimalRangeFactory $dataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );

        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Apply the filter to the collection.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        $value = (float) $request->getParam($this->getRequestVar());
        if (empty($value)) {
            return $this;
        }

        try {
            $fromAttributeModel = $this->getAttributeModel();

            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $toAttribute */
            $toAttribute = $this->attributeRepository->get($this->getToAttribute());

            /** This will apply the from and to attribute query to the collection. */
            $this->dataProvider->applyFilterToCollection(
                $toAttribute,
                $value,
                DataProvider::LOWER_THEN_OR_EQUAL
            )->applyFilterToCollection(
                $fromAttributeModel,
                $value,
                DataProvider::GREATER_THEN_OR_EQUAL
            );
        } catch (LocalizedException|Zend_Db_Select_Exception $e) {
            return $this;
        }

        /** This will add the filter to the 'Now Shopping By' list above the filters. */
        $this->getLayer()->getState()->addFilter(
            $this->_createItem($value, $fromAttributeModel->getStoreLabel())
        );

        return $this;
    }

    /**
     * Get the count number of records left that have sizes in the collection of products.
     *
     * @return int
     */
    public function getCount(): int
    {
        try {
            $fromAttribute = $this->getAttributeModel();
            $toAttribute = $this->attributeRepository->get($this->getToAttribute());
        } catch (LocalizedException $e) {
            return 0;
        }

        try {
            return $this->dataProvider->getCount($fromAttribute, $toAttribute);
        } catch (Zend_Db_Select_Exception $e) {
            return 0;
        }
    }

    /**
     * This is the default way of showing the filter.
     * We need a form to handle the input so we won't use this.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        return [];
    }

    /**
     * Get the '_to_number' attribute name based on the '_from_number'.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getToAttribute(): string
    {
        return str_replace(
            'from',
            'to',
            $this->getAttributeModel()->getAttributeCode()
        );
    }
}
