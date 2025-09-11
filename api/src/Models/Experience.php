<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Enums\HttpStatus as HTTPStatus;

class Experience extends Model
{

  public function create()
  {

    $sql = "INSERT INTO experiences (title, description, start_date, end_date)
            VALUES (:title, :description, :start_date, :end_date)";

    try {
      
      $db = new Database();

			$experienceId = $db->insert($sql, array(
				":title"       => $this->getTitle(),
        ":description" => $this->getDescription(),
        ":start_date"  => $this->getStartDate(),
        ":end_date"    => $this->getEndDate()
			));

      $this->setId($experienceId);

      return $this->getAttributes();

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao adicionar experiência", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    }

  }

  public function update() 
	{

    $sql = "UPDATE experiences
            SET title = :title, description = :description, start_date = :start_date, end_date = :end_date
            WHERE id = :id";

    try {

      $db = new Database();
      
      $db->query($sql, array(
				":id"          => $this->getId(),
        ":title"       => $this->getTitle(),
        ":description" => $this->getDescription(),
        ":start_date"  => $this->getStartDate(),
        ":end_date"    => $this->getEndDate()
      ));

      return $this->getAttributes();

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao atualizar experiência", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    }

  }

  public static function list($page = 1, $itemsPerPage = 10, $search = "")
  {
      
    $params = [];
      
    $where = ["1=1"];

    $search = trim($search);

    if (!empty($search)) {
        
      $where[] = "(e.title LIKE :search OR e.description LIKE :search)";
        
      $params[":search"] = "%$search%";

    }

    $sql = "
      SELECT " . ($page !== NULL && $itemsPerPage !== NULL ? "SQL_CALC_FOUND_ROWS" : "") . "
        e.id,
        e.title,
        e.description,
        e.start_date,
        e.end_date,
        e.created_at,
        e.updated_at
      FROM experiences e
      WHERE " . implode(" AND ", $where) . "
      ORDER BY e.start_date DESC
    ";

    if ($page !== NULL && $itemsPerPage !== NULL) {
      
      $start = ($page - 1) * $itemsPerPage;
      
      $sql .= " LIMIT $start, $itemsPerPage";

    }

    try {
      
      $db = new Database();
      
      $results = $db->select($sql, $params);

      if (empty($results)) {
          
        throw new \Exception("Nenhuma experiência encontrada", HTTPStatus::NO_CONTENT);
      
      }

      if ($page !== NULL && $itemsPerPage !== NULL) {
          
        $total = (int)$db->select("SELECT FOUND_ROWS() AS total")[0]["total"];

        return [
          "experiences" => $results,
          "total"       => $total,
          "pages"       => ceil($total / $itemsPerPage)
        ];

      }

      return $results;

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao obter lista de experiências", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }    

  }

  public static function get($id)
	{

    $sql = "SELECT * FROM experiences WHERE id = :id";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":id" => $id
			));

      if (empty($results)) {
			
			  throw new \Exception("Experiência não encontrada", HTTPStatus::NOT_FOUND);
        
      }

			return $results[0];

		} catch (\PDOException $e) {
        
      throw new \Exception("Erro ao obter detalhes da experiência", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public static function delete($id) 
	{

    $sql = "DELETE FROM experiences WHERE id = :id";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":id"=>$id
			));

		} catch (\PDOException $e) {
        
      throw new \Exception("Erro ao excluir experiência", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    }

  }

}
