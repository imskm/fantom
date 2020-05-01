<?php

namespace Fantom\Database;

use Fantom\Base;

/**
* Query Class
* Handles building of query
*/
class Query extends Base
{
	protected $fields;
	// protected $where;
	protected $bindKeysValues = array();
	protected $join;
	protected $like;
	protected $limit;
	protected $offset;
	protected $sql;

	/**
	 * Select query builder
	 * @param array
	 * @return string
	 */
	public function buildSelect($columns = array(), $params = array())
	{
		$template = "SELECT %s FROM %s";

		$this->fillFields($columns);

		$this->sql = sprintf($template, $this->getFields(), $this->getTableName());

		return $this;
	}

	/**
	 * Select query builder
	 * @param void
	 * @return string
	 */
	public function buildInsert(&$columns)
	{
		$template = "INSERT INTO %s (%s) VALUES (%s)";

		$column_name_string = "";
		$bind_params_string = "";
		$count = count($columns);
		$i = 0;
		foreach ($columns as $column) {

			$column_name_string .= "{$column}";
			$bind_params_string .= ":{$column}";

			if ($i < $count - 1) {
				$column_name_string .= ", ";
				$bind_params_string .= ", ";
			}

			$i++;
		}
		$this->sql = sprintf($template, $this->getTableName(), $column_name_string, $bind_params_string);

		return $this;

	}

	/**
	 * Select query builder
	 * @param void
	 * @return string
	 */
	public function buildUpdate($columns, $where_column , $operator, $value)
	{
		$template = "UPDATE %s SET %s";

		if (! $this->isOperatorAllowed($operator)) {
			throw new \Exception("Invalid operator $operator");
		}

		$set = "";
		$count = count($columns);
		$i = 0;

		foreach ($columns as $column) {
			$set .= "{$column} = :{$column}";
			if ($i < $count - 1) {
				$set .= ", ";
			}
			$i++;
		}

		$this->sql = sprintf($template, $this->getTableName(), $set);
		$this->setWhere($where_column, $operator, $value);

		return $this;
	}

	/**
	 * Select query builder
	 * @param void
	 * @return string
	 */
	public function buildDelete($where_column, $operator, $value)
	{
		$template = "DELETE FROM %s";

		if (! $this->isOperatorAllowed($operator)) {
			throw new \Exception("Invalid operator $operator");
		}
		
		$this->sql = sprintf($template, $this->getTableName());
		$this->setWhere($where_column, $operator, $value);

		return $this;
	}

	public function getFields()
	{
		if (! $this->fields) {
			$this->fields = "*";
		}

		return $this->fields;
	}

	public function buildParams(array $params)
	{
		$where = "";
		foreach ($params as $key => $value) {
			$where .= " $key = :$key";
		}

		return "WHERE $where";
	}

	public function getTableName()
	{
		if (! isset($this->table)) {
			if (! property_exists($this, "table")) {
				$class = get_class($this);
			}
			$this->model = $this->extractTableName($class);
		} else {
			$this->model = $this->table;
		}

		return $this->model;
	}

	protected function extractTableName($class_namespace)
	{
		$parts = explode("\\", $class_namespace);

		// Basic dummy pluralizing and returning table name.
		return strtolower($parts[count($parts) - 1]) . "s";
	}

	public function getPrimaryKey()
	{
		if (isset($this->primary)) {
			$this->primary_key = $this->primary;
		}

		return $this->primary_key;
	}

	protected function fillFields($columns)
	{
		if (is_array($columns)) {
			$this->fields = implode(", ", $columns);
		} elseif (is_string($columns)) {
			$this->fields = $columns;
		} else {
			$this->fields = "*";
		}

		return $this;
	}

	public function setWhere($column, $operator, $value)
	{
		// Setting the property dynamically
		// Handled by Base class
		// doing this so that query() method can bind the value
		$this->{$column} = $value;
		$template = " WHERE %s %s :%s";
		$this->sql .= $template;
		$this->sql = sprintf($this->sql, $column, $operator, $column);

		return $this;
	}

	public function setAndWhere($column, $operator, $value)
	{
		// Setting the property dynamically
		// Handled by Base class
		// doing this so that query() method can bind the value
		$this->{$column} = $value;
		$template = " AND %s %s :%s";
		$this->sql .= $template;
		$this->sql = sprintf($this->sql, $column, $operator, $column);

		return $this;
	}

	public function setOrWhere($column, $operator, $value)
	{
		// Setting the property dynamically
		// Handled by Base class
		// doing this so that query() method can bind the value
		$this->{$column} = $value;
		$template = " OR %s %s :%s";
		$this->sql .= $template;
		$this->sql = sprintf($this->sql, $column, $operator, $column);

		return $this;
	}

	protected function isOperatorAllowed($operator)
	{
		$operators_allowed = array('=', '>', '<', '>=', '<=', '!=', '<>');
		if (! in_array($operator, $operators_allowed)) {
			return false;
		}

		return true;
	}

	public function setRawSql($sql, array $params = array())
	{
		if ($params) {
			foreach ($params as $column => $value) {
				$this->{$column} = $value;
			}
		}

		$this->sql = $sql;

		return $this;
	}

	public function getSql()
	{
		return $this->sql;
	}


}