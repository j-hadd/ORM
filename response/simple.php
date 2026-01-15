<?php

namespace ORM\response;

include_once 'response/base.php';

/** 
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2013 Jad Haddouch
 */

class simple implements \ORM\response\base {
	
	/*
	 * 
	 */
	public function customOutput (mixed $json): void
	{
		echo('<pre>' . print_r($json, true) . '</pre>');
	}
	
	
	
	/*
	 * 
	 */
	public function normalize (array $params = array()): void
	{
		global $additional_output;

		$data_key = isset($params['data_key']) ? $params['data_key'] : 'data';
		$data = isset($params['data']) ? $params['data'] : array();

		$query_start_time = isset($params['query_start_time']) ? $params['query_start_time'] : 0;
		$query_end_time = isset($params['query_end_time']) ? ($params['query_end_time'] < 0 ? microtime(true) : $params['query_end_time']) : 0;

		//var_dump($additional_output);

		$json = array_merge(array(
			'success' => isset($params['success']) ? $params['success'] : false,
			$data_key => $data, 
			'total_records' => isset($params['total_records']) ? $params['total_records'] : sizeof($data),
			'total_' . $data_key => isset($params['total_' . $data_key]) ? $params['total_' . $data_key] : sizeof($data),
			'msg' => isset($params['msg']) ? $params['msg'] : '',
			'code_error' => isset($params['code_error']) ? $params['code_error'] : 'no-error',
			'query_start_time' => $query_start_time,
			'query_end_time' => $query_end_time,
			'query_time' => $query_end_time - $query_start_time,
			'env' => isset($params['env']) ? $params['env'] : '',
		), isset($additional_output) ? $additional_output : array());
		
		echo('<pre>' . print_r($json, true) . '</pre>');
	}
	
	
	
	/*
	 * Die the right syntax fo json response
	 *
	 * @param $msg The bdd message
	 * @return string
	 */
	public function dbError (string $msg): void
	{
		$this->normalize(array('msg' => $msg));
	}
	
	
	
	/*
	 * 
	 */
	public function apiError (array $params): void
	{
		$this->normalize($params);
	}
}
