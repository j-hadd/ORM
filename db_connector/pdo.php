<?php

namespace ORM\db_connector;

include_once 'db_connector\base.php';
include_once 'response\base.php';

/** 
 * mysql data base conector class
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2013 Jad Haddouch
 */

class _pdo implements \ORM\db_connector\base {
	
	/**
	 * Tn connection handler
	 */
	private \PDO|false $con = false;
	
	
	
	/**
	 * response Object
	 * this object normalize the service response
	 */
	private \ORM\response\base|false $response = false;
	
	
	
	/**
	 * A SELECT query result
	 */
	private \PDOStatement|false $results = false;
	
	
	
	/**
	 * Constructor
	 *
	 * @param string $dsn The data base source name
	 * @param string $user The data base user
	 * @param string $password The data base password
	 * @param object $response The respons object
	 */
	public function __construct (
		string $dsn,
		string $user,
		string $password,
		\ORM\response\base $response
	)
	{
		try
		{
			$this->con = new \PDO($dsn, $user, $password);
			$this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			$this->response = $response === false ? new json_response() : $response;
		}
		catch (\PDOException $e)
		{
			die('DB ERROR connection error ' . $e->getMessage());
			//$this->error('connection error ' . $e->getMessage());
		}
	}
	
	
	
	/**
	 * Destructor 
	 */
	public function __destruct ()
	{
        if ($this->results !== false)
		{
			$this->results->closeCursor();
		}
		$this->con = false;
    }
	
	
	
	/**
	 * Send the query to the data base
	 * If an error occurred a message is rise
	 *
	 * @param string $sql The sql query
	 * @return array Return the query result
	 */
	public function executeQuery (
		string|false $sql,
		bool $SELECT = false
	): \PDOStatement|int|null
	{
		if ($sql === false) { return null; }
		
		$this->debug($sql);
			
		$fn = $SELECT === true || strpos($sql, 'SELECT') === 0 ? 'query' : 'exec';
		
		try
		{
			return $this->con->$fn($sql);
		}
		catch (\PDOException $e)
		{
			$this->error('Execute query error : ' . $sql . ', Query error : ' . $e->getMessage());
		}

		return null;
	}
	
	
	
	/**
	 * Return an associative array
	 *
	 * @param string $sql The sql query
	 * @return array Return the query result
	 */
	public function fetchAssoc(string $sql): array
	{
		$data = array();
		
		$this->results = $this->executeQuery($sql, true);
		
		while ($result = $this->results->fetch(\PDO::FETCH_ASSOC)) {
			//$data[] = objectToArray($result);
			$data[] = $result;
		}
		
		return $data;
	}
	
	
	
	/**
	 * Return the last inserted id
	 *
	 * @return int The last inserted id
	 */
	public function getLastInsertedId (): int
	{
		return $this->con->lastInsertId();
	}
	
	
	
	/**
	 * Return a secured data
	 *
	 * @param mixed $data The data to secure
	 * @return string Return a secured data
	 */
	public function secureData (mixed $data): mixed
	{
		return $data;
	} 
	
	
	
	/**
	 * To have the right error message
	 *
	 * @param string $msg The message
	 */
	private function error (string $msg): void
	{
		$this->response->dbError($msg);
	}
	
	
	
	/**
	 * 
	 */
	private function debug (mixed $var): void
	{
		if (($_SERVER['REMOTE_ADDR'] == '95.143.48.98' || $_SERVER['REMOTE_ADDR'] == '86.200.244.108' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') && isset($_GET['debug'])) { 
			var_dump($var);
			echo "<br />\n";
		}
	}
}
