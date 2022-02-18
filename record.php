<?php

class record extends CRUD {
	var $table_name = 'record';
	
	var $table_fields = array('id', 'txt');
	var $add_user_log = false;
	
	var $read_fields_strict = true;
	var $read_fields = array(
		'record.id AS id', 'record.txt AS txt',
		'record_join.txt AS join_txt'
	);
	
	var $joins = array(
		'LEFT_JOIN' => array(
			array(
				'table_name' => 'record_join',
				'conditions' => array('record.id = record_join.record_id')
			),
		),
	);
	
	var $hasMany = array(
		'sub_records' => array(
			'foreign_key' => 'id',
			'local_key' => 'record_id',
			
			'table_name' => 'sub_record',
			
			'table_fields' => array('id', 'record_id', 'txt'),
			'add_user_log' => false,
			
			'hasMany' => array(
				'sub_sub_records' => array(
					'foreign_key' => 'id',
					'local_key' => 'sub_record_id',
					
					'table_name' => 'sub_sub_record',
					
					'table_fields' => array('id', 'sub_record_id', 'txt'),
					'add_user_log' => false,
					
					'read_fields_strict' => true,
					'read_fields' => array(
						'sub_sub_record.id', 'sub_sub_record.sub_record_id', 'sub_sub_record.txt',
						'sub_sub_record_join.txt AS join_txt',
					),
					
					'joins' => array(
						'LEFT_JOIN' => array(
							array(
								'table_name' => 'sub_sub_record_join',
								'conditions' => array('sub_sub_record.id = sub_sub_record_join.sub_sub_record_id')
							),
						),
					),
				),
			),
		),
	);
}

?>