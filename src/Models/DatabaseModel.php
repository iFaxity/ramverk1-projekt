<?php

namespace Faxity\Models;

use Anax\DatabaseActiveRecord\ActiveRecordModel;
use Anax\DatabaseQueryBuilder\DatabaseQueryBuilder;

/**
 * A database driven model using the Active Record design pattern.
 */
class DatabaseModel extends ActiveRecordModel
{
    /** @var array $propTypes Casting types to used when setting properties */
    protected static $propTypes = [];


    protected function getProperties()
    {
        // Prevent null variables from being in inserts and updates.
        // As we can use inferred defaults from
        return array_filter(parent::getProperties(), function ($value) {
            return !is_null($value);
        });
    }


    /**
     * Get property from class instance
     * @param string $prop Protected property to get value from
     *
     * @return mixed
     */
    public function __get(string $prop)
    {
        return $this->$prop;
    }


    /**
     * Set property on class instance, type casts value if set in $propTypes
     * This is a way to get around SQLite only fetching as strings
     * @param string $prop Protected property name to set
     * @param mixed $value New value of the property
     *
     * @return mixed
     */
    public function __set(string $prop, $value)
    {
        $propTypes = static::$propTypes;

        /*if (!array_key_exists($prop, $propTypes)) {
            trigger_error("Property $prop doesn't exists and cannot be set.", E_USER_ERROR);
        }*/

        $type = $propTypes[$prop] ?? "string";
        $valueType = gettype($value);
        if ($valueType != 'NULL' && $type != $valueType) {
            settype($value, $type);
        }

        $this->$prop = $value;
    }


    public function __construct(DatabaseQueryBuilder $db = null)
    {
        if (!is_null($db)) {
            $this->setDb($db);
        }
    }


    public function findAll(): array
    {
        return $this->mapModels(parent::findAll());
    }


    public function findAllWhere($where, $value): array
    {
        return $this->mapModels(parent::findAllWhere($where, $value));
    }


    /**
     * Gets all rows by a certain order
     */
    public function findAllTop(string $orderBy, ?int $limit = null): array
    {
        $this->checkDb();
        $this->db->connect();
        
        $query = $this->db->select()
            ->from($this->tableName)
            ->orderBy($orderBy);

        if (is_int($limit)) {
            $query->limit($limit);
        }

        $res = $query
            ->execute()
            ->fetchAllClass(get_class($this));
        return $this->mapModels($res);
    }


    /**
     * Maps database connection to each model
     * @param array $models
     *
     * @return array
     */
    protected function mapModels(array $models): array
    {
        $this->checkDb();
        return array_map(function ($model) {
            $model->setDb($this->db);
            return $model;
        }, $models);
    }
}
