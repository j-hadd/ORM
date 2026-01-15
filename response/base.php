<?php

namespace ORM\response;

/** 
 * 
 * @author Jad Haddouch <jad.haddouch@gmail.com>
 * @docauthor Jad Haddouch <jad.haddouch@gmail.com>
 * @copyright Copyright 2026 Jad Haddouch
 */

interface base {
    /*
	 * 
	 */
	public function normalize (array $params = array()): void;
	
	
	
	/*
	 * Die the right syntax fo json response
	 *
	 * @param $msg The bdd message
	 * @return string
	 */
	public function dbError (string $msg): void;
}
