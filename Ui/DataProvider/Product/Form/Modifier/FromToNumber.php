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
namespace MageBootcamp\SizeChart\Ui\DataProvider\Product\Form\Modifier;

use MageBootcamp\SizeChart\Helper\RangeHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * This class is responsible for the from - to range selector in the product detail page in the backend.
 */
class FromToNumber extends AbstractModifier
{
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface|mixed|null
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $fromToFields;

    /**
     * @param LocatorInterface                  $locator
     * @param ArrayManager                      $arrayManager
     * @param AttributeRepositoryInterface|null $attributeRepository
     * @param array                             $fromToFields
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        AttributeRepositoryInterface $attributeRepository = null,
        array $fromToFields = []
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->attributeRepository = $attributeRepository
            ?: ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
        $this->fromToFields = $fromToFields;
    }

    /**
     * Add the range field based on the fromToFields
     *
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        foreach ($this->fromToFields as $field) {
            $meta = $this->addRangeField($meta, $field);
        }

        return $meta;
    }

    /**
     * Add the range field based on the field-date from core Magento.
     *
     * @param array  $meta
     * @param string $fieldName
     *
     * @return array
     */
    protected function addRangeField(array $meta, string $fieldName)
    {
        $fromField = $fieldName . RangeHelper::FROM_SUFFIX;
        $toField = $fieldName . RangeHelper::TO_SUFFIX;

        $fromFieldPath = $this->arrayManager->findPath($fromField, $meta, null, 'children');
        $toFieldPath = $this->arrayManager->findPath($toField, $meta, null, 'children');

        if ($fromFieldPath && $toFieldPath) {
            $fromContainerPath = $this->arrayManager->slicePath($fromFieldPath, 0, -2);
            $toContainerPath = $this->arrayManager->slicePath($toFieldPath, 0, -2);

            $meta = $this->arrayManager->merge(
                $fromFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Set chest size from'),
                    'additionalClasses' => 'admin__field-date',
                ]
            );

            $meta = $this->arrayManager->merge(
                $toFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('To'),
                    'scopeLabel' => null,
                    'additionalClasses' => 'admin__field-date'
                ]
            );

            $meta = $this->arrayManager->merge(
                $fromContainerPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => false,
                    'required' => false,
                    'additionalClasses' => 'admin__control-grouped-date',
                    'breakLine' => false,
                    'component' => 'Magento_Ui/js/form/components/group',
                ]
            );

            $meta = $this->arrayManager->set(
                $fromContainerPath . '/children/' . $toField,
                $meta,
                $this->arrayManager->get($toFieldPath, $meta)
            );

            $meta = $this->arrayManager->remove($toContainerPath, $meta);
        }

        return $meta;
    }

    /**
     * Format the product data to the decimal defined in the helper.
     *
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        foreach ($data as $dataTypeId => $productInfo) {
            if ($this->hasProductInformation($productInfo)) {
                foreach ($productInfo['product'] as $attributeKey => $attributeValue) {
                    if ($this->isFromOrToAttribute($attributeKey)) {
                        $data[$dataTypeId]['product'][$attributeKey] = number_format(
                            $attributeValue,
                            RangeHelper::DECIMAL
                        );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param mixed $productInfo
     *
     * @return bool
     */
    protected function hasProductInformation($productInfo): bool
    {
        return isset($productInfo['product']) && is_array($productInfo['product']);
    }

    /**
     * @param string $attributeKey
     *
     * @return bool
     */
    protected function isFromOrToAttribute(string $attributeKey): bool
    {
        return strpos($attributeKey, RangeHelper::FROM_SUFFIX) !== false
            || strpos($attributeKey, RangeHelper::TO_SUFFIX) !== false;
    }
}
