<?php
namespace devmazon\myorm;

use Exception;
use PDOException;
use DateTime;
use stdClass;

class MyORM
{
    private $table;
    private $primary;
    private $required;
    protected $timestamps;
    protected $columns;
    protected $group;
    protected $order;
    protected $limit;
    protected $offset;
    protected $statement;
    protected $data;
    protected $error = [];


    public function __construct(string $table, array $required, string $primary = 'id', bool $timestamps = true)
    {
      
        $this->table = $table;
        $this->timestamps = $timestamps;
        $this->primary = $primary;
        $this->required = $required;
       
    }

    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->$name = $value;
    }


    public function __isset($name)
    {
        return isset($this->data->$name);
    }

   
    public function __get($name)
    {
        return ($this->data->$name ?? null);
    }

  
    public function error($error)
    {
        $this->error[] = $error;
    }
    
    public function checkError()
    {
        if (count($this->error) > 0) {  
            return true;
        } else {
            return false;
        }

    }

    public function messageError()
    {
        $msg = '';

        foreach ($this->error as $msgsError) {
            $msg .= $msgsError;
        }
        return $msg;
    }

    /**
    * Group By
    */
    public function group(string $column)
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    /**
    * Order By
    */
    public function order(string $columnOrder)
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * Limit
     */
    public function limit(int $limit)
    {

        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * Offset
     */
    public function offset(int $offset)
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }


    public function table(string $table)
    {

        $this->table = $table;
        return $this;
    }


    public function find(?String $where = null, string $columns = "*")
    {

            if($where){
            $this->statement = "SELECT {$columns} FROM {$this->table} WHERE {$where}";

                return $this;
            }
            
            $this->statement = "SELECT {$columns} FROM {$this->table}";

            return $this;
    }

    /**
     * fetch function, performs a query to the database and returns only one row of the table.
     */
    public function fetch()
    {
        try {

            $sql = Config::db()->prepare($this->statement . $this->group . $this->order . $this->limit . $this->offset);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return $sql->fetch();
            }

        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return false;
        }
    }

    /**
     * fetchAll function, performs a query to the database and returns several rows of the table.
     */
    public function fetchAll()
    {
        try {

            $sql = Config::db()->prepare($this->statement . $this->group . $this->order . $this->limit . $this->offset);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return $sql->fetchAll();
            }

        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return false;
        }
    }

     /**
     * count function, counts the data that comes from the database and returns the entire value.  
     */
    public function count(): int
    {
        $sql = Config::db()->prepare($this->statement);
        $sql->execute();
        return $sql->rowCount();
    }

    /**
     * creates new items in any database table.  
     */
    public function create(): bool
    {
        $primary = $this->primary;
        try {
        if(!$this->required()){
             $this->error("Preencha os campos obrigatorios!");
             return false;
        }    
        if ($this->timestamps) {
            $this->created_at = (new DateTime("now"))->format("Y-m-d H:i:s");
        }
            $this->$primary = md5(uniqid(rand(), true));
        
            $columns = implode(", ", array_keys($this->returnData()));
            $values = ":" . implode(", :", array_keys($this->returnData()));

            $sql = Config::db()->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");
            $sql->execute($this->filter(array_merge($this->returnData())));

            return true;
        } catch (PDOException $exception) {
            $this->error("Prezado usuário ocorreu uma ação não prevista, informe ao administrador do sistema, error: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * updates data from a database table.  
     */
    public function update(String $primaryKey): bool
    {
        if(!$this->required()){
             $this->error("Preencha os campos obrigatorios!");
             return false;
        }  
        if ($this->timestamps) {
            $this->updated_at = (new DateTime("now"))->format("Y-m-d H:i:s");
        }
        try {
            $keyValues = [];
            foreach ($this->returnData() as $key => $value) {
                $keyValues[] = "{$key} = :{$key}";
            }
            $keyValues = implode(", ", $keyValues);

            $sql = Config::db()->prepare("UPDATE {$this->table} SET {$keyValues} WHERE {$this->primary} = :{$this->primary}");
            $sql->bindValue(":{$this->primary}", $primaryKey);
            $sql->execute($this->filter(array_merge($this->returnData())));
            return ($sql->rowCount() ?? 1);
            return true;

        } catch (PDOException $exception) {
            $this->error("Prezado usuário ocorreu uma ação não prevista, informe ao administrador do sistema, error: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * delete data from a database table. 
     */
    public function delete(string $primaryKey): bool
    {
        try {
        $sql = Config::db()->prepare("DELETE FROM {$this->table} WHERE {$this->primary} = :{$this->primary}");
            $sql->bindValue(":{$this->primary}", $primaryKey);
            $sql->execute();
            return true;

        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return false;
        }
    }

    //check if mandatory fields are filled.
    protected function required(): bool
    {
        $data = (array)$this->data;
        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * filters the data that will be sent to the database. 
     */
    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

    
    protected function returnData(): ?array
    {
        $returnData = (array)$this->data;
        unset($returnData['validation']);
        unset($returnData['rules']);
        unset($returnData['rulesToEdit']);
        return $returnData;
    }

}
