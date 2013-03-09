<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_prefalizer';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.20';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Add, remove, or edit TXP prefs with ease';

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
$plugin['type'] = '1';

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
$plugin['textpack'] = <<< EOT
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
if (@txpinterface == 'admin') {
	global $smd_prefalizer_event, $smd_prefalizer_privs, $smd_prefalizer_types;

	$smd_prefalizer_event = 'smd_prefalizer';
	$smd_prefalizer_privs = '1';
	$smd_prefalizer_types = array(0 => 'basic', 1 => 'advanced', 2 => 'hidden');

	add_privs($smd_prefalizer_event, $smd_prefalizer_privs);
	register_tab("admin", $smd_prefalizer_event, smd_prefalizer_gTxt('tab_name'));
	register_callback('smd_prefalizer', $smd_prefalizer_event);
}

function smd_prefalizer($event, $step) {
	if(!$step or !in_array($step, array(
			'smd_prefalizer_save',
			'smd_prefalizer_delete',
			'smd_prefalizer_change_pageby',
		))) {
		smd_prefalizer_list('');
	} else $step();
}

// List the chosen prefs
function smd_prefalizer_list($msg='') {
	global $smd_prefalizer_event, $smd_prefalizer_types;

	pagetop(smd_prefalizer_gTxt('tab_name'), $msg);
	extract(gpsa(array('step', 'page', 'sort', 'dir', 'crit', 'search_method', 'name', 'value', 'type', 'pref_event', 'html', 'user_name', 'position', 'expose')));
	$msg = ($msg) ? $msg : gps('msg');

	// Handle paging / sorting
	$pageby = (gps('qty')) ? gps('qty') : ((cs('smd_prefalizer_pageby')) ? cs('smd_prefalizer_pageby') : 25);
	$dir = ($dir == 'desc') ? 'desc' : 'asc';
	switch ($sort) {
		case 'value':
			$sort_sql = 'val '.$dir.', name asc';
			break;
		case 'type':
			$sort_sql = 'type '.$dir.', name asc';
			break;
		case 'event':
			$sort_sql = 'event '.$dir.', name asc';
			break;
		case 'html':
			$sort_sql = 'html '.$dir.', name asc';
			break;
		case 'user':
			$sort_sql = 'user_name '.$dir.', name asc';
			break;
		case 'position':
			$sort_sql = 'position '.$dir.', name asc';
			break;
		case 'name':
		default:
			$sort = 'name';
			$sort_sql = 'name '.$dir;
			break;
	}
	$switch_dir = ($dir == 'desc') ? 'asc' : 'desc';
	$criteria = 1;

	if ($search_method && $crit !== false) {
		$crit_escaped = doSlash($crit);
		$critsql = array(
			'name' => "name like '%$crit_escaped%'",
			'value' => "val like '%$crit_escaped%'",
			'type' => "type = " . ((is_numeric($crit_escaped)) ? "$crit_escaped" : ((($crit_number = array_search(strtolower($crit_escaped), $smd_prefalizer_types)) !== false) ? $crit_number : 0)),
			'event' => "event like '%$crit_escaped%'",
			'html' => "html like '%$crit_escaped%'",
			'user' => "user_name like '%$crit_escaped%'",
			'position' => "position = '$crit_escaped'",
		);

		if (array_key_exists($search_method, $critsql)) {
			$criteria = $critsql[$search_method];
			$limit = 1000;
		} else {
			$search_method = '';
			$crit = '';
		}
	} else {
		$search_method = '';
		$crit = '';
	}
	$total = safe_count('txp_prefs', "$criteria");
	$limit = max(@$pageby, 15);
	list($page, $offset, $numPages) = pager($total, $limit, $page);

	$qs = array(
		"event" => $smd_prefalizer_event,
		"page" => $page,
		"sort" => $sort,
		"dir" => $dir,
		"crit" => $crit,
		"search_method" => $search_method,
		"expose" => $expose,
	);

	// List the desired prefs
	$thePrefs = safe_rows('*', 'txp_prefs', "$criteria order by $sort_sql limit $offset, $limit");
	$author_list = safe_column('name', 'txp_users', '1 = 1');
	echo smd_prefalizer_search_form($crit, $search_method);
	$editFocus = ($step == 'edit') ? 'jQuery(function() { jQuery("#smd_prefalizer_value textarea").focus(); });': '';

	// Any edits to an entry are copied to this hidden form and then submitted
	echo <<<EOC
<script type="text/javascript">
function smd_prefalizer_save() {
	var stype = jQuery("#smd_prefalizer_edited #smd_prefalizer_type option:selected").val();
	var utype = jQuery("#smd_prefalizer_edited #smd_prefalizer_user_name option:selected").val();
	jQuery("#smd_prefalizer_input input[name='newname']").val(jQuery("#smd_prefalizer_edited #smd_prefalizer_name input").val());
	jQuery("#smd_prefalizer_input input[name='value']").val(jQuery("#smd_prefalizer_edited #smd_prefalizer_value textarea").val());
	jQuery("#smd_prefalizer_input #smd_prefalizer_new_type option:eq("+stype+")").attr('selected', 'selected');
	jQuery("#smd_prefalizer_input input[name='pref_event']").val(jQuery("#smd_prefalizer_edited #smd_prefalizer_event input").val());
	jQuery("#smd_prefalizer_input input[name='html']").val(jQuery("#smd_prefalizer_edited #smd_prefalizer_html input").val());
	jQuery("#smd_prefalizer_input #smd_prefalizer_newuser option[value='"+utype+"']").attr('selected', 'selected');
	jQuery("#smd_prefalizer_input input[name='position']").val(jQuery("#smd_prefalizer_edited #smd_prefalizer_position input").val());
	jQuery("form[name='smd_prefalizer_input']").trigger("submit");
}
function smd_prefalizer_togglenew() {
	box = jQuery("form[name='smd_prefalizer_input']");
	if (box.css("display") == "none") {
		box.slideDown('fast');
	} else {
		box.slideUp('fast');
	}
	jQuery('#smd_prefalizer_new .smd_focus').focus();
	return false;
}
{$editFocus}
</script>
<style type="text/css">
.smd_hidden {
	display:none;
}
#smd_prefalizer_longform #list tr.data:hover {
	background:#e7e7e7;
}
</style>
EOC;

	$newbtn = '<a class="navlink" href="#" onclick="return smd_prefalizer_togglenew();">'.smd_prefalizer_gTxt('new').'</a>';
	$headings = assHead('name','value', smd_prefalizer_gTxt('visibility'), smd_prefalizer_gTxt('event'), smd_prefalizer_gTxt('html'), smd_prefalizer_gTxt('user'), smd_prefalizer_gTxt('position'), smd_prefalizer_gTxt('actions'));
	echo '<form method="post" name="smd_prefalizer_input" class="smd_hidden" action="'.join_qs($qs).'">';
	echo sInput('smd_prefalizer_save');
	echo hInput('name',(($step=='edit') ? $name : ''));
	echo hInput('user_name',(($step=='edit') ? $user_name : ''));
	echo startTable('smd_prefalizer_new', '', '', 8);
	echo tr($headings);
	echo '<tr id="smd_prefalizer_input">';
	echo td(fInput('text', 'newname', (($step=='edit') ? $name : ''), 'smd_focus'))
	    .td(fInput('text', 'value', (($step=='edit') ? $value : '')))
	    .td(selectInput('type', $smd_prefalizer_types, (($step=='edit') ? $type : 2), false, '', 'smd_prefalizer_new_type'))
	    .td(fInput('text', 'pref_event', (($step=='edit') ? $pref_event : '')))
	    .td(fInput('text', 'html', (($step=='edit') ? $html : 'text_input')))
	    .td(selectInput('newuser', $author_list, (($step=='edit') ? $user_name : ''), true, '', 'smd_prefalizer_newuser'))
	    .td(fInput('text', 'position', (($step=='edit') ? $position : '0')))
	    .td(fInput('submit', '', gTxt('add'), 'smallerbox'));
	echo '</tr>';
	echo endTable();
	echo '</form>';

	// The main list
	echo '<form method="post" name="longform" id="smd_prefalizer_longform" action="'.join_qs($qs).'">';
	echo startTable('list','','',8);
	echo tr(tda($newbtn, ' colspan="8"'));
	echo tr(
		column_head('name', 'name', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('name' == $sort) ? $dir : '').
		column_head('value', 'value', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('value' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('visibility'), 'type', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('type' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('event'), 'event', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('event' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('html'), 'html', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('html' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('user'), 'user', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('user' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('position'), 'position', 'smd_prefalizer', true, $switch_dir, $crit, $search_method, ('position' == $sort) ? $dir : '').
		column_head(smd_prefalizer_gTxt('actions'), 'actions', 'smd_prefalizer', false)
	);

	foreach ($thePrefs as $pref_row) {
		$pref_row['type'] = ($pref_row['type'] > 2) ? 2 : $pref_row['type'];
		if ($step == 'edit' && $pref_row['name'] == $name && $pref_row['user_name'] == $user_name) {
			$btnSave = fInput('button', '', gTxt('Save'), 'publish', '', 'smd_prefalizer_save()');
			echo tr($headings);
			echo tr(
				td(fInput('text', 'name', $pref_row['name']), 70, '', 'smd_prefalizer_name')
				.td(text_area('value', '80', '220', $pref_row['val']), 220, '', 'smd_prefalizer_value')
				.td(selectInput('type', $smd_prefalizer_types, $pref_row['type']), 60, '', 'smd_prefalizer_type')
				.td(fInput('text', 'pref_event', $pref_row['event']), 60, '', 'smd_prefalizer_event')
				.td(fInput('text', 'html', $pref_row['html']), 60, '', 'smd_prefalizer_html')
				.td(selectInput('user_name', $author_list, $pref_row['user_name'], true), 80, '', 'smd_prefalizer_user_name')
				.td(fInput('text', 'position', $pref_row['position']), 20, '', 'smd_prefalizer_position')
				.td($btnSave)
			, ' id="smd_prefalizer_edited"');
		} else {
			$btnEdit = '<a href="'.join_qs($qs).'&#38;step=edit&#38;name='.$pref_row['name'].'&#38;user_name='.$pref_row['user_name'].'">[' . gTxt('edit') . ']</a>';
			$btnDel = smd_prefalizer_can_alter($pref_row['name']) ? '<a href="'.join_qs($qs).'&#38;step=smd_prefalizer_delete&#38;name='.$pref_row['name'].'&#38;user_name='.$pref_row['user_name'].'" onclick="return confirm(\''.smd_prefalizer_gTxt('delete_confirm', array("{name}" => $pref_row['name'], "{user_name}" => (($pref_row['user_name']=='') ? smd_prefalizer_gTxt('all_users') : $pref_row['user_name'] ) )).'\');">[' . gTxt('delete') . ']</a>' : '';
			echo tr(
				td($pref_row['name'],70)
				.td($pref_row['val'], 220)
				.td(ucfirst($smd_prefalizer_types[$pref_row['type']]))
				.td($pref_row['event'])
				.td($pref_row['html'])
				.td($pref_row['user_name'])
				.td($pref_row['position'])
				.td($btnEdit.' '.$btnDel, 100)
			, ' class="data"');
		}
	}

	echo endTable();
	echo '</form>';
	echo n.nav_form($smd_prefalizer_event, $page, $numPages, $sort, $dir, $crit, $search_method);
	echo n.pageby_form($smd_prefalizer_event, $pageby);
}

// -------------------------------------------------------------
function smd_prefalizer_search_form($crit, $method) {
	$methods =	array(
		'name'     => gTxt('name'),
		'value'    => gTxt('value'),
		'type'     => smd_prefalizer_gTxt('visibility'),
		'event'    => smd_prefalizer_gTxt('event'),
		'html'     => smd_prefalizer_gTxt('html'),
		'user'     => smd_prefalizer_gTxt('user'),
		'position' => smd_prefalizer_gTxt('position'),
	);

	return search_form('smd_prefalizer', 'smd_prefalizer_list', $crit, $methods, $method, 'name');
}

// -------------------------------------------------------------
function smd_prefalizer_change_pageby() {
	setcookie('smd_prefalizer_pageby', gps('qty'));
	smd_prefalizer_list('');
}

// -------------------------------------------------------------
function smd_prefalizer_can_alter($name, $expose='') {
	$override = ($expose=='all') ? $expose : gps('expose');
	if ($override == 'all') {
		return true;
	}
	$protected_events = array('admin', 'comments', 'css', 'custom', 'discuss', 'feeds', 'publish');
	$row = safe_row('*', 'txp_prefs', "name='$name'");
	if ($row) {
		return (in_array($row['event'], $protected_events) && empty($row['user_name'])) ? false : true;
	} else {
		return true;
	}
}

// -------------------------------------------------------------
function smd_prefalizer_save($msg='') {
	global $smd_prefalizer_event;

	extract(doSlash(psa(array('name', 'newname', 'value', 'type', 'pref_event', 'html', 'user_name', 'newuser', 'position'))));
	$ret = false;
	$msg = '';

	// State machine-esque decision tree
	$exists = safe_row('name, user_name', 'txp_prefs', "name = '$newname' AND user_name = '$newuser'");
	if (empty($name)) {
		// Insert new
		if ($exists) {
			$msg = array(smd_prefalizer_gTxt('exist_already', array("{name}" => $newname)), E_WARNING);
			$_POST['step'] = 'edit';
		} else {
			$ret = safe_insert('txp_prefs', "prefs_id=1, name='$newname', val='$value', type='$type', event='$pref_event', html='$html', position='$position', user_name='$newuser'");
			if ($ret === false) {
				$msg = smd_prefalizer_gTxt('create_failed', array("{name}" => $newname));
			} else {
				$msg = smd_prefalizer_gTxt('create_ok', array("{name}" => $newname));
			}
		}

	} else if ($name == $newname) {
		// Update
		if ($user_name == $newuser) {
			// Direct update
			if ($exists) {
				$ret = safe_update('txp_prefs', "name='$newname', val='$value', type='$type', event='$pref_event', html='$html', position='$position'", "name='$name' AND user_name='$user_name'");
				if ($ret === false) {
					$msg = smd_prefalizer_gTxt('save_failed', array("{name}" => $newname));
				} else {
					$msg = smd_prefalizer_gTxt('save_ok', array("{name}" => $newname));
				}
			} else {
				$msg = smd_prefalizer_gTxt('exist_not', array("{name}" => $name));
			}
		} else {
			// Renamed user
			if ($exists) {
				$msg = array(smd_prefalizer_gTxt('exist_already', array("{name}" => $newname)), E_WARNING);
				$_POST['step'] = 'edit';
				$_POST['name'] = $name;
				$_POST['user_name'] = $user_name;
			} else {
				$ret = safe_update('txp_prefs', "name='$newname', val='$value', type='$type', event='$pref_event', html='$html', position='$position', user_name='$newuser'", "name='$name' AND user_name='$user_name'");
				if ($ret === false) {
					$msg = smd_prefalizer_gTxt('save_failed', array("{name}" => $newname));
				} else {
					$msg = smd_prefalizer_gTxt('save_ok', array("{name}" => $newname));
				}
			}
		}

	} else {
		// Update and rename
		if ($user_name == $newuser) {
			// Renamed update
			if ($exists) {
				$msg = smd_prefalizer_gTxt('exist_already', array("{name}" => $newname));
			} else {
				$ret = safe_update('txp_prefs', "name='$newname', val='$value', type='$type', event='$pref_event', html='$html', position='$position'", "name='$name' AND user_name='$user_name'");
				if ($ret === false) {
					$msg = smd_prefalizer_gTxt('save_failed', array("{name}" => $newname));
				} else {
					$msg = smd_prefalizer_gTxt('save_ok', array("{name}" => $newname));
				}
			}
		} else {
			// Renamed update and user
			if ($exists) {
				$msg = array(smd_prefalizer_gTxt('exist_already', array("{name}" => $newname)), E_WARNING);
				$_POST['step'] = 'edit';
				$_POST['name'] = $name;
				$_POST['user_name'] = $user_name;
			} else {
				$ret = safe_update('txp_prefs', "name='$newname', val='$value', type='$type', event='$pref_event', html='$html', position='$position', user_name='$newuser'", "name='$name' AND user_name='$user_name'");
				if ($ret === false) {
					$msg = smd_prefalizer_gTxt('save_failed', array("{name}" => $newname));
				} else {
					$msg = smd_prefalizer_gTxt('save_ok', array("{name}" => $newname));
				}
			}
		}
   }

	smd_prefalizer_list($msg);
}

// -------------------------------------------------------------
function smd_prefalizer_delete($msg='') {
	global $smd_prefalizer_event;

	extract(doSlash(gpsa(array('name', 'user_name', 'expose'))));
	$ret = false;
	$rs = safe_row('*', 'txp_prefs', "name = '$name' AND user_name = '$user_name'");
	if ($rs && smd_prefalizer_can_alter($name, $expose)) {
		extract($rs);
		$ret = safe_delete('txp_prefs', "name = '$name' AND user_name='$user_name'");
	}
	if ($ret === false) {
		smd_prefalizer_list(smd_prefalizer_gTxt('delete_failed', array("{name}" => $name)));
	} else {
		smd_prefalizer_list(smd_prefalizer_gTxt('delete_ok', array("{name}" => $name)));
	}
}

// ------------------------
function smd_prefalizer_get_type($type, $dflt='') {
	$prefType = $dflt;

	switch ($type) {
		case '0':
		case 'basic':
			$prefType = PREF_BASIC;
			break;
		case '1':
		case 'advanced':
			$prefType = PREF_ADVANCED;
			break;
		case '2':
		case 'hidden':
			$prefType = PREF_HIDDEN;
			break;
	}

	return $prefType;
}

//************
// PUBLIC TAGS
//************
function smd_pref_get($atts, $thing = NULL) {

	extract(lAtts(array(
		'name'       => '',
		'value'      => '',
		'visibility' => '',
		'event'      => '',
		'input_ctrl' => '',
		'position'   => '',
		'author'     => '',
		'form'       => '',
		'sort'       => 'name asc',
		'wraptag'    => '',
		'break'      => '',
		'class'      => '',
		'html_id'    => '',
		'debug'      => 0,
	),$atts));

	$attempt_content = isset($atts['form']) || $thing;
	$content = (empty($form)) ? $thing : fetch_form($form);
	$authors = do_list($author);
	if (($pos = array_search('SMD_PREF_LOGGED_IN', $authors)) !== false) {
		// Leave SMD_PREF_LOGGED_IN in the array so the tag returns nothing if you've tried
		// to find a specific user (otherwise it would return all unassigned entries)
		$authorInfo = is_logged_in();
		if (isset($authorInfo['name'])) {
			$authors[] = $authorInfo['name'];
		}
	}
	$author = join(',', $authors);

	$visibility = smd_prefalizer_get_type($visibility);
	$crit = array();
	$colmap = array(
		'name'       => 'name',
		'value'      => 'val',
		'visibility' => 'type',
		'event'      => 'event',
		'input_ctrl' => 'html',
		'position'   => 'position',
		'author'     => 'user_name',
	);

	foreach ($colmap as $key => $col) {
		if ($$key !== '') {
			$crit[$key] = "$col IN ('" . join("','", do_list(doSlash($$key))) . "')";
		}
	}

	$orderby = ($sort) ? ' ORDER BY '.doSlash($sort) : '';

	$out = '';
	$rs = array();
	if ($crit) {
		$rs = safe_rows('*', 'txp_prefs', join(' AND ', $crit) . $orderby, $debug);
		$total = count($rs);
		$ctr = 0;
		if ($rs) {
			$replacements = array();
			$content = ($content) ? $content : (($attempt_content) ? '' : '{smd_pref_value}');
			foreach ($rs as $row) {
				foreach ($colmap as $key => $col) {
					$replacements['{smd_pref_'.$key.'}'] = $row[$col];
				}
				$replacements['{smd_pref_total}'] = $total;
				$replacements['{smd_pref_index}'] = $ctr;
				$replacements['{smd_pref_count}'] = $ctr+1;
				$out[] = parse(EvalElse(strtr($content, $replacements), 1));
				$ctr++;
				if ($debug > 1) {
					echo '++ REPLACEMENTS ++';
					dmp($replacements);
				}
			}
			return dowrap($out, $wraptag, $break, $class, '', '', '', $html_id);
		}
	}
	return parse(EvalElse($content, 0));
}

// ------------------------
function smd_pref_set($atts, $thing = NULL) {
	global $txp_user;

	extract(lAtts(array(
		'name'       => '',
		'value'      => '',
		'visibility' => '',
		'event'      => '',
		'input_ctrl' => 'text_input',
		'position'   => '',
		'private'    => 0,
		'debug'      => 0,
	),$atts));

	$visibility = smd_prefalizer_get_type($visibility, 2);
	if ($private) {
		$scope = PREF_PRIVATE;
		$ili = is_logged_in();
		if ($ili) {
			$txp_user = $ili['name'];
		}
	} else {
		$scope = PREF_GLOBAL;
	}

	$ret = false;
	if (smd_prefalizer_can_alter($name)) {
		$ret = set_pref($name, $value, $event, $visibility, $input_ctrl, $position, $scope);
	}
	if ($debug) {
		echo '++ SET RESULT FOR ' . $name . ': ' .(($ret) ? 'SUCCESS' : 'FAIL'). ' ++';
	}
	return parse(EvalElse($thing, $ret));
}

// ------------------------
function smd_pref_delete($atts, $thing = NULL) {

	extract(lAtts(array(
		'name'   => '',
		'event'  => '',
		'author' => '',
		'debug'  => '',
	),$atts));

	$authors = do_list($author);
	if (($pos = array_search('SMD_PREF_LOGGED_IN', $authors)) !== false) {
		$authorInfo = is_logged_in();
		if (isset($authorInfo['name'])) {
			$authors[] = $authorInfo['name'];
		}
	}
	$author = join(',', $authors);

	$crit = array();
	$colmap = array(
		'name'   => 'name',
		'event'  => 'event',
		'author' => 'user_name',
	);

	foreach ($colmap as $key => $col) {
		if ($$key !== '') {
			$crit[$key] = "$col IN ('" . join("','", do_list(doSlash($$key))) . "')";
		}
	}

	$out = array();
	if ($crit) {
		$rs = safe_rows('*', 'txp_prefs', join(' AND ', $crit), $debug);
		$total = count($rs);
		$replacements = array();
		$ctr = 0;
		foreach ($rs as $row) {
			$replacements['{smd_pref_name}'] = $row['name'];
			$replacements['{smd_pref_value}'] = $row['val'];
			$replacements['{smd_pref_visibility}'] = $row['type'];
			$replacements['{smd_pref_event}'] = $row['event'];
			$replacements['{smd_pref_input_ctrl}'] = $row['html'];
			$replacements['{smd_pref_position}'] = $row['position'];
			$replacements['{smd_pref_author}'] = $row['user_name'];
			$replacements['{smd_pref_total}'] = $total;
			$replacements['{smd_pref_index}'] = $ctr;
			$replacements['{smd_pref_count}'] = $ctr+1;
			if (smd_prefalizer_can_alter($row['name'])) {
				$ret = safe_delete('txp_prefs', "name = '" . doSlash($row['name']) . "' AND user_name='" . doSlash($row['user_name']) . "'", $debug);
				$retCode = $ret;
			} else {
				$ret = 0;
				$retCode = -1;
			}

			$replacements['{smd_pref_del_result}'] = $retCode;

			if ($debug > 1) {
				echo '++ REPLACEMENTS ++';
				dmp($replacements);
			}

			$out[] = parse(EvalElse(strtr($thing, $replacements), $ret));

			if ($debug > 2) {
				dmp('++ DELETE RESULT FOR '.$row['name'].': ' . (($retCode==1) ? 'SUCCESS' : ( ($retCode==-1) ? 'DISALLOWED' : 'FAIL') ) .' ++');
			}
			$ctr++;
		}
	}
	return join(n, $out);
}

// ------------------------
// Plugin-specific replacement strings - localise as required
function smd_prefalizer_gTxt($what, $atts = array()) {
	$lang = array(
		'actions' => 'Actions',
		'all_users' => 'all users',
		'create_failed' => 'Pref {name} NOT created',
		'create_ok' => 'Pref {name} created',
		'delete_confirm' => 'Really delete pref {name} ({user_name})?',
		'delete_failed' => 'Pref {name} NOT deleted',
		'delete_ok' => 'Pref {name} deleted',
		'exist_already' => 'Pref {name} already exists. Choose another name/user',
		'exist_not' => 'Cannot update: pref {name} does not exist',
		'event' => 'Event',
		'html' => 'Input control',
		'new' => 'New pref',
		'position' => 'Position',
		'save_failed' => 'Pref {name} NOT saved',
		'save_ok' => 'Pref {name} saved',
		'tab_name' => 'Prefalizer',
		'user' => 'User',
		'visibility' => 'Visibility',
	);
	return strtr($lang[$what], $atts);
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
#smd_help { line-height:1.5 ;}
#smd_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #FFFFCC;}
#smd_help code.block { font-weight:normal; border:1px dotted #999; background-color: #f0e68c; display:block; margin:10px 10px 20px; padding:10px; }
#smd_help h1 { color: #369; font: 20px Georgia, sans-serif; margin: 0; text-align: center; }
#smd_help h2 { border-bottom: 1px solid black; padding:10px 0 0; color: #369; font: 17px Georgia, sans-serif; }
#smd_help h3 { color: #275685; font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0;text-transform: uppercase; text-decoration:underline;}
#smd_help h4 { font: bold 11px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0 ;text-transform: uppercase; }
#smd_help .atnm { font-weight:bold; color:#33d; }
#smd_help .mand { background:#eee; border:1px dotted #999; }
#smd_help table {width:90%; text-align:center; padding-bottom:1em;}
#smd_help td, #smd_help th {border:1px solid #999; padding:.5em 0;}
#smd_help ul { list-style-type:square; }
#smd_help .important {color:red;}
#smd_help li { margin:5px 20px 5px 30px; }
#smd_help .break { margin-top:5px; }
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="smd_help">

	<h1>smd_prefalizer</h1>

	<p>With the advent of per-user preferences, your prefs table could become rather large. Keep it under control with this plugin. Also useful if you have uninstalled plugins you no longer use that have left prefs behind.</p>

	<h2>Features</h2>

	<ul>
		<li>Add, edit or remove <span class="caps">TXP</span> preferences set in your database</li>
		<li>Filter prefs by name / value / user / type to help find the ones you want</li>
		<li>Create regular or hidden prefs, for all or specific users</li>
		<li>Create, display or delete prefs from your public site</li>
	</ul>

	<h2>Author / credits</h2>

	<p>Written by <a href="http://stefdawson.com/contact">Stef Dawson</a>.</p>

	<h2>Installation / uninstallation</h2>

	<p>Download the plugin from either <a href="http://textpattern.org/plugins/1129/smd_prefalizer">textpattern.org</a>, or the <a href="http://stefdawson.com/sw">software page</a>, paste the code into the <span class="caps">TXP</span> Admin -&gt; Plugins pane, install and enable the plugin. Visit the <a href="http://forum.textpattern.com/viewtopic.php?id=32272">forum thread</a> for more info or to report on the success or otherwise of the plugin.</p>

	<p>To uninstall, delete from the Admin -&gt; Plugins page.</p>

	<h2>Usage &#8212; admin side</h2>

	<p>Visit the Admin =&gt; Prefalizer page to see a list of all defined preferences in your <span class="caps">TXP</span> database.</p>

	<p>Each row has actions in the last column that allow you to edit or delete any preference. Some (system) prefs cannot be deleted because that would potentially hurt your site; the plugin tries to limit you to only being able to delete per-user or plugin prefs, but you should still exercise caution.</p>

	<p>The columns are as follows:</p>

	<ul>
		<li><span class="atnm">Name</span> : The name of the preference</li>
		<li><span class="atnm">Value</span> : The stored contents of the preference</li>
		<li><span class="atnm">Visibility</span> : Whether the preference appears on the <em>Basic Prefs</em> or <em>Advanced Prefs</em> subtab. If it does not appear anywhere at all, it is <em>Hidden</em> and is usually used by <span class="caps">TXP</span> or a plugin to keep track of things</li>
		<li><span class="atnm">Event</span> : The event (or admin-side tab) to which this pref is associated. Useful for grouping pref values together on-screen</li>
		<li><span class="atnm">Input control</span> : If the preference is rendered on-screen then this governs which UI widget is drawn or how the control will behave. Common entries in this column are:
	<ul>
		<li><span class="atnm">text_input</span> : (default) A standard text box</li>
		<li><span class="atnm">custom_set</span> : A custom field</li>
		<li><span class="atnm">dateformats</span> : Some kind of date formatting string</li>
		<li><span class="atnm">yesnoradio</span> : A yes or no radio button set</li>
	</ul></li>
		<li><span class="atnm">User</span> : Login name to whom the preference belongs</li>
		<li><span class="atnm">Position</span> : When listing preferences on-screen, the position dictates their order. Lowest numbered values are displayed first. It is common practice to assign positions in increments of 10 or 20 so there is room to easily insert values later</li>
		<li><span class="atnm">Actions</span> : Actions to perform on each preference:
	<ul>
		<li><span class="atnm">[Edit]</span> : Edit the various details of the preference. Input controls appear inline for you to alter the values. When you are done, click <em>Save</em></li>
		<li><span class="atnm">[Delete]</span> : Permanently remove the preference value from your database. You will have a chance to confirm your intentions. Be careful!</li>
	</ul></li>
	</ul>

	<p>Use the search feature to limit the list to the matched items. When searching by <code>Visibility</code> you can either use <code>basic</code>, <code>advanced</code> or <code>hidden</code>, or use their numeric counterparts 0, 1 or 2 respectively.</p>

	<p>Click <em>New pref</em> to add a preference value to the database. A row of input controls will slide down allowing you to enter your desired values. Click <em>Add</em> to commit the new preference.</p>

	<h3>Notes</h3>

	<ul>
		<li>If you try to edit a preference and rename it to that of an existing preference owned by someone else, the plugin will warn you and your edit will be reverted to its original state</li>
		<li>If you try to create a preference value that would clobber an existing setting, the plugin will warn you. If you click <em>New pref</em> again though, your most recently entered values will still be available. The name and user will be reset, however</li>
		<li>If you really, really, know what you are doing and want to delete a &#8216;protected&#8217; pref setting (e.g. <code>rvm_css_dir</code> is one such entry) then simply add <code>&amp;expose=all</code> to the <span class="caps">URL</span>. The delete button will become available for all pref settings. Be careful! As a precaution, when you change page or alter the search/sort criteria the plugin will revert to &#8216;safe&#8217; mode and you will have to add <code>&amp;expose=all</code> to the <span class="caps">URL</span> again to notify the plugin of your intentions</li>
	</ul>

	<h2>Usage &#8212; tags</h2>

	<h3><code>&lt;txp:smd_pref_set /&gt;</code></h3>

	<p>Set a preference value. Attributes:</p>

	<ul>
		<li><span class="atnm">name</span> : the name of the preference you wish to set. If it exists it will be updated, if it doesn&#8217;t exist it will be created. <em>Note that you cannot create or update a preference with the same name as one of the system preferences</em> &#8212; use the admin interface for that</li>
		<li><span class="atnm">value</span> : the value to assign to the named preference</li>
		<li><span class="atnm">event</span><sup class="footnote"><a href="#fn1968631644c2928d2a1b35">1</a></sup> : a group to which you wish to assign your preference. It could be the event of an admin-side tab (e.g. &#8216;file&#8217;) or an entry of your own choosing. It is recommended you stay away from the name of existing events unless you know what you&#8217;re doing! You can leave this blank, but it&#8217;s advisable to set it to something</li>
		<li><span class="atnm">visibility</span><sup class="footnote"><a href="#fn1968631644c2928d2a1b35">1</a></sup> : one of <code>basic</code>, <code>advanced</code> or <code>hidden</code>. Default: <code>hidden</code>. If you set it to basic or advanced, your preference value will appear on the relevant basic/advanced Preferences page</li>
		<li><span class="atnm">input_ctrl</span><sup class="footnote"><a href="#fn1968631644c2928d2a1b35">1</a></sup> : the type of input control your preference requires. <code>text_input</code> is the default but you can set this to whatever makes sense for your app (e.g. <span class="caps">TXP</span> uses <code>yesnoradio</code> for yes/no radio choices)</li>
		<li><span class="atnm">position</span><sup class="footnote"><a href="#fn1968631644c2928d2a1b35">1</a></sup> : the position (order) you want your pref to appear in. Takes an integer value and the prefs will be displayed in ascending order, grouped by <code>event</code></li>
		<li><span class="atnm">private</span><sup class="footnote"><a href="#fn1968631644c2928d2a1b35">1</a></sup> : by default this is 0 which means the preference is available to everyone. If you set this to 1 then the plugin will attempt to make a private (per-user) preference. If you&#8217;re not logged in it won&#8217;t have any effect and the preference will <strong>not</strong> be created</li>
	</ul>

	<p id="fn1968631644c2928d2a1b35" class="footnote"><sup>1</sup> Note that these attributes can only be set when the preference is created for the first time. Once the pref is made, these attributes will be ignored and you can only update the <code>value</code> of the pref.</p>

	<p>The tag works with <code>&lt;txp:else /&gt;</code> so you can, for example, report the success or failure of the set operation.</p>

	<h3><code>&lt;txp:smd_pref_get /&gt;</code></h3>

	<p>Retrieve one or more preference values. Attributes:</p>

	<ul>
		<li><span class="atnm">name</span> : a comma separated list of preference names to retrieve</li>
		<li><span class="atnm">value</span> : a list of preference values to extract. Useful if using one <code>name</code> to check if the preference is set to this particular value</li>
		<li><span class="atnm">event</span> : a list of preference events to retrieve</li>
		<li><span class="atnm">visibility</span> : one of either <code>basic</code>, <code>advanced</code> or <code>hidden</code> to retrieve that type of preference</li>
		<li><span class="atnm">input_ctrl</span> : filter by this list of input controls</li>
		<li><span class="atnm">position</span> : only show preferences that are set to appear at this list of positions in the table</li>
		<li><span class="atnm">author</span> : list of author (login) names from which you wish to retrieve (per-user) preferences. If you specify the special value <code>SMD_PREF_LOGGED_IN</code> then the current logged in user&#8217;s name (if available) will be considered. You can use this to your advantage to only display a user&#8217;s preferences when they&#8217;re logged in and visit the public site</li>
		<li><span class="atnm">sort</span> : order the returned prefs by these columns and sort directions. Default: <code>name asc</code>. Choose from:
	<ul>
		<li><strong>name</strong> : pref name</li>
		<li><strong>val</strong> : pref value. <em><span class="caps">NOTE</span>: it&#8217;s <code>val</code> not <code>value</code></em></li>
		<li><strong>event</strong> : pref event group</li>
		<li><strong>type</strong> : pref type (visibility)</li>
		<li><strong>html</strong> : input control</li>
		<li><strong>position</strong> : list position</li>
		<li><strong>user_name</strong> : pref owner&#8217;s login name</li>
	</ul></li>
		<li><span class="atnm">form</span> : use this form to display each preference value. If unset, the container will be used. If both are empty the plugin will display nothing. Note that if you use the tag as a self-closing tag, <em>the value(s) will be displayed</em></li>
		<li><span class="atnm">wraptag</span> : the (X)HTML tag (without <code>&lt;&gt;</code> brackets) in which to wrap the displayed list of preferences</li>
		<li><span class="atnm">break</span> : the (X)HTML tag (without <code>&lt;&gt;</code> brackets) in which to wrap each preference</li>
		<li><span class="atnm">class</span> : the <span class="caps">CSS</span> class name to apply to the wraptag</li>
		<li><span class="atnm">html_id</span> : the <span class="caps">HTML</span> <span class="caps">DOM</span> node ID to apply to the wraptag</li>
	</ul>

	<p>The tag works with <code>&lt;txp:else /&gt;</code> so you can, for example, detect if a preference exists or has a certain value and then update or create it if it doesn&#8217;t match.</p>

	<h4>Replacement variables</h4>

	<p>In your container or form you may use the following replacement variables to display the various values from each returned preference:</p>

	<ul>
		<li><span class="atnm">{smd_pref_name}</span> : the preference name</li>
		<li><span class="atnm">{smd_pref_value}</span> : the preference value</li>
		<li><span class="atnm">{smd_pref_event}</span> : the event to which the pref belongs</li>
		<li><span class="atnm">{smd_pref_visibility}</span> : the numeric preference flavour (0 = basic; 1=advanced; 2=hidden)</li>
		<li><span class="atnm">{smd_pref_input_ctrl}</span> : the html input control type assigned to the pref</li>
		<li><span class="atnm">{smd_pref_position}</span> : the order / position of the current pref</li>
		<li><span class="atnm">{smd_pref_author}</span> : the owner (author login name) of the preference. Will be empty for global prefs</li>
		<li><span class="atnm">{smd_pref_total}</span> : the total number of preference values that matched the criteria</li>
		<li><span class="atnm">{smd_pref_index}</span> : the current pref count value, counting from 0</li>
		<li><span class="atnm">{smd_pref_counter}</span> : the current pref count value, counting from 1</li>
	</ul>

	<h3><code>&lt;txp:smd_pref_delete /&gt;</code></h3>

	<p>Remove one or more preference values. You may not remove a system preference using this tag: you can only do this if you use the admin interface and indicate that you know what you&#8217;re doing with the <code>expose</code> <span class="caps">URL</span> variable.</p>

	<p>Tag attributes:</p>

	<ul>
		<li><span class="atnm">name</span> : a comma separated list of preference names to remove</li>
		<li><span class="atnm">event</span> : a list of preference events to remove</li>
		<li><span class="atnm">author</span> : remove prefs belonging to this list of author (login) names. If you specify the special value <code>SMD_PREF_LOGGED_IN</code> then the current logged in user&#8217;s name (if available) will be considered</li>
	</ul>

	<p>The tag supports <code>&lt;txp:else /&gt;</code> so you can indicate the success or otherwise of the delete operation on each preference. Inside your container you can use any of the replacement variables as given for the <code>&lt;txp:smd_pref_get&gt;</code> tag. There is one additional replacement: <code>{smd_pref_del_result}</code> is a numeric value indicating the outcome of the delete, as follows:</p>

	<ul>
		<li><strong>-1</strong>: disallowed (it&#8217;s a system pref)</li>
		<li><strong>0</strong>: failed (for some other reason)</li>
		<li><strong>1</strong>: successfully deleted</li>
	</ul>

	<h2>Examples</h2>

	<h3 id="eg1">Example 1: list of preferences by event</h3>

<pre class="block"><code class="block">&lt;txp:smd_pref_get event=&quot;my_event&quot;
     wraptag=&quot;ul&quot; break=&quot;li&quot;&gt;
   {smd_pref_value}
&lt;/txp:smd_pref_get&gt;
</code></pre>

	<h3 id="eg2">Example 2: set pref if it doesn&#8217;t exist; display otherwise</h3>

<pre class="block"><code class="block">&lt;txp:smd_pref_get name=&quot;kermit&quot;&gt;
  Current setting of {smd_pref_name} is {smd_pref_value}
&lt;txp:else /&gt;
   &lt;txp:smd_pref_set name=&quot;kermit&quot; value=&quot;a frog&quot;
     event=&quot;muppets&quot; /&gt;
&lt;/txp:smd_pref_get&gt;
</code></pre>

	<h3 id="eg3">Example 3: set per-user pref if logged in</h3>

<pre class="block"><code class="block">&lt;txp:smd_pref_set name=&quot;list_order&quot;
     value=&quot;asc&quot; event=&quot;control_sys&quot; private=&quot;1&quot; /&gt;
</code></pre>

	<p>If the user is logged in, the <code>list_order</code> will be set. If they are not logged in, nothing will happen. This could be wrapped in a <code>&lt;txp:rvm_if_privileged&gt;</code> call. It could also be wrapepd in an smd_pref_get so it was created only if it didn&#8217;t exist.</p>

	<h3 id="eg4">Example 4: toggle per-user pref</h3>

<pre class="block"><code class="block">&lt;txp:smd_pref_get name=&quot;list_order&quot;
     author=&quot;SMD_PREF_LOGGED_IN&quot;&gt;
   &lt;txp:variable name=&quot;the_order&quot;&gt;{smd_pref_value}&lt;/txp:variable&gt;
   &lt;txp:if_variable name=&quot;the_order&quot; value=&quot;asc&quot;&gt;
      &lt;txp:smd_pref_set name=&quot;list_order&quot; value=&quot;desc&quot; /&gt;
   &lt;txp:else /&gt;
      &lt;txp:smd_pref_set name=&quot;list_order&quot; value=&quot;asc&quot; /&gt;
   &lt;/txp:if_variable&gt;
&lt;/txp:smd_pref_get&gt;
</code></pre>

	<p>If the user is logged in and the <code>list_order</code> is <code>asc</code>, swap it to <code>desc</code>, and vice-versa. If the user&#8217;s not logged in, nothing happens.</p>

	<h2 id="changelog">Changelog</h2>

	<ul>
		<li>06 Nov 09 | 0.10 | Initial release</li>
		<li>07 Nov 09 | 0.11 | Added heading row above row being edited (thanks Uli)</li>
		<li>13 Nov 09 | 0.12 | Added <code>expose</code> <span class="caps">URL</span> override parameter</li>
		<li>27 Jun 10 | 0.20 | Added <code>smd_pref_get</code>, <code>smd_pref_set</code> and <code>smd_pref_delete</code> ; fixed pageby typo (thanks speeke) ; pref types &gt; 2 now considered Hidden ; removed unused code</li>
	</ul>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>