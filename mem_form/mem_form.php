<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_form';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.8.6';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'A library plugin that provides support for html forms.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '2';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '0';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

/** Uncomment me, if you need a textpack
$plugin['textpack'] = <<<EOT
#@admin
#@language en-gb
abc_sample_string => Sample String
abc_one_more => One more
#@language de-de
abc_sample_string => Beispieltext
abc_one_more => Noch einer
EOT;
**/
// End of textpack

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---


$mem_glz_custom_fields_plugin = load_plugin('glz_custom_fields');

// needed for MLP
define( 'MEM_FORM_PREFIX' , 'mem_form' );

global $mem_form_lang;

if (!is_array($mem_form_lang))
{
	$mem_form_lang = array(
		'error_file_extension'	=> 'File upload failed for field {label}.',
		'error_file_failed'	=> 'Failed to upload file for field {label}.',
		'error_file_size'	=> 'Failed to upload File for field {label}. File is too large.',
		'field_missing'	=> 'The field {label} is required.',
		'form_expired'	=>	'The form has expired.',
		'form_misconfigured'	=> 'The mem_form is misconfigured. You must specify the "form" attribute.',
		'form_sorry'	=> 'The form is currently unavailable.',
		'form_used'	=>	'This form has already been used to submit.',
		'general_inquiry'	=> '',
		'invalid_email'	=> 'The email address {email} is invalid.',
		'invalid_host'	=> 'The host {domain} is invalid.',
		'invalid_utf8'	=> 'Invalid UTF8 string for field {label}.',
		'invalid_value'	=> 'The value "{value}" is invalid for the input field {label}.',
		'invalid_format'	=>	'The input field {label} must match the format "{example}".',
		'invalid_too_many_selected'	=> 'The input field {label} only allows {count} selected {plural}.',
		'item'	=> 'item',
		'items'	=> 'items',
		'max_warning'	=> 'The input field {label} must be smaller than {max} characters long.',
		'min_warning'	=> 'The input field {label} must be at least {min} characters long.',
		'refresh'	=> 'Refresh',
		'spam'	=> 'Your submission was blocked by a spam filter.',
		'submitted_thanks'	=>	'You have successfully submitted the form. Thank you.',
	);
}

register_callback( 'mem_form_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_form_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_form_lang;
	$r = array	(
				'owner'		=> 'mem_form',			#	Change to your plugin's name
				'prefix'	=> MEM_FORM_PREFIX,		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'public',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_form_lang,		#	The strings themselves.
				);
	return $r;
}


function mem_form_gTxt($what,$args = array())
{
	global $mem_form_lang, $textarray;

	$key = strtolower( MEM_FORM_PREFIX . '-' . $what );

	if (isset($textarray[$key]))
	{
		$str = $textarray[$key];
	}
	else
	{
		$key = strtolower($what);

		if (isset($mem_form_lang[$key]))
			$str = $mem_form_lang[$key];
		elseif (isset($textarray[$key]))
			$str = $textarray[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}


function mem_form($atts, $thing='', $default=false)
{
	global $sitename, $prefs, $file_max_upload_size, $mem_form_error, $mem_form_submit,
		$mem_form, $mem_form_labels, $mem_form_values, $mem_form_default_break,
		$mem_form_default, $mem_form_type, $mem_form_thanks_form,
		$mem_glz_custom_fields_plugin;

	extract(mem_form_lAtts(array(
		'form'		=> '',
		'thanks_form'	=> '',
		'thanks'	=> graf(mem_form_gTxt('submitted_thanks')),
		'label'		=> '',
		'type'		=> '',
		'redirect'	=> '',
		'redirect_form'	=> '',
		'class'		=> 'memForm',
		'enctype'	=> '',
		'file_accept'	=> '',
		'max_file_size'	=> $file_max_upload_size,
		'form_expired_msg' => mem_form_gTxt('form_expired'),
		'show_error'	=> 1,
		'show_input'	=> 1,
		'default_break'	=> br,
	), $atts));

	if (empty($type) or (empty($form) && empty($thing))) {
		trigger_error('Argument not specified for mem_form tag', E_USER_WARNING);

		return '';
	}
	$out = '';

	// init error structure
	mem_form_error();

	$mem_form_type = $type;

	$mem_form_default = is_array($default) ? $default : array();
	callback_event('mem_form.defaults');

	unset($atts['show_error'], $atts['show_input']);
	$mem_form_id = md5(serialize($atts).preg_replace('/[\t\s\r\n]/','',$thing));
	$mem_form_submit = (ps('mem_form_id') == $mem_form_id);

	$nonce   = doSlash(ps('mem_form_nonce'));
	$renonce = false;

	if ($mem_form_submit) {
		safe_delete('txp_discuss_nonce', 'issue_time < date_sub(now(), interval 10 minute)');
		if ($rs = safe_row('used', 'txp_discuss_nonce', "nonce = '$nonce'"))
		{
			if ($rs['used'])
			{
				unset($mem_form_error);
				mem_form_error(mem_form_gTxt('form_used'));
				$renonce = true;

				$_POST['mem_form_submit'] = TRUE;
				$_POST['mem_form_id'] = $mem_form_id;
				$_POST['mem_form_nonce'] = $nonce;
			}
		}
		else
		{
			mem_form_error($form_expired_msg);
			$renonce = true;
		}
	}

	if ($mem_form_submit and $nonce and !$renonce)
	{
		$mem_form_nonce = $nonce;
	}

	elseif (!$show_error or $show_input)
	{
		$mem_form_nonce = md5(uniqid(rand(), true));
		safe_insert('txp_discuss_nonce', "issue_time = now(), nonce = '$mem_form_nonce'");
	}

	$form = ($form) ? fetch_form($form) : $thing;
	$form = parse($form);

	if ($mem_form_submit && empty($mem_form_error))
	{
		// let plugins validate after individual fields are validated
		callback_event('mem_form.validate');
	}

	if (!$mem_form_submit) {
	  # don't show errors or send mail
	}
	elseif (mem_form_error())
	{
		if ($show_error or !$show_input)
		{
			$out .= mem_form_display_error();

			if (!$show_input) return $out;
		}
	}
	elseif ($show_input and is_array($mem_form))
	{
		if ($mem_glz_custom_fields_plugin) {
			// prep the values
			glz_custom_fields_before_save();
		}

		callback_event('mem_form.spam');

		/// load and check spam plugins/
		$evaluator =& get_mem_form_evaluator();
		$is_spam = $evaluator->is_spam();

		if ($is_spam) {
			return mem_form_gTxt('spam');
		}

		$mem_form_thanks_form = ($thanks_form ? fetch_form($thanks_form) : $thanks);

		safe_update('txp_discuss_nonce', "used = '1', issue_time = now()", "nonce = '$nonce'");

		$result = callback_event('mem_form.submit');

		if (mem_form_error()) {
			$out .= mem_form_display_error();
			$redirect = false;
		}

		$thanks_form = $mem_form_thanks_form;
		unset($mem_form_thanks_form);

		if (!empty($result))
			return $result;

		if (mem_form_error() and $show_input)
		{
			// no-op, reshow form with errors
		}
		else if ($redirect)
		{
			$_POST = array();

			while (@ob_end_clean());
			$uri = hu.ltrim($redirect,'/');
			if (empty($_SERVER['FCGI_ROLE']) and empty($_ENV['FCGI_ROLE']))
			{
				txp_status_header('303 See Other');
				header('Location: '.$uri);
				header('Connection: close');
				header('Content-Length: 0');
			}
			else
			{
				$uri = htmlspecialchars($uri);
				$refresh = mem_form_gTxt('refresh');

				if (!empty($redirect_form))
				{
					$redirect_form = fetch_form($redirect_form);

					echo str_replace('{uri}', $uri, $redirect_form);
				}

				if (empty($redirect_form))
				{
					echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>$sitename</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="0;url=$uri" />
</head>
<body>
<a href="$uri">$refresh</a>
</body>
</html>
END;
				}
			}
			exit;
		}
		else {
			return '<div class="memThanks" id="mem'.$mem_form_id.'">' .
				$thanks_form . '</div>';
		}
	}

	if ($show_input)
	{
		$file_accept = (!empty($file_accept) ? ' accept="'.$file_accept.'"' : '');

		$class = htmlspecialchars($class);

		$enctype = !empty($enctype) ? ' enctype="'.$enctype.'"' : '';

		return '<form method="post"'.((!$show_error and $mem_form_error) ? '' : ' id="mem'.$mem_form_id.'"').' class="'.$class.'" action="'.htmlspecialchars(serverSet('REQUEST_URI')).'#mem'.$mem_form_id.'"'.$file_accept.$enctype.'>'.
			( $label ? n.'<fieldset>' : n.'<div>' ).
			( $label ? n.'<legend>'.htmlspecialchars($label).'</legend>' : '' ).
			$out.
			n.'<input type="hidden" name="mem_form_nonce" value="'.$mem_form_nonce.'" />'.
			n.'<input type="hidden" name="mem_form_id" value="'.$mem_form_id.'" />'.
			(!empty($max_file_size) ? n.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$max_file_size.'" />' : '' ).
			callback_event('mem_form.display','',1).
			$form.
			callback_event('mem_form.display').
			( $label ? (n.'</fieldset>') : (n.'</div>') ).
			n.'</form>';
	}

	return '';
}

function mem_form_text($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'break'		=> $mem_form_default_break,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('text'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'class'		=> 'memText',
		'required'	=> 1,
		'size'		=> '',
		'password'	=> 0,
		'format'	=> '',
		'example'	=> '',
		'escape_value'	=> 1,
		'attrs'		=> ''
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$size = intval($size);

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = trim(ps($name));
		$utf8len = preg_match_all("/./su", $value, $utf8ar);
		$hlabel = empty($label) ? htmlspecialchars($name) : htmlspecialchars($label);


		if (strlen($value) == 0 && $required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = true;
		}
		elseif ($required && !empty($format) && !preg_match($format, $value))
		{
			//echo "format=$format<br />value=$value<br />";
			$mem_form_error[] = mem_form_gTxt('invalid_format', array('{label}'=>$hlabel, '{example}'=> htmlspecialchars($example)));
			$isError = true;
		}
		elseif (strlen($value))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = true;
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_form_error[] = mem_form_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = true;
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_form_error[] = mem_form_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = true;
			}

			else
			{
				$isError = false === mem_form_store($name, $label, $value);
			}
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $default;
	}

	$size = ($size) ? ' size="'.$size.'"' : '';
	$maxlength = ($max) ? ' maxlength="'.$max.'"' : '';

	$isError = $isError ? "errorElement" : '';

	$memRequired = $required ? 'memRequired' : '';
	$class = htmlspecialchars($class);

	if ($escape_value)
	{
		$value = htmlspecialchars($value);
	}

    return '<label for="'.$name.'" class="'.$class.' '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<input type="'.($password ? 'password' : 'text').'" id="'.$name.'" class="'.$class.' '.$memRequired.$isError.'" name="'.$name.'" value="'.$value.'"'.$size.$maxlength.
		( !empty($attrs) ? ' ' . $attrs : '').' />';
}


function mem_form_file($atts)
{
	global $mem_form_submit, $mem_form_error, $mem_form_default, $file_max_upload_size, $tempdir, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'break'		=> $mem_form_default_break,
		'isError'	=> '',
		'label'		=> mem_form_gTxt('file'),
		'name'		=> '',
		'class'		=> 'memFile',
		'size'		=> '',
		'accept'	=> '',
		'no_replace' => 1,
		'max_file_size'	=> $file_max_upload_size,
		'required'	=> 1,
		'default'	=> FALSE,
	), $atts));

	$fname = ps('file_'.$name);
	$frealname = ps('file_info_'.$name.'_name');
	$ftype = ps('file_info_'.$name.'_type');

	if (empty($name)) $name = mem_form_label2name($label);

	$out = '';

	if ($mem_form_submit)
	{
		if (!empty($fname))
		{
			// see if user uploaded a different file to replace already uploaded
			if (isset($_FILES[$name]) && !empty($_FILES[$name]['tmp_name']))
			{
				// unlink last temp file
				if (file_exists($fname) && substr_compare($fname, $tempdir, 0, strlen($tempdir), 1)==0)
					unlink($fname);

				$fname = '';
			}
			else
			{
				// pass through already uploaded filename
				mem_form_store($name, $label, array('tmp_name'=>$fname, 'name' => $frealname, 'type' => $ftype));
				$out .= "<input type='hidden' name='file_".$name."' value='".htmlspecialchars($fname)."' />"
						. "<input type='hidden' name='file_info_".$name."_name' value='".htmlspecialchars($frealname)."' />"
						. "<input type='hidden' name='file_info_".$name."_type' value='".htmlspecialchars($ftype)."' />";
			}
		}

		if (empty($fname))
		{
			$hlabel = empty($label) ? htmlspecialchars($name) : htmlspecialchars($label);

			$fname = $_FILES[$name]['tmp_name'];
			$frealname = $_FILES[$name]['name'];
			$ftype = $_FILES[$name]['type'];
			$err = 0;

			switch ($_FILES[$name]['error']) {
				case UPLOAD_ERR_OK:
					if (is_uploaded_file($fname) and $max_file_size >= filesize($fname))
						mem_form_store($name, $label, $_FILES[$name]);
					elseif (!is_uploaded_file($fname)) {
						if ($required) {
							$mem_form_error[] = mem_form_gTxt('error_file_failed', array('{label}'=>$hlabel));
							$err = 1;
						}
					}
					else {
						$mem_form_error[] = mem_form_gTxt('error_file_size', array('{label}'=>$hlabel));
						$err = 1;
					}
					break;

				case UPLOAD_ERR_NO_FILE:
					if ($required) {
						$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
						$err = 1;
					}
					break;

				case UPLOAD_ERR_EXTENSION:
					$mem_form_error[] = mem_form_gTxt('error_file_extension', array('{label}'=>$hlabel));
					$err = 1;
					break;

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$mem_form_error[] = mem_form_gTxt('error_file_size', array('{label}'=>$hlabel));
					$err = 1;
					break;

				default:
					$mem_form_error[] = mem_form_gTxt('error_file_failed', array('{label}'=>$hlabel));
					$err = 1;
					break;
			}

			if (!$err)
			{
				// store as a txp tmp file to be used later
				$fname = get_uploaded_file($fname);
				$err = false === mem_form_store($name, $label, array('tmp_name'=>$fname, 'name' => $frealname, 'type' => $ftype));
				if ($err)
				{
					// clean up file
					@unlink($fname);
				}
				else
				{
					$out .= "<input type='hidden' name='file_".$name."' value='".htmlspecialchars($fname)."' />"
							. "<input type='hidden' name='file_info_".$name."_name' value='".htmlspecialchars($_FILES[$name]['name'])."' />"
							. "<input type='hidden' name='file_info_".$name."_type' value='".htmlspecialchars($_FILES[$name]['type'])."' />";
				}
			}

			$isError = $err ? 'errorElement' : '';
		}
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else if (is_array($default))
			$value = $default;

		if (is_array(@$value))
		{
			$fname = @$value['tmp_name'];
			$frealname = @$value['name'];
			$ftype = @$value['type'];
			$out .= "<input type='hidden' name='file_".$name."' value='".htmlspecialchars($fname)."' />"
				. "<input type='hidden' name='file_info_".$name."_name' value='".htmlspecialchars($frealname)."' />"
				. "<input type='hidden' name='file_info_".$name."_type' value='".htmlspecialchars($ftype)."' />";
		}
	}

	$memRequired = $required ? 'memRequired' : '';
	$class = htmlspecialchars($class);

	$size = ($size) ? ' size="'.$size.'"' : '';
	$accept = (!empty($accept) ? ' accept="'.$accept.'"' : '');


	$field_out = '<label for="'.$name.'" class="'.$class.' '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break;

	if (!empty($frealname) && $no_replace)
	{
		$field_out .= '<div id="'.$name.'">'.htmlspecialchars($frealname) . ' <span id="'.$name.'_ftype">('. htmlspecialchars($ftype).')</span></div>';
	}
	else
	{
		$field_out .= '<input type="file" id="'.$name.'" class="'.$class.' '.$memRequired.$isError.'" name="'.$name.'"' .$size.' />';
	}

  return $out.$field_out;
}

function mem_form_textarea($atts, $thing='')
{
	global $mem_form_error, $mem_form_submit, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'break'		=> $mem_form_default_break,
		'cols'		=> 58,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('textarea'),
		'max'		=> 10000,
		'min'		=> 0,
		'name'		=> '',
		'class'		=> 'memTextarea',
		'required'	=> 1,
		'rows'		=> 8,
		'escape_value'	=> 1,
		'attrs'		=> ''
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$cols = intval($cols);
	$rows = intval($rows);

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen(ltrim($value)))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = true;
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_form_error[] = mem_form_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = true;
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_form_error[] = mem_form_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = true;
			}

			else
			{
				$isError = false === mem_form_store($name, $label, $value);
			}
		}

		elseif ($required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = true;
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else if (!empty($default))
			$value = $default;
		else
			$value = parse($thing);
	}

	$isError = $isError ? 'errorElement' : '';
	$memRequired = $required ? 'memRequired' : '';
	$class = htmlspecialchars($class);

	if ($escape_value)
	{
		$value = htmlspecialchars($value);
	}

	return '<label for="'.$name.'" class="'.$class.' '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<textarea id="'.$name.'" class="'.$class.' '.$memRequired.$isError.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'"'.
		( !empty($attrs) ? ' ' . $attrs : '').'>'.$value.'</textarea>';
}

function mem_form_email($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_from, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('email'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'break'		=> $mem_form_default_break,
		'size'		=> '',
		'class'		=> 'memEmail',
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$email = trim(ps($name));

		if (strlen($email))
		{
			if (!is_valid_email($email))
			{
				$mem_form_error[] = mem_form_gTxt('invalid_email', array('{email}'=>htmlspecialchars($email)));
				$isError = true;
			}
			else
			{
				preg_match("/@(.+)$/", $email, $match);
				$domain = $match[1];

				if (is_callable('checkdnsrr') and checkdnsrr('textpattern.com.','A') and !checkdnsrr($domain.'.','MX') and !checkdnsrr($domain.'.','A'))
				{
					$mem_form_error[] = mem_form_gTxt('invalid_host', array('{domain}'=>htmlspecialchars($domain)));
					$isError = true;
				}
				else
				{
					$mem_form_from = $email;
				}
			}
		}
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$email = $mem_form_default[$name];
		else
			$email = $default;
	}

	return mem_form_text(array(
		'default'	=> $email,
		'isError'	=> $isError,
		'label'		=> $label,
		'max'		=> $max,
		'min'		=> $min,
		'name'		=> $name,
		'required'	=> $required,
		'break'		=> $break,
		'size'		=> $size,
		'class'		=> $class,
	));
}

function mem_form_select_section($atts)
{
	extract(mem_form_lAtts(array(
		'exclude'	=> '',
		'sort'		=> 'name ASC',
		'delimiter'	=> ',',
	),$atts,false));

	if (!empty($exclude)) {
		$exclusion = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$exclude)));
		$exclusion = array_map('strtolower', $exclusion);

		if (count($exclusion))
			$exclusion = join($delimiter, quote_list($exclusion));
	}

	$where = empty($exclusion) ? '1=1' : 'LOWER(name) NOT IN ('.$exclusion.')';

	$sort = empty($sort) ? '' : ' ORDER BY '. doSlash($sort);

	$rs = safe_rows('name, title','txp_section',$where . $sort);

	$items = array();
	$values = array();

	if ($rs) {
		foreach($rs as $r) {
			$items[] = $r['title'];
			$values[] = $r['name'];
		}
	}

	unset($atts['exclude'], $atts['sort']);

	$atts['items'] = join($delimiter, $items);
	$atts['values'] = join($delimiter, $values);

	return mem_form_select($atts);
}

function mem_form_select_category($atts)
{
	extract(mem_form_lAtts(array(
		'root'	=> 'root',
		'exclude'	=> '',
		'delimiter'	=> ',',
		'type'	=> 'article'
	),$atts,false));

	$rs = getTree($root, $type);

	if (!empty($exclude)) {
		$exclusion = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$exclude)));
		$exclusion = array_map('strtolower', $exclusion);
	}
	else
		$exclusion = array();

	$items = array();
	$values = array();

	if ($rs) {
		foreach ($rs as $cat) {
			if (count($exclusion) && in_array(strtolower($cat['name']), $exclusion))
				continue;

			$items[] = $cat['title'];
			$values[] = $cat['name'];
		}
	}

	unset($atts['root'], $atts['type']);

	$atts['items'] = join($delimiter, $items);
	$atts['values'] = join($delimiter, $values);

	return mem_form_select($atts);
}

function mem_form_select_range($atts)
{
	global $mem_form_default_break;

	$latts = mem_form_lAtts(array(
		'start'		=> 0,
		'stop'		=> false,
		'step'		=> 1,
		'name'		=> '',
		'break'		=> $mem_form_default_break,
		'delimiter'	=> ',',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('option'),
		'first'		=> FALSE,
		'required'	=> 1,
		'select_limit'	=> FALSE,
		'as_csv'	=> FALSE,
		'selected'	=> '',
		'class'		=> 'memSelect',
		'attrs'		=> ''
	), $atts);

	if ($stop === false)
	{
		trigger_error(gTxt('missing_required_attribute', array('{name}' => 'stop')), E_USER_ERROR);
	}

	$step = empty($latts['step']) ? 1 : assert_int($latts['step']);
	$start= assert_int($latts['start']);
	$stop = assert_int($latts['stop']);

	// fixup start/stop based upon step direction
	$start = $step > 0 ? min($start, $stop) : max($start, $stop);
	$stop = $step > 0 ? max($start, $stop) : min($start, $stop);

	$values = array();
	for($i=$start; $i >= $start && $i < $stop; $i += $step)
	{
		array_push($values, $i);
	}

	// intentional trample
	$latts['items'] = $latts['values'] = implode($latts['delimiter'], $values);

	return mem_form_select($latts);
}

function mem_form_select($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'name'		=> '',
		'break'		=> $mem_form_default_break,
		'delimiter'	=> ',',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('option'),
		'items'		=> mem_form_gTxt('general_inquiry'),
		'values'	=> '',
		'first'		=> FALSE,
		'required'	=> 1,
		'select_limit'	=> FALSE,
		'as_csv'	=> FALSE,
		'selected'	=> '',
		'class'		=> 'memSelect',
		'attrs'		=> ''
	), $atts, false));

	if (empty($name)) $name = mem_form_label2name($label);

	if (!empty($items) && $items[0] == '<') $items = parse($items);
	if (!empty($values) && $values[0] == '<') $values = parse($values);

	if ($first !== FALSE) {
		$items = $first.$delimiter.$atts['items'];
		$values = $first.$delimiter.$atts['values'];
	}

	$select_limit = empty($select_limit) ? 1 : assert_int($select_limit);

	$items = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$items)));
	$values = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$values)));
	if ($select_limit > 1)
	{
		$selected = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$seelcted)));
	}
	else
	{
		$selected = array(trim($selected));
	}

	$use_values_array = (count($items) == count($values));

	if ($mem_form_submit)
	{
		if (strpos($name, '[]'))
		{
			$value = ps(substr($name, 0, strlen($name)-2));

			$selected = $value;

			if ($as_csv)
			{
				$value = implode($delimiter, $value);
			}
		}
		else
		{
			$value = trim(ps($name));

			$selected = array($value);
		}

		if (!empty($selected))
		{
			if (count($selected) <= $select_limit)
			{
				foreach ($selected as $v)
				{
					$is_valid = ($use_values_array && in_array($v, $values)) or (!$use_values_array && in_array($v, $items));
					if (!$is_valid)
					{
						$invalid_value = $v;
						break;
					}
				}

				if ($is_valid)
				{
					$isError = false === mem_form_store($name, $label, $value);
				}
				else
				{
					$mem_form_error[] = mem_form_gTxt('invalid_value', array('{label}'=> htmlspecialchars($label), '{value}'=> htmlspecialchars($invalid_value)));
					$isError = true;
				}
			}
			else
			{
				$mem_form_error[] = mem_form_gTxt('invalid_too_many_selected', array(
											'{label}'=> htmlspecialchars($label),
											'{count}'=> $select_limit,
											'{plural}'=> ($select_limit==1 ? mem_form_gTxt('item') : mem_form_gTxt('items'))
										));
				$isError = true;
			}
		}

		elseif ($required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = true;
		}
	}
	else if (isset($mem_form_default[$name]))
	{
		$selected = array($mem_form_default[$name]);
	}

	$out = '';

	foreach ($items as $item)
	{
		$v = $use_values_array ? array_shift($values) : $item;

		$sel = !empty($selected) && in_array($v, $selected);

		$out .= n.t.'<option'.($use_values_array ? ' value="'.$v.'"' : '').($sel ? ' selected="selected">' : '>').
				(strlen($item) ? htmlspecialchars($item) : ' ').'</option>';
	}

	$isError = $isError ? 'errorElement' : '';
	$memRequired = $required ? 'memRequired' : '';
	$class = htmlspecialchars($class);

	$multiple = $select_limit > 1 ? ' multiple="multiple"' : '';

	return '<label for="'.$name.'" class="'.$class.' '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		n.'<select id="'.$name.'" name="'.$name.'" class="'.$class.' '.$memRequired.$isError.'"' . $multiple .
			( !empty($attrs) ? ' ' . $attrs : '').'>'.
			$out.
		n.'</select>';
}

function mem_form_checkbox($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'break'		=> $mem_form_default_break,
		'checked'	=> 0,
		'isError'	=> '',
		'label'		=> mem_form_gTxt('checkbox'),
		'name'		=> '',
		'class'		=> 'memCheckbox',
		'required'	=> 1,
		'attrs'		=> ''
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = (bool) ps($name);

		if ($required and !$value)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = true;
		}

		else
		{
			$isError = false === mem_form_store($name, $label, $value ? gTxt('yes') : gTxt('no'));
		}
	}

	else {
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $checked;
	}

	$isError = $isError ? 'errorElement' : '';
	$memRequired = $required ? 'memRequired' : '';
	$class = htmlspecialchars($class);

	return '<input type="checkbox" id="'.$name.'" class="'.$class.' '.$memRequired.$isError.'" name="'.$name.'"'.
		( !empty($attrs) ? ' ' . $attrs : '').
		($value ? ' checked="checked"' : '').' />'.$break.
		'<label for="'.$name.'" class="'.$class.' '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>';
}


function mem_form_serverinfo($atts)
{
	global $mem_form_submit;

	extract(mem_form_lAtts(array(
		'label'		=> '',
		'name'		=> ''
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	if (strlen($name) and $mem_form_submit)
	{
		if (!$label) $label = $name;
		mem_form_store($name, $label, serverSet($name));
	}
}

function mem_form_secret($atts, $thing = '')
{
	global $mem_form_submit;

	extract(mem_form_lAtts(array(
		'name'	=> '',
		'label'	=> mem_form_gTxt('secret'),
		'value'	=> ''
	), $atts));


	$name = mem_form_label2name($name ? $name : $label);

	if ($mem_form_submit)
	{
		if ($thing)
			$value = trim(parse($thing));
		else
			$value = trim(parse($value));

		mem_form_store($name, $label, $value);
	}

	return '';
}

function mem_form_hidden($atts, $thing='')
{
	global $mem_form_submit, $mem_form_default;

	extract(mem_form_lAtts(array(
		'name'		=> '',
		'label'		=> mem_form_gTxt('hidden'),
		'value'		=> '',
		'isError'	=> '',
		'required'	=> 1,
		'class'		=> 'memHidden',
		'escape_value'	=> 1,
		'attrs'		=> ''
	), $atts));

	$name = mem_form_label2name($name ? $name : $label);

	if ($mem_form_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen($value))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', $hlabel);
				$isError = true;
			}
			else
			{
				$isError = false === mem_form_store($name, $label, $value);
			}
		}
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else if ($thing)
			$value = trim(parse($thing));
	}

	$isError = $isError ? 'errorElement' : '';
	$memRequired = $required ? 'memRequired' : '';

	if ($escape_value)
	{
		$value = htmlspecialchars($value);
	}

	return '<input type="hidden" class="'.$class.' '.$memRequired.$isError.' '.$name
			. '" name="'.$name.'" value="'.$value.'" id="'.$name.'" '.$attrs.'/>';
}

function mem_form_radio($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_values, $mem_form_default, $mem_form_default_break;

	extract(mem_form_lAtts(array(
		'break'		=> $mem_form_default_break,
		'checked'	=> 0,
		'group'		=> '',
		'label'		=> mem_form_gTxt('option'),
		'name'		=> '',
		'class'		=> 'memRadio',
		'isError'	=> '',
		'attrs'		=> '',
		'value'		=> false
	), $atts));

	static $cur_name = '';
	static $cur_group = '';

	if (!$name and !$group and !$cur_name and !$cur_group) {
		$cur_group = mem_form_gTxt('radio');
		$cur_name = $cur_group;
	}
	if ($group and !$name and $group != $cur_group) $name = $group;

	if ($name) $cur_name = $name;
	else $name = $cur_name;

	if ($group) $cur_group = $group;
	else $group = $cur_group;

	$id   = 'q'.md5($name.'=>'.$label);
	$name = mem_form_label2name($name);

	$value = $value === false ? $id : $value;

	if ($mem_form_submit)
	{
		$is_checked = (ps($name) == $value);

		if ($is_checked or $checked and !isset($mem_form_values[$name]))
		{
			$isError = false === mem_form_store($name, $group, $value);
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$is_checked = $mem_form_default[$name] == $value;
		else
			$is_checked = $checked;
	}

	$class = htmlspecialchars($class);

	$isError = $isError ? ' errorElement' : '';

	return '<input value="'.$value.'" type="radio" id="'.$id.'" class="'.$class.' '.$name.$isError.'" name="'.$name.'"'.
		( !empty($attrs) ? ' ' . $attrs : '').
		( $is_checked ? ' checked="checked" />' : ' />').$break.
		'<label for="'.$id.'" class="'.$class.' '.$name.'">'.htmlspecialchars($label).'</label>';
}

function mem_form_submit($atts, $thing='')
{
	global $mem_form_submit;

	extract(mem_form_lAtts(array(
		'button'	=> 0,
		'label'		=> mem_form_gTxt('save'),
		'name'		=> 'mem_form_submit',
		'class'		=> 'memSubmit',
	), $atts));

	$label = htmlspecialchars($label);
	$name = htmlspecialchars($name);
	$class = htmlspecialchars($class);

	if ($mem_form_submit)
	{
		$value = ps($name);

		if (!empty($value) && $value == $label)
		{
			// save the clicked button value
			mem_form_store($name, $label, $value);
		}
	}

	if ($button or strlen($thing))
	{
		return '<button type="submit" class="'.$class.'" name="'.$name.'" value="'.$label.'">'.($thing ? trim(parse($thing)) : $label).'</button>';
	}
	else
	{
		return '<input type="submit" class="'.$class.'" name="'.$name.'" value="'.$label.'" />';
	}
}

function mem_form_lAtts($arr, $atts, $warn=true)
{
	foreach(array('button', 'checked', 'required', 'show_input', 'show_error') as $key)
	{
		if (isset($atts[$key]))
		{
			$atts[$key] = ($atts[$key] === 'yes' or intval($atts[$key])) ? 1 : 0;
		}
	}
	if (isset($atts['break']) and $atts['break'] == 'br') $atts['break'] = '<br />';
	return lAtts($arr, $atts, $warn);
}

function mem_form_label2name($label)
{
	$label = trim($label);
	if (strlen($label) == 0) return 'invalid';
	if (strlen($label) <= 32 and preg_match('/^[a-zA-Z][A-Za-z0-9:_-]*$/', $label)) return $label;
	else return 'q'.md5($label);
}

function mem_form_store($name, $label, $value)
{
	global $mem_form, $mem_form_labels, $mem_form_values;

	$mem_form[$label] = $value;
	$mem_form_labels[$name] = $label;
	$mem_form_values[$name] = $value;

	$is_valid = false !== callback_event('mem_form.store_value', $name);

	// invalid data, unstore it
	if (!$is_valid)
		mem_form_remove($name);

	return $is_valid;
}

function mem_form_remove($name)
{
	global $mem_form, $mem_form_labels, $mem_form_values;

	$label = $mem_form_labels[$name];
	unset($mem_form_labels[$name], $mem_form[$label], $mem_form_values[$name]);
}

function mem_form_display_error()
{
	global $mem_form_error;

	$out = n.'<ul class="memError">';

	foreach (array_unique($mem_form_error) as $error)
	{
		$out .= n.t.'<li>'.$error.'</li>';
	}

	$out .= n.'</ul>';

	return $out;
}

function mem_form_value($atts, $thing)
{
	global $mem_form_submit, $mem_form_values, $mem_form_default;

	extract(mem_form_lAtts(array(
		'name'		=> '',
		'wraptag'	=> '',
		'class'		=> '',
		'attributes'=> '',
		'id'		=> '',
	), $atts));

	$out = '';

	if ($mem_form_submit)
	{
		if (isset($mem_form_values[$name]))
			$out = $mem_form_values[$name];
	}
	else {
		if (isset($mem_form_default[$name]))
			$out = $mem_form_default[$name];
	}

	return doTag($out, $wraptag, $class, $attributes, $id);
}

function mem_form_error($err=NULL)
{
	global $mem_form_error;

	if (!is_array($mem_form_error))
		$mem_form_error = array();

	if ($err == NULL)
		return !empty($mem_form_error) ? $mem_form_error : false;

	$mem_form_error[] = $err;
}

function mem_form_default($key,$val=NULL)
{
	global $mem_form_default;

	if (is_array($key))
	{
		foreach ($key as $k=>$v)
		{
			mem_form_default($k,$v);
		}
		return;
	}

	$name = mem_form_label2name($key);

	if ($val == NULL)
	{
		return (isset($mem_form_default[$name]) ? $mem_form_default[$name] : false);
	}

	$mem_form_default[$name] = $val;

	return $val;
}



function mem_form_mail($from,$reply,$to,$subject,$msg, $content_type='text/plain')
{
	global $prefs;

	if (!is_callable('mail'))
		return false;

	$to = mem_form_strip($to);
	$from = mem_form_strip($from);
	$reply = mem_form_strip($reply);
	$subject = mem_form_strip($subject);
	$msg = mem_form_strip($msg,FALSE);

	if ($prefs['override_emailcharset'] and is_callable('utf8_decode')) {
		$charset = 'ISO-8859-1';
		$subject = utf8_decode($subject);
		$msg     = utf8_decode($msg);
	}
	else {
		$charset = 'UTF-8';
	}

	$subject = mem_form_mailheader($subject,'text');

	$sep = !is_windows() ? "\n" : "\r\n";

	$headers = 'From: '.$from.
		($reply ? ($sep.'Reply-To: '.$reply) : '').
		$sep.'X-Mailer: Textpattern (mem_form)'.
		$sep.'X-Originating-IP: '.mem_form_strip((!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].' via ' : '').$_SERVER['REMOTE_ADDR']).
		$sep.'Content-Transfer-Encoding: 8bit'.
		$sep.'Content-Type: '.$content_type.'; charset="'.$charset.'"';

	return mail($to, $subject, $msg, $headers);
}

function mem_form_mailheader($string, $type)
{
	global $prefs;

	if (!strstr($string,'=?') and !preg_match('/[\x00-\x1F\x7F-\xFF]/', $string)) {
		if ("phrase" == $type) {
			if (preg_match('/[][()<>@,;:".\x5C]/', $string)) {
				$string = '"'. strtr($string, array("\\" => "\\\\", '"' => '\"')) . '"';
			}
		}
		elseif ("text" != $type) {
			trigger_error('Unknown encode_mailheader type', E_USER_WARNING);
		}
		return $string;
	}
	if ($prefs['override_emailcharset']) {
		$start = '=?ISO-8859-1?B?';
		$pcre  = '/.{1,42}/s';
	}
	else {
		$start = '=?UTF-8?B?';
		$pcre  = '/.{1,45}(?=[\x00-\x7F\xC0-\xFF]|$)/s';
	}
	$end = '?=';
	$sep = is_windows() ? "\r\n" : "\n";
	preg_match_all($pcre, $string, $matches);
	return $start . join($end.$sep.' '.$start, array_map('base64_encode',$matches[0])) . $end;
}

function mem_form_strip($str, $header = TRUE) {
	if ($header) $str = strip_rn($str);
	return preg_replace('/[\x00]/', ' ', $str);
}

///////////////////////////////////////////////
// Spam Evaluator
class mem_form_evaluation
{
	var $status;

	function mem_form_evaluation() {
		$this->status = 0;
	}

	function add_status($rating=-1) {
		$this->status += $rating;
	}

	function get_status() {
		return $this->status;
	}

	function is_spam() {
		return ($this->status < 0);
	}
}

function &get_mem_form_evaluator()
{
	static $instance;

	if(!isset($instance)) {
		$instance = new mem_form_evaluation();
	}
	return $instance;
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>mem_form plugin</h1>

	<h2>Summary</h2>

	<p>This plugin provides <span class="caps">HTML</span> form capabilities for other plugins. This allows for consistent form tags and behaviors, while reducing overall plugin size and development time.</p>

	<h2>Author Contact</h2>

	<p><a href="mailto:mmanfre@gmail.com?subject=Textpattern%20mem_form%20plugin" rel="nofollow">Michael Manfre</a><br />
<a href="http://manfre.net" rel="nofollow">http://manfre.net</a></p>

	<h2>License</h2>

	<p>This plugin is licensed under the <a href="http://www.fsf.org/licensing/licenses/info/GPLv2.html" rel="nofollow">GPLv2</a>.</p>

	<h2>Tags</h2>

	<ul>
		<li><a href="#mem_form" rel="nofollow">mem_form</a></li>
		<li><a href="#mem_form_checkbox" rel="nofollow">mem_form_checkbox</a></li>
		<li><a href="#mem_form_email" rel="nofollow">mem_form_email</a></li>
		<li><a href="#mem_form_file" rel="nofollow">mem_form_file</a></li>
		<li><a href="#mem_form_hidden" rel="nofollow">mem_form_hidden</a></li>
		<li><a href="#mem_form_radio" rel="nofollow">mem_form_radio</a></li>
		<li><a href="#mem_form_secret" rel="nofollow">mem_form_secret</a></li>
		<li><a href="#mem_form_select" rel="nofollow">mem_form_select</a></li>
		<li><a href="#mem_form_select_category" rel="nofollow">mem_form_select_category</a></li>
		<li><a href="#mem_form_select_range" rel="nofollow">mem_form_select_range</a></li>
		<li><a href="#mem_form_select_section" rel="nofollow">mem_form_select_section</a></li>
		<li><a href="#mem_form_serverinfo" rel="nofollow">mem_form_serverinfo</a></li>
		<li><a href="#mem_form_submit" rel="nofollow">mem_form_submit</a></li>
		<li><a href="#mem_form_text" rel="nofollow">mem_form_text</a></li>
		<li><a href="#mem_form_textarea" rel="nofollow">mem_form_textarea</a></li>
		<li><a href="#mem_form_value" rel="nofollow">mem_form_value</a></li>
	</ul>

	<h3>mem_form</h3>

	<p>This tag will create an <span class="caps">HTML</span> form and contains all of the processing and validation.</p>

	<ul>
		<li><span>form</span> <span>string</span> Name of a form that will be parsed to display the form.</li>
		<li><span>thanks_form</span> <span>string</span> Name of a form that will be parsed upon successful form submission.</li>
		<li><span>label</span> <span>string</span> Accessible name for the form.</li>
		<li><span>type</span> <span>string</span> Name of the form to identify itself to bound plugin.</li>
		<li><span>thanks</span> <span>string</span> Message to display to user upon successful form submission.</li>
		<li><span>redirect</span> <span>url</span> <span class="caps">URL</span> to redirect upon successful form submission. Overrides &#8220;thanks&#8221; and &#8220;thanks_form&#8221;.</li>
		<li><span>redirect_form</span> <span>string</span> Name of a form that will be parsed as displayed to the user on a redirect. The string &#8220;<em>{uri}</em>&#8221; will be replaced with the redirect url.</li>
		<li><span>enctype</span> <span>string</span> <span class="caps">HTML</span> encoding type used when the form is submitted. <code>enctype=&quot;multipart/form-data&quot;</code> is required when using mem_form_file.</li>
		<li><span>default_break</span> <span>string</span> Separator between label tag and input tag to be used as the default for every mem_form compatible field contained in the form. Default is &lt;br&gt;</li>
	</ul>

	<h3>mem_form_checkbox</h3>

	<p>This will output an <span class="caps">HTML</span> checkbox field.</p>

	<ul>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>checked</span> <span>int</span> Is this box checked. Default &#8220;0&#8221;.</li>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
	</ul>

	<h3>mem_form_email</h3>

	<p>This will output an <span class="caps">HTML</span> text input field and validates the submitted value as an email address.</p>

	<ul>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>default</span> <span>string</span> The default value.</li>
		<li><span>max</span> <span>int</span> Max character length.</li>
		<li><span>min</span> <span>int</span> Min character length.</li>
		<li><span>size</span> <span>int</span> Size of input field.</li>
	</ul>

	<h3>mem_form_file</h3>

	<p>+p(tag-summary). This will output an <span class="caps">HTML</span> file input field. You must add the <code>enctype=&quot;multipart/form-data&quot;</code> attribute to your enclosing mem_form for this to work.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>no_replace</span> <span>int</span> Specifies whether a user can upload another file and replace the existing file that will be submitted on successful completion of the form. If &#8220;1&#8221;, the file input field will be replaced with details about the already uploaded file.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>size</span> <span>int</span> Size of input field.</li>
		<li><span>max_file_size</span> <span>int</span> Maximum size for the uploaded file. Checked server-side.</li>
		<li><span>accept</span> <span>string</span> The <span class="caps">HTML</span> file input field&#8217;s &#8220;accept&#8221; argument that specifies which file types the field should permit.</li>
	</ul>

	<h3>mem_form_hidden</h3>

	<p>This will output an <span class="caps">HTML</span> hidden text input field.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>value</span> <span>string</span> The input value.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>escape_value</span> <span>int</span> Set to &#8220;0&#8221; to prevent html escaping the value. Default &#8220;1&#8221;.</li>
	</ul>

	<h3>mem_form_radio</h3>

	<p>This will output an <span class="caps">HTML</span> radio button.</p>

	<ul>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>group</span> <span>string</span> A name that identifies a group of radio buttons.</li>
		<li><span>value</span> <span>string</span> The value of the radio button. If not set, a unique value is generated.</li>
		<li><span>checked</span> <span>int</span> Is this box checked. Default &#8220;0&#8221;.</li>
	</ul>

	<h3>mem_form_secret</h3>

	<p>This will output nothing in <span class="caps">HTML</span> and is meant to pass information to the sumbit handler plugins.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>value</span> <span>string</span> The input value.</li>
	</ul>

	<h3>mem_form_select</h3>

	<p>This will output an <span class="caps">HTML</span> select field.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>delimiter</span> <span>string</span> List separator. Default &#8220;,&#8221;</li>
		<li><span>items</span> <span>string</span> Delimited list containing a select list display values.</li>
		<li><span>values</span> <span>string</span> Delimited list containing a select list item values.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>selected</span> <span>string</span> The value of the selected item.</li>
		<li><span>first</span> <span>string</span> Display value of the first item in the list. E.g. &#8220;Select a Section&#8221; or &#8220;&#8221; for a blank option.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>select_limit</span> <span>int</span> Specifies the maximum number of items that may be selected. If set to a value greater than 1, a multiselect will be used. The stored value will be an array.</li>
		<li><span>as_csv</span> <span>int</span> If set to 1, the value will be stored as a delimited string of values instead of an array. This does nothing when select_limit is less than 2.</li>
	</ul>

	<h3>mem_form_select_category</h3>

	<p>This will output an <span class="caps">HTML</span> select field populated with the specified Textpattern categories.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>delimiter</span> <span>string</span> List separator. Default &#8220;,&#8221;</li>
		<li><span>items</span> <span>string</span> Delimited list containing a select list display values.</li>
		<li><span>values</span> <span>string</span> Delimited list containing a select list item values.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>selected</span> <span>string</span> The value of the selected item.</li>
		<li><span>first</span> <span>string</span> Display value of the first item in the list. E.g. &#8220;Select a Section&#8221; or &#8220;&#8221; for a blank option.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>exclude</span> <span>string</span> List of item values that will not be included.</li>
		<li><span>sort</span> <span>string</span>  How will the list values be sorted.</li>
		<li><span>type</span> <span>string</span> Category type name. E.g. &#8220;article&#8221;</li>
	</ul>

	<p>h3(tag#mem_form_select_range) . mem_form_select_range</p>

	<p>This will output an <span class="caps">HTML</span> select field populated with a range of numbers.</p>

	<ul>
		<li><span>start</span> <span>int</span> The initial number to include. Default is 0.</li>
		<li><span>stop</span> <span>int</span> The largest/smallest number to include.</li>
		<li><span>step</span> <span>int</span> The increment between numbers in the range. Default is 1.</li>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>delimiter</span> <span>string</span> List separator. Default &#8220;,&#8221;</li>
		<li><span>items</span> <span>string</span> Delimited list containing a select list display values.</li>
		<li><span>values</span> <span>string</span> Delimited list containing a select list item values.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>selected</span> <span>string</span> The value of the selected item.</li>
		<li><span>first</span> <span>string</span> Display value of the first item in the list. E.g. &#8220;Select a Section&#8221; or &#8220;&#8221; for a blank option.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>exclude</span> <span>string</span> List of item values that will not be included.</li>
		<li><span>sort</span> <span>string</span>  How will the list values be sorted.</li>
		<li><span>type</span> <span>string</span> Category type name. E.g. &#8220;article&#8221;</li>
	</ul>

	<h3>mem_form_select_section</h3>

	<p>This will output an <span class="caps">HTML</span> select field populated with the specified Textpattern sections.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>delimiter</span> <span>string</span> List separator. Default &#8220;,&#8221;</li>
		<li><span>items</span> <span>string</span> Delimited list containing a select list display values.</li>
		<li><span>values</span> <span>string</span> Delimited list containing a select list item values.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>selected</span> <span>string</span> The value of the selected item.</li>
		<li><span>first</span> <span>string</span> Display value of the first item in the list. E.g. &#8220;Select a Section&#8221; or &#8220;&#8221; for a blank option.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>exclude</span> <span>string</span> List of item values that will not be included.</li>
		<li><span>sort</span> <span>string</span>  How will the list values be sorted.</li>
	</ul>

	<h3>mem_form_serverinfo</h3>

	<p>This will output no <span class="caps">HTML</span> and is used to pass server information to the plugin handling the form submission.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
	</ul>

	<h3>mem_form_submit</h3>

	<p>This will output either an <span class="caps">HTML</span> submit input field or an <span class="caps">HTML</span> button.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>button</span> <span>int</span> If &#8220;1&#8221;, an html button tag will be used instead of an input tag.</li>
	</ul>

	<h3>mem_form_text</h3>

	<p>This will output an <span class="caps">HTML</span> text input field.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>default</span> <span>string</span> The default value.</li>
		<li><span>format</span> <span>string</span> A regex pattern that will be matched against the input value. You must escape all backslashes &#8216;\&#8217;. E.g &#8220;/\\d/&#8221; is a single digit.</li>
		<li><span>example</span> <span>string</span> An example of a correctly formatted input value.</li>
		<li><span>password</span> <span>int</span> Specifies if the input field is a password field.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>max</span> <span>int</span> Max character length.</li>
		<li><span>min</span> <span>int</span> Min character length.</li>
		<li><span>size</span> <span>int</span> Size of input field.</li>
		<li><span>escape_value</span> <span>int</span> Set to &#8220;0&#8221; to prevent html escaping the value. Default &#8220;1&#8221;.</li>
	</ul>

	<h3>mem_form_textarea</h3>

	<p>This will output an <span class="caps">HTML</span> textarea.</p>

	<ul>
		<li><span>label</span> <span>string</span> Friendly name for the input field. If set, this will output an <span class="caps">HTML</span> &lt;label&gt; tag linked to the input field.</li>
		<li><span>name</span> <span>string</span> Input field name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>break</span> <span>string</span> Separator between label tag and input tag.</li>
		<li><span>default</span> <span>string</span> The default value.</li>
		<li><span>max</span> <span>int</span> Max character length.</li>
		<li><span>min</span> <span>int</span> Min character length.</li>
		<li><span>required</span> <span>int</span> Specifies if input is required.</li>
		<li><span>rows</span> <span>int</span> Number of rows in the textarea.</li>
		<li><span>cols</span> <span>int</span> Number of columns in the textarea.</li>
		<li><span>escape_value</span> <span>int</span> Set to &#8220;0&#8221; to prevent html escaping the value. Default &#8220;1&#8221;.</li>
	</ul>

	<h3>mem_form_value</h3>

	<p>This will output the value associated with a form field. Useful to mix <span class="caps">HTML</span> input fields with mem_form.</p>

	<ul>
		<li><span>id</span> <span>string</span> ID for output wrap tag.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class name.</li>
		<li><span>class</span> <span>string</span> <span class="caps">CSS</span> class.</li>
		<li><span>wraptag</span> <span>string</span> <span class="caps">HTML</span> tag to wrap around the value.</li>
		<li><span>attributes</span> <span>string</span> Additional <span class="caps">HTML</span> tag attributes that should be passed to the output tag.</li>
	</ul>

	<h2>Exposed Functions</h2>

	<h3>mem_form_mail</h3>

	<p>This will send an email message.</p>

	<ul>
		<li><span>Return Value</span> <span>bool</span> Returns true or false, indicating whether the email was successfully given to the mail system. This does not indicate the validity of the email address or that the recipient actually received the email.</li>
		<li><span>from</span> <span>string</span> The From email address.</li>
		<li><span>reply</span> <span>string</span> The Reply To email address.</li>
		<li><span>to</span> <span>string</span> The To email address(es).</li>
		<li><span>subject</span> <span>string</span> The email&#8217;s Subject.</li>
		<li><span>msg</span> <span>string</span> The email message.</li>
	</ul>

	<h3>mem_form_error</h3>

	<p>This will set or get errors associated with the form.</p>

	<ul>
		<li><span>Return Value</span> <span>mixed</span> If err is <span class="caps">NULL</span>, then it will return an array of errors that have been set.</li>
		<li><span>err</span> <span>string</span> An error that will be added to the list of form errors that will be displayed to the form user.</li>
	</ul>

	<h3>mem_form_default</h3>

	<p>This will get or set a default value for a form.</p>

	<ul>
		<li><span>Return Value</span> <span>mixed</span> If <span>val is <span class="caps">NULL</span>, then it will return the default value set for the input field matching %(atts-name)key</span>. If <span>key</span> does not exist, then it will return <span class="caps">FALSE</span>.</li>
		<li><span>key</span> <span>string</span> The name of the input field.</li>
		<li><span>val</span> <span>string</span> If specified, this will be specified as the default value for the input field named &#8220;key&#8221;.</li>
	</ul>

	<h3>mem_form_store</h3>

	<p>This will store the name, label and value for a field in to the appropriate global variables.</p>

	<ul>
		<li><span>name</span> <span>string</span> The name of the field.</li>
		<li><span>label</span> <span>string</span> The label of the field.</li>
		<li><span>value</span> <span>mixed</span> The value of the field.</li>
	</ul>

	<h3>mem_form_remove</h3>

	<p>This will remove the information associated with a field that has been stored.</p>

	<ul>
		<li><span>name</span> <span>string</span> The name of the field.</li>
	</ul>

	<h2>Global Variables</h2>

	<p>This library allows other plugins to hook in to events with the <code>register_callback</code> function.</p>

	<ul>
		<li><span>$mem_form_type</span> <span>string</span> A text value that allows a plugin determine if it should process the current form.</li>
		<li><span>$mem_form_submit</span> <span>bool</span> This specifies if the form is doing a postback.</li>
		<li><span>$mem_form_default</span> <span>array</span> An array containing the default values to use when displaying the form.</li>
		<li><span>$mem_form</span> <span>array</span> An array mapping all input labels to their values.</li>
		<li><span>$mem_form_labels</span> <span>array</span> An array mapping all input names to their labels.</li>
		<li><span>$mem_form_values</span> <span>array</span> An array mapping all input names to their values.</li>
		<li><span>$mem_form_thanks_form</span> <span>string</span> Contains the message that will be shown to the user after a successful submission. Either the &#8220;thanks_form&#8221; or the &#8220;thanks&#8221; attribute. A plugin can modify this value or return a string to over</li>
	</ul>

	<h2>Plugin Events</h2>

	<h3>mem_form.defaults</h3>

	<p>Allows a plugin to alter the default values for a form prior to being displayed.</p>

	<h3>mem_form.display</h3>

	<p>Allows a plugin to insert additional html in the rendered html form tag.</p>

	<h3>mem_form.submit</h3>

	<p>Allows a plugin to act upon a successful form submission.</p>

	<h3>mem_form.spam</h3>

	<p>Allows a plugin to test a submission as spam. The function get_mem_form_evaluator() returns the evaluator.</p>

	<h3>mem_form.store_value</h3>

	<p>On submit, this event is called for each field that passed the builtin checks and was just stored in to the global variables. The callback step is the field name. This callback can be used for custom field validation. If the value is invalid, return <span class="caps">FALSE</span>. Warning: This event is called for each field even if a previously checked field has failed.</p>

	<h3>mem_form.validate</h3>

	<p>This event is called on form submit, after the individual fields are parsed and validated. This event is not called if there are any errors after the fields are validated. Any multi-field or form specific validation should happen here. Use mem_form_error() to set any validation error messages to prevent a successful post.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>