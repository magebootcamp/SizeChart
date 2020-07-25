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
namespace MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

/**
 * Class DecimalRange is responsible for the database handling of a '_from_number' and '_to_number' filter.
 */
class DecimalRange
{
    const LOWER_THEN_OR_EQUAL = '<=';
    const GREATER_THEN_OR_EQUAL = '>=';

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     */
    public function __construct(Layer $layer)
    {
        $this->layer = $layer;
    }

    /**
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer(): Layer
    {
        return $this->layer;
    }

    /**
     * Apply attribute filter to product collection.
     * We first apply the inner join with a subquery and filter the product entity ids.
     * After that we apply the lower then or great then filter to whole query.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param float                                              $value
     * @param string                                             $compare
     *
     * @return \MageBootcamp\SizeChart\Model\Layer\Filter\DataProvider\DecimalRange
     * @throws \Zend_Db_Select_Exception
     */
    public function applyFilterToCollection(Attribute $attribute, float $value, string $compare): DecimalRange
    {
        $select = $this->getSelect(false);
        $this->addDecimalFilterToSelect($select, $attribute, $compare);
        $select->where(
            "? {$compare} {$this->getTableAlias($attribute->getAttributeCode())}.value",
            $value
        );

        return $this;
    }

    /**
     * Clone and reset the select and count the results.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFrom
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeTo
     *
     * @return mixed
     * @throws \Zend_Db_Select_Exception
     */
    public function getCount(Attribute $attributeFrom, Attribute $attributeTo)
    {
        $select = $this->getSelect(true);
        $this->addDecimalFilterToSelect($select, $attributeFrom, self::GREATER_THEN_OR_EQUAL);
        $this->addDecimalFilterToSelect($select, $attributeTo, self::LOWER_THEN_OR_EQUAL);
        $countExpr = new Zend_Db_Expr("COUNT(*)");
        $select->columns(['count' => $countExpr]);

        $countFetch = $this->getLayer()->getProductCollection()->getConnection()->fetchPairs($select);

        return !empty($countFetch) && current($countFetch) > 0
            ? current($countFetch)
            : 0;
    }

    /**
     * This function will add a inner join with a sub-query to retrieve the lowest or highest size.
     * After that we will filter based on the product entity id. This filter for specific value is not done in this
     * function.
     *
     * @param \Magento\Framework\DB\Select                       $select
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param string                                             $compare
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    protected function addDecimalFilterToSelect(Select $select, Attribute $attribute, string $compare): Select
    {
        $compareMinMax = $compare == self::GREATER_THEN_OR_EQUAL ? 'MIN' : 'MAX';
        $collection = $this->getLayer()->getProductCollection();
        $tableAlias = $this->getTableAlias($attribute->getAttributeCode());

        // Return if the filter already is applied
        if (isset($select->getPart('from')[$tableAlias])) {
            return $select;
        }

        $subQuery = new Zend_Db_Expr(
            "(SELECT `cpied`.`entity_id`, {$compareMinMax}(`cpied`.`value`) as `value`
                     FROM `catalog_product_index_eav_decimal` as `cpied`
                     WHERE `cpied`.`attribute_id` = {$attribute->getAttributeId()}
                     AND `cpied`.`store_id` = {$collection->getStoreId()}
                     GROUP BY `cpied`.`entity_id`)"
        );

        $select->join(
            [$tableAlias => $subQuery],
            "{$tableAlias}.entity_id = e.entity_id",
            []
        );

        return $select;
    }

    /**
     * Get the select from the product collection.
     * Optionally we can clone the select.
     *
     * @param bool $cloned
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelect(bool $cloned): Select
    {
        return $cloned
            ? clone $this->getLayer()->getProductCollection()->getSelect()
            : $this->getLayer()->getProductCollection()->getSelect();
    }

    /**
     * @param string $attributeCode
     *
     * @return string
     */
    protected function getTableAlias(string $attributeCode): string
    {
        return sprintf('%s_idx', $attributeCode);
    }
}
