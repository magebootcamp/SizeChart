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
namespace MageBootcamp\SizeChart\Plugin;

use MageBootcamp\SizeChart\Helper\RangeHelper;
use MageBootcamp\SizeChart\Model\Layer\Filter\DecimalRangeFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class will add the size filters to the frontend filters based on the '_from_number' in the attribute code.
 */
class AddSizeFilters
{
    /**
     * @var \MageBootcamp\SizeChart\Model\Layer\Filter\DecimalRangeFactory
     */
    protected $decimalRangeFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @param \MageBootcamp\SizeChart\Model\Layer\Filter\DecimalRangeFactory $decimalRangeFactory
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface       $productAttributeRepository
     */
    public function __construct(
        DecimalRangeFactory $decimalRangeFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->decimalRangeFactory = $decimalRangeFactory;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList              $subject
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter[] $filters
     * @param \Magento\Catalog\Model\Layer                         $layer
     *
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    public function afterGetFilters(FilterList $subject, array $filters, Layer $layer): array
    {
        foreach ($filters as $key => $filter) {
            try {
                $attribute = $filter->getAttributeModel();
            } catch (LocalizedException $e) {
                continue;
            }

            if (strpos($attribute->getAttributeCode(), RangeHelper::FROM_SUFFIX) === false) {
                continue;
            }

            $filters[$key] = $this->decimalRangeFactory->create(
                ['data' => ['attribute_model' => $attribute], 'layer' => $layer]
            );
        }

        return $filters;
    }
}
