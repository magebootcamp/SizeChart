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
namespace MageBootcamp\SizeChart\Model\Charts;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * This is a single size chart that can be contained in a chart list (e.g. PredefinedChartList).
 */
class SizeChart implements SizeChartInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $sizeMapping;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $key;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder            $filterBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param string                                          $label
     * @param string                                          $key
     * @param array                                           $sizeMapping
     */
    public function __construct(

        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductRepositoryInterface $productRepository,
        string $label = '',
        string $key = '',
        array $sizeMapping = []
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->productRepository = $productRepository;
        $this->sizeMapping = $sizeMapping;
        $this->label = $label;
        $this->key = $key;
    }

    /**
     * Get a mapping of the sizes, e.g.:
     * [
     *      'xs' => [
     *          'chest_size_from_number' => 81
     *      ]
     * ]
     *
     * @return array
     */
    public function getSizeMapping(): array
    {
        return $this->sizeMapping;
    }

    /**
     * Get the label of this size chart
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the unique key of this size chart
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
