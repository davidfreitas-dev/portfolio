<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Handlers\UploadHandler;
use App\Enums\HttpStatus as HTTPStatus;

class Project extends Model
{

  public function create()
  {
    
    $sql = "INSERT INTO projects (title, description, link, is_active) 
            VALUES (:title, :description, :link, :is_active)";

    try {
      
      $db = new Database();

      $projectId = $db->insert($sql, [
        ":title"       => $this->getTitle(),
        ":description" => $this->getDescription(),
        ":link"        => $this->getLink(),
        ":is_active"   => $this->getIsActive() ?? true
      ]);

      $this->setId($projectId);

      if (NULL !== $this->getImage() && !is_string($this->getImage())) {
        
        $image = $this->setPhoto($this->getId(), $this->getImage());
        
        if ($image) {
          
          $imageUrl = $_ENV['API_URL'] . "/images/projects/" . $image;
          
          $this->setImage($imageUrl);
        
        }
      
      }

      $this->syncTechnologies($this->getTechnologies());

      return $this->getAttributes();

    } catch (\PDOException $e) {
      
      throw new \Exception("Erro ao criar projeto", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }
  }

  public function update()
  {
    
    $sql = "UPDATE projects
            SET title = :title, description = :description, link = :link, is_active = :is_active
            WHERE id = :id";

    try {
      
      $db = new Database();

      $db->query($sql, [
        ":title"       => $this->getTitle(),
        ":description" => $this->getDescription(),
        ":link"        => $this->getLink(),
        ":is_active"   => $this->getIsActive() ?? 1,
        ":id"          => $this->getId()
      ]);

      if (NULL !== $this->getImage() && !is_string($this->getImage())) {
        
        $image = $this->setPhoto($this->getId(), $this->getImage());
        
        if ($image) {
          
          $imageUrl = $_ENV['API_URL'] . "/images/projects/" . $image;
          
          $this->setImage($imageUrl);
        
        }
      
      }

      $this->syncTechnologies($this->getTechnologies());

      return $this->getAttributes();

    } catch (\PDOException $e) {
      
      throw new \Exception("Erro ao atualizar projeto", HTTPStatus::INTERNAL_SERVER_ERROR);
      
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
      
      $where[] = "(p.title LIKE :search OR p.description LIKE :search)";
      
      $params[":search"] = "%$search%";
    
    }

    $sql = "
      SELECT " . ($page !== NULL && $itemsPerPage !== NULL ? "SQL_CALC_FOUND_ROWS" : "") . "
        p.id,
        p.title,
        p.description,
        p.image,
        p.link,
        p.is_active,
        p.created_at,
        p.updated_at,
        (
          SELECT JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'name', t.name, 'image', t.image))
          FROM project_technologies pt
          JOIN technologies t ON pt.technology_id = t.id
          WHERE pt.project_id = p.id
        ) AS technologies
      FROM projects p
      WHERE " . implode(" AND ", $where) . "
      ORDER BY p.created_at DESC
    ";

    if ($page !== NULL && $itemsPerPage !== NULL) {
      
      $start = ($page - 1) * $itemsPerPage;
      
      $sql .= " LIMIT $start, $itemsPerPage";
    
    }

    try {
      
      $db = new Database();
      
      $results = $db->select($sql, $params);

      if (empty($results)) {
        
        throw new \Exception("Nenhum projeto encontrado", HTTPStatus::NO_CONTENT);
        
      }

      foreach ($results as &$project) {
        
        $project['technologies'] = $project['technologies']
          ? json_decode($project['technologies'], true)
          : [];
      
      }

      if ($page !== NULL && $itemsPerPage !== NULL) {
        
        $total = (int)$db->select("SELECT FOUND_ROWS() AS total")[0]["total"];
        
        return [
          "projects" => $results,
          "total"    => $total,
          "pages"    => ceil($total / $itemsPerPage)
        ];

      }

      return $results;

    } catch (\PDOException $e) {
      
      throw new \Exception("Erro ao obter lista de projetos", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }
  }

  public static function get($id)
  {
    
    try {
      
      $db = new Database();

      $result = $db->select(
        "SELECT * FROM projects WHERE id = :id LIMIT 1",
        [":id" => $id]
      );

      if (empty($result)) {
        
        throw new \Exception("Projeto não encontrado", HTTPStatus::NOT_FOUND);
        
      }

      $project = $result[0];

      $project['technologies'] = $db->select(
        "SELECT t.id, t.name, t.image 
        FROM technologies t 
        INNER JOIN project_technologies pt ON pt.technology_id = t.id 
        WHERE pt.project_id = :id",
        [":id" => $id]
      );

      return $project;

    } catch (\PDOException $e) {
      
      throw new \Exception("Erro ao obter detalhes do projeto", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  public static function delete($id)
  {
    
    $sql = "DELETE FROM projects WHERE id = :id";
    
    try {
      
      $db = new Database();
      
      $db->query($sql, [":id" => $id]);

      UploadHandler::deletePhoto($id, "projects");

      return true;

    } catch (\PDOException $e) {
      
      throw new \Exception("Erro ao excluir projeto", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

  private function setPhoto($id, $image)
  {
    $imageName = UploadHandler::uploadPhoto($id, $image, "projects");

    if (!$imageName) {
      
      return NULL;
      
    }

    $image = $imageName . ".jpg";

    $sql = "UPDATE projects SET image = :image WHERE id = :id";

    try {
      
      $db = new Database();
      
      $db->query($sql, [
        ':image' => $image,
        ':id'    => $id
      ]);

      return $image;

    } catch (\PDOException $e) {
      
      return NULL;
      
    }

  }

  private function syncTechnologies($technologies)
  {

    if (empty($technologies)) return;
    
    if (is_string($technologies)) {
      
      $technologies = array_filter(array_map('trim', explode(',', $technologies)));
      
    }

    if (!is_array($technologies)) return;

    $db = new Database();

    $db->query(
      "DELETE FROM project_technologies WHERE project_id = :id", 
      [
        ":id" => $this->getId()
      ]
    );

    foreach ($technologies as $techId) {
      
      $db->query(
        "INSERT INTO project_technologies (project_id, technology_id) VALUES (:pid, :tid)", 
        [
          ":pid" => $this->getId(),
          ":tid" => $techId
        ]
      );

    }

  }
  
}
