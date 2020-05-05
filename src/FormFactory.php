<?php

namespace h2lsoft\FormFactory;

use \voku\helper\AntiXSS;
use \h2lsoft\Data\Validator;


class Form
{
	private $form = [];
	private $locale = 'en';
	private $form_layout = 'normal';
	private $fields = [];
	private $fields_cancel_auto_validation = [];
	private $form_has_files = false;
	private $form_enc_type = 'multipart/form-data';
	private $field_next_index = 0;
	private $current_field;
	private $default_label_size = 3;
	
	private $form_values = [];
	private $anti_xss;
	
	public $validator;
	private $auto_validator = true;
	
	public function __construct($locale='en', $name='form', $method='post', $action='', $attributes=[])
	{
		$this->form = [];
		$this->form['name'] = $name;
		$this->form['method'] = $method;
		$this->form['action'] = $action;
		$this->form['attributes'] = $attributes;
		$this->anti_xss = new AntiXSS();
		
		$this->locale = $locale;
		$this->validator = new Validator($locale, strtoupper($method));
		
		return $this;
	}
	
	
	public function setAutoValidator($boolean)
	{
		$this->auto_validator = $boolean;
	}
	
	public function xssClean($str)
	{
		$str = $this->anti_xss->xss_clean($str);
		
		$str = strip_tags($str);
		$str = str_replace("<", '&lt;', $str);
		$str = str_replace(">", '&gt;', $str);
		
		return $str;
	}
	
	public function initialize($values, $xss_prevent=true, $field_exceptions=[])
	{
		foreach($values as $field => $val)
		{
			if($xss_prevent && !in_array($field, $field_exceptions))
			{
				if(!is_array($val))
				{
					$values[$field] = $this->xssClean($values[$field]);
				}
				else
				{
					for($i=0; $i < count($val); $i++)
						$values[$field][$i] =  $this->xssClean($val[$i]);
				}
			}
			
			if(!is_array($val))
				$values[$field] = trim($values[$field]);
			else
				$values[$field] = array_map('trim', $values[$field]);
			
			
		}
		
		$this->form_values = $values;
	}
	
	public function isSubmitted()
	{
		return (isset($_POST) && count($_POST));
	}
	
	public function setDefaultLabelSize($size)
	{
		$this->default_label_size = $size;
	}
	
	public function setCurrentField($field)
	{
		$this->current_field = $field;
	}
	
	public function addAttribute($attribute, $value)
	{
		if(!isset($this->fields[$this->current_field][$attribute]))
			$this->fields[$this->current_field][$attribute] = $value;
		else
			$this->fields[$this->current_field][$attribute] = " ".$value;
		
		return $this;
	}
	
	public function setValue($value)
	{
		$this->fields[$this->current_field]['value'] = $value;
		return $this;
	}
	
	public function setHelp($message)
	{
		$this->fields[$this->current_field]['help'] = $message;
		return $this;
	}
	
	public function setLabelSize($size)
	{
		$this->fields[$this->current_field]['label-size'] = $size;
		return $this;
	}
	
	public function setLabelAlignmentLeft()
	{
		$this->fields[$this->current_field]['label-aligment'] = 'left';
		return $this;
	}
	
	public function setLabelAlignmentCenter()
	{
		$this->fields[$this->current_field]['label-aligment'] = 'center';
		return $this;
	}
	
	public function setLabelAlignmentRight()
	{
		$this->fields[$this->current_field]['label-aligment'] = 'right';
		return $this;
	}
	
	public function setInputSize($size=10)
	{
		$this->fields[$this->current_field]['input-size'] = $size;
		return $this;
	}
	
	public function prependText($icon)
	{
		$this->fields[$this->current_field]['icon-text-before'] = $icon;
		return $this;
	}
	
	public function appendText($icon)
	{
		$this->fields[$this->current_field]['icon-text-after'] = $icon;
		return $this;
	}
	
	public function appendButton($text, $attributes=[])
	{
		$class = (!isset($attributes['class'])) ? "" : $attributes['class'];
		
		$attrs = "";
		foreach($attributes as $attr => $value)
		{
			if($attr != 'class')
				$attrs .= "{$attr} = \"{$value}\" ";
		}
		
		$str = <<<BTN
<button type="button" class="btn btn-light {$class}" {$attrs}>{$text}</button>
BTN;
		
		$this->fields[$this->current_field]['icon-text-after'] = $str;
		return $this;
	}
	
	public function addText($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'text';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['attributes'] = $attributes;
		
		return $this;
	}
	
	public function addNumber($name, $label='', $required=false, $attributes=[])
	{
		$this->addText($name, $label, $required, '', $attributes);
		$this->fields[$name]['type'] = 'number';
		
		return $this;
	}
	
	public function hasFiles($bool)
	{
		$this->form_has_files = $bool;
	}
	
	public function addFile($name, $label='', $required=false, $attributes=[])
	{
		$this->addText($name, $label, $required, '', $attributes);
		$this->fields[$name]['type'] = 'file';
		// $this->prependtext('<i class="fa-fw far fa-file"></i>');
		
		$this->hasFiles(1);
		
		return $this;
	}
	
	public function addTextArea($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'textarea';
		return $this;
	}
	
	public function addSearch($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'search';
		$this->prependText('<i class="fa-fw fas fa-search"></i>');
		
		return $this;
	}
	
	public function addEmail($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'email';
		$this->prependText('<i class="fa-fw far fa-envelope"></i>');
		
		return $this;
	}
	
	public function addUrl($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'url';
		$this->prependText('<i class="fa-fw fas fa-link"></i>');
		
		// $this->appendText("<button class='btn btn-outline-secondary' type='button'><i class=\"fa-fw fas fa-globe-americas\"></i></button>");
		
		return $this;
	}
	
	public function addPassword($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'password';
		
		$icon = '<i class="fa-fw fas fa-lock"></i>';
		$this->PrependText($icon);
		
		return $this;
	}
	
	public function addTel($name, $label='', $required=false, $value="", $attributes=[])
	{
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'tel';
		
		$icon = '<i class="fa-fw fas fa-phone-alt"></i>';
		$this->prependText($icon);
		$this->setInputSize(4);
		
		return $this;
	}
	
	public function addDate($name, $label='', $required=false, $value="", $attributes=[])
	{
		if(!isset($attributes['class']))$attributes['class'] = '';
		$attributes['class'] .= ' text-center';
		
		if(!isset($attributes['data-mask']))
			$attributes['data-mask'] = '9999-99-99';
		
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'date';
		
		$icon = '<i class="fa-fw far fa-calendar-alt"></i>';
		$this->prependText($icon);
		$this->setInputSize(4);
		
		return $this;
	}
	
	public function addTime($name, $label='', $required=false, $value="", $attributes=[])
	{
		if(!isset($attributes['class']))$attributes['class'] = '';
		$attributes['class'] .= ' text-center';
		
		if(!isset($attributes['data-mask']))
			$attributes['data-mask'] = '99:99';
		
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'time';
		
		$icon = '<i class="fa-fw far fa-clock"></i>';
		$this->prependText($icon);
		$this->setInputSize(2);
		
		return $this;
	}
	
	public function addDateTime($name, $label='', $required=false, $value="", $attributes=[])
	{
		if(!isset($attributes['class']))$attributes['class'] = '';
		$attributes['class'] .= ' text-center';
		
		if(!isset($attributes['data-mask']))
			$attributes['data-mask'] = '9999-99-99T99:99';
		
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'datetime-local';
		
		$icon = '<i class="fa-fw far fa-calendar-alt"></i>';
		$this->prependText($icon);
		$this->setInputSize(4);
		
		return $this;
	}
	
	public function addColorPicker($name, $label='', $required=false, $value="", $attributes=[])
	{
		$attributes['data-mask'] = '#******';
		
		$this->addText($name, $label, $required, $value, $attributes);
		$this->fields[$name]['type'] = 'color';
		
		$icon = '<i class="fa-fw fas fa-eye-dropper"></i>';
		$this->prependText($icon);
		$this->setInputSize(2);
		
		return $this;
	}
	
	public function addMoneyDollar($name, $label='', $required=false, $value="", $attributes=[])
	{
		if(!isset($attributes['class']))
			$attributes['class'] = "text-center";
		else
			$attributes['class'] .= " text-center";
		
		$this->addText($name, $label, $required, $value, $attributes);
		
		$icon = '<i class="fa-fw fas fa-dollar-sign"></i>';
		$this->prependText($icon);
		
		$this->fields[$name]['type2'] = 'money';
		
		return $this;
	}
	
	public function addMoneyEuro($name, $label='', $required=false, $value="", $attributes=[])
	{
		if(!isset($attributes['class']))
			$attributes['class'] = "text-center";
		else
			$attributes['class'] .= " text-center";
		
		
		$this->addText($name, $label, $required, $value, $attributes);
		
		$icon = '<i class="fa-fw fas fa-euro-sign"></i>';
		$this->appendText($icon);
		
		$this->fields[$name]['type2'] = 'money';
		
		return $this;
	}

	public function addHidden($name, $value='', $attributes=[])
	{
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'hidden';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['attributes'] = $attributes;
		$this->fields[$name]['row'] = false;
		
		return $this;
	}
	
	public function addSelect($name, $label='', $options=[], $empty_first=true, $required=false, $value="", $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'select';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['attributes'] = $attributes;
		$this->fields[$name]['empty-first'] = $empty_first;
		
		return $this;
	}
	
	public function addSelectMultiple($name, $label='', $options=[], $required=false, $value=[], $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'select-multiple';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['attributes'] = $attributes;
		$this->fields[$name]['empty-first'] = false;
		
		return $this;
	}
	
	public function addCheckboxes($name, $label='', $options=[], $required=false, $value=[], $inline=true, $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'checkbox';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['inline'] = $inline;
		$this->fields[$name]['attributes'] = $attributes;
		
		return $this;
	}
	
	public function addSwitch($name, $label='', $text='', $value='yes', $required=false, $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name]['type'] = 'switch';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		
		$options = [];
		$options[] = ['label' => $text, 'value' => $value];
		
		$this->fields[$name]['value'] = "";
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['attributes'] = $attributes;
		$this->fields[$name]['inline'] = false;
		
		return $this;
	}
	
	public function addRadio($name, $label='', $options=[], $required=false, $value="", $inline=true, $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'radio';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['value'] = $value;
		$this->fields[$name]['inline'] = $inline;
		$this->fields[$name]['attributes'] = $attributes;
		
		return $this;
	}
	
	public function addFieldsetStart($name, $label='', $attributes=[])
	{
		$this->field_next_index++;
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'fieldset-start';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = (empty($label)) ? $name : $label;
		$this->fields[$name]['required'] = false;
		$this->fields[$name]['value'] = '';
		$this->fields[$name]['attributes'] = $attributes;
		
		return $this;
	}
	
	public function addFieldsetEnd()
	{
		$this->field_next_index++;
		
		$name = "fieldset_end__{$this->field_next_index}";
		
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'fieldset-end';
	}
	
	public function addFooter($submit_button_text="Submit", $reset_button=true, $reset_button_text="Cancel")
	{
		$this->field_next_index++;
		
		$name = "footer__{$this->field_next_index}";
		
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'footer-normal';
		$this->fields[$name]['label-added'] = false;
		
		$this->fields[$name]['submit-button-text'] = $submit_button_text;
		$this->fields[$name]['reset-button'] = $reset_button;
		$this->fields[$name]['reset-button-text'] = $reset_button_text;
	}
	
	public function addHtml($html)
	{
		$this->field_next_index++;
		
		$name = "html_{$this->field_next_index}";
		$this->setCurrentField($name);
		
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'html';
		$this->fields[$name]['value'] = $html;
		$this->fields[$name]['row'] = false;
		$this->fields[$name]['label-added'] = false;
		
		return $this;
	}
	
	public function addHeading($title, $size=1, $attributes=[])
	{
		$this->field_next_index++;
		
		$name = "heading_{$this->field_next_index}";
		
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'heading';
		$this->fields[$name]['value'] = $title;
		$this->fields[$name]['size'] = $size;
		$this->fields[$name]['attributes'] = $attributes;
		
		if(!isset($this->fields[$name]['attributes']['class']))
			$this->fields[$name]['attributes']['class'] = 'form-heading';
		else
			$this->fields[$name]['attributes']['class'] = ' form-heading';
		
		return $this;
	}
	
	public function addRowStart($attributes=[])
	{
		$this->field_next_index++;
		
		$name = "row_{$this->field_next_index}";
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'row-start';
		$this->fields[$name]['attributes'] = $attributes;
		
		return $this;
	}
	
	public function addRowEnd()
	{
		$this->field_next_index++;
		
		$name = "row_{$this->field_next_index}";
		$this->fields[$name] = [];
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['type'] = 'row-end';
		
		return $this;
	}
	
	public function noLabel()
	{
		$this->fields[$this->current_field]['label-added'] = false;
		return $this;
	}
	
	public function cancelAutoValidation($input='')
	{
		if(empty($input))$input = $this->current_field;
		$this->fields_cancel_auto_validation[] = $input;
	}
	
	public function addAfter($html)
	{
		$this->fields[$this->current_field]['after'] = $html;
		return $this;
	}
	
	public function addDatalist($options)
	{
		$this->fields[$this->current_field]['attributes']['autocomplete'] = "disabled";
		$this->fields[$this->current_field]['datalist'] = $options;
		return $this;
	}
	
	public function clean()
	{
		$this->form_values = [];
	}
	
	// RENDER **********************************************************************************************************
	public function isValid()
	{
		// add compilation rules
		if($this->auto_validator)
		{
			foreach($this->fields as $name => $props)
			{
				$type = (!isset($this->fields[$name]['type2'])) ? $this->fields[$name]['type'] : $this->fields[$name]['type2'];
				if(in_array($type, ['text', 'number', 'tel', 'email', 'password','file', 'url', 'textarea', 'select', 'select-multiple', 'checkbox', 'radio', 'switch', 'money', 'date', 'time', 'datetime-local', 'color']))
				{
					$input = $this->validator->input($name, $this->fields[$name]['label']);
					if($this->fields[$name]['required'] && $type != 'switch')
						$input->required();
					
					// mask
					if(isset($this->fields[$name]['attributes']['data-mask']) && !empty($this->fields[$name]['attributes']['data-mask']))
						$input->mask($this->fields[$name]['attributes']['data-mask']);
						
					// email
					if($type == 'email')
						$input->email();
					
					// url
					if($type == 'url')
						$input->url();
					
					// number
					if($type == 'number')
					{
						$input->integer();
						
						// min && max
						if(
							(isset($this->fields[$name]['attributes']['min']) && !empty($this->fields[$name]['attributes']['min'])) &&
							(isset($this->fields[$name]['attributes']['max']) && !empty($this->fields[$name]['attributes']['max']))
						)
						{
							$input->between($this->fields[$name]['attributes']['min'], $this->fields[$name]['attributes']['max']);
						}
						elseif((isset($this->fields[$name]['attributes']['min']) && !empty($this->fields[$name]['attributes']['min'])))
						{
							$input->min($this->fields[$name]['attributes']['min']);
						}
						elseif((isset($this->fields[$name]['attributes']['max']) && !empty($this->fields[$name]['attributes']['max'])))
						{
							$input->max($this->fields[$name]['attributes']['max']);
						}
					}
					
					// @todo> date min, max
					// @todo> time min, max
					// @todo> datetime-local min, max
					
					
					// minlength
					if(isset($this->fields[$name]['attributes']['minlength']))
						$input->minLength($this->fields[$name]['attributes']['minlength']);
					
					// maxlength
					if(isset($this->fields[$name]['attributes']['maxlength']))
						$input->maxLength($this->fields[$name]['attributes']['maxlength']);
					
					// select, select-multiple, checkbox, radio,
					if(in_array($type, ['select', 'select-multiple', 'radio', 'checkbox']))
					{
						$allowed_values = [];
						$multi = (count($this->fields[$name]['options']) == count($this->fields[$name]['options'], COUNT_RECURSIVE)) ? false : true;
						
						if(!$multi)
						{
							$values = $this->fields[$name]['options'];
						}
						else
						{
							$values = [];
							foreach($this->fields[$name]['options'] as $opt)
							{
								$values[] = $opt['value'];
							}
						}
						
						$input->in($values);
					}
					
					// switch
					if($type == 'switch')
					{
						$input->accepted();
					}
					
				}
			}
		}
	}
	
	
	public function render()
	{
		if($this->isSubmitted() && strtolower($this->form['method']) == 'post')
			$this->form_values = $_POST;
		
		// initialize values
		foreach($this->form_values as $field => $value)
		{
			if(isset($this->fields[$field]))
			{
				$this->fields[$field]['value'] = $value;
			}
		}
		
		
		$errors = $this->validator->result()['error_stack_deep'];
		$fields_errors = [];
		foreach($errors as $key => $stack)
		{
			$fields_errors[] = $key;
		}
		
		
		// <form>
		$render = "\n<!-- FORM FACTORY -->\n";
		$render .= "<form novalidate ";
		$render .= " name=\"{$this->form['name']}\"  ";
		$render .= " method=\"{$this->form['method']}\" ";
		$render .= " action=\"{$this->form['action']}\" ";
		
		if($this->form_has_files)
			$render .= " enctype=\"multipart/form-data\" ";
		
		foreach($this->form['attributes'] as $attr => $value)
			$render .= " {$attr}=\"{$value}\" ";
		$render .= ">\n";
		
		// validator
		$result = $this->validator->result();
		if(count($fields_errors))
		{
			$render .= "<div class=\"form-errors alert alert-danger\">\n";
			foreach($fields_errors as $field_error)
			{
				$stack = $this->validator->result()['error_stack_deep'];
				foreach($stack[$field_error] as $error)
				{
					$render .= "&bull; {$error}<br>\n";
				}
				
			}
			
			$render .= "</div>\n";
		}
		
		// inputs
		$row_started = false;
		foreach($this->fields as $field => $vals)
		{
			if(!isset($this->fields[$field]['id']))
				$this->fields[$field]['id'] = $this->fields[$field]['name'];
			
			if(!isset($this->fields[$field]['input-size']) || empty($this->fields[$field]['input-size']))
				$this->fields[$field]['input-size'] = 'col';
			else
				$this->fields[$field]['input-size'] = 'col-sm-'.$this->fields[$field]['input-size'];
			
			if(!isset($this->fields[$field]['help']))
				$this->fields[$field]['help'] = '';
			
			if(!isset($this->fields[$field]['icon-text-before']))
				$this->fields[$field]['icon-text-before'] = '';
			
			if(!isset($this->fields[$field]['icon-text-after']))
				$this->fields[$field]['icon-text-after'] = '';
			
			if(!isset($this->fields[$field]['row']))
				$this->fields[$field]['row'] = true;
			
			if(!isset($this->fields[$field]['label-added']))
				$this->fields[$field]['label-added'] = true;
			
			if(!isset($this->fields[$field]['label-aligment']))
				$this->fields[$field]['label-aligment'] = 'left';
			
			if(!isset($this->fields[$field]['label-size']))
				$this->fields[$field]['label-size'] = $this->default_label_size;
			
			if(!isset($this->fields[$field]['datalist']))
				$this->fields[$field]['datalist'] = [];
			
			$f = &$this->fields[$field];
			
			if($f['type'] == 'html')
			{
				$render .= "\n{$f['value']}\n";
			}
			elseif($f['type'] == 'row-start')
			{
				$render .= "<div class=\"form-group row\">\n";
				$row_started = true;
			}
			elseif($f['type'] == 'row-end')
			{
				$render .= "</div>\n";
				$row_started = false;
			}
			elseif($f['type'] == 'fieldset-start')
			{
				$render .= "\n<fieldset id=\"{$f['name']}\" ";
				
				foreach($f['attributes'] as $key => $val)
					$render .= " {$key}=\"{$val}\"";
				$render .= ">\n";
				$render .= "\t\t<legend>{$f['label']}</legend>\n";
				
			}
			elseif($f['type'] == 'fieldset-end')
			{
				$render .= "\n</fieldset>\n";
			}
			elseif($f['type'] == 'footer-normal')
			{
				$render .= "<div class=\"row row-form-footer\">\n";
				
				$render .= "	<div class=\"col text-center\">\n";
				if($f['reset-button'])
				{
					$render .= "		<button type=\"reset\" class=\"btn btn-default btn-cancel\">{$f['reset-button-text']}</button>\n";
				}
				$render .= "	</div>\n";
				
				// submit
				$class = (!$f['reset-button']) ? 'right' : 'center';
				
				$render .= "	<div class=\"col text-{$class}\">\n";
				$render .= "		<button type=\"submit\" class=\"btn btn-primary btn-submit\" name=\"submit\" value=\"1\">{$f['submit-button-text']}</button>\n";
				$render .= "	</div>\n";
				
				
				$render .= "</div>\n";
			}
			elseif($f['type'] == 'heading')
			{
				$render .= "<div class=\"row\">\n";
				$render .= "	<div class=\"col\">\n";
				
				$attributes = '';
				foreach($f['attributes'] as $key => $val)
					$attributes .= " {$key}=\"{$val}\"";
				
				$render .= "		<h{$f['size']} {$attributes}>{$f['value']}</h{$f['size']}>\n";
				
				$render .= "	</div>\n";
				$render .= "</div>\n";
			}
			elseif($f['type'] != 'hidden')
			{
				// row
				if(!$row_started && $f['row'])
					$render .= "	\n\t<div class=\"form-group row\">\n";
				
				// label
				if($f['label-added'])
				{
					$required = ($f['required']) ? "required " : "";
					if(trim($f['label']) == "")$required = '';
					
					// label
					$render .= "		<label class=\"col-sm-{$f['label-size']} col-form-label text-{$f['label-aligment']}\" for=\"{$f['id']}\" {$required}>{$f['label']}</label>\n";
					$render .= "		<div class=\"{$this->fields[$field]['input-size']}\">\n";
				}
				else
				{
					$render .= "		<div class=\"col\">\n";
				}
				
				// fields: text, password, tel, email, search, url
				if(in_array($f['type'], ['text', 'number', 'password', 'tel', 'email', 'search', 'url', 'textarea', 'file', 'select', 'select-multiple', 'date', 'time', 'datetime-local', 'color']))
				{
					if(!empty($f['icon-text-before']) || !empty($f['icon-text-after']))
					{
						$render .= "		<div class=\"input-group\">\n";
						
						if(!empty($f['icon-text-before']))
						{
							$render .= "			<div class=\"input-group-prepend\">\n";
							$render .= "				<span class=\"input-group-text\">{$f['icon-text-before']}</span>\n";
							$render .= "			</div>\n";
						}
					}
					
					if($f['type'] == 'textarea')
						$render .= "			<textarea ";
					if($f['type'] == 'select' || $f['type'] == 'select-multiple')
					{
						$render .= "			<select ";
						if($f['type'] == 'select-multiple')$render .= "multiple ";
					}
					else
						$render .= "			<input type=\"{$f['type']}\" ";
					
					if($f['type'] == 'select-multiple')
						$render .= "name=\"{$f['name']}[]\" ";
					else
						$render .= "name=\"{$f['name']}\" ";
					
					$render .= "id=\"{$f['id']}\" ";
					if(!in_array($f['type'], ['textarea', 'select', 'select-multiple']))
						$render .= "value=\"{$f['value']}\" ";
					
					if($f['required'] == true)
						$render .= "required ";
					
					if(!isset($f['attributes']['class'])) $f['attributes']['class'] = '';
					if($f['type'] != 'file')$f['attributes']['class'] .= ' form-control';
					
					// is-invalid
					if(in_array($f['name'], $fields_errors))
						$f['attributes']['class'] .= ' is-invalid';
					
					
					foreach($f['attributes'] as $attr => $value)
						$render .= "$attr=\"{$value}\" ";
					
					if($f['type'] == 'textarea')
						$render .= ">{$f['value']}</textarea>\n";
					elseif($f['type'] == 'select' || $f['type'] == 'select-multiple')
					{
						$options = '';
						if($f['empty-first'])
							$options .= "<option value=\"\"></option>";
						
						// $multi = (array_values($f['options']) === $f['options']) ? false : true;
						$multi = (count($f['options']) == count($f['options'], COUNT_RECURSIVE)) ? false : true;
						
						// transform single array to multiarray
						if(!$multi)
						{
							$options2 = [];
							foreach($f['options'] as $option)
								$options2[] = ['value' => $option, 'label' => $option];
							$f['options'] = $options2;
						}
						
						foreach($f['options'] as $option)
						{
							$label = $option['label'];
							$value = $option['value'];
							
							if($f['type'] == 'select')
								$selected = ($value == $f['value']) ? 'selected' : '';
							elseif($f['type'] == 'select-multiple')
								$selected = (in_array($value, $f['value'])) ? 'selected' : '';
							
							$options .= "<option value=\"{$value}\" {$selected}>{$label}</option>\n";
						}
						
						$render .= ">{$options}</select>\n";
					}
					else
					{
						// input text datalist
						if(count($f['datalist']))
						{
							$list_name = $f['id'].'__datalist';
							$render .= "list=\"{$list_name}\"";
							$render .= ">\n";
							
							// render list
							$render .= "<datalist id=\"{$list_name}\">\n";
							
							// multi
							// $multi = (array_values($f['datalist']) === $f['datalist']) ? false : true;
							$multi = (count($f['datalist']) == count($f['datalist'], COUNT_RECURSIVE)) ? false : true;
							
							// transform single array to multiarray
							if(!$multi)
							{
								$options2 = [];
								foreach($f['datalist'] as $option)
									$options2[] = ['value' => $option, 'label' => $option];
								$f['datalist'] = $options2;
							}
							
							foreach($f['datalist'] as $option)
							{
								$label = $option['label'];
								$value = $option['value'];
								$render .= "<option value=\"{$value}\">{$label}</option>\n";
							}
							
							$render .= "</datalist>\n";
						}
						else
						{
							$render .= ">\n";
						}
						
						
					}
					
					
					// icon close
					if(!empty($f['icon-text-before']) || !empty($f['icon-text-after']))
					{
						if(!empty($f['icon-text-after']))
						{
							$render .= "			<div class=\"input-group-append\">\n";
							
							if(strpos($f['icon-text-after'], '<button') === FALSE)
								$render .= "				<span class=\"input-group-text\">{$f['icon-text-after']}</span>\n";
							else
								$render .= "				{$f['icon-text-after']}\n";
							$render .= "			</div>\n";
						}
						$render .= "		</div>\n";
					}
				}
				// RADIO && CHECKBOX
				elseif(in_array($f['type'], ['radio', 'checkbox', 'switch']))
				{
					// $multi = (array_values($f['options']) === $f['options']) ? false : true;
					$multi = (count($f['options']) == count($f['options'], COUNT_RECURSIVE)) ? false : true;
					
					$inline = ($f['inline']) ? 'custom-control-inline' : '';
					
					// transform to single array
					if(!$multi)
					{
						$options2 = [];
						foreach($f['options'] as $option)
							$options2[] = ['value' => $option, 'label' => $option];
						$f['options'] = $options2;
					}
					
					$i = 0;
					$options = "";
					foreach($f['options'] as $opt)
					{
						$label = $opt['label'];
						$value = $opt['value'];
						
						$cur_type = $f['type'];
						
						if($f['type'] == 'radio')
							$checked = ($value == $f['value']) ? 'checked' : '';
						
						if($f['type'] == 'checkbox')
							$checked = (is_array($f['value']) && in_array($value, $f['value'])) ? 'checked' : '';
						
						if($f['type'] == 'switch')
						{
							$checked = ($value == $f['value']) ? 'checked' : '';
							$cur_type = 'checkbox';
						}
						
						$attributes = "";
						foreach($f['attributes'] as $attr => $attr_value)
							$attributes .= "{$attr}=\"{$attr_value}\"\n";
						
						$name = $f['name'];
						if($f['type'] == 'checkbox')
							$name .= "[]";
						
						// is-invalid
						$in_valid = '';
						if(in_array($f['name'], $fields_errors))
							$in_valid .= ' is-invalid';
						
						$options .= "<div class=\"custom-control custom-{$f['type']} {$inline} \">\n";
						$options .= "	<input type=\"{$cur_type}\" id=\"{$f['name']}_{$i}\" name=\"{$name}\" class=\"custom-control-input {$in_valid} \" value=\"{$value}\" {$checked} {$attributes}>\n";
						$options .= "	<label class=\"custom-control-label\" for=\"{$f['name']}_{$i}\">{$label}</label>\n";
						$options .= "</div>\n";
						
						$i++;
					}
					
					$render .= "{$options}\n";
				}
				
				// help block
				if(!empty($f['help']))
				{
					$render .= "			<small id=\"{$f['id']}HelpBlock\" class=\"form-text text-muted\">\n";
					$render .= $f['help'];
					$render .= "			\n</small>\n";
				}
				
				// end label
				if($f['label-added'])
				{
					$render .= "		</div>\n";
				}
				else
				{
					$render .= "		</div>\n";
				}
				
				if(!$row_started && $f['row'])
					$render .= "	</div>\n";
			}
			
		}
		
		// hiddens
		foreach($this->fields as $field => $vals)
		{
			$f = &$this->fields[$field];
			if($f['type'] == 'hidden')
			{
				$render .= "			<input type=\"hidden\" ";
				$render .= "name='{$f['name']}' ";
				$render .= "id=\"{$f['id']}\" ";
				$render .= "value=\"{$f['value']}\" ";
				foreach($f['attributes'] as $attr => $value)
					$render .= "$attr=\"{$value}\" ";
				$render .= ">\n";
			}
		}
		
		// </form>
		$render .= "</form>\n";
		$render .= "<!-- /FORM FACTORY -->\n";
		
		return $render;
	}
}