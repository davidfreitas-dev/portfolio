<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\UploadHandler;
use App\Enums\HttpStatus as HTTPStatus;

class Technology extends Model
{

  public function create()
  {

    $sql = "INSERT INTO technologies (name) VALUES (:name)";

    try {

      $this->checkTechnologyExists($this->getName());
      
      $db = new Database();

			$techId = $db->insert($sql, array(
				":name" => $this->getName()
			));

      $this->setId($techId);

      if (NULL !== $this->getImage() && !is_string($this->getImage())) {

        $image = $this->setPhoto($this->getId(), $this->getImage());
        
        if ($image) {

          $imageUrl = $_ENV['API_URL'] . "/images/technologies/" . $image;

          $this->setImage($imageUrl);

        }

      }

      return $this->getAttributes();

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao adicionar tecnologia", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public function update() 
	{

    $sql = "UPDATE technologies
            SET name = :name
            WHERE id = :id";

    try {

      $this->checkTechnologyExists($this->getName(), $this->getId());

      $db = new Database();
      
      $db->query($sql, array(
        ":name" => $this->getName(),
        ":id"   => $this->getId()
      ));

      if (NULL !== $this->getImage() && !is_string($this->getImage())) {

        $image = $this->setPhoto($this->getId(), $this->getImage());
        
        if ($image) {

          $imageUrl = $_ENV['API_URL'] . "/images/technologies/" . $image;

          $this->setImage($imageUrl);

        }

      }

      return $this->getAttributes();

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao atualizar tecnologia", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public static function list($page = 1, $itemsPerPage = 10, $search = "")
  {
      
    $params = [];
      
    $where = ["1=1"];
    
    $search = trim($search);

    if (!empty($search)) {
        
      $where[] = "(t.name LIKE :search)";
        
      $params[":search"] = "%$search%";

    }

    $sql = "
      SELECT " . ($page !== NULL && $itemsPerPage !== NULL ? "SQL_CALC_FOUND_ROWS" : "") . "
        t.id,
        t.name,
        t.image
      FROM technologies t
      WHERE " . implode(" AND ", $where) . "
      ORDER BY t.name ASC
    ";

    if ($page !== NULL && $itemsPerPage !== NULL) {
      
      $start = ($page - 1) * $itemsPerPage;
      
      $sql .= " LIMIT $start, $itemsPerPage";
    
    }

    try {

      $db = new Database();

      $results = $db->select($sql, $params);

      if (empty($results)) {
          
        throw new \Exception("Nenhuma tecnologia encontrada", HTTPStatus::NO_CONTENT);
        
      }

      if ($page !== NULL && $itemsPerPage !== NULL) {
        
        $total = (int)$db->select("SELECT FOUND_ROWS() AS total")[0]["total"];

        return [
          "technologies" => $results,
          "total" => $total,
          "pages" => ceil($total / $itemsPerPage)
        ];
      
      }

      return $results;

    } catch (\PDOException $e) {

      var_dump($e->getMessage());exit;
        
      throw new \Exception("Erro ao obter lista de tecnologias", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public static function get($id)
	{

    $sql = "SELECT * FROM technologies WHERE id = :id";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":id" => $id
			));

      if (empty($results)) {
			
        throw new \Exception("Tecnologia não encontrada", HTTPStatus::NOT_FOUND);
        
      }
      
      return $results[0];

		} catch (\PDOException $e) {
        
      throw new \Exception("Erro ao obter detalhes da tecnologia", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public static function delete($id) 
	{

    $sql = "DELETE FROM technologies WHERE id = :id";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":id" => $id
			));

      UploadHandler::deletePhoto($id, "technologies");
			
			return true;

		} catch (\PDOException $e) {
        
      throw new \Exception("Erro ao excluir tecnologia", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  private function checkTechnologyExists($name, $id = NULL) 
  {

    $sql = "SELECT * FROM technologies WHERE name = :name";

    		
    if ($id) {

      $sql .= " AND id != :id";

    }
    
    try {

      $db = new Database();
      
      $params = [":name" => $name];
      
      if ($id) {

        $params[":id"] = $id;

      }
      
      $results = $db->select($sql, $params);
      
      if (count($results) > 0) {

        throw new \Exception("Uma tecnologia com este nome já foi cadastrada.", HTTPStatus::CONFLICT);

      }

    } catch (\PDOException $e) {
        
      throw new \Exception("Erro ao verificar existência da tecnologia", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  private function setPhoto($id, $image)
  {
    
    $imageName = UploadHandler::uploadPhoto($id, $image, "technologies");

    if (!$imageName) {
      
      return NULL;      

    }

    $image = $imageName . ".jpg";
      
    $sql = "UPDATE technologies SET image = :image WHERE id = :id";
    
    try {

      $db = new Database();

      $db->query($sql, array(
        ':image' => $image,
        ':id'    => $id
      ));

      return $image;

    } catch (\PDOException $e) {

      return NULL;

    }

  }

}
