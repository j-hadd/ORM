<?php

namespace ORM\request_maker;

include_once 'request_maker/base.php';

/** 
 * Class to make mySQL queries
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2013 Jad Haddouch
 */

class MySQL implements \ORM\request_maker\base {
	
	/*
	 * create
	 */
	public function create (string $table_name): string
	{
		return 'INSERT INTO ' . $table_name . ' (id) VALUES (NULL);';
	}
	
	
	
	/*
	 * read
	 */
	public function read (
		array $fields,
		string $table_name,
		array $joins = array(),
		array $conditions = array(), string $conditions_glue = ') AND (',
		array $sorters = array(array('id', 'ASC')),
		array $limit = array(0, 0)
	): string
	{
		$LEFT_JOINS = array();
		if (isset($joins['LEFT_JOIN']))
		{
			foreach ($joins['LEFT_JOIN'] as $LEFT_JOIN)
			{
				$LEFT_JOINS[] =
					'LEFT JOIN ' . $LEFT_JOIN['table_name'] .
					' ON (' . implode(isset($LEFT_JOIN['conditions_glue']) ? $LEFT_JOIN['conditions_glue'] : ') AND (', $LEFT_JOIN['conditions']) . ')';
			}
		}
		
		$order_by = array();
		foreach ($sorters as $sorter) 
		{
			$order_by[] = implode(' ', $sorter);
		}
		
		return '' .
			'SELECT ' . implode(', ', $fields) . ' ' .
			'FROM ' . $table_name .
			(sizeof($LEFT_JOINS) > 0 ? ' ' . implode(' ', $LEFT_JOINS) : '') .
			(sizeof($conditions) > 0 ? ' WHERE (' . implode($conditions_glue, $conditions) . ')' : '') .
			(sizeof($order_by) > 0 ? ' ORDER BY ' . implode(', ', $order_by) : '') .
			($limit[1] > 0 ? ' LIMIT ' . intval($limit[0]) . ',' . intval($limit[1]) : '') .
			';';
	}
	
	
	
	/*
	 * update
	 */
	public function update (
		string $table_name,
		array $data,
		array $table_fields
	): string
	{
		$set = array();
		
		//echo 'MySQL->update : $table_fields = '; var_dump($table_fields); echo "\n";
		//echo 'MySQL->update : $data = '; var_dump($data); echo "\n";
		
		foreach ($data as $field => $value) {
			if ($field !== 'id' && 
				in_array($field, $table_fields)
			) { 
				$set[] = $field . ' = \'' . $this->secureData($value) . '\''; 
			}
		}
		
		if (sizeof($set) < 1) { return false; } 
		
		$set = implode(', ', $set);
			
		return '' . 
			'UPDATE ' . $table_name . ' ' .
			'SET ' . $set . ' ' . 
			'WHERE ' . $table_name . '.id = ' . intval($data['id']) .
			';';
	}
	
	
	
	/*
	 * delete
	 */
	public function delete (
		string $table_name,
		array $data
	): string
	{
		return 'DELETE FROM ' . $table_name . ' WHERE ' . $table_name . '.id = ' . intval($data['id']) . ';';
	}
	
	
	
	/*
	 * _delete
	 */
	public function _delete (
		string $table_name,
		string $condition
	): string
	{
		return 'DELETE FROM ' . $table_name . ' WHERE ' . $condition . ';';
	}
	
	
	
	/*
	 * secureData
	 */
	public function secureData (string $data): string
	{
		$search =  array("'", );
		$replace = array("\'",);
		return str_replace($search, $replace, $data);
	}
}
