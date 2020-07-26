![MageBootcamp Logo](https://magebootcamp.com/wp-content/uploads/2020/07/mbc_logo_export01.png)

## Overview
MageBootcamp SizeChart is a module that allows you to add
size chart to your product detail page and filter the products based on the product sizes.
The sizes charts are data-driven, so if you add all the data to your products, the size chart will automatically be generated.

This module is free to use and is part of the MageBootcamp course. I've added extra comments in the module to
help you out learning Magento but also to use this module as reference.

If you need any help with this module, please let me know.

Kind regards,

Daniel Donselaar

[Mentor at MagebootCamp](https://magebootcamp.com)

[Daniel@MageBootcamp.com](mailto:daniel@magebootcamp.com)

## Installation
Setup a Magento 2 store with composer. Go to your Magento root directory and type in:
```
composer require 'magebootcamp/sizechart';
```
After composer installation is complete:
```
php bin/magento module:enable MageBootcamp_SizeChart;
php bin/magento setup:upgrade;
```

To uninstall the module:
```
php bin/magento module:uninstall MageBootcamp_SizeChart;
```

## Features
### 1. Size Chart in the product detail page
Add a size chart to your product detail view and let the customer know which sizes you have.
You can show the chest, waist, and hip size. By hovering over the list of sizes you can preselect the default sizes (S, M, L, and XL).

![Preselect swatches based on the Size Chart](https://magebootcamp.com/wp-content/uploads/2020/07/product-detail-page-size-chart.png)

> Preselect swatches based on the Size Chart

### 2. Filter by sizes on the product overview page
Your customer can filter products based on their chest, waist, and hip size. You will see a custom sidebar widget that has multiple fields.
Optionally, you can install the CustomerFitness module of MageBootcamp and filter the size based on the saved sizes in the customer account.

![Add filter to products](https://magebootcamp.com/wp-content/uploads/2020/07/product-overview-page-size-chart-filter.png)

> Add filter to products

![Size filters applied](https://magebootcamp.com/wp-content/uploads/2020/07/product-overview-page-size-chart-filter-enabled.png)

> Size filters applied

### 3. Data driven size chart: import sizes
The sizes (chest, waist, and hip size) are product attributes. There are multiple ways you can import your data:
- Magento default importer to fill product attribute data (for example import from a PIM)
- Add the data by hand through the Magento backend
- Use the chart sizes import command and automatically assign sizes to products through a predefined size chart in the di.xml.
You can add various types to your category.

**Add sizes to your product in the backend**

In the backend, you can add the 'from size' and 'to size'. The sizes only apply to simple products
The customer applies a size in the frontend and the filter will look if a size exists between these values.
For example, if a customer searches for chest size 86 and the configuration of the product is 81 - 92, then the filter response will be positive.

![Configure Product Sizes](https://magebootcamp.com/wp-content/uploads/2020/07/configure-product-sizes.png)

> Configure your product sizes

**Size Chart selector in Category settings**

Backend > Catalog > Category > [select category] > Size Guide > Size Chart

![Size Mapping Configuration](https://magebootcamp.com/wp-content/uploads/2020/07/backend-category-edit-add-size-chart.png)

> Add a size chart mapping in the category configuration in the backend

**Update Sizes based on category settings**

```php bin/magento magebootcamp:sizes:update```

![MageBootcamp Logo](https://magebootcamp.com/wp-content/uploads/2020/07/magebootcamp-import-sizes-1.png)

> Load sizes based on a size chart mapping

### 4. Optional: Save sizes for a customer

Optionally, you can install the MageBootcamp CustomerFitness module. The module allows you to add a chest, waist, and hip size
to the customer account. The customer can press the 'Use my size' button to apply the size filter with the customer sizes.

![MageBootcamp Logo](https://magebootcamp.com/wp-content/uploads/2020/07/customer-overview-page-customer-fitness-size-chart.png)

> 'Use my size' button that allows the customer to apply their account sizes.

**[MageBootcamp CustomerFitness Module](https://github.com/magebootcamp/CustomerFitness)**

## Support
Created by MageBootcamp: The Ultimate Online Magento Course.
We are here to help you become a Magento PRO.
Watch and learn at https://magebootcamp.com.

For feature requests, feedback and support, please contact [Daniel@MageBootcamp.com](mailto:daniel@magebootcamp.com)
