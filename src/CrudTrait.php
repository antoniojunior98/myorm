<?php
namespace devmazon\myorm;

use PDOException;
use DateTime;


trait CrudTrait
{
/**
     * @return bool
     * creates new items in any database table.  
     */
    public function create(): bool
    {        
        if ($this->timestamps) {
            $this->created_at = (new DateTime("now"))->format("Y-m-d H:i:s");
        }
        try {
            if(!$this->required()){
                throw new PDOException("Preencha os campos obrigatorios!");
            }
            $primary = $this->primary;
            $this->$primary = md5(uniqid(rand(), true));
        
            $columns = implode(", ", array_keys($this->returnData()));
            $values = ":" . implode(", :", array_keys($this->returnData()));

            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
            $sql = is_null($this->data_base)? Config::db()->prepare($sql): Config::db_another($this->data_base)->prepare($sql);
            $sql->execute($this->filter(array_merge($this->returnData())));
            return true;
            
        } catch (PDOException $exception) {
            $this->error("Prezado usuário ocorreu uma ação não prevista, informe ao administrador do sistema, error: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * @param string $primaryKey
     * @return bool
     * updates data from a database table.  
     */
    public function update(): bool
    {

        if ($this->timestamps) {
            $this->updated_at = (new DateTime("now"))->format("Y-m-d H:i:s");
        }
        try {
            if(!$this->required()){
                throw new PDOException("Preencha os campos obrigatorios!");
            }

            $keyValues = [];
            foreach ($this->returnData() as $key => $value) {
                $keyValues[] = "{$key} = :{$key}";
            }
            $keyValues = implode(", ", $keyValues);

            $id = $this->returnData()[$this->primary];

            $sql = "UPDATE {$this->table} SET {$keyValues} WHERE {$this->primary} = :{$this->primary}";
            $sql = is_null($this->data_base)? Config::db()->prepare($sql): Config::db_another($this->data_base)->prepare($sql);
            $sql->bindValue(":{$this->primary}", $id);
            $sql->execute($this->filter(array_merge($this->returnData())));
            return true;

        } catch (PDOException $exception) {
            $this->error("Prezado usuário ocorreu uma ação não prevista, informe ao administrador do sistema, error: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * @param string $primaryKey
     * @return bool
     * delete data from a database table. 
     */
    public function delete(): bool
    {
        try {
            $id = $this->returnData()[$this->primary];
            $sql = "DELETE FROM {$this->table} WHERE {$this->primary} = :{$this->primary}";
            $sql = is_null($this->data_base)? Config::db()->prepare($sql): Config::db_another($this->data_base)->prepare($sql);
            $sql->bindValue(":{$this->primary}", "{$id}");
            $sql->execute();
            return true;

        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return false;
        }
    }

    /**
     * @param array $data
     * @return array|null
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
}