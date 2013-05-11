<?php
// This is a PLUGIN TEMPLATE.
// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mrd_form_values';
$plugin['version'] = '0.1';
$plugin['author'] = 'Dale Chapman';
$plugin['author_uri'] = 'http://chapmancordova.com';
$plugin['description'] = 'Add a tag to output values in a ZCR form';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = '1';

@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

function hak_wine_val($atts, $thing='')
{
	global $zem_contact_values, $thiswineitem;
	
	extract(lAtts(array(
			'name'	=>	'',
			'wraptag'	=>	'',
	),$atts));

	if (empty($name) && is_array($thiswineitem))
		return $thiswineitem['quantity'];
	
	if (!isset($zem_contact_values[$name]))
		return '';
	
	return $zem_contact_values[$name];
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

h1. Adds hak_wine_val tag

p. Add a tag to output values in a ZCR form.

# --- END PLUGIN HELP ---
-->
<?php
}
?>