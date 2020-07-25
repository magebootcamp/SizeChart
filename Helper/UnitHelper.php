<?php

namespace MageBootcamp\SizeChart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * This helper is responsible for unit type in the store (Metric or Imperial).
 */
class UnitHelper extends AbstractHelper
{
    const XML_PATH_WEIGHT = 'general/locale/weight_unit';
    const METRIC_COMPARE = 'kgs';

    const METRIC_WEIGHT_UNIT = 'kg';
    const IMPERIAL_WEIGHT_UNIT = 'lbs';

    const METRIC_DISTANCE_UNIT = 'cm';
    const IMPERIAL_DISTANCE_UNIT = 'inches';

    /**
     * Is metric system active in this store.
     *
     * @return bool
     */
    public function useMetricSystem(): bool
    {
        return $this->scopeConfig->getValue(
                self::XML_PATH_WEIGHT,
                ScopeInterface::SCOPE_STORE
            ) === self::METRIC_COMPARE;
    }

    /**
     * Get the current store weight unit
     *
     * @return string
     */
    public function getWeightUnit(): string
    {
        return __($this->useMetricSystem() ? self::METRIC_WEIGHT_UNIT : self::IMPERIAL_WEIGHT_UNIT);
    }

    /**
     * Get the current store distance unit
     *
     * @return string
     */
    public function getDistanceUnit(): string
    {
        return __($this->useMetricSystem() ? self::METRIC_DISTANCE_UNIT : self::IMPERIAL_DISTANCE_UNIT);
    }
}
