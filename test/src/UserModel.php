<?php
declare(strict_types=1);

namespace Test\PTS\MongoRepo\src;

use MongoDB\BSON\ObjectId;

class UserModel
{
    protected ObjectId $id;
    protected string $name = '';
    protected string $email = '';

    public function __construct(string $email, string $name)
    {
        $this->id = new ObjectId;
        $this->email = $email;
        $this->name = $name;
    }

    public function getId(): ?ObjectId
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return UserModel
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

}
