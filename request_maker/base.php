<?php

namespace ORM\request_maker;

/** 
 * Class to make mySQL queries
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2026 Jad Haddouch
 */

interface base {
    /*
	 * create
	 */
	public function create (string $table_name): string;
	
	
	
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
	): string;
	
	
	
	/*
	 * update
	 */
	public function update (
		string $table_name,
		array $data,
		array $table_fields
	): string;
	
	
	
	/*
	 * delete
	 */
	public function delete (
		string $table_name,
		array $data
	): string;
	
	
	
	/*
	 * _delete
	 */
	public function _delete (
		string $table_name,
		string $condition
	): string;
}
