<?php

namespace devmazon\myorm;

use Exception;
use PDOException;
use DateTime;
use stdClass;

class MyORM
{
    use CrudTrait;

    private $table;
    private $primary;
    private $required;
    protected $data_base;
    protected $timestamps;
    protected $columns;
    protected $group;
    protected $order;
    protected $limit;
    protected $offset;
    protected $statement;
    protected $data;
    protected $error = [];


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

    /**
     * @return PDOException|Exception|null|false
     */
    public function error($error)
    {
        $this->error[] = $error;
    }

    /**
     * @return bool
     */
    public function checkError()
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
    public function messageError()
    {
        $msg = '';

        foreach ($this->error as $msgsError) {
            $msg .= $msgsError;
        }
        return $msg;
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
    public function group(string $column)
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return MyORM|null
     */
    public function order(string $columnOrder)
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param int $limit
     * @return MyORM|null
     */
    public function limit(int $limit)
    {

        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param int $offset
     * @return MyORM|null
     */
    public function offset(int $offset)
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    /**
     * @param string|null $where
     * @param string $column
     * @return MyORM
     */
    public function find(?String $where = null, string $columns = "*")
    {

        if ($where) {
            $this->statement = "SELECT {$columns} FROM {$this->table} WHERE {$where}";

            return $this;
        }

        $this->statement = "SELECT {$columns} FROM {$this->table}";

        return $this;
    }

    /**
     * @return array|null
     * fetch function, performs a query to the database and returns only one row of the table.
     */
    public function fetch()
    {
        try {

            $sql = $this->statement . $this->group . $this->order . $this->limit . $this->offset;
            $sql = is_null($this->data_base) ? Config::db()->prepare($sql) : Config::db_another($this->data_base)->prepare($sql);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return $sql->fetch(\PDO::FETCH_ASSOC);
            }
        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
            return null;
        }
    }

    /**
     * @return array|null
     * fetchAll function, performs a query to the database and returns several rows of the table.
     */
    public function fetchAll()
    {
        try {

            $sql = $this->statement . $this->group . $this->order . $this->limit . $this->offset;
            $sql = is_null($this->data_base) ? Config::db()->prepare($sql) : Config::db_another($this->data_base)->prepare($sql);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
            }
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
        $sql = is_null($this->data_base) ? Config::db()->prepare($this->statement) : Config::db_another($this->data_base)->prepare($this->statement);
        $sql->execute();
        return $sql->rowCount();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $primaryKey = $this->primary;            

            if (!empty($this->data->$primaryKey)) 
            {
                return $this->update($this->data->$primaryKey);
            }
            if(empty($this->data->$primaryKey)) {
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
