# **Getting Started With OkaPaginationBundle**
==============================================

This bundle provides a flexible pagination system.

Prerequisites
=============

The OkaPaginationBundle has the following requirements:
 - PHP 7.2+
 - Symfony 4.4

Installation
============

Installation is a quick (I promise!) 4 step process:

1. Download OkaPaginationBundle
2. Enable the Bundle
3. Configure the OkaPaginationBundle
4. Use bundle and enjoy!

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require coka/pagination-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
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

Step 3: Configure the Bundle
----------------------------

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
oka_pagination:
    db_driver: orm
    object_manager_name: default
    item_per_page: 10                      # The defaults number of items to show by page
    max_page_number: 4000                  # The defaults number max of page to show
    filters:
        createdAt:
            cast_type: datetime
            orderable: true
    sort:
        delimiter: ','                     # The defaults sort query delimiter value
        order:                             # The defaults order
            createdAt: DESC
    query_mappings:
        page: page                         # The defaults page query parameter name
        item_per_page: item_per_page       # The defaults number of items by page query parameter name
        sort: sort                         # The defaults sort field query parameter name
        desc: desc                         # The defaults sort direction query parameter name
        fields: fields                     # The defaults sort direction query parameter name
        distinct: distinct                 # The defaults sort direction query parameter name
    twig:
        template: '@OkaPagination:widget:pagination.html.twig'    # The defaults twig template used for shown pagination widget
    pagination_managers:
        foo:
            db_driver: orm
            object_manager_name: default
            class: App\Entity\Foo
            item_per_page: 10
            max_page_number: 4000
            template: '@OkaPagination:widget:pagination.html.twig'
            filters:
                id:
                    cast_type: int
                name:
                    property_name: lastName    # Not required if the filter name is equal to the field name.
                    cast_type: string          # The PHP type in which the filter value will be casted. The available values are `array`, `boolean`, `bool`, `double`, `float`, `real`, `integer`, `int`, `string`, `datetime`, `object`.
                    searchable: false          # Indicates whether the filter can be used for filter the request, the defaults value is `true`.
                    orderable: true            # Indicates whether the filter can be used for sort the request, the defaults value is `true`.
                enabled:
                    cast_type: boolean
                createdAt:
                    cast_type: datetime
                    orderable: true
            sort:
                order:
                    enabled: DESC
                    createdAt: ASC
            query_mappings:
                page: page
                item_per_page: item_per_page
                sort: sort
                desc: desc
                fields: fields
                distinct: distinct
```

Step 4: Use the bundle is simple
--------------------------------

The goal of this bundle is to paginate some `Entity` (ORM) or `Document` (MongoDB) class.
You can use it in two ways.

1. Use default pagination manager.
2. Use custom pagination manager.

#### In Controllers

Initialize pagination 

```php
// App\Controller\FooController.php

use App\Entity\Foo;

//...

/**
 * @Route("/foo/list", methods="GET", name="app_foo_list")
 */
public function list(Request $request)
{
    /** @var \Oka\PaginationBundle\Pagination\PaginationManager $paginationManager */
    $paginationManager = $this->get('oka_pagination.pagination_manager');
    
    // Use default pagination manager
    /** @var \Oka\PaginationBundle\Pagination\Page $page */
    $page = $pm->paginate(Foo::class, $request, [], ['name' => 'ASC']);
    
    // Or use custom pagination manager
    // /** @var \Oka\PaginationBundle\Pagination\Page $page */
    // $page = $pm->paginate('foo', $request, [], ['name' => 'ASC']);
    
    // parameters to template
    return $this->render('foo/list.html.twig', ['page' => $page]);
}
```

#### In Views (Twig)

```twig
{# total items count #}
<div class="count">
    {{ page.getFullyItems() }}
</div>

<table>
{# table body #}
{% for item in page.items %}
<tr {% if loop.index is odd %}class="color"{% endif %}>
    <td>{{ item.id }}</td>
    <td>{{ item.title }}</td>
</tr>
{% endfor %}
</table>

{# display navigation #}
<div class="navigation">
    {# Use the current pagination manager #}
    {{ paginate('foo_path' , {'query': 'query'}) }}
    
    {# Or use a specific pagination manager #}
    {# {{ paginate_foo('foo_path' , {'query': 'query'}) }} #}
</div>
```

#### Advanced Usage

```php
// App\Controller\FooController.php

public function listAction(Request $request, EntityManagerInterface $em)
{
    /** @var \Oka\PaginationBundle\Pagination\PaginationManager $paginationManager */
    $paginationManager = $this->get('oka_pagination.pagination_manager');
    
    $queryBuilder = $em->createQueryBuilder();
    // Adds custom query expresion `$queryBuilder->andWhere(...)`
    // If you use the orm driver you can use the `Query::getDqlAlias()` method to retrieve the value of the dql alias. 
    // ...
    
    // Use default pagination manager
    /** @var \Oka\PaginationBundle\Pagination\Query $query */
    $query = $paginationManager->createQuery('foo', $request, [], ['name' => 'ASC']);
    $query->setDBALQueryBuilder($queryBuilder);
    
    /** @var \Oka\PaginationBundle\Pagination\Page $page */
    $page = $query->fetch();
    
    // parameters to template
    return $this->render('foo/list.html.twig', ['page' => $page]);
}
```
