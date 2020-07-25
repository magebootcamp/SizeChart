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
namespace MageBootcamp\SizeChart\Block\Sidebar;

use MageBootcamp\SizeChart\Model\Layer\Filter\DecimalRange;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

class MySize extends Template
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->isMySizesActive() ? parent::_toHtml() : '';
    }

    /**
     * Check if my_size filters are active to show this block.
     *
     * @return bool
     */
    protected function isMySizesActive(): bool
    {
        try {
            /** @var \Magento\LayeredNavigation\Block\Navigation $catalogLeftNav */
            $catalogLeftNav = $this->getLayout()->getBlock('catalog.leftnav');
        } catch (LocalizedException $e) {
            return false;
        }

        if (!$catalogLeftNav || !($filters = $catalogLeftNav->getFilters())) {
            return false;
        }

        foreach ($filters as $filter) {
            if ($filter instanceof DecimalRange && $filter->getCount() > 0) {
                return true;
            }
        }

        return false;
    }
}
