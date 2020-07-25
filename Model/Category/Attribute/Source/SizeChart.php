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
namespace MageBootcamp\SizeChart\Model\Category\Attribute\Source;

use MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * This attribute source is responsible for the dropdown in the category page in the backend.
 * You can choose which chart you want to use to automatically update the category products.
 * If you like you can add your own source(s).
 */
class SizeChart extends AbstractSource
{
    /**
     * @var \MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface
     */
    protected $chartSource;

    /**
     * @param \MageBootcamp\SizeChart\Model\Charts\ChartSourceInterface $chartSource
     */
    public function __construct(
        ChartSourceInterface $chartSource
    ) {
        $this->chartSource = $chartSource;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [['value' => '', 'label' => __('No chart')]];

        /** @var \MageBootcamp\SizeChart\Model\Charts\SizeChartInterface $chart */
        foreach ($this->chartSource->getCharts() as $chart) {
            $options[] = [
                'value' => $chart->getKey(),
                'label' => $chart->getLabel(),
            ];
        }

        return $options;
    }
}
