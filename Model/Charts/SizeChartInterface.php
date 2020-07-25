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
 * This is a single size chart that can be contained in a chart list (e.g. PredefinedChartList).
 */
interface SizeChartInterface
{
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
    public function getSizeMapping(): array;

    /**
     * Get the label of this size chart
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Get the unique key of this size chart
     *
     * @return string
     */
    public function getKey(): string;
}
