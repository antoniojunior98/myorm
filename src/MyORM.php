<?php

namespace devmazon\myorm;

use Exception;
use PDOException;
use PDO;
use stdClass;

abstract class MyORM
{
    use CrudTrait;
    
    /** @var string $table database table */
    private $table;
    
    /** @var string $primary table primary key field */
    private $primary;

    /** @var array $required table required fields */
    private $required;

    /** @var string $data_base database */
    protected $data_base;

    /** @var bool $timestamps control created and updated at */
    protected $timestamps;

    /** @var string */
    protected $columns;

    /** @var string */
    protected $where;

    /** @var string */
    protected $params;

    /** @var string */
    protected $group;

    /** @var string */
    protected $order;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var string */
    protected $statement;

    /** @var object|null */
    protected $data;

    /** @var \PDOException|null */
    protected $error;


    /**
     * MyORM construct.
     * @param string $table
     * @param array $required
     * @param string $primary
     * @param bool $timestamps
     */
    public function __construct(string $table, array $required, string $primary = "id", bool $timestamps = true)
    {
        $this->table = $table;
        $this->timestamps = $timestamps;
        $this->primary = $primary;
        $this->required = $required;
    }

    /**
     * @param $name
     * @param $value
     */
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

    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @return PDOException|Exception|null|false
     */
    public function error($error)
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function checkError(): bool
    {
        if (count($this->error) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function fail()
    {
        return $this->error;
    }

    /**
     * @param string|null $data_base
     * @return MyORM|null 
     */
    public function data_base(string $data_base = null)
    {
        $this->data_base = $data_base;
        return $this;
    }

    /**
     * @param string $column
     * @return MyORM|null
     */
    public function group(string $column): ?MyORM
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return MyORM|null
     */
    public function order(string $columnOrder): ?MyORM
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param int $limit
     * @return MyORM|null
     */
    public function limit(int $limit): ?MyORM
    {

        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param int $offset
     * @return MyORM|null
     */
    public function offset(int $offset): ?MyORM
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    public function where(string $where, string $params = null): ?MyORM
    {
        $this->where = " WHERE {$where} = :{$where}";
        $params = ":{$where}={$params}";
        parse_str($params, $this->params);
        return $this;
    }

    /**
     * @param string|null $where
     * @param string $column
     * @return MyORM
     */
    public function find(string $columns = "*"): ?MyORM
    {
        $this->statement = "SELECT {$columns} FROM {$this->table}";
        return $this;
    }

    /**
     * @param string $id
     * @param string $columns
     * @return MyORM|null
     */
    public function findOne(string $id, string $columns = "*"): ?MyORM
    {
        $find = $this->find($columns)->where($this->primary, $id);
        return $find->fetch('object');
    }

    /**
     * @return array|null
     * fetch function, performs a query to the database and returns only one row of the table.
     */
    public function fetch(string $fetch = null)
    {
        try {

            $sql = $this->statement . $this->where . $this->group . $this->order . $this->limit . $this->offset;
            $sql = is_null($this->data_base) ? Config::db()->prepare($sql) : Config::db_another($this->data_base)->prepare($sql);
            $sql->execute($this->params);

            if (!$sql->rowCount()) {
                return null;
            }

            switch ($fetch) {
                case 'one':
                    $sql = $sql->fetch(\PDO::FETCH_ASSOC);
                    break;
                case 'object':
                    $sql = $sql->fetchObject(static::class);
                    break;
                case 'all':
                    $sql = $sql->fetchAll(\PDO::FETCH_ASSOC);
                    break;
                default:
                    $sql = $sql->fetchAll(PDO::FETCH_CLASS, static::class);
                    break;
            }
            return $sql;
        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return null;
        }
    }

    /**
     * @return int
     * count function, counts the data that comes from the database and returns the entire value.  
     */
    public function count(): int
    {
        $sql = $this->statement . $this->where;
        $sql = is_null($this->data_base) ? Config::db()->prepare($sql) : Config::db_another($this->data_base)->prepare($sql);
        $sql->execute($this->params);
        return $sql->rowCount();
    }

    /**
     * @return MyORM|null
     */
    public function get(string $fetch = null)
    {
        return $this->find()->fetch($fetch);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $id = $this->primary;

        if (!empty($this->data->$id)) {
            return $this->update();
        }
        if (empty($this->data->$id)) {
            return $this->create();
        }
        return false;
    }

    /**
     * @return bool
     * check if mandatory fields are filled.
     */
    protected function required(): bool
    {
        $data = (array) $this->data;
        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    protected function returnData(): ?array
    {
        $returnData = (array) $this->data;
        return $returnData;
    }
}
