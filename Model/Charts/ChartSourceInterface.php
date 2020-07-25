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

/**
 * This interface is a generic approach to getting size charts for the category.
 * Based on this chart we will import the sizes for the products.
 */
interface ChartSourceInterface
{
    /**
     * @return array
     */
    public function getCharts(): array;
}
