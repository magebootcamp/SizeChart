<?php

namespace MageBootcamp\SizeChart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class RangeHelper extends AbstractHelper
{
    /**
     * By default we use rounded sizes but you can also choose a decimal, e.g. 0.01.
     */
    const DECIMAL = 0;

    /**
     * Suffix for the from attribute
     */
    const FROM_SUFFIX =  '_from_number';

    /**
     * Suffix for the to attribute
     */
    const TO_SUFFIX =  '_to_number';
}
