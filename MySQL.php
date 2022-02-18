<?php
/** 
 * Class to make mySQL queries
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2013 Jad Haddouch
 */

class MySQL {
	
	/*
	 * create
	 */
	public function create ($table_name) {
		return 'INSERT INTO ' . $table_name . ' (id) VALUES (NULL);';
	}
	
	
	
	/*
	 * read
	 */
	public function read (
		$fields, 
		$table_name, 
		$joins = array(), 
		$conditions = array(), $conditions_glue = ') AND (', 
		$sorters = array(array('id', 'ASC')), 
		$limit = array(0, 0)
	) {
		$LEFT_JOINS = array();
		if (isset($joins['LEFT_JOIN'])) {
			foreach ($joins['LEFT_JOIN'] as $LEFT_JOIN) {
				$LEFT_JOINS[] = 'LEFT JOIN ' . $LEFT_JOIN['table_name'] . ' ON (' . implode(isset($LEFT_JOIN['conditions_glue']) ? $LEFT_JOIN['conditions_glue'] : ') AND (', $LEFT_JOIN['conditions']) . ')';
			}
		}
		
		$order_by = array();
		foreach ($sorters as $sorter) {
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
	public function update ($table_name, $data, $table_fields) {
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
	public function delete ($table_name, $data) {
		return 'DELETE FROM ' . $table_name . ' WHERE ' . $table_name . '.id = ' . intval($data['id']) . ';';
	}
	
	
	
	/*
	 * _delete
	 */
	public function _delete ($table_name, $condition) {
		return 'DELETE FROM ' . $table_name . ' WHERE ' . $condition . ';';
	}
	
	
	
	/*
	 * secureData
	 */
	public function secureData ($data) {
		$search =  array("'", );
		$replace = array("\'",);
		return str_replace($search, $replace, $data); 
	}
}
?>
