<?php

namespace ORM;

class records extends \ORM\CRUD {
	protected string $table_name = 'record';
	
	protected array $table_fields = array('id', 'txt');
	protected bool $add_user_log = false;
}
