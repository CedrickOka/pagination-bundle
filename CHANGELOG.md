CHANGELOG
=========

#### 2.7.2 (2018-10-17)

* Fixed bug.

#### 2.7.1 (2018-08-17)

* Fixed an error in `README.md` file.

#### 2.7.0 (2018-07-19)

* Updated `composer.json`.
* Added service ID `oka_pagination.query_builder_handler`.
* Defined service ID `oka_pagination.query_builder_manipulator` like alias of service ID `oka_pagination.query_builder_handler`.
* Added `Oka\PaginationBundle\Converter\ORM\IsNullQueryExprConverter` class.
* Added `Oka\PaginationBundle\Converter\ORM\IsNotNullQueryExprConverter` class.

#### 2.6.0 (2018-06-21)

* Added `Oka\PaginationBundle\Converter\DBAL\EqualQueryExprConverter` class.
* Added `Oka\PaginationBundle\Converter\DBAL\NotEqualQueryExprConverter` class.
* Renamed from `Oka\PaginationBundle\Converter\LikeQueryExprConverter` to `Oka\PaginationBundle\Converter\DBAL\LikeQueryExprConverter`.
* Renamed from `Oka\PaginationBundle\Converter\NotLikeQueryExprConverter` to `Oka\PaginationBundle\Converter\DBAL\NotLikeQueryExprConverter`.

#### 2.5.1 (2018-05-19)

* Fixed bug `composer.json` bad definition.

#### 2.5.0 (2018-05-19)

* Changed project folders structure.
* Added `.gitignore` file.

#### 2.4.4 (2018-01-24)

* Fixed `oka_pagination.pagination_managers.*.template` configuration values bad definition.

#### 2.4.3 (2017-12-19)

* Fixed bad parameter given has `preg_match()` function in `QueryBuilderManipulator::supports()` method.

#### 2.4.2 (2017-12-08)

* Fixed bad count query caused by many fields given for query projection.

#### 2.4.1 (2017-12-07)

* Improves comparison of booleans that must be done strictly.

#### 2.4.0 (2017-12-07)

* Defined by default `count` and `select` query like `distinct`.

#### 2.3.0 (2017-12-07)

* Added `Oka\PaginationBundle\Util\PaginationQuery::addQueryPart()` method.
* Added `Oka\PaginationBundle\Util\PaginationQuery::getQueryParts()` method.
* Added `oka_pagination.request.query_map.fields` configuration values.
* Added `oka_pagination.request.query_map.distinct` configuration values.
* Allowed to select specific fields return to a page.

#### 2.2.0 (2017-12-05)

* Added `Oka\PaginationBundle\Converter\Exception\PaginationException` class
* Made all exception classes inherited from the `PaginationException` class.
* Added `FilterUtil` class.
* Added `Oka\PaginationBundle\Converter\ORM\RangeQueryExprConverter` class.
* Added `Oka\PaginationBundle\Converter\Mongodb\RangeQueryExprConverter` class.

#### 2.1.2 (2017-11-29)

* Fixed token not defined in the query.

#### 2.1.1 (2017-11-29)

* Modified `QueryBuilderManipulator::applyExprFromString()` method signature.

#### 2.1.0 (2017-11-29)

* Fixed bug undefined constant in class method `Oka\PaginationBundle\Service\PagingationManager::paginate()`.
* Added the ability of filtering query with sql expresion in URL query filters.
* Added `oka_pagination.query_builder_manipulator` service ID.
* Added `oka_pagination.query_expr_converters` configuration values.
* Added `Oka\PaginationBundle\Converter\LikeQueryExprConverter` class in query expression map converter.
* Added `Oka\PaginationBundle\Converter\NotLikeQueryExprConverter` class in query expression map converter.
* Updated documentation.

#### 2.0.1 (2017-11-27)

* Adds support for array type in request filters.

#### 2.0.0 (2017-11-25)

* Removed `oka_pagination.sort` configuration values.
* Removed `oka_pagination.pagination_managers.[manageName].sort` configuration values.
* Removed `oka_pagination.twig.enable_global` configuration values.
* Added `Oka\PaginationBundle\Util\PaginationQuery` class.
* Added `PaginationManager::createQuery()` method.
* Deprecated `PaginationManager::prepare()` method.
* [BC break] Removed `Oka\PaginationBundle\Service\PaginationManager` properties `$page`, `$itemPerPage`, `$maxPageNumber` and associated getters and setters.
* [BC break] Removed `PaginationManager::fetch()` deprecated method since bundle version `1.3.0`.
* [BC break] Removed `PaginationManager::getPaginationStore()` deprecated method since bundle version `1.3.0`.
* [BC break] Removed `PaginationManager::getCurrentManagerName()` deprecated method since bundle version `1.3.0`.
* Updated documentation.

#### 1.3.0 (2017-11-22)

* Deprecated `oka_pagination.sort` configuration values.
* Deprecated `oka_pagination.pagination_managers.[manageName].sort` configuration values.
* Added `oka_pagination.request.sort` configuration values.
* Added `oka_pagination.pagination_managers.[manageName].request.sort` configuration values.
* Updated documentation.

#### 1.2.14 (2017-09-25)

* Fixed bad definition of arguments of `oka pagination.default.object manager` service.

#### 1.2.13 (2017-09-20)

* Updated `composer.json`.

#### 1.2.12 (2017-09-20)

* Renamed `PaginationManagersConfig` class to `PaginationManagerBag`.
* Changed service ID form `oka_pagination.managers_config` class to `oka_pagination.manager_bag`.

#### 1.2.11 (2017-09-19)

* Fixed text files should end with a newline character.

#### 1.2.10 (2017-09-19)

* Fixed text files should end with a newline character.

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

