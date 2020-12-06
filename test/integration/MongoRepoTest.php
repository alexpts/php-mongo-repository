<?php
declare(strict_types=1);

namespace Test\PTS\MongoRepo\integration;

use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use PTS\DataTransformer\DataTransformer;
use PTS\MongoRepo\CollectionManager;
use RuntimeException;
use Test\PTS\MongoRepo\src\UserModel;
use Test\PTS\MongoRepo\src\UserRepo;

class MongoRepoTest extends TestCase
{
    protected UserRepo $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->initRepo();
    }

    public function tearDown(): void
    {
        $this->repo->deleteMany([]);
        parent::tearDown();
    }

    protected function initRepo(): void
    {
        $collectionManager = new CollectionManager([
            'dsn' => 'mongodb://127.0.0.1:27017/',
            'db' => 'test',
            'uriOptions' => [
                'serverSelectionTimeoutMS' => 3000,
                'socketTimeoutMS' => 3000,
            ],
            'driverOptions' => [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ],
            ],
        ]);

        $mapper = new DataTransformer;
        $mapper->getMapsManager()->setDefaultMapDir(dirname(__DIR__) . '/src/map');

        $this->repo = new UserRepo($collectionManager, $mapper);
    }

    public function testGetCollection(): void
    {
        $collection = $this->repo->getCollection();
        static::assertInstanceOf(Collection::class, $collection);
        static::assertSame('users', $collection->getCollectionName());

        $collection = $this->repo->getCollection('posts');
        static::assertInstanceOf(Collection::class, $collection);
        static::assertSame('posts', $collection->getCollectionName());
    }

    public function testProxyMethod(): void
    {
        static::assertSame('primary', $this->repo->getReadPreference()->getModeString());
    }

    public function testProxyException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown method same');

        $this->repo->same();
    }

    public function testInsertOneModel(): void
    {
        $model = new UserModel('test@some.io', 'alex');
        $result = $this->repo->insertOneModel($model);
        static::assertSame(1, $result->getInsertedCount());
        static::assertNotNull($model->getId());
        static::assertSame($result->getInsertedId(), $model->getId());

        $docs = $this->repo->find()->toArray();
        static::assertCount(1, $docs);
        static::assertSame('alex', $docs[0]['name']);
        static::assertSame('test@some.io', $docs[0]['email']);
    }

    public function testReplaceOneModel(): void
    {
        $model = new UserModel('test2@some.io', 'alex');
        $result = $this->repo->insertOneModel($model);
        static::assertSame(1, $result->getInsertedCount());

        $model->setName('max');
        $result = $this->repo->replaceOneModelById($model, $model->getId());
        static::assertSame(1, $result->getModifiedCount());

        $docs = $this->repo->find()->toArray();
        static::assertCount(1, $docs);
        static::assertSame('max', $docs[0]['name']);
        static::assertSame('test2@some.io', $docs[0]['email']);
    }

    public function testFindOneModel(): void
    {
        $this->seed(2);

        /** @var UserModel|null $model */
        $model = $this->repo->findOneModel(['name' => 'model1']);
        static::assertNotNull($model);
        static::assertInstanceOf(UserModel::class, $model);
        static::assertSame('model1@some.io', $model->getEmail());

        $model = $this->repo->findOneModel(['name' => 'model4']);
        static::assertNull($model);
    }

    public function testFindModels(): void
    {
        $this->seed(2);

        $models = $this->repo->findModels();
        static::assertCount(2, $models);
        static::assertInstanceOf(UserModel::class, $models[0]);
    }

    protected function seed(int $i = 1): void
    {
        while ($i) {
            $model = new UserModel("model{$i}@some.io", "model{$i}");
            $this->repo->insertOneModel($model);
            $i--;
        }
    }
}
