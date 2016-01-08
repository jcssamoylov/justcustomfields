<?php

namespace JCF\interfaces;

interface FieldSettings{
	public function get_fields($post_type, $id = FALSE);
	public function update_fields($post_type, $key, $values = array(), $fieldset_id = '');
	public function get_fieldsets($post_type, $id = FALSE);
	public function update_fieldsets($post_type, $key, $values = array());
}

