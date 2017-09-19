CHANGELOG
=========

#### 1.2.9 (2017-09-19)

* Updated `LICENCE`.
* Updated `README`.
* Updated documentation.
* Improve code quality.

#### 1.2.8 (2017-09-11)

* Added the ability to get the value of a datetime query filter from a timestamps.

#### 1.2.7 (2017-09-11)

* Lets you not supply the 'oka_pagintaion.pagination_managers` configuration value.
* Added french translation.

#### 1.2.6 (2017-06-01)

* Upgraded property `filter` in `PaginationResultSet` class of string to array value.

#### 1.2.5 (2017-06-01)

* Added property `filter` in `PaginationResultSet` class.

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

#### 1.1.5 (2017-02-16)

* Added itemOffset in pagination resultset.

#### 1.1.4 (2017-02-25)

* Modify propertie support.doc within composer.json file.

#### 1.1.3 (2017-02-25)

* Fixed pagination manager stricMode.

#### 1.1.2 (2017-02-25)

* Updated composer file.

#### 1.1.1 (2017-02-25)

* Added getter method for page and itemPerPage.

#### 1.1.0 (2017-02-15)

* Added getting started in documentation.
* Allow support of object class name as value of pagination manager name.
* Allow to paginate many entity class in single request.
* Added criteria in count item closure.
* Thrown an exception if the values returned by the closures are not valid.
* Fixed mongodb query builder skip method bad using.
* Added value `doctrine/mongodb-odm` in propertie `suggests` of composer.json file.

#### 1.0.0 (2017-02-01)

* Fixed bug callable.
* Fixed pagination result set attribute `pageNumber` is undefined.
* Fixed pagination manger bad hydratation mode.
* Update composer file.