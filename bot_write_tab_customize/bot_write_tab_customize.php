<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'bot_write_tab_customize';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.7.2';
$plugin['author'] = 'redbot';
$plugin['author_uri'] = 'http://www.redbot.it/txp';
$plugin['description'] = 'Rearrange and style items in the write tab';

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
$plugin['type'] = '3';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '2';

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
//<?
if(@txpinterface == 'admin') {
	add_privs('bot_wtc_tab', '1,2');
	register_tab('extensions', 'bot_wtc_tab', 'Write tab customize');
	register_callback('bot_wtc_tab', 'bot_wtc_tab');
	register_callback('bot_wtc_css','admin_side','head_end');
	register_callback('bot_wtc', 'article');
	register_callback('bot_hide_per_section', 'article');
	register_callback('bot_hidden_sections', 'article');
	register_callback('bot_wtc_update','plugin_lifecycle.bot_write_tab_customize', 'enabled');
}



// ===========================================




$bot_arr_selectors = array(
    'textile_help' => '$("#textile_group")',
    'advanced_options' => '$("#advanced_group")',
    'article_markup' => '$(".markup-body")',
    'excerpt_markup' => '$(".markup-excerpt")',
    'override_default_form' => '$(".override-form")',
    'custom' => '$("#custom_field_group")',
    'article_image' => '$("#image_group")',
    'meta' => '$("#meta_group")',
    'keywords' => '$(".keywords")',
    'url_title' => '$(".url-title")',
    'recent_articles' => '$("#recent_group")',

    'title' => '$(".title")',
    'body' => '$(".body")',
    'excerpt' => '$(".excerpt")',

    'create_new' => '$(".action-create")',
    'page_article_nav_hed' => '$(".nav-tertiary")',
    'status' => '$("#write-status")',
    'sort_display' => '$("#write-sort")',
    'category1' => '$(".category-1")',
    'category2' => '$(".category-2")',
    'section' => '$(".section")',
    'date_settings' => '$("#dates_group")',
    'comments' => '$("#comments_group")',
    'timestamp' => '$("#write-timestamp")',
    'expires' => '$("#write-expires")',
    'publish' => '$("#write-publish")',
    'save' => '$("#write-save")',

    'logged_in_as' => '$("#moniker")',

    'TD Column 1' => '$("#article-col-1")',
    'TD Column 2' => '$("#article-col-2")',
    'TD Main column' => '$("#article-main")',
    'TD !bot!preview!bot! etc.' => '$("#article-tabs")',
);

// creates the translated main plugins array ($bot_items)
global $bot_items;
foreach ( $bot_arr_selectors as $title => $selector ) {
    bot_wtc_insert_in_main_array($title, $selector);
}
natcasesort($bot_items);



// ===========================================================
// Helper functions
// ===========================================================



function bot_wtc_gTxt($what) {

	global $language;

	$en_us = array(
		'install_message' => 'bot_wtc is not yet properly initialized.  Use the button below to create the preferences table.',
		'upgrade_message' => 'bot_wtc must be upgraded. Use the button below to add the new fields to the preferences table.',
		'uninstall' => 'Uninstall',
		'uninstall_message' => 'Using the button below will remove all preferences from the db. <br />Use before a complete uninstall or to reset all preferences. ',
		'uninstall_confirm' => 'Are you sure you want to delete the preferences table?',
		'td_warning' => 'Columns cannot be moved relative to single items and vice-versa',
		'same_item_warning' => 'Oops! You are trying to move an item relative to itself',
		'combo_warning' => 'Oops! You tried to insert an incomplete rule',
		);

	$lang = array(
		'en-us' => $en_us
		);

		$language = (isset($lang[$language])) ? $language : 'en-us';
		$msg = (isset($lang[$language][$what])) ? $lang[$language][$what] : $what;
		return $msg;
}



// ===========================================



function bot_wtc_insert_in_main_array ($title, $selector) // helps build the main array
{
	global $bot_items;
	if (strpos($title, '!bot!'))
	{
		$split_titles = explode("!bot!", $title);
		$title = '';
		for ($i = 0; $i < count($split_titles); $i++)
		{
			$title .= gTxt($split_titles[$i]); // split and build translated title
		}
	}
	else
	{
		$title = gTxt($title);// gets the title to allow translation
	}
	$bot_items [$selector] = gTxt($title);
	return $bot_items;
}



// ===========================================



function bot_wtc_fetch_db() // creates an array of values extracted from the database
{
	if(bot_wtc_check_install()){
		$out = safe_rows('id, item, position, destination, sections, class', 'bot_wtc ','1=1');
		return $out;
	}
}



// ===========================================================



function bot_get_cfs() // creates an array of all cfs for selectInput
{
	$r = safe_rows_start('name, val, html', 'txp_prefs','event = "custom" AND val != ""');
	if ($r) {
		global $arr_custom_fields;
		while ($a = nextRow($r)) {
			$name = str_replace('_set', '', $a['name']);
			$html = $a['html'];
			if ($html == 'checkbox' || $html == 'multi-select') {
				$selector = '$("p:has(*[name=\''.$name.'[]\'])")';
			}
			else
			{
				$selector = '$("p:has(*[name=\''.$name.'\'])")';
			}
			$val = $a['val'];
			$arr_custom_fields[$selector] = $val;
		}
	}
	if ($arr_custom_fields) {
 	natcasesort($arr_custom_fields); // sort cfs - used instead of asort because is case-insensitive
	return $arr_custom_fields;
    }
};



// ===========================================================



function bot_get_sections() // creates an array of all sections for selectInput
{
	$r = safe_rows_start('name, title', 'txp_section','1=1');
	if ($r) {
		while ($a = nextRow($r)) {
			$name = $a['name'];
			$title = $a['title'];
			$sections[$name] = $title;
		}
	}
    natcasesort($sections);
    return $sections;
}



// ===========================================================



function bot_update_button()
{
	return n.'<div class="bot_update_button">' // update button
		.n.eInput('bot_wtc_tab')
		.n.sInput('update')
		.n.fInput('submit', 'update', 'Update', 'publish')
		.'</div>';
}



// ===========================================================



function bot_wtc_is_td($item) // checks if item is a table td
{
    $item = get_magic_quotes_gpc() ? $item : mysql_real_escape_string($item) ;

	if($item == '$(\"#article-col-1\")'
	|| $item == '$(\"#article-col-2\")'
	|| $item == '$(\"#article-main\")'
	|| $item == '$(\"#article-tabs\")'
	)
	{
		return 1;
	}
	return 0;
}



// ===========================================================



function bot_warning($warning) // outputs html for warnings
{
	return graf(hed(bot_wtc_gTxt($warning),'3', ' id="bot_warning"'));
};



//===========================================



function bot_wtc_install()
{
	// figure out what MySQL version we are using (from _update.php)
	$mysqlversion = mysql_get_server_info();
	$tabletype = (intval($mysqlversion[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$mysqlversion))
		? " ENGINE=MyISAM "
		: " TYPE=MyISAM ";
	if (isset($txpcfg['dbcharset']) && (intval($mysqlversion[0]) >= 5 || preg_match('#^4\.[1-9]#',$mysqlversion))) {
		$tabletype .= " CHARACTER SET = ". $txpcfg['dbcharset'] ." ";
	}

	// Create the bot_wtc table
	$bot_wtc = safe_query("CREATE TABLE `".PFX."bot_wtc` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`item` VARCHAR(255) NOT NULL,
		`position` VARCHAR(255)  NOT NULL,
		`destination` VARCHAR(255)  NOT NULL,
		`sections` TEXT  NOT NULL,
		`class` VARCHAR(255)  NOT NULL,
		PRIMARY KEY (`id`)
		) $tabletype");

	set_pref ('bot_wtc_script','', 'bot_wtc_','2'); // entry in txp_prefs table
	set_pref ('bot_wtc_static_sections','', 'bot_wtc_', '2'); // entry in txp_prefs table
}



// ===========================================================



function bot_wtc_update() // updates cfs selectors in db | introduced in bot_wtc 0.7.1
{ 
	if (!bot_wtc_check_install()) { // poceeds only if plugin is already installed
		return;
	}
	safe_alter('bot_wtc', 'CHANGE sections sections TEXT',1);
	$db_values = bot_wtc_fetch_db(); // array
	for ($i =0; $i < count($db_values); $i++) {
		$id = $db_values[$i]['id'];
		$item = $db_values[$i]['item'];
		$destination = $db_values[$i]['destination'];
		// updates cfs
    	if (strpos($item,'custom')) { // if item contains the substring 'custom'
			$cf_number = preg_replace("/[^0-9]/", '', $item); // ditch anything that is not a number
			$type = safe_field('html', 'txp_prefs', 'name = "custom_'.$cf_number.'_set"'); // retrieve cfs type
			if ($type == 'checkbox' || $type == 'multi-select') {
				$selector = '$("p:has(*[name=\'custom_'.$cf_number.'[]\'])")'; // adds the '[]' part
			}
			else
			{
				$selector = '$("p:has(*[name=\'custom_'.$cf_number.'\'])")';
			}
			safe_update('bot_wtc', 'item = "'.doslash($selector).'"', 'id = "'.$id.'"');
     	}
    	if (strpos($destination,'custom')) { // if destination contains the substring 'custom'
			$cf_number = preg_replace("/[^0-9]/", '', $destination); // ditch anything that is not a number
			$type = safe_field('html', 'txp_prefs', 'name = "custom_'.$cf_number.'_set"'); // retrieve cfs type
			if ($type == 'checkbox' || $type == 'multi-select') {
				$selector = '$("p:has(*[name=\'custom_'.$cf_number.'[]\'])")'; // adds the '[]' part
			}
			else
			{
				$selector = '$("p:has(*[name=\'custom_'.$cf_number.'\'])")';
			}
			safe_update('bot_wtc', 'destination = "'.doslash($selector).'"', 'id = "'.$id.'"');
     	}
    }
}


// ===========================================================



function bot_wtc_check_install()
{
	// Check if the bot_wtc table exists
	if (getThings("Show tables like '".PFX."bot_wtc'")) {
		return true;
	}
	return false;
}



//===========================================



function bot_all_items_selectinput() // outputs all items for selectInput() (used for destination dropdown)
{
	global $bot_items;
	$cfs = bot_get_cfs(); // get cfs array in the form: cf_selector => cf_name
	// final values for the txp function selectInput (including cfs if any)
	if (is_array($cfs)) { // if there is at least one custom field set adds cfs to $bot_items array
		$all_items_select = array_merge($cfs, $bot_items);
	}
	else {
		$all_items_select = $bot_items;
	}
	return $all_items_select;
	natcasesort($all_items_select);
}


//===========================================



function bot_contextual_selectinput($current = "") // outputs only 'yet-not-used' items for selectInput() (used for items dropdown)
{
	global $bot_items;
	$db_values = bot_wtc_fetch_db(); // array of values from the db
	$all_items = bot_all_items_selectinput();
	if (bot_wtc_check_install()) {
		$used_items = safe_column('item', 'bot_wtc', '1=1'); // numeric array of item values from the db
		foreach ($all_items as $item => $title) {
	   		if (!in_array($item, $used_items)) {
	 			$items_selectInput[$item] = $title;
	 		}
		}
	}
	else {
		$items_selectInput = $all_items;
	}
    if ($current) { // if the parameter is given adds current value to array
    	$items_selectInput[$current] = $all_items[$current];
    }
	return  $items_selectInput;
}



// ===========================================================
// bot_wtc tab
// ===========================================================



function bot_wtc_output_rows() // outputs the rows for the html table in the bot_wtc_tab
{
	global $bot_items;

	$selectInput_for_position = array('insertBefore'=>'before','insertAfter'=>'after'); // position values for the txp function selectInput
	$db_values = bot_wtc_fetch_db(); // array of values from the db

    $destination_selectInput = bot_all_items_selectinput();
	$items_selectInput = bot_contextual_selectinput();

	// builds rows for new item sections list
	$sections= bot_get_sections(); // get sections array
	$new_item_sections_rows = '';
	foreach ($sections as $key => $value) {
		$new_item_sections_row = '<label>'.checkbox('new_item_sections[]', $key, '0').$value.'</label><br />';
		$new_item_sections_rows .= $new_item_sections_row;
    }
    $new_item_sections_rows .= '<p ><a href="#" class="bot_all">'.gTxt("all").'</a> | <a href="#" class="bot_none">'.gTxt("none").'</a></p>'; // hide all/none

	// new item insertion
	$rows = "";
	$input_row = tr(
		td(selectInput('new_item',bot_contextual_selectinput(), '', '1'), '', 'bot_hilight')
		.td(selectInput('new_item_position', $selectInput_for_position, '', '1'))
		.td(selectInput('new_item_destination',bot_all_items_selectinput(), '', '1'))
		.td('<p><a href="#" class="bot_push">'.gTxt("tag_section_list").'</a></p><div class="bot_collapse">'.$new_item_sections_rows.'</div>')
		.td(finput('text','new_item_class', ''))
		.td()
		);
		$rows .= $input_row;

	// other rows - output if at least one record was already set
	if ($db_values){
		for ($i = 0; $i < count( $db_values ); $i++){
			// data for "sections to show" selectinput - decides wether a section is checked or not
			$bot_hide_in_this_sections_array = explode('|', $db_values[$i]['sections']);
			$item_sections_rows = '';
			foreach ($sections as $key => $value) { // if section is in db mark as checked
			    $checked = in_array($key, $bot_hide_in_this_sections_array) ? '1': '0';
				$item_sections_row =  '<label>'.checkbox('bot_wtc_sections_for_id_'.$db_values[$i]['id'].'[]', $key, $checked).$value.'</label><br />';
				$item_sections_rows .= $item_sections_row;
		    }
		    $item_sections_rows .= '<p><a href="#" class="bot_all">'.gTxt("all").'</a> | <a href="#" class="bot_none">'.gTxt("none").'</a></p>'; // hide all/none
			$single_row = tr(
			td(selectInput('item[]',bot_contextual_selectinput($db_values[$i]['item']), $db_values[$i]['item'],'0'), '', 'bot_hilight')
			.td(selectInput('item_position[]', $selectInput_for_position, $db_values[$i]['position'], '1'))
			.td(selectInput('item_destination[]',bot_all_items_selectinput(), $db_values[$i]['destination'],'1'))
 			.td('<p><a href="#" class="bot_push">'.gTxt("tag_section_list").'</a></p><div class="bot_collapse">'.$item_sections_rows.'</div>')
			.td(finput('text', 'item_class[]', $db_values[$i]['class']))
			.td(checkbox('bot_delete_id[]', $db_values[$i]['id'], '0').'<label for="bot_delete_id"> '.gTxt('delete').'</label>'))
			.hInput('bot_wtc_id[]', $db_values[$i]['id']);

			$rows .= $single_row;
		}
	};
	return $rows;
}



//===========================================



function bot_wtc_static_sections_select()
{
	// builds rows for sections list
	$sections= bot_get_sections(); // get sections array
	$static_sections = safe_field('val', 'txp_prefs', 'name = "bot_wtc_static_sections"'); //  fetch prefs value for bot_wtc_static_sections
	$static_sections = explode('|', $static_sections); // creates an array of statica sections from the string in txp_prefs
    $static_sections_rows = '';
	foreach ($sections as $key => $value) {
	    // if section is in db mark as checked
	    $checked = in_array($key, $static_sections) ? '1': '0';
		$static_sections_row = '<label>'.checkbox('static_sections[]', $key, $checked).$value.'</label><br />';
		$static_sections_rows .= $static_sections_row;
    }
    return $static_sections_rows;
}



//===========================================


function bot_advanced()
{
    global $bot_items;
    $items = bot_all_items_selectinput(); // get items array
    $item_rows = '';
    foreach ($items as $key => $value) {
		$item_row = '<label>'.checkbox('bot_adv_items[]', htmlspecialchars($key), '0').$value.'</label><br />';
		$item_rows .= $item_row;
    $sections= bot_get_sections(); // get sections array
    }
	$sections_rows = '';
	foreach ($sections as $key => $value) {
		$sections_row = '<label>'.checkbox('bot_adv_sections[]', $key, '0').$value.'</label><br />';
		$sections_rows .= $sections_row;
    }
    return '<section role="region" class="txp-details" id="bot_advanced" aria-labelledby="bot_advanced-label">'
        .n.'<h3 id="bot_advanced-label">Advanced/Multiple selection</h3>'
        .n.'<div role="group">'
        .n.form(n.bot_update_button()
        .n.'<div id="bot_adv_items"><h4>Items</h4>'.$item_rows.'</div>' // items list
        .n.'<div  id="bot_adv_hide"><h4>Hide in sections</h4>'.$sections_rows.'<p><a href="#" class="bot_all">'.gTxt("all").'</a> | <a href="#" class="bot_none">'.gTxt("none").'</a></p></div>' // sections list
        .n.'<div  id="bot_adv_class"><h4>Set css class</h4>'.finput('text','bot_adv_class', '').'</div>' // class
        .n.bot_update_button()
        .n.'</div>'
        .n.'</section>'
    );
}

//===========================================



function bot_wtc_tab($event, $step)
{
	global $bot_items;
	$cfs = bot_get_cfs();

	pagetop('Write tab customize '.gTxt('preferences'), ($step == 'update' ? gTxt('preferences_saved') : ''));
	echo hed('Write tab customize','2');

	if ($step == 'install'){
		// Install the preferences table.
		bot_wtc_install();
	}

	if ($step == 'uninstall'){
		//remove table
		safe_query("DROP TABLE ".PFX."bot_wtc");
		safe_delete('txp_prefs', 'event = "bot_wtc_"' );
	}

	if ($step == 'update'){
	    // set function variables
		$new_item = ps('new_item'); //variable
		$new_item_position = ps('new_item_position'); //variable
		$new_item_destination = ps('new_item_destination'); //variable
		$new_item_sections = ps('new_item_sections'); //array
		$new_item_class = ps('new_item_class'); //variable
		$bot_wtc_script = ps('bot_wtc_script'); //variable
		$static_sections = ps('static_sections'); //variable
		$item = ps('item'); //array
		$item_position = ps('item_position'); //array
		$item_destination = ps('item_destination'); //array
		$item_class = ps('item_class'); //array
		$bot_wtc_id = ps('bot_wtc_id'); //array
		$delete_id = ps('bot_delete_id'); //array
		$bot_adv_items = ps('bot_adv_items'); //array
		$bot_adv_sections = ps('bot_adv_sections'); //array
		$bot_adv_class = ps('bot_adv_class'); //variable

		// db update for existing items
		if ($item){ // if at least a saved item exists

           	$db_values = bot_wtc_fetch_db(); // array of values from the db
			for ($i = 0; $i < count($item); $i++){
			    // builds the posted variable name for current item sections
			    $item_posted_sections_name = 'bot_wtc_sections_for_id_'.$db_values[$i]['id'];
			    $item_sections = isset($_POST[$item_posted_sections_name]) ? $_POST[$item_posted_sections_name] : ''; //array
                // builds sections string for current item
				$item_sections_string = $item_sections ? implode('|', $item_sections): '';
				// allowed input data combinations
				if (($item[$i] && $item_destination[$i] && $item_position[$i])
				|| ($item[$i] && $item_class[$i] && !$item_destination[$i] && !$item_position[$i])
				|| ($item[$i] && $item_sections_string && !$item_destination[$i] && !$item_position[$i])) {
					// check if a column is linked with a non-column item BUT ONLY IF both items are set (otherwise couldn't apply i.e. class to a single td)
					if (!((bot_wtc_is_td($item[$i]) XOR bot_wtc_is_td($item_destination[$i])) && $item_destination[$i])){
  					    // check if item is different from destination
						if($item[$i] != $item_destination[$i]){
       						safe_update("bot_wtc",
							"position = '"
							.doslash($item_position[$i])
							."', destination = '"
							.doslash($item_destination[$i])
							."', item = '"
							.doslash($item[$i])
							."', sections = '"
							.doslash($item_sections_string)
							."', class = '"
							.doslash($item_class[$i])
							."'", "id = '".$bot_wtc_id[$i]."'");
						}
						else {
							echo bot_warning('same_item_warning');
						}
					}
					else {
						echo bot_warning('td_warning');
					}
				}
				else {
					echo bot_warning('combo_warning');
				}
			}
		}

		// db insert for new item
		// allowed input combinations
		if (($new_item && $new_item_destination && $new_item_position)
		|| ($new_item && $new_item_class && !$new_item_destination && !$new_item_position)
		|| ($new_item && $new_item_sections && !$new_item_destination && !$new_item_position)){
			// check if a column is linked with a non-column item
			if (!((bot_wtc_is_td($new_item) XOR bot_wtc_is_td($new_item_destination)) &&  $new_item_destination)){
				// check items are not the same
				if($new_item != $new_item_destination){
                    // transforms the sections array in a string
                    $new_item_sections_string = $new_item_sections ? implode('|', $new_item_sections) : '';
					safe_insert("bot_wtc",
					"position = '"
					.doslash($new_item_position)
					."', destination = '"
					.doslash($new_item_destination)
					."', class = '"
					.doslash($new_item_class)
					."', sections = '"
					.doslash($new_item_sections_string)
					."', item = '"
					.doslash($new_item)
					."'");
				}
				else {
					echo bot_warning('same_item_warning');
				}
			}
			else {
				echo bot_warning('td_warning');
			}
		}

		elseif ($new_item || $new_item_destination || $new_item_position || $new_item_class || $new_item_sections){
			echo bot_warning('combo_warning');
		}

		if ($delete_id){ // checks if there is something to delete
			foreach ($delete_id as $id) {
				safe_delete('bot_wtc', 'id ="'.$id.'"' );
			}
		}


		// update advanced prefereces
        if ($bot_adv_items AND ($bot_adv_sections || $bot_adv_class)) { // check if item AND section OR class is selected

            $db_values = bot_wtc_fetch_db(); // first array: all values from db

            if ($bot_adv_sections) {
            	$bot_db_sections = array(); // more specific array: only item => sections
                for ($i =0; $i < count($db_values); $i++) {
                	$bot_db_sections[$db_values[$i]['item']] = $db_values[$i]['sections'];
                }

                foreach ($bot_adv_items as $item) { // iterates posted items
                    // fetch -if any- existing sections from db for current item and merges arrays eliminating duplicates
                    if (is_array($bot_db_sections) AND array_key_exists($item, $bot_db_sections)) {
                       	$db_sect_array = explode('|', $bot_db_sections[$item]);
                        $final_array = array_unique(array_merge($db_sect_array, $bot_adv_sections));
                        $bot_adv_sections_string = implode('|', $final_array); // new sections string
                    }
                    else {
                    	$bot_adv_sections_string = implode('|', $bot_adv_sections);
                    }
                    safe_upsert(
                        "bot_wtc",
    					"sections = '"
    					.doslash($bot_adv_sections_string)
    					."'",
                        "item = '".doslash($item)."'"
                    );
            	}
            }

            if ($bot_adv_class) {
                $bot_db_classes = array(); // more specific array: only item => classes
                for ($i =0; $i < count($db_values); $i++) {
                	$bot_db_classes[$db_values[$i]['item']] = $db_values[$i]['class'];
                }

                foreach ($bot_adv_items as $item) { // iterates posted items
                    // fetch -if any- existing class from db for current item and merges arrays eliminating duplicates
                    if (is_array($bot_db_classes) AND array_key_exists($item, $bot_db_classes)) {
                       	$db_class_array = explode(' ', $bot_db_classes[$item]);
                       	$posted_class_array = explode(' ', $bot_adv_class);
                        $final_array = array_unique(array_merge($db_class_array, $posted_class_array));
                        $bot_adv_classes_string = implode(' ', $final_array); // new sections string
                    }
                    else {
                    	$bot_adv_classes_string = $bot_adv_class;
                    }
                    safe_upsert(
                        "bot_wtc",
    					"class = '"
    					.doslash($bot_adv_classes_string)
    					."'",
                        "item = '".doslash($item)."'"
                    );
            	}
            }
        }
        elseif ($bot_adv_sections || $bot_adv_class) {
        	echo bot_warning('Warning: at least an item must be selected');
        }


		// updates static sections prefs
        if ($static_sections) {
        	$static_sections_string = implode('|', $static_sections);
			safe_update('txp_prefs', 'val= "'.doslash($static_sections_string).'", html="text_input" ', 'name = "bot_wtc_static_sections"' );
        }

        // updates script prefs
		if ($bot_wtc_script) {
	  		safe_update('txp_prefs', 'val= \''.doslash($bot_wtc_script).'\', html=\'textarea\' ', 'name = \'bot_wtc_script\'' );
		}
	}

	if (bot_wtc_check_install()) { // what to show when accessing tab

		$bot_wtc_script = safe_field('val', 'txp_prefs', 'name = "bot_wtc_script"'); // fetch prefs value for bot_wtc_script
		echo n.t.'<div class="txp-layout-textbox">'; // main div
		echo '<p id="bot_controls" class="nav-tertiary">
            <a id="bot_expand_all" class="navlink" href="#">Expand all</a>
            <a id="bot_collapse_all" class="navlink" href="#">Collapse all</a>
            <a id="bot_advanced_open" class="navlink" href="#">Toggle advanced</a>
            </p>';
		echo n.t.bot_advanced();
		echo n.t.'<div id="bot_main">'; // main div

		echo form( // beginning of the form
 			'<table id="bot_wtc_table" class="txp-list">' // beginning of the table
			.'<thead>'
            .tr(hcell(strong(gTxt('Item')))
			.hcell(strong(gTxt('Position')))
			.hcell(strong(gTxt('Destination')))
			.hcell(strong(gTxt('Hide in:')))
			.hcell(strong(gTxt('Class')))
			.hcell() // collapse all/show all)
			).'</thead>'
			.bot_wtc_output_rows() // html rows generated by "bot_wtc_output_rows()"
			.'</table>' // end of the table

            .bot_update_button()

			.n.'<section role="region" class="txp-details" id="bot_static_sections" aria-labelledby="bot_static_sections-label">'  // static sections
			.n.'<h3 id="bot_static_sections-label" class="txp-summary expanded">'
			.n.'<a class="bot_push toggle" role="button" href="#bot_static_sections-details" aria-expanded="true">Hide sections in sections dropdown</a>'
			.n.'</h3>'
			.n.'<div id="bot_static_sections-details" class="bot_collapse">'
			.bot_wtc_static_sections_select()
			.bot_update_button()
			.n.'</div>'
			.n.'</section>'

			.n.'<section role="region" class="txp-details" id="bot_js_box" aria-labelledby="bot_js_box-label">'  // js code box
			.n.'<h3 id="bot_js_box-label" class="txp-summary expanded">'
			.n.'<a class="bot_push toggle" href="#" role="button" href="#bot_js_box-details" aria-expanded="true">Additional js code</a>'
			.n.'</h3>'
			.n.'<div id="bot_js_box-details" class="bot_collapse">'
			.n.'<a id="bot_js_link" href="#">Add external script</a> | <a id="bot_jq_link" href="#">Add Jquery script</a>'
			.n.'<textarea id="bot_wtc_script" name="bot_wtc_script" cols="60" rows="10">'.$bot_wtc_script.'</textarea>' // script textarea
			.n.bot_update_button()
			.n.'</div>'
			.n.'</section>'

		);

		echo n.t.'<section role="region" class="txp-details" id="bot_uninstall" aria-labelledby="bot_uninstall-label">'  // js code box
			.n.'<h3 id="bot_uninstall-label" class="txp-summary expanded">'
			.n.'<a class="bot_push toggle" href="#" role="button" href="#bot_uninstall-details" aria-expanded="true">'.bot_wtc_gTxt('uninstall').'</a>'
			.n.'</h3>'
			.n.'<div id="bot_uninstall-details" class="bot_collapse">'
			.n.t.t.graf(bot_wtc_gTxt('uninstall_message'))
			.n.form(
			n.eInput('bot_wtc_tab')
			.n.sInput('uninstall')
			.n.n.fInput('submit', 'uninstall', 'Uninstall ', 'smallerbox'),"","confirm('".bot_wtc_gTxt('uninstall_confirm')."')"
			)
			.n.'</div>'
			.n.'</section>';
	}

	else { // install button
		echo n.t.'<div  id="bot_install">'.
			n.t.t.hed(gTxt('install'), '1').
			n.graf(bot_wtc_gTxt('install_message')).
			n.n.form(
				n.eInput('bot_wtc_tab').
				n.sInput('install').
				n.n.fInput('submit', 'install', 'Install ', 'publish')
				)
			.'</div>'
			.'</div>';
	}

	// snippets to insert in the script box
	$bot_jquery_snippet = '<script type=\"text/javascript\">\n    $(document).ready(function() {\n        //your code here\n    });\n<\/script>\n';
	$bot_js_snippet = '<script type=\"text/javascript\" src=\"path_to_script\"><\/script>\n';

	echo // add some jquery action
	'<script  type="text/javascript">'.n.
	'	$(document).ready(function() {'.n.
			'$("div.bot_collapse").hide()'.n.
			'$("section#bot_advanced").hide()'.n.
			'$("a.bot_push").click(function(){'.n.
			'  $(this).toggleClass("bot_arrow").parent().next().slideToggle();'.n.
			'  return false;'.n.
			'});'.n.
			'$("#bot_collapse_all").click(function(){'.n.
			'  $("div.bot_collapse").slideUp();'.n.
			'  return false;'.n.
  			 '});'.n.
			'$("#bot_expand_all").click(function(){'.n.
			'  $("div.bot_collapse").slideDown();'.n.
			'  return false;'.n.
  			 '});'.n.
			'$("#bot_advanced_open").click(function(){'.n.
			'  $("section#bot_advanced").slideToggle();'.n.
			'  $("div#bot_main").toggle();'.n.
			'  return false;'.n.
  			 '});'.n.
			'$("a.bot_all").click(function(){'.n.
			'  $(this).parent().parent().find("input").attr("checked", true);'.n.
			'  return false;'.n.
			'});'.n.
			'$("a.bot_none").click(function(){'.n.
			'  $(this).parent().parent().find("input").attr("checked", false);'.n.
			'  return false;'.n.
			'});'.n.
			'$("#bot_jq_link").click(function(){'.n.
			'  var areaValue = $("#bot_wtc_script").val();'.n.
			'  $("#bot_wtc_script").val(areaValue + "'.$bot_jquery_snippet.'");'.n.
			'  return(false);'.n.
  			'});'.n.
			'$("#bot_js_link").click(function(){'.n.
			'  var areaValue = $("#bot_wtc_script").val();'.n.
			'  $("#bot_wtc_script").val(areaValue + "'.$bot_js_snippet.'");'.n.
			'  return(false);'.n.
  			'});'.n.
	'	});'.n.
	'</script>';
}



// ===========================================================
// plugins output
// ===========================================================



function bot_wtc_css() { // css for the plugin tab under extensions

	global $event;
	if($event != 'bot_wtc_tab') { // Outputs css only in 'bot_wtc' extensions tab.
		return;
	}

	echo '<style type="text/css">
			#bot_main {
				margin: auto; width:800px;
			}
			#page-bot_wtc_tab h2 {
				text-align: center;	margin:20px auto; padding-bottom:10px;
			}
			#bot_controls {
				margin: 20px auto; 
			}
			#bot_controls a{margin-right:-5px}
			#bot_expand_all,
			#bot_collapse_all,
			#bot_advanced_open {
				font-size:10px;
			}
			#bot_wtc_table {
			 	padding:10px 0 20px; margin-left:0;
			}
			#bot_wtc_table td {
				vertical-align:center;
				padding:5px; 
				white-space:nowrap;
			}
			#bot_wtc_table td p{margin:3px 0 0 0}
			#bot_advanced {}
			#bot_adv_items,
			#bot_adv_hide,
			#bot_adv_class
			{
				width:260px; float:left; margin-bottom:20px;
			}
			#bot_uninstall-details,
			#bot_static_sections-details,
			#bot_js_box-details {
				padding:0 20px;
			}
		    #bot_uninstall-details{padding-bottom:20px;}

			#bot_wtc_script {
				width:100%; border:dotted #ccc 1px;
			}
			.bot_update_button {
				margin:20px 0; clear:both;
			}
			#bot_uninstall {
			}
			#bot_install {
				margin: auto; width:800px;
			}
			.bot_hilight {
				background:#eaeaea
			}
			a.bot_push {
				font-weight:bold; background: url(txp_img/arrowupdn.gif) no-repeat right bottom; padding-right:13px;
			}
			#bot_warning {
				text-align:center; background:#990000; color:#fff; margin: 20px auto; padding:10px; text-shadow:none;
			}
		</style>';
}



// ===========================================================



function bot_hide_per_section_array(){ // builds array of sections to hide

	$db_values = bot_wtc_fetch_db();  // array of values from the db

	for ($i =0; $i<count($db_values); $i++) {
		if ($db_values[$i]['sections']) {
		    $sections_to_hide = explode('|', $db_values[$i]['sections']);
		    foreach ($sections_to_hide as $section) {
				$bot_hide_per_section[$section][] = $db_values[$i]['item'];
			}
	    }
	}
	if (isset($bot_hide_per_section)) { // return array only if values exist
 		return $bot_hide_per_section;
 	}
}



// ===========================================================




function bot_wtc_jquery_hide_sections_rows(){ // js rows dealing with items to hide on section change AND on page load

	$bot_hide_per_section = bot_hide_per_section_array();
	foreach ($bot_hide_per_section as $section => $fields) {
		echo n.'			if (value=="'.$section.'"){'.n;
        for ($i =0; $i<count($fields); $i++) {
			echo '				'.$fields[$i].'.hide();'.n;
        }
		echo '			}'.n;
	}
}



// ===========================================================



function bot_wtc_jquery_restore_rows(){ // js rows to restore every previously hidden item on section change

	$bot_hide_per_section = bot_hide_per_section_array();
	foreach ($bot_hide_per_section as $section => $fields) {
        for ($i =0; $i<count($fields); $i++) {
			$out[] = $fields[$i];
        }
	}
	$out = array_unique($out);
	foreach ($out as $value) {
	echo '			'.$value.'.show();'.n;
	}
}



// ===========================================================



function bot_hide_per_section(){ //  builds the script

    $bot_hide_per_section = bot_hide_per_section_array();
	if ($bot_hide_per_section) { // output js only if values exist
		 	echo
				'<script  type="text/javascript">'.n.
				'	$(document).ready(function() {'.n;
			echo
				'		$("select#section").change(function(){'.n;
							bot_wtc_jquery_restore_rows();
			echo
				'			var value = $("select#section").val();';
							bot_wtc_jquery_hide_sections_rows();
			echo
				'		}).change();'.n.
				'	});'.n.
				'</script>';
		}
	}



// ===========================================================



function bot_hidden_sections(){ // invisible sections in section list

	$bot_hidden_sections = safe_field('val', 'txp_prefs', 'name = "bot_wtc_static_sections"'); // fetch prefs value for bot_wtc_static_sections
	if ($bot_hidden_sections) { // output js only if values exist
		$sections = explode("|", $bot_hidden_sections);
		echo
		'<script  type="text/javascript">'.n.
		'	$(document).ready(function() {'.n;
		foreach ($sections as $value) {
			echo    '           $("select#section option:not(:selected)[value=\''.$value.'\']").remove();'.n;
		}
		echo
		'	});'.n.
		'</script>';
	}
}



// ===========================================================



function bot_wtc_jquery_rows()
{
	global $bot_items;
	$db_values = bot_wtc_fetch_db();  // array of values from the db

	$rows = '';
	for ($i = 0; $i <count($db_values); $i++)
	{
		$item = ($db_values[$i]['item'] != '') ? $db_values[$i]['item'] : '';
		$position = ($db_values[$i]['position'] != '') ? '.'.$db_values[$i]['position'] : '';
		$destination = ($db_values[$i]['destination'] != '') ? '('.$db_values[$i]['destination'].')' : '';
		$class = ($db_values[$i]['class'] != '') ? '.addClass("'.$db_values[$i]['class'].'")' : '';
  		$row = $item.$position.$destination.$class.';'.n;
		$rows .= $row;
	}
	return $rows;
};



// ===========================================================



function bot_wtc()
{

	$bot_wtc_script = safe_field('val', 'txp_prefs', 'name = "bot_wtc_script"'); // fetch prefs value for bot_wtc_script
 	$position = safe_column('position', 'bot_wtc', '1=1'); // fetch 'position' from db to check if a move is saved
 	$class = safe_column('class', 'bot_wtc', '1=1'); // fetch 'class' from db to check if a class is saved

	if(isset($position) || isset($class)){ // output code only if a preference is saved
		echo
		'<script  type="text/javascript">'.n.
		'	$(document).ready(function() {'.n.
				bot_wtc_jquery_rows().n.
		'	});'.n.
		'</script>';
	}
	if ($bot_wtc_script) {
		echo n.$bot_wtc_script.n;
	};

}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h2>Write tab customize help</h2>



  <h3>Overview</h3>

  <p>This plugin aims to be an all-in-one solution for the "write" tab customization. It allows to  rearrange items, hide them on a per-section basis,  remove some sections from the <em>sections </em>dropdown and much more. By combining   its features you can get a totally different <em>write</em> tab arrangement depending on which section you choose in the <em>sections</em> dropdown.  Used alone or togheter with other plugins (glz_custom_fields and  bot_admin_body_class to name a few)  it will help you customize the site  backend   for your clients quickly and easily. </p>


  <h3>Features</h3>

  <ul>
    <li>Single items (custom fields, body, excerpt etc.) or whole columns can be moved around relative to other single items or columns</li>
    <li>Items can be hidden on a per-section basis</li>
    <li>Sections can be removed from the "write" tab sections dropdown (for static sections like "about us" or "search") </li>
    <li>A custom css class can be set for each item. This let's you define   classes for items that normally couldn't be targeted with simple css  (i.e. a &lt;p&gt; surrounding a specific custom field and his label - if  you are using glz_custom_fields). This feature has now a reduced  importance with txp 3.0 since a lot more page elements can be directly  targeted by css.  Nonetheless it can still prove useful in some cases</li>

    <li>Javascript code can be set directly throught the plugin interface.  Particularly useful  for use with an external jquery plugin and in  conjunction with the ability to add a css class to any item on the page.  The script will be executed only in the <em>write</em> tab </li>
    <li>Advanced preferences allows for multiple items hiding and class  attribution,  speeding up the customization process if a lot of custom  fields are set. </li>
    <li>Compatible with glz_custom_fields, rah_section_titles and              rah_write_each_section </li>
  </ul>
  <p>Note that the rearrange capability    is mainly intended to be  an  intermediate step in the  customization process. To to fine-tune your  customization  you <em>may</em> need to  modify  the "textpattern.css" file.</p>


  <h3>A simple example</h3>

  <p>Suppose you want a  custom field called "Files" to be the first item at the top left corner of the <em>write</em> tab.<br/>
    Quite easy:  set the rule "Files" before "advanced Options ".</p>
  <p>Ok, now you want it to show only in section "Media".<br/>

    Easy too: in the "hide in" column check all sections <em>but</em> the "Media" section.</p>
  <p>But wait. You now want it to have a different look compared to other  custom fields and you are using glz_custom_fields (which applies to all  cfs a generic  "glz_custom_field" class removing the default  more-specific default class).    In this case set an arbitrary class for the item  and, in the  textpattern.css, set a corresponding css rule, or - if you are in a rush  - define the rule directly in the js box.</p>
  <p>That's not enough? Maybe you want the field to perform an animation using a jquery plugin when you hover on it?<br/>
    Then again, set a class for the item,  reference the plugin and write the appropriate code in the js box like this:</p>
  <p><code>&lt;script type="text/javascript" src= "../js/your_jquery_animations_plugin.js"&gt;&lt;/script&gt;<br/>

    &lt;script language="javascript" type="text/javascript"&gt;<br/>
    $(document).ready(function() {<br/>
    $(".animate").your_jquery_animations_plugin();<br/>
    });<br/>
    &lt;/script&gt;</code></p>


  <h3>A little tip </h3>

  <p>Textpattern 4.3 brought a lot of cool enhancements to the "write" tab but also a few drawbacks.</p>
  <p>On the 'enhancement' side almost every item has now an id or a class.  This means a snappier jquery execution and, above all, the possibility  to hide these elements with simple css. Unfortunately there are some  little drawbacks in the way the page is designed (IMHO). I'm referring  to the 	several collapsible groups which crowd the 'write' tab. </p>
  <p> The issue here is while you can easily hide a group <em>label</em> (well, actually it isn't an html label but an h3) with css, this may  lead to trouble if the group is already collapsed. In this case a user  will not be able to expand it anymore being there nothing to be clicked.<br/>
    Of course this is nothing dramatic and can be fixed inserting a tiny jquery rule in the js box. Something like:</p>

  <p><code>&lt;script type="text/javascript"&gt;<br/>
    $(document).ready(function() {<br/>
    $(".toggle").show();<br/>
    });<br/>
    &lt;/script&gt;</code></p>
  <p>will keep everything expanded so you can safely hide any <em>label</em> with css (in your textpattern.css file</p>


  <h3>Upgrade notes </h3>

  <p> The plugin should take care of updating the data stored in the db on activation, anyway I strongly reccommend - especially if you have a lot of rules set - to  backup your db before upgrading in case something goes wrong. </p>


  <h3>Notes</h3>

  <ul>
    <li>The order in which the rules for moving items are inserted <strong>does</strong> matter. Rules execution goes from top to bottom so in case the sequence  gets garbled it's advisable to delete all and start over </li>

    <li>Class names must be inserted <strong>without</strong> the dot</li>
    <li>If you want to hide an item in <strong>all </strong>sections   set a rule directly in your textpattern css file.      For example: .override-form {display:none;} Is <strong>a lot</strong> more efficient than hiding the item in each section using this plugin.</li>
    <li>This plugin may not function properly if you have the Suhosing  module (a security-related module for PHP) installed. In this case  follow <a href="http://forum.textpattern.com/viewtopic.php?pid=243861#p243861">these instructions</a> (thanks maniqui!) </li>

  </ul>


  <h3>Installation</h3>

  <p> Paste the code into the  Admin &gt; Plugins tab, install and enable the plugin. Visit the "Extensions" tab and click the <em>Install </em> button.</p>


  <h3>Changelog</h3>


  <p>v 7.1</p>
  <ul>
    <li>  fixed <a href="http://forum.textpattern.com/viewtopic.php?pid=245511#p245511">issue with multiselect and checkboxes</a> cfs</li>
    <li>  fixed deprecated jquery</li>
    <li> changed 'sections' field <a href="http://forum.textpattern.com/viewtopic.php?pid=254132#p254132">from varchar to text</a></li>

    <li>  added missing semicolon at the end of line 903</li>
    <li>  removed deprecated language attribute from 'script' tag</li>
    <li>  updated help</li>
    <li>  cleaned up code</li>
# --- END PLUGIN HELP ---
-->
<?php
}
?>