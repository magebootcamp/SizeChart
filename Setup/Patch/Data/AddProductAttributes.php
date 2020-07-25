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
namespace MageBootcamp\SizeChart\Setup\Patch\Data;

use MageBootcamp\SizeChart\Helper\RangeHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * This class is responsible for installing the sizes product attributes.
 */
class AddProductAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Main attributes for sizes
     */
    const ATTRIBUTE_CHEST_SIZE = 'chest_size';
    const ATTRIBUTE_WAIST_SIZE = 'waist_size';
    const ATTRIBUTE_HIP_SIZE = 'hip_size';

    /**
     * Product attributes [key => label]
     */
    const ATTRIBUTES_TO_ADD = [
        self::ATTRIBUTE_CHEST_SIZE . RangeHelper::FROM_SUFFIX => 'Chest Size',
        self::ATTRIBUTE_CHEST_SIZE . RangeHelper::TO_SUFFIX => 'Chest Size To',
        self::ATTRIBUTE_WAIST_SIZE . RangeHelper::FROM_SUFFIX => 'Waist Size',
        self::ATTRIBUTE_WAIST_SIZE . RangeHelper::TO_SUFFIX => 'Waist Size To',
        self::ATTRIBUTE_HIP_SIZE . RangeHelper::FROM_SUFFIX => 'Hip Size',
        self::ATTRIBUTE_HIP_SIZE . RangeHelper::TO_SUFFIX => 'Hip Size To',
    ];

    /**
     * Meta data for adding product attributes
     */
    const ADD_TO_ATTRIBUTE_GROUP = 'Product Details';
    const ATTRIBUTE_START_SORT_ORDER = 100;
    const ATTRIBUTE_SORT_ORDER_STEPS = 10;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Eav\Setup\EavSetupFactory                $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return \MageBootcamp\SizeChart\Setup\Patch\Data\AddProductAttributes|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);

        $sortOrder = self::ATTRIBUTE_START_SORT_ORDER;
        foreach (self::ATTRIBUTES_TO_ADD as $code => $label) {
            $this->addAttribute($eavSetup, $code, $label);
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                self::ADD_TO_ATTRIBUTE_GROUP,
                $code,
                $sortOrder
            );

            $sortOrder += self::ATTRIBUTE_SORT_ORDER_STEPS;
        }
    }

    /**
     * Add the product attribute
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param string                      $code
     * @param string                      $label
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    protected function addAttribute(EavSetup $eavSetup, string $code, string $label)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            $code,
            [
                'type' => 'decimal',
                'backend' => '',
                'frontend' => '',
                'label' => $label,
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => Type::TYPE_SIMPLE
            ]
        );
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Rollback all changes, done by this patch
     *
     * @return void
     */
    public function revert()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach (self::ATTRIBUTES_TO_ADD as $code => $label) {
            $eavSetup->removeAttribute(Product::ENTITY, $code);
        }
    }
}
