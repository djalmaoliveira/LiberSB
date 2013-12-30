<?php

/**
 *  Html Form helpers.
 * @package     helpers
 * @author		djalmaoliveira@gmail.com
 * @copyright	djalmaoliveira@gmail.com
 * @license
 * @link
 * @since		Version 1.0
 */


    /**
    * Form Declaration
    *
    * Creates the opening portion of the form.
    *
    * @param	string	$action the URI segments of the form destination
    * @param	array	$attributes a key/value pair of attributes
    * @param	array	$hidden a key/value pair hidden data
    * @param   boolean $return
    * @return	string
    */
	function form_open_($action = '', $attributes = '', $hidden = array(), $return=false)
	{


		if ($attributes == '') {
			$attributes = 'method="post"';
		}

		$action = ( strpos($action, '://') === FALSE) ? Liber::conf('APP_URL') : $action;

		$form = '<form action="'.$action.'"';

		$form .= _attributes_to_string_($attributes, TRUE);

		$form .= '>';

		if (is_array($hidden) AND count($hidden) > 0)
		{
			$form .= form_hidden_($hidden);
		}

		if ( $return  ) {
    		return $form;
		} else {
		    echo $form;
		}

	}




/**
 * Form Declaration - Multipart type
 *
 * Creates the opening portion of the form, but with "multipart/form-data".
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */
    function form_open_multipart_($action, $attributes = array(), $hidden = array(), $return=false)
	{
		$attributes['enctype'] = 'multipart/form-data';
		if ( $return ) {
    		return form_open_($action, $attributes, $hidden, $return);
		} else {
		    echo form_open_($action, $attributes, $hidden);
		}

	}




    function _parse_input_params_($params) {
        $out['name']  = &$params[0];
        $out['value'] = ( isset($params[1]) and is_array($params[1]) and isset($params[1][$out['name']]) )?$params[1][$out['name']]:(isset($params[1])?$params[1]:'');
        $out['extras']= isset($params[2])?$params[2]:'';
        return 'id="'.$out['name'].'" name="'.$out['name'].'" value="'. htmlspecialchars($out['value']).'" '.$out['extras'];
    }


    /**
     * Create a Text Input Field.
     * Params usage:    (name)
     *                  (name, value)
     *                  (name, Array()) => 'value' will be the 'name' as an item of Array
     *                  (name, value, extras)
     *                  (name, Array(), extras) =>  same of above
     *                  (..., boolean) => will return a html string of element.
     * @param	string
     * @param	String | Array
     * @param	String
     * @return	String
     */
    function form_input_($name = '', $value = '', $extra = '', $return=false)	{
        $args = func_get_args();
        $elem = '<input type="text" '._parse_input_params_( $args).'/>';
        if ( (is_bool(end($args))?end($args):false) ) {
            return $elem;
        } else {
            echo $elem;
        }
	}

    /**
     * Create a Hidden Input Field
     * Params usage:    (name)
     *                  (name, value)
     *                  (name, Array()) => 'value' will be the 'name' as an item of Array
     *                  (name, value, extras)
     *                  (name, Array(), extras) =>  same of above
     *                  (..., boolean) => will return a html string of element.
     * @param	string
     * @param	String | Array
     * @param	String
     * @return	String
     */
    function form_hidden_($name = '', $value = '', $extra = '', $return=false)	{
        $args = func_get_args();
        $elem = '<input type="hidden" '._parse_input_params_( $args).'/>';
        if ( (is_bool(end($args))?end($args):false) ) {
            return $elem;
        } else {
            echo $elem;
        }
	}

    /**
     * Create a Password Input Field.
     * Params usage:    (name)
     *                  (name, value)
     *                  (name, Array()) => 'value' will be the 'name' as an item of Array
     *                  (name, value, extras)
     *                  (name, Array(), extras) =>  same of above
     *                  (..., boolean) => will return a html string of element.
     * @param	string
     * @param	String | Array
     * @param	String
     * @return	String
     */
    function form_password_($name = '', $value = '', $extra = '', $return=false) {
        $args = func_get_args();
        $elem = '<input type="password" '._parse_input_params_( $args).'/>';
        if ( (is_bool(end($args))?end($args):false) ) {
            return $elem;
        } else {
            echo $elem;
        }
    }




    /**
    * Upload Field
    *
    * Identical to the input function but adds the "file" type
    *
    * @param	mixed
    * @param	string
    * @param	string
    * @return	string
    */
    function form_upload_($data = '', $value = '', $extra = '', $return=false)	{
        $defaults = array('id' =>(( ! is_array($data)) ? $data : ''), 'type' => 'file', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
        $elem = "<input ". _parse_form_attributes_($data, $defaults).$extra." />";
        if ( $return ) {
            return $elem;
        } else {
            echo  $elem;
        }
    }




    /**
     * Create a TextArea  Field
     * Params usage:    (name)
     *                  (name, value)
     *                  (name, Array()) => 'value' will be the 'name' as an item of Array
     *                  (name, value, extras)
     *                  (name, Array(), extras) =>  same of above
     *                  (..., boolean) => will return a html string of element.
     * @param	string
     * @param	String | Array
     * @param	String
     * @return	String
     */
	  function form_textarea_($data = '', $value = '', $extra = '', $return=false)	{
	    $params = func_get_args();
        $out['name']  = &$params[0];
        $out['value'] = ( isset($params[1]) and is_array($params[1]) and isset($params[1][$out['name']]) )?$params[1][$out['name']]:(isset($params[1])?$params[1]:'');
        $out['extras']= isset($params[2])?$params[2]:'';
	    $cols = strpos(strtolower($out['extras']), 'cols=')!==false?'':'cols="30"';
	    $rows = strpos(strtolower($out['extras']), 'rows=')!==false?'':'rows="3"';
	    $elem = '<textarea id="'.$out['name'].'" name="'.$out['name'].'" '." $cols $rows ".' '.$out['extras'].' >'.$out['value'].'</textarea>';

		if ( $return ) {
    		return $elem;
		} else {
		    echo $elem;
		}
	}




/**
 * Multi-select menu
 *
 * @param	string
 * @param	array
 * @param	mixed
 * @param	string
 * @return	type
 */
	  function form_multiselect_($name = '', $options = array(), $selected = array(), $extra = '', $return=false)
	{
		if ( ! strpos($extra, 'multiple'))
		{
			$extra .= ' multiple="multiple"';
		}
		if ( $return ) {
    		return form_select_($name, $options, $selected, $extra);
        } else {
            echo form_select_($name, $options, $selected, $extra, $return);
        }
	}


// --------------------------------------------------------------------

/**
 * Creates a 'select' element form.
 *
 * @param	$name string - Name and Id of element.
 * @param	$options array - key/value of filled data.
 * @param	$selected array - value that must be selected.
 * @param	$extra string - others params for element like: class='css_class' onclick='func()'.
 * @return	string
 */
	  function form_select_($name = '', $options = array(), $selected = array(), $extra = '', $return=false)
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;

		$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

		$form = '<select id="'.$name.'" name="'.$name.'"'.$extra.$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			}
			else
			{
				$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}

		$form .= '</select>';

        if ( $return ) {
    		return $form;
    	} else {
    	    echo $form;
    	}
	}




/**
 * Checkbox Field
 *
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
	  function form_checkbox_($data = '', $value = '', $checked = FALSE, $extra = '', $return=false)
	{
		$name = (( ! is_array($data)) ? $data : '');
		$defaults = array('type' => 'checkbox', 'name' => $name, 'id'=>$name, 'value' => htmlspecialchars($value));

		if (is_array($data) AND isset($data['checked']))
		{
			$checked = $data['checked'];

			if ($checked == FALSE)
			{
				unset($data['checked']);
			}
			else
			{
				$data['checked'] = 'checked';
			}
		}

		if ($checked == TRUE)
		{
			$defaults['checked'] = 'checked';
		}
		else
		{
			unset($defaults['checked']);
		}

		$elem = "<input ". _parse_form_attributes_($data, $defaults).$extra." />";
        if ( $return ) {
            return $elem;
        } else {
            echo $elem;
        }

	}




/**
 * Radio Button
 *
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
	  function form_radio_($data = '', $value = '', $checked = FALSE, $extra = '', $return=false)
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';
		if ( $return ) {
		    return form_checkbox_($data, $value, $checked, $extra, $return);
		} else {
		    echo form_checkbox_($data, $value, $checked, $extra, $return);
		}
	}




/**
 * Submit Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
	  function form_submit_($data = '', $value = '', $extra = '', $return=false)
	{
		$defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => htmlspecialchars($value));

        $elem = "<input ". _parse_form_attributes_($data, $defaults).$extra." />";;
        if ( $return ) {
		    return $elem;
		} else {
		    echo $elem;
		}
	}




/**
 * Reset Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
	  function form_reset_($data = '', $value = '', $extra = '', $return=false)
	{
		$defaults = array('type' => 'reset', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => htmlspecialchars($value));

        $elem = "<input ". _parse_form_attributes_($data, $defaults).$extra." />";
        if ( $return ) {
		    return $elem;
		} else {
		    echo $elem;
		}
	}




/**
 * Form Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
	  function form_button_($data = '', $content = '', $extra = '', $return=false)
	{
		$defaults = array('id'=>(( ! is_array($data)) ? $data : ''),'name' => (( ! is_array($data)) ? $data : ''), 'type' => 'button');

		if ( is_array($data) AND isset($data['content']))
		{
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}
		$elem = "<button ". _parse_form_attributes_($data, $defaults).$extra.">".$content."</button>";
        if ( $return ) {
		    return $elem;
		} else {
		    echo $elem;
		}
	}




/**
 * Form Label Tag
 *
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 * @return	string
 */
	  function form_label_($label_text = '', $id = '', $attributes = array(), $return=false)
	{

		$label = '<label';

		if ($id != '')
		{
			 $label .= " for=\"$id\"";
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}

		$label .= ">$label_text</label>";
        if ( $return ) {
		    return $label;
		} else {
		    echo $label;
		}
	}



/**
 * Fieldset Tag
 *
 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
 * use fieldset_close()
 * @param	string	The legend text
 * @param	string	Additional attributes
 * @return	string
 */
	  function form_fieldset_($legend_text = '', $attributes = array(), $return=false)
	{
		$fieldset = "<fieldset";

		$fieldset .=  _attributes_to_string_($attributes, FALSE);

		$fieldset .= ">\n";

		if ($legend_text != '')
		{
			$fieldset .= "<legend>$legend_text</legend>\n";
		}
        if ( $return ) {
    		return $fieldset;
    	} else {
    	    echo $fieldset;
    	}
	}




/**
 * Fieldset Close Tag
 *
 * @param	string
 * @return	string
 */
	function form_fieldset_close_($extra = '', $return=false)
	{
	    if ( $return ) {
		    return "</fieldset>".$extra;
		} else {
		    echo "</fieldset>".$extra;
		}
	}




/**
 * Form Close Tag
 *
 * @param	string
 * @return	string
 */
	function form_close_($extra = '', $return=false)
	{
	    if ( $return ) {
		    return "</form>".$extra;
		} else {
		    echo "</form>".$extra;;
		}
	}




/**
 * Form Prep
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 * @param	string
 * @return	string
 */
	function form_prep_($str = '', $field_name = '')
	{
		static $prepped_fields = array();

		// if the field name is an array we do this recursively
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] =  form_prep_($val);
			}

			return $str;
		}

		if ($str === '')
		{
			return '';
		}

		// we've already prepped a field with this name
		// @todo need to figure out a way to namespace this so
		// that we know the *exact* field and not just one with
		// the same name
		if (isset($prepped_fields[$field_name]))
		{
			return $str;
		}

		$str = htmlspecialchars($str);

		// In case htmlspecialchars misses these.
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '')
		{
			$prepped_fields[$field_name] = $str;
		}

		return $str;
	}




/**
 * Form Value
 *
 * Grabs a value from the POST array for the specified field so you can
 * re-populate an input field or textarea.  If Form Validation
 * is active it retrieves the info from the validation class
 * @param	string
 * @return	mixed
 */
	function form_set_value_($field = '', $default = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			if ( ! isset($_POST[$field]))
			{
				return $default;
			}

			return  form_prep_($_POST[$field], $field);
		}

		return  form_prep_($OBJ->set_value($field, $default), $field);
	}




/**
 * Set Select
 *
 * Let's you set the selected value of a <select> menu via data in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
	function form_set_select_($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' selected="selected"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' selected="selected"';
		}

		return $OBJ->set_select($field, $value, $default);
	}




/**
 * Set Checkbox
 *
 * Let's you set the selected value of a checkbox via the value in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
	function form_set_checkbox_($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_checkbox($field, $value, $default);
	}




/**
 * Set Radio
 *
 * Let's you set the selected value of a radio field via info in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
	function form_set_radio_($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_radio($field, $value, $default);
	}




/**
 * Parse the form attributes
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	array
 * @param	array
 * @return	string
 */
	 function _parse_form_attributes_($attributes, $default)
	{
		if (is_array($attributes))
		{
			foreach ($default as $key => $val)
			{
				if (isset($attributes[$key]))
				{
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}

			if (count($attributes) > 0)
			{
				$default = array_merge($default, $attributes);
			}
		}

		$att = '';

		foreach ($default as $key => $val)
		{
			if ($key == 'value')
			{
				$val =  form_prep_($val, $default['name']);
			}

			$att .= $key . '="' . $val . '" ';
		}

		return $att;
	}




/**
 * Attributes To String
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	mixed
 * @param	bool
 * @return	string
 */
	function _attributes_to_string_($attributes, $formtag = FALSE)
	{
		if (is_string($attributes) AND strlen($attributes) > 0)
		{
			if ($formtag == TRUE AND strpos($attributes, 'method=') === FALSE)
			{
				$attributes .= ' method="post"';
			}

		return ' '.$attributes;
		}

		if (is_object($attributes) AND count($attributes) > 0)
		{
			$attributes = (array)$attributes;
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
		$atts = '';

		if ( ! isset($attributes['method']) AND $formtag === TRUE)
		{
			$atts .= ' method="post"';
		}

		foreach ($attributes as $key => $val)
		{
			$atts .= ' '.$key.'="'.$val.'"';
		}

		return $atts;
		}
	}


?>