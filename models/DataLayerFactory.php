<?php

namespace JCF\models;

class DataLayerFactory {
	
	public function __construct(){}
	
	public function create($type){
		return new $type();
	}
}

