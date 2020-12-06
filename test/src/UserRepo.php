<?php
declare(strict_types=1);

namespace Test\PTS\MongoRepo\src;

use PTS\MongoRepo\MongoRepo;

class UserRepo extends MongoRepo
{
    protected string $tableName = 'users';
    protected string $classModel = UserModel::class;
}
