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

use MageBootcamp\SizeChart\Model\Category\Attribute\Source\SizeChart;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * This class is responsible for adding size chart list to the category page.
 * The size chart list can be used for importing sizes through the command line.
 */
class AddCategoryAttributes implements DataPatchInterface, PatchRevertableInterface
{
    const ADD_TO_ATTRIBUTE_GROUP = 'Size Guide';
    const ATTRIBUTE_CODE = 'category_size_chart';
    const ATTRIBUTE_LABEL = 'Size Chart';

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
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Category::ENTITY);

        $this->addAttribute($eavSetup, self::ATTRIBUTE_CODE, self::ATTRIBUTE_LABEL);
        $eavSetup->addAttributeToGroup(
            Category::ENTITY,
            $attributeSetId,
            self::ADD_TO_ATTRIBUTE_GROUP,
            self::ATTRIBUTE_CODE,
            110
        );
    }

    /**
     * Add the category attribute
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
            Category::ENTITY,
            $code,
            [
                'type' => 'varchar',
                'label' => $label,
                'input' => 'select',
                'source' => SizeChart::class,
                'required' => false,
                'sort_order' => 110,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => self::ADD_TO_ATTRIBUTE_GROUP,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true
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
        $eavSetup->removeAttribute(Category::ENTITY, self::ATTRIBUTE_CODE);

    }
}
