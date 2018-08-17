# **Getting Started With OkaPaginationBundle**
==============================================

This bundle provides a flexible pagination system.

Prerequisites
=============

The OkaPaginationBundle has the following requirements:
 - PHP 5.5+
 - Symfony 2.8+
 - Twig Extension

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
    model_manager_name: default
    item_per_page: 10                                           # Global number of items to show by page
    max_page_number: 4000                                       # Global number max of page to show
    template: OkaPaginationBundle:Pagination:paginate.html.twig # Global twig template used for shown pagination menu
    request:
        query_map:
            page: page                                          # Global page query parameter name
            item_per_page: item_per_page                        # Global number of items by page query parameter name
            sort: sort                                          # Global sort field query parameter name
            desc: desc                                          # Global sort direction query parameter name
        sort:
            delimiter: ','                                      # Global sort query delimiter value
            attributes_availables: ['name']                     # Global sort query value availables attributes
    twig:
        enable_extension: true
        enable_global: false
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
                    filters:
                        name:
                            type: string
                        enabled:
                            field: enabled         # Not required if the filter name is equal to the field name
                            type: boolean          # The type in which the value of the filter will be casted
                sort:
                    delimiter: ','
                    attributes_availables: ['name', 'createdAt']
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
// Acme\DemoBundle\Controller\FooController.php

public function listAction(Request $request)
{
    /** @var \Oka\PaginationBundle\Service\PaginationManager $pm */
    $pm = $this->get('oka_pagination.manager');
    
    // Use default pagination manager
    /** @var \Oka\PaginationBundle\Util\PaginationResultSet $page */
    $page = $pm->paginate(Foo::class, $request, [], ['name' => 'ASC']);
    
    // Or use custom pagination manager
    // /** @var \Oka\PaginationBundle\Util\PaginationResultSet $page */
    // $page = $pm->paginate('foo', $request, [], ['name' => 'ASC']);
    
    // parameters to template
    return $this->render('AcmeDemoBundle:Foo:list.html.twig', ['page' => $page]);
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
// Acme\DemoBundle\Controller\FooController.php

public function listAction(Request $request)
{
    /** @var \Oka\PaginationBundle\Service\PaginationManager $pm */
    $pm = $this->get('oka_pagination.manager');
    
    // Use default pagination manager
    /** @var \Oka\PaginationBundle\Util\PaginationResultSet $page */
    $pm->createQuery('foo', $request, [], ['name' => 'ASC'])
       ->setCountItemsCallable(function(EntityRepository $er, array $criteria){
           // Here your code to return the number of elements
           // ...
       })
       ->setSelectItemsCallable(function(EntityRepository $er, array $criteria, array $orderBy, $limit, $offset){
           // Here your code to return the elements list
           // ...
       });
    
    /** @var \Oka\PaginationBundle\Util\PaginationResultSet $page */
    $page = $pm->fetch();
    
    // parameters to template
    return $this->render('AcmeDemoBundle:Foo:list.html.twig', ['page' => $page]);
}
```
