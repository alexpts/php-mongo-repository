# php-mongo-repository

[![Build Status](https://travis-ci.org/alexpts/php-mongo-repository.svg?branch=master)](https://travis-ci.org/alexpts/php-mongo-repository)
[![Code Coverage](https://scrutinizer-ci.com/g/alexpts/php-mongo-repository/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/alexpts/php-mongo-repository/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexpts/php-mongo-repository/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexpts/php-mongo-repository/?branch=master)



### Example

[integration tests](https://github.com/alexpts/php-mongo-repository/blob/master/test/integration/MongoRepoTest.php)

```php
<?php
use PTS\DataTransformer\DataTransformer;
use PTS\MongoRepo\CollectionManager;
use Test\PTS\MongoRepo\src\UserRepo;

$collectionManager = new CollectionManager([
	'dsn' => 'mongodb://127.0.0.1:27017/',
	'db' => 'test'
]);
$mapper = new DataTransformer;
$repo = new UserRepo($collectionManager, $mapper);

$models = $repo->findModels(['name' => 'alex']); // models
$docsAsArray = $models = $repo->find(['name' => 'alex']); // native mongo docs
```
