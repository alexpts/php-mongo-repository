<?php
declare(strict_types=1);

namespace PTS\MongoRepo;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class CollectionManager
{

    protected Database $db;
    /** @var Collection[] */
    protected array $store = [];

    public function __construct(array $params)
    {
        $client = new Client(
            $params['dsn'] ?? 'mongodb://127.0.0.1/',
            $params['uriOptions'] ?? [],
            $params['driverOptions'] ?? []
        );

        $this->db = $client->selectDatabase($params['db']);
    }

    public function getCollection(string $name): Collection
    {
        if (!array_key_exists($name, $this->store)) {
            $this->store[$name] = $this->db->selectCollection($name);
        }

        return $this->store[$name];
    }
}
