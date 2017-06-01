Changelog
=========

#### 1.2.4 (2017-06-01)

* Adds support for the resolution of the filters of the request.

#### 1.2.3 (2017-05-31)

* Makes the node `pagination_manager` optional in the bundle config.

#### 1.2.2 (2017-05-31)

* Adds support of Symfony3 and PHP7.

#### 1.2.O (2017-03-15)

* Separate the twig extension configuration from the PaginationManager to an OkaPaginationExtension class in Namespace `Oka\PaginationBundle\Twig\OkaPaginationExtension`.
* Allows you to enable or disable the twig extension.
* Allows to activate or deactivate the injection of globals variables in the twig template.
* Added params context in pagintaion template.

#### 1.1.1 (2017-02-16)

* Added itemOffset in pagination resultset.

#### 1.1.1 (2017-02-15)

* Added getter method for page and itemPerPage.

#### 1.1.0 (2017-02-15)

* Added getting started in documentation.
* Allow support of object class name as value of pagination manager name.
* Allow to paginate many entity class in single request.