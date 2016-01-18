<?php

namespace jcf\interfaces;

interface FieldSettings{
	public function getFields($post_type, $id = FALSE);
	public function updateFields($post_type, $key, $values = array(), $fieldset_id = '');
	public function getFieldsets($post_type, $id = FALSE);
	public function updateFieldsets($post_type, $key, $values = array());
}

