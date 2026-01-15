<?php

namespace ORM\db_connector;

/** 
 * basic data base conector class
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2026 Jad Haddouch
 */

interface base {
    /**
     * TODO: how to remove the \PDOStatement reference here ?
     * 
	 * Send the query to the data base
	 * If an error occurred a message is rise
	 *
	 * @param string $sql The sql query
	 * @return array Return the query result
	 */
	public function executeQuery (
		string|false $sql,
		bool $SELECT = false
	): \PDOStatement|int|null;



    /**
	 * Return an associative array
	 *
	 * @param string $sql The sql query
	 * @return array Return the query result
	 */
	public function fetchAssoc(string $sql): array;
	
	
	
	/**
	 * Return the last inserted id
	 *
	 * @return int The last inserted id
	 */
	public function getLastInsertedId (): int;
}
