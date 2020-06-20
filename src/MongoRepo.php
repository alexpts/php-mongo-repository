<?php
declare(strict_types=1);

namespace PTS\MongoRepo;

use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use PTS\DataTransformer\DataTransformerInterface;
use RuntimeException;

/**
 * @mixin Collection
 */
abstract class MongoRepo
{

	public const SORT_DESC = -1;
	public const SORT_ASC = 1;

	protected string $tableName;
	protected string $classModel;

	protected string $map = 'db';
	protected string $pk = '_id';

	protected DataTransformerInterface $mapper;
	protected CollectionManager $collectionManager;

	public function __construct(CollectionManager $collectionManager, DataTransformerInterface $mapper)
	{
		$this->collectionManager = $collectionManager;
		$this->mapper = $mapper;
	}

	public function __call(string $name, array $arguments = [])
	{
		$collection = $this->getCollection();
		if (method_exists($collection, $name)) {
			return $collection->$name(...$arguments);
		}

		throw new RuntimeException("Unknown method {$name}");
	}

	public function getCollection(string $table = null): Collection
	{
		return $this->collectionManager->getCollection($table ?? $this->tableName);
	}

	/**
	 * @param object $model
	 * @param array $options
	 *
	 * @return InsertOneResult
	 */
	public function insertOneModel(object $model, array $options = []): InsertOneResult
	{
		$dto = $this->modelToDoc($model);
		$result = $this->getCollection()->insertOne($dto, $options);
		$this->fillId($result->getInsertedId(), $model);

		return $result;
	}

	public function replaceOneModel(object $model, array $filter, array $options = []): UpdateResult
	{
		$dto = $this->modelToDoc($model);
		return $this->getCollection()->replaceOne($filter, $dto, $options);
	}

	public function replaceOneModelById(object $model, ObjectId $id, array $options = []): UpdateResult
	{
		return $this->replaceOneModel($model, [$this->pk => $id], $options);
	}

	public function findOneModel(array $filter = [], array $options = []): ?object
	{
		$doc = $this->getCollection()->findOne($filter, $options);
		if ($doc === null) {
			return null;
		}

		return $this->docsToModels($this->classModel, [$doc], $this->getMap())[0];
	}

	public function findModels(array $filter = [], array $options = []): array
	{
		$cursor = $this->getCollection()->find($filter, $options);
		return $this->docsToModels($this->classModel, $cursor, $this->getMap());
	}

	protected function modelToDoc(object $model): array
	{
		return $this->mapper->toDTO($model, $this->getMap());
	}

	protected function docsToModels(string $class, iterable $docs, string $map): array
	{
		return $this->mapper->toModelsCollection($class, $docs, $map);
	}

	protected function fillId(ObjectId $id, object $model): void
	{
		$this->mapper->fillModel($model, ['_id' => $id], $this->getMap());
	}

	protected function getMap(): string
	{
		return $this->map;
	}
}
