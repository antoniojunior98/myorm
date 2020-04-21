<?php
namespace DevMazon\myORM;

use Exception;
use PDOException;
use DateTime;
use \Core\Model;
use stdClass;

class MyORM extends Model
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
        parent:: __construct();
        $this->table = $table;
        $this->timestamps = $timestamps;
        $this->primary = $primary;
        $this->required = $required;
        $this->validation = New Validation();
    }

    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    /**
     * @param $name
     * @return string|null
     */
    public function __get($name)
    {
        return ($this->data->$name ?? null);
    }

    //função erro, retorna todos os erros.
    public function error($error)
    {
        $this->error[] = $error;
    }

    //função "checkError" verifica se existe erros.
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

    /**
     * função table, define a tabela que será utilizada do banco de dados.
     */
    public function table(string $table)
    {

        $this->table = $table;
        return $this;
    }

    /**
     * função find, monta as query de cosuntas.
     */
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
     * função fetch realiza as consutas ao banco de dados de acordo com as funções definidas acima e retorna uma unica linha da consulta,.
     */
    public function fetch()
    {
        try {

            $sql = Config::db()->prepare($this->statement . $this->order . $this->limit);
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
     * função fetchAll realiza as consutas ao banco de dados de acordo com as funções definidas acima e retorna um array com todas as linhas da consulta.
     */
    public function fetchAll()
    {
        try {

            $sql = Config::db()->prepare( $this->statement . $this->order . $this->limit);
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
     * função count, conta os dados que vem do banco é retorna valor inteiro.  
     */
    public function count(): int
    {
        $sql = Config::db()->prepare($this->statement);
        $sql->execute();
        return $sql->rowCount();
    }

    /**
     * função create, cria novos itens em qualquer tabela do banco de dados, 
     * necessita que o programador defina a tabela na função "table", colunas e valores na função "data".  
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
     * função update, atualiza item em qualquer tabela do banco de dados, 
     * necessita que o programador defina a tabela na função "table", colunas e valores na função "data".  
     */
    public function update(String $where): bool
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

            $sql = Config::db()->prepare("UPDATE {$this->table} SET {$keyValues} WHERE {$where}");
            $sql->execute($this->filter(array_merge($this->returnData())));
            return ($sql->rowCount() ?? 1);
            return true;

        } catch (PDOException $exception) {
            $this->error("Prezado usuário ocorreu uma ação não prevista, informe ao administrador do sistema, error: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * função delete, exclui item em qualquer tabela do banco de dados, 
     * necessita que o programador defina o id. a função retornará um boleano.  
     */
    public function delete(string $id): bool
    {
        try {
            $sql = Config::db()->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $sql->bindValue(":id", $id);
            $sql->execute();
            return true;

        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return false;
        }
    }

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
     * função filter, filtra os valores antes de salvar no banco. 
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
