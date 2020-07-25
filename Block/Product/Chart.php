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
namespace MageBootcamp\SizeChart\Block\Product;

use MageBootcamp\SizeChart\Helper\RangeHelper;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Chart extends Template
{
    /**
     * The format of the range
     */
    const RANGE_FORMAT = '%s-%s';

    /**
     * The attribute we use for sizes (S, M, L, XL)
     */
    const SIZE_ATTRIBUTE = 'size';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterface
     */
    protected $productAttributeOptionManagement;

    /**
     * In memory cache of the key value pair of the size attribute
     * [attribute_option_id => option_label]
     *
     * @var array|null
     */
    protected $sizeOptions;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagement
     * @param \Magento\Framework\View\Element\Template\Context               $context
     * @param \Magento\Framework\Registry                                    $registry
     * @param array                                                          $data
     */
    public function __construct(
        ProductAttributeOptionManagementInterface $productAttributeOptionManagement,
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $registry;
        $this->productAttributeOptionManagement = $productAttributeOptionManagement;
    }

    /**
     * Get the product of the Magento registry.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct(): Product
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Get the configurable children because the sizes are set on the simples.
     * We group by sizes because per color (or other variant) the size can be set.
     * If we don't group we will see duplicate sizes.
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     */
    public function getChildren(): Collection
    {
        $collection = $this->getProduct()
            ->getTypeInstance()
            ->getUsedProductCollection($this->getProduct())
            ->groupByAttribute('size');

        foreach ($this->getAttributes() as $key => $label) {
            $collection->addAttributeToSelect($key . RangeHelper::FROM_SUFFIX);
            $collection->addAttributeToSelect($key . RangeHelper::TO_SUFFIX);
        }

        return $collection;
    }

    /**
     * Get the option value label based on the size options key value array.
     *
     * @param int $optionId
     *
     * @return string
     */
    public function getOptionValueLabel(int $optionId): string
    {
        $sizeOptions = $this->getSizeOptions();

        return $sizeOptions[$optionId] ?? '';
    }

    /**
     * Get attributes inject through the di.xml
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->getData('attributes') ?? [];
    }

    /**
     * Format a range based on FROM and TO suffix.
     * E.g. 10-20.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $key
     *
     * @return string
     */
    public function formatRange(Product $product, string $key): string
    {
        return sprintf(
            self::RANGE_FORMAT,
            number_format($product->getData($key . RangeHelper::FROM_SUFFIX), RangeHelper::DECIMAL),
            number_format($product->getData($key . RangeHelper::TO_SUFFIX), RangeHelper::DECIMAL)
        );
    }

    /**
     * Get all the sizes from the size option attribute. By default Magento
     * returns the option ids. We need to map that to text so we return an
     * array of option ids and labels.
     *
     * @return array
     */
    protected function getSizeOptions(): array
    {
        if (!isset($this->sizeOptions)) {
            try {
                $sizeOptions = $this->productAttributeOptionManagement->getItems(self::SIZE_ATTRIBUTE);
            } catch (InputException|StateException $e) {
                return [];
            }

            foreach ($sizeOptions as $option) {
                $this->sizeOptions[$option->getValue()] = $option->getLabel();
            }
        }

        return $this->sizeOptions ?? [];
    }

    /**
     * Apply this block only for configurables
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getProduct()->getTypeId() != Configurable::TYPE_CODE) {
            return '';
        }

        return parent::_toHtml();
    }
}
