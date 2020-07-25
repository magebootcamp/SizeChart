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

namespace MageBootcamp\SizeChart\Model;

use Exception;
use http\Exception\InvalidArgumentException;
use MageBootcamp\SizeChart\Api\Data\LogInterface;
use MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface;
use MageBootcamp\SizeChart\Model\Charts\SizeChartInterface;
use MageBootcamp\SizeChart\Setup\Patch\Data\AddCategoryAttributes;
use MageBootcamp\SizeChart\Setup\Patch\Data\AddProductAttributes;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ConfigurableCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class updates the sizes based on the predefined sizes in the di.xml
 */
class ProductSizeUpdater
{
    const PROGRESS_BAR_CATEGORY_PRODUCTS = 'category_products';

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface
     */
    protected $chartSource;

    /**
     * @var \Magento\Catalog\Api\CategoryListInterface
     */
    protected $categoryList;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var array
     */
    protected $charts;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $progressBars;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                $productRepository
     * @param \Magento\Framework\Api\SortOrderBuilder                        $sortOrderBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory            $searchCriteriaBuilderFactory
     * @param \Magento\Framework\Api\FilterBuilder                           $filterBuilder
     * @param \Magento\Catalog\Api\CategoryListInterface                     $categoryList
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface           $categoryLinkManagement
     * @param \MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface      $chartSource
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        CategoryListInterface $categoryList,
        CategoryLinkManagementInterface $categoryLinkManagement,
        ChartSourceInterface $chartSource
    ) {
        $this->chartSource = $chartSource;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryList = $categoryList;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Import sizes based on a predefined size list
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return bool
     * @throws \Exception
     */
    public function update(?OutputInterface $output = null): bool
    {
        $this->setOutput($output);

        $categories = $this->getCategories();
        foreach ($categories->getItems() as $category) {
            $this->write(
                sprintf(
                    'Importing sizes for category %s (id: %s)',
                    $category->getName(),
                    $category->getId()
                )
            );

            $chart = $this->getCategoryChart($category);

            /** @var \Magento\Catalog\Model\Product $categoryProduct */
            $categoryProducts = $this->getCategoryProducts($category);
            $this->newBar(self::PROGRESS_BAR_CATEGORY_PRODUCTS, $categoryProducts->getSize());

            foreach ($categoryProducts->getItems() as $categoryProduct) {
                foreach ($chart->getSizeMapping() as $size => $chartData) {
                    if ($categoryProduct->getTypeId() === Configurable::TYPE_CODE) {
                        $this->importByParentProduct($categoryProduct, $size, $chartData);
                    }

                    if ($categoryProduct->getTypeId() === Type::TYPE_SIMPLE) {
                        $this->importValue($categoryProduct, $chartData);
                    }
                }

                $this->advanceBar(self::PROGRESS_BAR_CATEGORY_PRODUCTS);
            }

            $this->finishBar(self::PROGRESS_BAR_CATEGORY_PRODUCTS);
        }

        return true;
    }

    /**
     * Import the sizes based on a parent configurable product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $parentProduct
     * @param string                                     $size
     * @param array                                      $chartData
     */
    protected function importByParentProduct(ProductInterface $parentProduct, string $size, array $chartData)
    {
        $childProducts = $this->getChildrenBySize($parentProduct, $size);

        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $this->importValue($childProduct, $chartData);
        }
    }

    /**
     * Import the product size value
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array                                      $chartData
     */
    protected function importValue(ProductInterface $product, array $chartData): void
    {
        foreach ($chartData as $attributeName => $attributeValue) {
            $product->addAttributeUpdate(
                $attributeName,
                $attributeValue,
                $product->getStoreId()
            );
        }
    }

    /**
     * Get the products in a category.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getCategoryProducts(CategoryInterface $category): Collection
    {
        $productCollection = $this->productCollectionFactory->create();

        return $productCollection->addFieldToFilter(
            ProductInterface::SKU,
            ['in' => $this->getCategorySkuList($category)]
        );
    }

    /**
     * Get a list of SKU's in a category.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     *
     * @return array
     */
    protected function getCategorySkuList(CategoryInterface $category): array
    {
        return $this->convertCategoryProductsResultToSkuList(
            $this->categoryLinkManagement->getAssignedProducts($category->getId())
        );
    }

    /**
     * Get chart assigned to category.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     *
     * @return \MageBootcamp\SizeChart\Model\Charts\SizeChartInterface
     * @throws \Exception
     */
    protected function getCategoryChart(CategoryInterface $category): SizeChartInterface
    {
        $charts = $this->getCharts();
        if (!isset($charts[$category->getCategorySizeChart()])) {
            throw new Exception(
                sprintf('Missing size chart for category id %s', $category->getId())
            );
        }

        return $charts[$category->getCategorySizeChart()];
    }

    /**
     * Get all the categories with the category size chart attribute.
     *
     * @return \Magento\Catalog\Api\Data\CategorySearchResultsInterface
     */
    protected function getCategories(): CategorySearchResultsInterface
    {
        $searchCriteriaBuilder = $this->getSearchCriteriaBuilder()->addFilter(
            AddCategoryAttributes::ATTRIBUTE_CODE,
            true,
            'notnull'
        );

        return $this->categoryList->getList($searchCriteriaBuilder->create());
    }

    /**
     * Convert the category product results into a list of SKU's.
     *
     * @param array $assignedProducts
     *
     * @return array
     */
    protected function convertCategoryProductsResultToSkuList(array $assignedProducts): array
    {
        /** @var \Magento\Catalog\Model\CategoryProductLink $assignedProduct */
        foreach ($assignedProducts as $assignedProduct) {
            $skuList[] = $assignedProduct->getSku();
        }

        return $skuList ?? [];
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected function getSearchCriteriaBuilder(): SearchCriteriaBuilder
    {
        return $this->searchCriteriaBuilderFactory->create();
    }

    /**
     * Set the console output
     *
     * @return OutputInterface
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * Set the console output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output): ProductSizeUpdater
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get the charts from the chart source
     *
     * @return array
     */
    protected function getCharts(): array
    {
        if (!isset($this->charts)) {
            $this->charts = $this->chartSource->getCharts();
        }

        return $this->charts;
    }

    /**
     * Get children by size. Currently, we use the SKU to define the size.
     * A better way is to change the mapping to allow you to define the filter that will apply this size mapping.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $parentProduct
     * @param string                                     $size
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     */
    protected function getChildrenBySize(ProductInterface $parentProduct, string $size): ConfigurableCollection
    {
        /** @var ConfigurableCollection $childCollection */
        $childCollection = $parentProduct->getTypeInstance()->getUsedProductCollection($parentProduct);

        return $childCollection->addFieldToFilter(ProductInterface::SKU, ['like' => "%-{$size}-%"])
            ->addAttributeToSelect(AddProductAttributes::ATTRIBUTE_CHEST_SIZE)
            ->addAttributeToSelect(AddProductAttributes::ATTRIBUTE_HIP_SIZE)
            ->addAttributeToSelect(AddProductAttributes::ATTRIBUTE_WAIST_SIZE);
    }

    /**
     * Write to the console
     *
     * @param string $message
     */
    protected function write(string $message): void
    {
        if ($this->getOutput()) {
            $this->getOutput()->writeln($message);
        }
    }

    /**
     * Add a new progress page
     *
     * @param string $name
     * @param int    $steps
     */
    protected function newBar(string $name, int $steps): void
    {
        $output = $this->getOutput();
        if (!$output) {
            return;
        }

        $progressBar = new ProgressBar($this->output, $steps);
        $progressBar->start();
        $this->progressBars[$name] = $progressBar;
    }

    /**
     * Continue with the progress bar
     *
     * @param string $name
     *
     * @return void
     */
    public function advanceBar(string $name): void
    {
        if (array_key_exists($name, $this->progressBars)) {
            $this->progressBars[$name]->advance();
        }
    }

    /**
     * Finish the progress bar
     *
     * @param string $name
     *
     * @return void
     */
    public function finishBar(string $name): void
    {
        if (array_key_exists($name, $this->progressBars)) {
            $this->progressBars[$name]->finish();
            $this->output->writeln('');
        }
    }
}
