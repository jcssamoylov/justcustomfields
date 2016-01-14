<?php

namespace jcf\controllers;
use jcf\models;

class FieldController {

	public $registered_fields;
	public $_field_factory;
	protected $_dataLayer;

	public function __construct($source_settings){
		$layer_type = $source_settings == JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
		$layer_factory = new models\DataLayerFactory();
		$this->_dataLayer = $layer_factory->create($layer_type, $source_settings);

		$this->_field_factory = new models\JustFieldFactory($this->_dataLayer);
		$this->registered_fields = $this->_field_factory->get_registered_fields();

		add_action('wp_ajax_jcf_add_field', array($this, 'ajaxCreate'));
		add_action('wp_ajax_jcf_save_field', array($this, 'ajaxSave'));
		add_action('wp_ajax_jcf_delete_field', array($this, 'ajaxDelete'));
		add_action('wp_ajax_jcf_edit_field', array($this, 'ajaxEdit'));
		add_action('wp_ajax_jcf_fields_order', array($this, 'ajaxSort'));
		
	}

	public function findByPostType($post_type){
		return $fields = $this->_dataLayer->get_fields($post_type);
	}
	
	/**
	 *  add field form show callback
	 */
	public function ajaxCreate(){
		$post_type = $_POST['post_type'];
		$field_type =  $_POST['field_type'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = $this->_field_factory->initObject($post_type, $field_type, $fieldset_id, $collection_id);
		$html = $field_obj->do_form();
		jcf_ajax_response($html, 'html');
		
	}
	
	/**
	 * save field from the form callback
	 */
	public function ajaxSave(){
		$post_type = $_POST['post_type'];
		$field_type =  $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = $this->_field_factory->initObject($post_type, $field_type, $fieldset_id, $collection_id);

		$field_index = $this->_field_factory->getIndex($field_obj->id_base);
		$resp = $field_obj->do_update($field_index);

		if(isset($resp['id_base']) && $resp['id_base'] == 'collection'){
			ob_start();
			$field_obj->settings_row($post_type, $resp['id'], $fieldset_id);
			$resp["collection_fields"] = ob_get_clean();
		}
		jcf_ajax_response($resp, 'json');

	}
	
	/**
	 * delete field processor callback
	 */
	public function ajaxDelete(){
		$post_type = $_POST['post_type'];
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		if($collection_id){
			$field_obj = $this->_field_factory->initObject($post_type, $collection_id, $fieldset_id);
			$field_obj->delete_field($field_id);
		} else {
			$field_obj = $this->_field_factory->initObject($post_type, $field_id, $fieldset_id);
			$field_obj->do_delete();			
		}
		
		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}
	
	/**
	 * edit field show form callback
	 */
	public function ajaxEdit(){
		$post_type = $_POST['post_type'];
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = (isset($_POST['collection_id'])?$_POST['collection_id']:'');
		
		$field_obj = $this->_field_factory->initObject($post_type, $field_id, $fieldset_id, $collection_id);
		$html = $field_obj->do_form();
		jcf_ajax_response($html, 'html');
	}
	
	/**
	 * fields order change callback
	 */
	public function ajaxSort(){
		$post_type = $_POST['post_type'];
		$fieldset_id = $_POST['fieldset_id'];
		$order  = trim($_POST['fields_order'], ',');
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $fieldset_id);
		$new_fields = explode(',', $order);
		
		$fieldset['fields'] = array();
		foreach($new_fields as $field_id){
			$fieldset['fields'][$field_id] = $field_id;
		}
		
		$this->_dataLayer->update_fieldsets($post_type, $fieldset_id, $fieldset);
		
		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}
}

