<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

class Database
{
    private readonly \PDO $conn;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        string $charset,
    ) {
        $this->conn = new \PDO(
            "mysql:dbname=$database;host=$host;charset=$charset",
            $username,
            $password,
        );
        $this->conn->exec("SET time_zone='America/Sao_Paulo'");
    }

    public function getConnection(): \PDO
    {
        return $this->conn;
    }

    public function insert(string $rawQuery, array $params = []): int
    {
        try {

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare($rawQuery);

            $this->setParams($stmt, $params);

            $stmt->execute();

            $lastInsertId = $this->conn->lastInsertId();

            $this->conn->commit();

            return (int)$lastInsertId;

        } catch (\PDOException $e) {

            $this->conn->rollBack();

            throw $e;
        }
    }

    public function select(string $rawQuery, array $params = []): array
    {

        try {

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare($rawQuery);

            $this->setParams($stmt, $params);

            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt->closeCursor();

            $this->conn->commit();

            return $results;

        } catch (\PDOException $e) {

            $this->conn->rollBack();

            throw $e;

        }

    }

    public function query(string $rawQuery, array $params = []): int
    {

        try {

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare($rawQuery);

            $this->setParams($stmt, $params);

            $stmt->execute();

            $this->conn->commit();

            return $stmt->rowCount();

        } catch (\PDOException $e) {

            $this->conn->rollBack();

            throw $e;

        }

    }

    private function setParams(\PDOStatement $statement, array $parameters = []): void
    {

        foreach ($parameters as $key => $value) {

            $this->bindParam($statement, $key, $value);

        }

    }

    private function bindParam(\PDOStatement $statement, string|int $key, mixed $value): void
    {

        $statement->bindParam($key, $value);

    }
}
