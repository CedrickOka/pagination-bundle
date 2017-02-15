#**Getting Started With OkaPaginationBundle**
=====================================

This bundle provides a flexible pagination system.

## Prerequisites

The OkaPaginationBundle has the following requirements:
 - PHP 5.5
 - Symfony 2.5+
 - Twig Extension

## Installation

Installation is a quick (I promise!) 2 step process:

1. Download OkaPaginationBundle
2. Enable the Bundle
3. Configure the OkaPaginationBundle
4. Use bundle and enjoy!

### Step 1: Download OkaPaginationBundle

Use composer for install bundle.

```
$ composer require coka/pagination-bundle
```

### Step 2: Enable the Bundle

After installation, enable the bundle by adding it to the list of registered bundles 
in the `app/AppKernel.php` file of your project:

```
<?php
// app/AppKernel.php
// ...

class AppKernel extends Kernel
{
	public function registerBundles()
	{
		$bundles = array(
			// ...
			
			new Oka\PaginationBundle\OkaPaginationBundle(),
		);
		
		// ...
	}
	
	// ...
}
```

### Step 3: Configure the OkaPaginationBundle

Add the following configuration to your `config.yml`.

```
# app/config/config.yml
oka_pagination:
    db_driver: orm
    model_manager_name: default
    item_per_page: 10
    max_page_number: 4000
    template: OkaPaginationBundle:Pagination:paginate.html.twig
    request:
       query_map:
            page: page
            item_per_page: item_per_page
            sort: sort
            desc: desc
    sort:
        delimiter: ','
        attributes_availables: ['name']
    pagination_managers:
        foo:
            db_driver: orm
            model_manager_name: default
            class: Acme\DemoBundle\Entity\Foo
            item_per_page: 10
            max_page_number: 4000
            template: OkaPaginationBundle:Pagination:paginate.html.twig
            request:
                query_map:
                    page: page
                    item_per_page: item_per_page
                    sort: sort
                    desc: desc
            sort:
                delimiter: ','
                attributes_availables: ['name']
```

### Step 4: Use the bundle is simple.

The goal of this bundle is to paginate some `Entity` (ORM) or `Document` (MongoDB) class.
You can use it in two ways.

1. Use default pagination manager.
2. Use custom pagination manager.

#### In Controllers

Initialize pagination 

```
/** @var \Oka\PaginationBundle\Service\PaginationManager */
$paginationManager = $this->get('oka_pagination.manager');

// Use default pagination manager
/** @var \Oka\PaginationBundle\Util\PaginationResultSet $paginationResultSet */
$paginationResultSet = $paginationManager->paginate(Foo::class, $request, $criteria, $orderBy);

/** @var array $items */
$items = $paginationResultSet->getItems();

```

```
/** @var \Oka\PaginationBundle\Service\PaginationManager */
$paginationManager = $this->get('oka_pagination.manager');

// Use custom pagination manager
/** @var \Oka\PaginationBundle\Util\PaginationResultSet $paginationResultSet */
$paginationResultSet = $paginationManager->paginate('foo', $request, $criteria, $orderBy);

/** @var array $items */
$items = $paginationResultSet->getItems();

```

#### In Views (Twig)

```
{# Use default pagination manager #}
{{ paginate('foo_path' , {'query': 'query'}) }}
```

```
{# Use custom pagination manager #}
{{ paginate_foo('foo_path' , {'query': 'query'}) }}
```