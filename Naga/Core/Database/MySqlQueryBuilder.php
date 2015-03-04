<?php

namespace Naga\Core\Database;

use Naga\Core\Database\Connection\MySQL\MySqlConnection;
use Naga\Core\Database\iQueryBuilder;

class MySqlQueryBuilder extends MySqlConnection implements iQueryBuilder
{
	protected $_query;

	public function createDatabase($database) { }
	public function dropDatabase($database) { }
	public function alterDatabase($database) { }

	/**
	 * Creates a table.
	 *
	 * @param string $table
	 * @param array $settings
	 * @param array $columns
	 * @return $this
	 */
	public function createTable($table, array $settings, array $columns)
	{
		$this->_query['createTable'] = (object)array(
			'table' => $table,
			'settings' => $settings,
			'columns' => $columns
		);

		return $this;
	}

	public function dropTable($table) { }
	public function truncateTable($table) { }
	public function alterTable($table) { }
	public function renameTable($table) { }

	/**
	 * Selects a table.
	 *
	 * @param string $table
	 * @return $this
	 */
	public function table($table)
	{
		$this->_query['table'] = $table;

		return $this;
	}

	/**
	 * Select statement. Arguments are the columns you want retrieve.
	 * Example:
	 * select('column1', 'column2', ...)
	 *
	 * @return $this
	 */
	public function select()
	{
		$this->_query['select'] = func_get_args();

		return $this;
	}

	/**
	 * Update statement.
	 * Example:
	 * update(array('column1' => 'some value', ...))
	 *
	 * @param array $data
	 * @return $this
	 */
	public function update(array $data)
	{
		$this->_query['update'] = $data;

		return $this;
	}

	/**
	 * Delete statement.
	 *
	 * @return $this
	 */
	public function delete()
	{
		$this->_query['delete'] = '';

		return $this;
	}

	/**
	 * Insert statement.
	 * Example:
	 * insert(array('column1' => 'some value'))
	 *
	 * @param array $columns
	 * @return $this
	 */
	public function insert(array $columns)
	{
		$this->_query['insert'] = $columns;

		return $this;
	}

	public function innerJoin($target) { }
	public function leftJoin($target) { }
	public function rightJoin($target) { }
	public function join($target) { }

	/**
	 * Adds a condition (and first operator second).
	 *
	 * @param mixed $first
	 * @param string $operator
	 * @param mixed $second
	 * @return $this
	 */
	public function condition($first, $operator, $second)
	{
		$this->_query['condition'] = (object)array(
			'first' => $first,
			'operator' => $operator,
			'second' => $second
		);

		return $this;
	}

	/**
	 * Adds an "or" condition (or first operator second).
	 *
	 * @param mixed $first
	 * @param string $operator
	 * @param mixed $second
	 * @return $this
	 */
	public function orCondition($first, $operator, $second)
	{
		$this->_query['orCondition'] = (object)array(
			'first' => $first,
			'operator' =>  $operator,
			'second' => $second
		);

		return $this;
	}

	/**
	 * Adds an equal condition (and first = second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function equals($first, $second)
	{
		return $this->condition($first, '=', $second);
	}

	/**
	 * Adds an "or" equal condition (or first = second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function orEquals($first, $second)
	{
		return $this->orCondition($first, '=', $second);
	}

	/**
	 * Adds a greater than condition (and first > second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function greaterThan($first, $second)
	{
		return $this->condition($first, '>', $second);
	}

	/**
	 * Adds an "or" greater than condition (or first > second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function orGreaterThan($first, $second)
	{
		return $this->orCondition($first, '>', $second);
	}

	/**
	 * Adds a smaller than condition (and first < second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function smallerThan($first, $second)
	{
		return $this->condition($first, '<', $second);
	}

	/**
	 * Adds an "or" smaller than condition (or first < second).
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @return $this
	 */
	public function orSmallerThan($first, $second)
	{
		return $this->orCondition($first, '<', $second);
	}

	public function between($what, $from, $to)
	{
		$this->_query['between'] = (object)array(
			'what' => $what,
			'from' => $from,
			'to' => $to
		);

		return $this;
	}

	public function orBetween($what, $from, $to)
	{
		$this->_query['between'] = (object)array(
			'what' => $what,
			'from' => $from,
			'to' => $to
		);

		return $this;
	}

	public function in($what, array $list)
	{
		$this->_query['in'] = (object)array(
			'what' => $what,
			'list' => $list
		);

		return $this;
	}

	public function orIn($what, array $list)
	{
		$this->_query['orIn'] = (object)array(
			'what' => $what,
			'list' => $list
		);

		return $this;
	}

	public function exists($callback)
	{
		$query = clone $this;
		$query->reset();

		if (is_callable($callback))
			$this->_query['exists'] = $callback($query);
		else
			$this->_query['exists'] = (string)$callback;

		return $this;
	}

	public function orExists($callback)
	{
		$query = clone $this;
		$query->reset();

		if (is_callable($callback))
			$this->_query['exists'] = $callback($query);
		else
			$this->_query['exists'] = (string)$callback;

		return $this;
	}

	public function groupStart($name){ }
	public function groupEnd($name) { }

	/**
	 * Resets the instance.
	 *
	 * @return $this|iQueryBuilder
	 */
	public function reset()
	{
		$this->_query = array();

		return $this;
	}

	public function execute($oneRow = false)
	{
		$params = array();
		foreach ($this->_query as $operation => $data)
		{
			if (in_array($operation, array('condition', 'orCondition')))
			{
				$id = md5($data->second);
				$params[":{$id}"] = $data->second;
			}
			else if ($operation == 'insert')
			{
				foreach ($data as $colName => $val)
					$params[":{$colName}"] = $val;
			}
			else if ($operation == 'update')
			{
				foreach ($data as $colName => $val)
				{
					$id = md5($colName);
					$params[":{$id}"] = $val;
				}
			}
		}

		return !$oneRow ? $this->query($this->generate(), $params) : $this->queryOne($this->generate(), $params);
	}

	public function generate()
	{
		$generated = '';
		$table = '';

		$previousOperation = '';
		foreach ($this->_query as $operation => &$data)
		{
			switch ($operation)
			{
				case 'table':
					$table = "`{$data}`";
					break;

				case 'select':
					$columns = is_array($data) && count($data) ? implode(', ', $data) : '*';
					$generated = "select {$columns}\nfrom {$table}\n";
					break;
				case 'delete':
					$generated = "delete from {$table}\n";
					break;
				case 'insert':
					$generated = $this->generateInsert($table, $data);
					break;
				case 'update':
					$generated = $this->generateUpdate($table, $data);
					break;

				case 'condition':
					$id = md5($data->second);
					if (in_array($previousOperation, array('select', 'delete', 'update')))
						$generated .= "where (\n`{$data->first}` {$data->operator} :{$id}\n";
					else
						$generated .= "and `{$data->first}` {$data->operator} :{$id}\n";
					break;
				case 'orCondition':
					$id = md5($data->second);
					if (in_array($previousOperation, 'select', 'delete', 'update'))
						$generated .= "where (\n`{$data->first}` {$data->operator} :{$id}\n";
					else
						$generated .= "or `{$data->first}` {$data->operator} :{$id}\n";
					break;
				case 'exists':
					$data = $data instanceof iQueryBuilder ? $data->generate() : $data;
					if ($previousOperation == 'select')
						$generated .= "where (\nexists(\n{$data}\n)\n";
					else
						$generated .= "and exists(\n{$data}\n)\n";
					break;
				case 'createTable':
					$generated = $this->generateCreateTable($data->table, $data->settings, $data->columns);
					break;
				default:
					continue;
					break;
			}

			$previousOperation = $operation;
		}

		return $generated . (in_array($previousOperation, array('condition', 'orCondition', 'exists', 'orExists')) ? ')' : '');
	}

	protected function generateCreateTable($table, $settings, $columns)
	{
		$engine = isset($settings['engine']) ? $settings['engine'] : 'InnoDB';
		$generated = "create table `{$table}` (\n";
		$current = 0;
		foreach ($columns as $name => $data)
		{
			$first = !$current;
			$type = isset($data->primary) && $data->primary ? 'bigint' : $data->type;
			$length = isset($data->length) && $data->length ? "({$data->length}) " : ' ';
			$primary = isset($data->primary) && $data->primary ? ", primary key(`{$name}`) " : ' ';
			$notNull = isset($data->null) && !$data->null ? ' not null ' : '';
			$unique = isset($data->unique) && $data->unique ? ", unique(`{$name}`) " : '';
			$index = !$unique && isset($data->index) && $data->index ? ", index using {$data->index}(`{$name}`) " : ' ';
			$autoIncrement = isset($data->autoIncrement) && $data->autoIncrement ? ' auto_increment ' : ' ';
			$unsigned = isset($data->unsigned) && $data->unsigned ? ' unsigned ' : ' ';
			$generated .= (!$first ? ', ' : '') . "`{$name}` {$type}{$length}{$unsigned}{$notNull}{$autoIncrement}{$unique}{$index}{$primary}";
			++$current;
		}

		$generated .= "\n) ENGINE = {$engine}";

		return $generated;
	}

	protected function generateInsert($table, array $columns)
	{
		$columnNames = array_keys($columns);
		foreach ($columnNames as &$col)
			$col = "`{$col}`";

		$columnNames = implode(', ', $columnNames);

		$data = array_keys($columns);
		foreach ($data as &$col)
			$col = ":{$col}";

		$data = implode(', ', $data);

		return "insert into {$table} ({$columnNames}) values ({$data})";
	}

	protected function generateUpdate($table, array $columns)
	{
		$values = array();
		foreach ($columns as $name => $data)
		{
			$id = md5($name);
			$values[] = "`{$name}` = :{$id}";
		}

		$values = implode(', ', $values);

		return "update {$table} set {$values}\n";
	}
}