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
 * This chart list is predefined through the settings inserted in the di.xml.
 */
class PredefinedChartList implements ChartSourceInterface
{
    /**
     * @var array
     */
    protected $charts;

    /**
     * @param array $charts
     */
    public function __construct(
        array $charts = []
    ) {
        $this->charts = $charts;
    }

    /**
     * Get all the charts in this list
     *
     * @return array
     */
    public function getCharts(): array
    {
        return $this->charts;
    }
}
