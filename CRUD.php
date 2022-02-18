<?php
/** 
 * Class to execute the CRUD on data base
 * 
 * @author Jad Haddouch <jad.haddouch@prezenz.com>
 * @docauthor Jad Haddouch <jad.haddouch@prezenz.com>
 * @copyright Copyright 2019 Prezenz
 */

class CRUD {
	
	/**
	 * Tn connection handler
	 */
	var $db_connector = false;
	var $request_maker = false;
	
	var $table_name = '';
	
	var $table_fields = array();
	var $add_user_log = true;
	
	var $read_fields_strict = false;
	var $read_fields = false;
	
	var $joins = array();
	
	var $read_conditions = array();
	var $read_conditions_glue = ') AND (';
	
	var $order_by_in_cfg = true;
	var $order_by = array(array('id', 'ASC'));
	
	var $limit_in_cfg = true;
	var $limit = array(0, 0);
	
	var $data = array();
	
	var $hasMany = false;
	
	var $record_name = '';
	
	var $all_records_size = 0;
	
	var $add_authorised_methodes = false;
	var $authorised_methodes = array('create', 'read', 'update', 'delete');
	
	
	
	/**
	 * Constructor
	 *
	 * @param string $db_connector The data base connector
	 * @param string $request_maker The data base requeste maker
	 * @param array $cfg
	 */
	public function __construct ($db_connector, $request_maker, &$cfg) {
		//var_dump($cfg);
		
		$this->db_connector = $db_connector;
		$this->request_maker = $request_maker;
		
		if (isset($cfg['record_name'])) { $this->record_name = $cfg['record_name']; }
		
		if (isset($cfg['table_name'])) { $this->table_name = $cfg['table_name']; }
		
		if (isset($cfg['add_user_log'])) { $this->add_user_log = $cfg['add_user_log']; }
		$this->table_fields = array_merge(
			isset($cfg['table_fields']) ? $cfg['table_fields'] : $this->table_fields, 
			$this->add_user_log === true ? array('id', 'create_date', 'create_user', 'update_date', 'update_user') : array()
		);
		/*$this->table_fields = array_merge(
			$cfg['table_fields'], 
			array('id', 'create_date', 'create_user', 'update_date', 'update_user')
		);*/
		
		if (isset($cfg['read_fields_strict'])) { $this->read_fields_strict = $cfg['read_fields_strict']; }
		if (isset($cfg['read_fields'])) { $this->read_fields = $cfg['read_fields']; }
		$this->read_fields = $this->read_fields === false ? $this->table_fields : array_merge($this->read_fields, $this->read_fields_strict !== true ? array(
			$this->table_name . '.id AS id', 
			$this->table_name . '.create_date AS create_date', 
			$this->table_name . '.create_user AS create_user', 
			$this->table_name . '.update_date AS update_date', 
			$this->table_name . '.update_user AS update_user'
		) : array());
		/*$this->read_fields = isset($cfg['read_fields_strict']) && $cfg['read_fields_strict'] === true ? $cfg['read_fields'] : array_merge(isset($cfg['read_fields']) ? $cfg['read_fields'] : $cfg['table_fields'], array(
			$this->table_name . '.id AS id', 
			$this->table_name . '.create_date AS create_date', 
			$this->table_name . '.create_user AS create_user', 
			$this->table_name . '.update_date AS update_date', 
			$this->table_name . '.update_user AS update_user'
		));*/
		
		if (isset($cfg['joins'])) { $this->joins = $cfg['joins']; }
		
		if (isset($cfg['read_conditions'])) { $this->read_conditions = $cfg['read_conditions']; }
		if (isset($cfg['read_conditions_glue'])) { $this->read_conditions_glue = $cfg['read_conditions_glue']; }
		
		if (isset($cfg['order_by_in_cfg'])) { $this->order_by_in_cfg = $cfg['order_by_in_cfg']; }
		if (isset($cfg['order_by']) && $this->order_by_in_cfg === true) { $this->order_by = $cfg['order_by']; }
		//var_dump($this->table_name, $this->order_by_in_cfg, $cfg['order_by'], $this->order_by);

		if (isset($cfg['limit']) && $this->limit_in_cfg === true) { $this->limit = $cfg['limit']; }
		
		if (!isset($cfg['hasMany']) && $this->hasMany !== false) { $cfg['hasMany'] = $this->hasMany; } 

		if (isset($cfg['hasMany'])) {
			$this->hasMany = array();
			
			foreach ($cfg['hasMany'] as $name => &$hasMany) {
				$hasMany['CRUD'] = new CRUD($db_connector, $request_maker, $hasMany);
				
				$this->hasMany[$name] = $hasMany;
			}
		}
		
		if (is_array($this->add_authorised_methodes)) { $this->authorised_methodes = array_merge($this->authorised_methodes, $this->add_authorised_methodes); }
	}
	
	
	
	/**
	 * Destructor 
	 */
	public function __destruct () {
		unset($this->db_connector, $this->request_maker);
	}
	
	
	
	/*
	 * create
	 */
	public function create () {
		$this->db_connector->executeQuery($this->request_maker->create($this->table_name));
		
		$this->data['id'] = $this->db_connector->getLastInsertedId();
		
		//var_dump($this->data);
		
		return $this->update();
	}
	
	
	
	/*
	 * read
	 */
	public function read ($read_conditions = false, $read_conditions_glue = ') AND (') {
		if ($read_conditions !== false && is_array($read_conditions)) { $this->setReadCondition($read_conditions, $read_conditions_glue); }
		
		//var_dump($this->table_name, $this->order_by);
		$this->data = $this->db_connector->fetchAssoc(
			$this->request_maker->read(
				$this->read_fields, 
				$this->table_name, 
				$this->joins, 
				$this->read_conditions, 
				$this->read_conditions_glue,
				$this->order_by,
				$this->limit
			)
		);
		
		if ($this->all_records_size !== false && $this->limit[1] > 0) {
			$this->all_records_size = current($this->db_connector->fetchAssoc(
				$this->request_maker->read(
					array('COUNT(*) AS all_records_size'), 
					$this->table_name, 
					$this->joins, 
					$this->read_conditions, 
					$this->read_conditions_glue,
					array()
				)
			))['all_records_size'];
		} else {
			$this->all_records_size = sizeof($this->data);
		}
		
		$this->readHasMany();
		
		if ($this->record_name !== '') {
			foreach ($this->data as &$data) {
				$data = array($this->record_name => $data);
			}
		}
		
		return $this->data;
	}
	
	
	
	/*
	 * readHasMany
	 */
	public function readHasMany () {
		if ($this->hasMany !== false) {
			$foreign_key_values = array();
			
			foreach ($this->hasMany as $hasMany_name => $hasMany) {
				// find the foreign keys values
				if (!isset($foreign_key_values[$hasMany['foreign_key']])) { 
					$foreign_key_values[$hasMany['foreign_key']] = array(); 
				
					foreach ($this->data as $data) { $foreign_key_values[$hasMany['foreign_key']][] = $data[$hasMany['foreign_key']]; }
				}
				
				if (sizeof($foreign_key_values[$hasMany['foreign_key']]) > 0) {
					$conditions = isset($hasMany['conditions']) ? $hasMany['conditions'] : array();
					
					if (isset($hasMany['foreign_key_is_string']) && $hasMany['foreign_key_is_string'] === true) {
						$conditions[] = $hasMany['local_key'] . ' IN (\'' . implode('\', \'', $foreign_key_values[$hasMany['foreign_key']]) . '\')';
					} else { 
						$conditions[] = $hasMany['local_key'] . ' IN (' . implode(', ', $foreign_key_values[$hasMany['foreign_key']]) . ')'; 
					}
					
					$hasMany['CRUD']->setReadCondition($conditions, isset($hasMany['conditions_glue']) ? $hasMany['conditions_glue'] : ') AND (');
					$hasMany['CRUD']->read();

					// put the hasMans values on the right parent
					foreach ($hasMany['CRUD']->data as $hasMany_data) {
						foreach ($this->data as &$data) {
							if ((isset($data[$hasMany['foreign_key']], $hasMany['local_key'], $hasMany_data[$hasMany['local_key']]) && 
								 $data[$hasMany['foreign_key']] == $hasMany_data[$hasMany['local_key']]) 
								||
								(isset($data[$hasMany['foreign_key']], $hasMany['local_key_val_name'], $hasMany_data[$hasMany['local_key_val_name']]) && 
								 $data[$hasMany['foreign_key']] == $hasMany_data[$hasMany['local_key_val_name']])
							) {
								if (!isset($data[$hasMany_name])) { $data[$hasMany_name] = array(); }

								$data[$hasMany_name][] = $hasMany_data;
							}
						}
					}
				}
			}
		}
	}
	
	
	
	/*
	 * setReadCondition
	 */
	public function setReadCondition ($read_conditions, $read_conditions_glue = ') AND (') {
		$this->read_conditions = $read_conditions;
		
		$this->read_conditions_glue = $read_conditions_glue;
	}
	
	
	
	/*
	 * setReadCondition
	 */
	public function setReadOrderBy ($order_by) {
		$this->order_by = $order_by;
	}
	
	
	
	/*
	 * update
	 */
	public function update () {
		//echo 'CRUD->update : $this->data = '; var_dump($this->data); echo "\n";
		
		$this->db_connector->executeQuery($this->request_maker->update($this->table_name, $this->data, $this->table_fields));
		
		$this->updateHasMany();
		
		if ($this->record_name !== '') { $this->data = array($this->record_name => $this->data); }
		
		return $this->data;
	}
	
	
	
	/*
	 * updateHasMany 
	 */
	private function updateHasMany () {
		if ($this->hasMany !== false) {
			
			foreach ($this->hasMany as $hasMany_name => $hasMany) {
				if (isset($this->data[$hasMany_name]) && (!isset($hasMany['read_only']) || $hasMany['read_only'] === false)) {
					foreach ($this->data[$hasMany_name] as &$hasMany_data) {
						$hasMany['CRUD']->data = $hasMany_data;
						
						if (isset($hasMany_data['delete']) && $hasMany_data['delete'] == true) {
							$hasMany_data = $hasMany['CRUD']->delete();
						} else {
							if (!isset($hasMany_data['id']) || $hasMany_data['id'] == 0) { 

								$hasMany['CRUD']->data[$hasMany['local_key']] = $this->data[$hasMany['foreign_key']];
								
								if ($this->add_user_log === true) {
									if (isset($this->data['update_date']) && $this->data['update_date'] != '0000-00-00 00:00:00') { 
										$create_date = $this->data['update_date'];
										$create_user = $this->data['update_user'];
									} else {
										$create_date = $this->data['create_date'];
										$create_user = $this->data['create_user'];
									}

									$hasMany['CRUD']->data['create_date'] = $create_date;
									$hasMany['CRUD']->data['create_user'] = $create_user;
								}

								$hasMany_data = $hasMany['CRUD']->create(); 
							}
							else { 
								if ($this->add_user_log === true) {
									$hasMany['CRUD']->data['update_date'] = $this->data['update_date'];
									$hasMany['CRUD']->data['update_user'] = $this->data['update_user'];
								}

								$hasMany_data = $hasMany['CRUD']->update(); 
							}

							foreach ($hasMany_data as &$data) {
								if ($data === true) { $data = '1'; }
								else if ($data === false) { $data = '0'; }
							}
						}
					}
				}
			}
		}
	}
	
	
	
	/*
	 * delete 
	 */
	public function delete () {		
		$this->db_connector->executeQuery($this->request_maker->delete($this->table_name, $this->data));
		
		$this->deleteHasMany();
		
		$this->data = array();
		
		return $this->data;
	}
	
	
	
	/*
	 * deleteHasMany 
	 */
	private function deleteHasMany () {
		if ($this->hasMany !== false) {
			
			foreach ($this->hasMany as $hasMany_name => $hasMany) {
				if (!isset($hasMany['read_only']) || $hasMany['read_only'] === false){
				
					if (isset($this->data[$hasMany_name])) {
						foreach ($this->data[$hasMany_name] as &$hasMany_data) {
							$hasMany['CRUD']->data = $hasMany_data;

							$hasMany_data = $hasMany['CRUD']->delete();
						}
					} else {
						$conditions = isset($hasMany['conditions']) ? $hasMany['conditions'] : array();
						$conditions[] = $hasMany['local_key'] . ' = ' . intval($this->data['id']) . '';

						$this->db_connector->executeQuery($this->request_maker->_delete(
							$hasMany['CRUD']->table_name, 
							'(' . implode(isset($hasMany['conditions_glue']) ? $hasMany['conditions_glue'] : ') AND (', $conditions) . ')'
						));
					}
				}
			}
		}
	}
	
	
	
	/*
	 * isMethodeExistAndAuthorised 
	 */
	public function isMethodeExistAndAuthorised ($methode) {
		return in_array($methode, $this->authorised_methodes) && method_exists($this, $methode);
	}
	
	
	
	/*
	 * setData 
	 */
	public function setData (&$data, $field, $value) {
		if ($this->record_name !== '') { $data[$this->record_name][$field] = $value; }
		else { $data[$field] = $value; }
	}
	
	
	
	/*
	 * getData 
	 */
	public function getData (&$data, $field) {
		if ($this->record_name !== '') { return isset($data[$this->record_name][$field]) ? $data[$this->record_name][$field] : false; }
		
		return isset($data[$field]) ? $data[$field] : false;
	}
}
?>
