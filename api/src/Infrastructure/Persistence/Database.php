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
        string $password
    ) {
        $this->conn = new \PDO(
            "mysql:dbname=$database;host=$host",
            $username,
            $password,
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET time_zone='America/Sao_Paulo'"],
        );
    }

    public function getConnection(): \PDO
    {
        return $this->conn;
    }

    public function insert($rawQuery, $params = []): int
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

    public function select($rawQuery, $params = []): array
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

    public function query($rawQuery, $params = []): int
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

    private function setParams($statement, $parameters = []): void
    {

        foreach ($parameters as $key => $value) {

            $this->bindParam($statement, $key, $value);

        }

    }

    private function bindParam($statement, $key, $value): void
    {

        $statement->bindParam($key, $value);

    }
}
