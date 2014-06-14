<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'adi_matrix';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '2.0beta';
$plugin['author'] = 'Adi Gilbert';
$plugin['author_uri'] = 'http://www.greatoceanmedia.com.au/';
$plugin['description'] = 'Multi-article update tabs';

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

$plugin['flags'] = '2';

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
/*
	adi_matrix - Multi-article update tabs

	Written by Adi Gilbert

	Released under the GNU General Public License

	Version history:
	2.0		- enhancements:
				- publish & delete articles (for mrdale)
				- extra article data options: title, section
				- show sections
				- added section & author to article title tooltip
				- matrix under Content or Home tab
				- improved validation error messages
				- custom WHERE clause conditions
			- requires TXP 4.5+
			- and as Apple says: "Includes general performance and stability improvements"
	1.2		- TXP 4.5-ified
			- French colon-isation
			- lifecycle "upgrade" pseudo-event
	1.1		- code tidy up (thanks gocom)
			- enhancement: matrix tab optional footer
			- enhancement: matrix tab column sorting
			- enhancement: "Any parent" & "Any child" category wildcards
			- enhancement: posted timestamp (& reset)
			- enhancement: expires timestamp
			- enhancement: multi-section select (for maniqui & mrdale)
			- enhancement: input field tooltips (for masa)
			- for mrdale:
				- <body class="adi_matrix"> on all matrix tabs
				- another attempt at horizontal scrolling, this time with a fixed article title column
				- more sorting options
				- TinyMCE support for glz_custom_field textareas
				- option to include descendent categories
			- fixed: checkboxes again! (thanks redbot)
			- changed: "Nothing to do" message changed to "No articles modified"
			- changed: admin tab name/title now "Article Matrix"/"Article Matrix Admin"
	1.0.1	- not officially released
			- fixed: completely unticked checkboxes not updated (thanks redbot)
			- fixed: detect glz_custom_fields in plugin cache (thanks gocom)
	1.0		- enhancement: glz_custom_fields compatibility
			- enhancement: force numeric sort (for jpdupont)
			- enhancement: sort by Expires timestamp
			- enhancement: article catagories (for maniqui)
			- enhancement: option to switch on horizontal scroll (for mrdale)
			- fixed: MySQL 4.1 compatibility (thanks colak)
			- fixed: error if custom field contains single quote (thanks maniqui)
			- fixed: superfluous "Logged in user" wildcard option in matrix appearance
			- now uses lifecycle events
	0.3		- enhancement: "One category", "Two categories" wildcards
			- enhancement: timestamp (for CeBe)
			- enhancement: expiry (for CeBe)
			- enhancement: future/expired articles highlighted & preference (for CeBe)
			- enhancement: article title tooltip, & preference
			- admin: install/uninstall/textpack moved to plugin options
	0.2		- fixed: missing child categories (thanks Zanza)
			- enhancement: "No category", "Any category" wildcards
			- enhancement: "Logged in user" wildcard
			- enhancement: article image field (for Zanza)
			- enhancement: article limit preference (for milosevic)
	0.1		- initial release

	Custom fields
	- "standard" TXP custom fields: custom_1 ...custom_10, always present
	- with glz_custom_fields, standard CFs can disappear (on "reset"), or additional ones added: custom_11 ...

	Upgrade notes (1.0 - 1.1+)
	- due to bug in 1.0, expires sort option will get changed to modified

	Downgrade (from 2.0 to 1.1/1.2 only)
	- go to adi_matrix plugin options tab
	- add "&step=downgrade" to end of URL & hit return
	- then immediately install previous version of adi_matrix
	- BEWARE: multiple sections won't translate very well

*/

if (txpinterface === 'admin') {
	global $adi_matrix_debug;

	$adi_matrix_debug = 0;

	// using article_validate & new default section pref (4.5.0), so decamp sharpish if need be
	if (!version_compare(txp_version,'4.5.0','>=')) return;

	adi_matrix_init();
}

function adi_matrix_init() {
// general setup
	global $prefs,$txp_groups,$txp_user,$event,$step,$adi_matrix_gtxt,$adi_matrix_url,$adi_matrix_glz_cfs,$adi_matrix_privs,$adi_matrix_groups,$adi_matrix_cfs,$adi_matrix_expiry_options,$adi_matrix_statuses,$adi_matrix_sort_options,$adi_matrix_sort_dir,$adi_matrix_timestamp_options,$adi_matrix_prefs,$adi_matrix_plugin_status,$adi_matrix_debug,$adi_matrix_glz_cfs,$adi_matrix_list,$adi_matrix_validation_errors,$adi_matrix_sort_type,$adi_matrix_categories,$adi_matrix_tabs;

	$adi_matrix_txp460 = (version_compare(txp_version,'4.5.4','>')); // future use

# --- BEGIN PLUGIN TEXTPACK ---
	$adi_matrix_gtxt = array(
		'adi_alphabetical' => 'Alphabetical',
		'adi_any_category' => 'Any category',
		'adi_any_child_category' => 'Any child category',
		'adi_article_data' => 'Article Data',
		'adi_article_matrix' => 'Article Matrix',
		'adi_article_highlighting' => 'Article title highlighting',
		'adi_article_limit' => 'Maximum number of articles',
		'adi_articles_not_modified' => 'No articles modified',
		'adi_article_selection' => 'Article Selection',
		'adi_article_tooltips' => 'Article title tooltips',
		'adi_article_update_fail' => 'Article update failed',
		'adi_articles_saved' => 'Articles saved',
		'adi_blank_url_title' => 'URL-only title blank',
		'adi_cancel' => 'Cancel',
		'adi_custom_condition' => 'Custom condition',
		'adi_cf_links' => 'Custom field links',
		'adi_default_sort' => 'Default sort',
		'adi_display_article_id' => 'Display article ID#',
		'adi_duplicate_url_title' => 'URL-only title already used',
		'adi_edit_titles' => 'Edit titles',
		'adi_expiry' => 'Expiry',
		'adi_footer' => 'Footer',
		'adi_has_expiry' => 'Has expiry',
		'adi_include_descendent_cats' => 'Include descendent categories',
		'adi_install_fail' => 'Unable to install',
		'adi_installed' => 'Installed',
		'adi_invalid_timestamp' => 'Invalid timestamp',
		'adi_jquery_ui' => 'jQuery UI script file',
		'adi_jquery_ui_css' => 'jQuery UI CSS file',
		'adi_logged_in_user' => 'Logged in user',
		'adi_matrix' => 'Matrix',
		'adi_matrix_admin' => 'Article Matrix Admin',
		'adi_matrix_cfs_modified' => 'Custom field list modified',
		'adi_matrix_delete_fail' => 'Matrix delete failed',
		'adi_matrix_deleted' => 'Matrix deleted',
		'adi_matrix_input_field_tooltips' => 'Input field tooltips',
		'adi_matrix_validation_error' => 'Validation errors',
		'adi_matrix_name' => 'Matrix name',
		'adi_matrix_update_fail' => 'Matrix settings update failed',
		'adi_matrix_updated' => 'Matrix settings updated',
		'adi_no_category' => 'No category',
		'adi_no_expiry' => 'No expiry',
		'adi_not_installed' => 'Not installed',
		'adi_numerical' => 'Numerical',
		'adi_ok' => 'OK',
		'adi_one_category' => 'One category',
		'adi_any_parent_category' => 'Any parent category',
		'adi_pref_update_fail' => 'Preference update failed',
		'adi_reset' => 'Reset',
		'adi_scroll' => 'Scroll',
		'adi_show_section' => 'Show section',
		'adi_sort_type' => 'Sort type',
		'adi_tab' => 'Tab',
		'adi_textpack_fail' => 'Textpack installation failed',
		'adi_textpack_feedback' => 'Textpack feedback',
		'adi_textpack_online' => 'Textpack also available online',
		'adi_tiny_mce' => 'TinyMCE',
		'adi_tiny_mce_dir_path' => 'TinyMCE directory path',
		'adi_tiny_mce_hak' => 'TinyMCE (hak_tinymce)',
		'adi_tiny_mce_javascript' =>'TinyMCE (Javascript)',
		'adi_tiny_mce_jquery' => 'TinyMCE (jQuery)',
		'adi_tiny_mce_config' => 'TinyMCE configuration',
		'adi_two_categories' => 'Two categories',
		'adi_uninstall' => 'Uninstall',
		'adi_uninstall_fail' => 'Unable to uninstall',
		'adi_uninstalled' => 'Uninstalled',
		'adi_update_matrix' => 'Update matrix settings',
		'adi_update_prefs' => 'Update preferences',
		'adi_upgrade_fail' => 'Unable to upgrade',
		'adi_upgrade_required' => 'Upgrade required',
		'adi_upgraded' => 'Upgraded',
		'adi_user' => 'User',
	);
# --- END PLUGIN TEXTPACK ---

	// Textpack
	$adi_matrix_url = array(
		'textpack' => 'http://www.greatoceanmedia.com.au/files/adi_textpack.txt',
		'textpack_download' => 'http://www.greatoceanmedia.com.au/textpack/download',
		'textpack_feedback' => 'http://www.greatoceanmedia.com.au/textpack/?plugin=adi_matrix',
	);
	if (strpos($prefs['plugin_cache_dir'],'adi') !== FALSE) // use Adi's local version
		$adi_matrix_url['textpack'] = $prefs['plugin_cache_dir'].'/adi_textpack.txt';

	// plugin lifecycle
	register_callback('adi_matrix_lifecycle','plugin_lifecycle.adi_matrix');

	// adi_matrix admin tab
	add_privs('adi_matrix_admin'); // defaults to priv '1' only
	register_tab('extensions','adi_matrix_admin',adi_matrix_gtxt('adi_article_matrix')); // add new tab under 'Extensions'
	register_callback('adi_matrix_admin','adi_matrix_admin');

	// look for glz_custom_fields
	$adi_matrix_glz_cfs = load_plugin('glz_custom_fields');

	/*	User privilege summary:
			0 - none			- can't even login
			1 - publisher		- full matrix data & adi_matrix admin capability
			2 - manager			- matrix data only
			3 - copy editor		- matrix data only
			4 - staff writer	- matrix data only
			5 - freelancer		- matrix data only
			6 - designer		- matrix data only
		Standard article editing privileges:
			'article.edit'                => '1,2,3',
			'article.edit.published'      => '1,2,3',
			'article.edit.own'            => '1,2,3,4,5,6',
			'article.edit.own.published'  => '1,2,3,4',
	*/

	// defines privileges required to view a matrix with privilege restriction (same indexing as $txp_groups)
	$adi_matrix_privs = array(
		1 => '1',			// publisher
		2 => '1,2',			// managing_editor
		3 => '1,2,3',		// copy_editor
		4 => '1,2,3,4',		// staff_writer
		5 => '1,2,3,4,5',	// freelancer
		6 => '1,6',			// designer
	);

	// set up user privilege groups
	$adi_matrix_groups = $txp_groups; // to get: 1 => 'publisher', 2 => 'managing_editor' etc
	unset($adi_matrix_groups[0]); // lose index zero (none) - gets us a blank select option too!
	foreach ($adi_matrix_groups as $index => $group) {
		$adi_matrix_groups[$index] = adi_matrix_gtxt($group); // to get: 1 => 'Publisher', 2 => 'Managing Editor' etc in the language 'de jour'
		add_privs('adi_matrix_'.$group,$adi_matrix_privs[$index]); // i.e. adi_matrix_publisher = '1', adi_matrix_managing_editor = '1,2' etc
	}

	// discover custom fields (standard 1-10 & glz 11+) and their non-lowercased titles
	$adi_matrix_cfs = getCustomFields();
	foreach ($adi_matrix_cfs as $index => $value)
		$adi_matrix_cfs[$index] = $prefs['custom_'.$index.'_set']; // index = custom fields number, value = custom field title

	// build a picture of article categories
	$adi_matrix_categories = adi_matrix_categories(getTree('root','article'));

	// article expiry options
	$adi_matrix_expiry_options = array(
		0 => '',
		1 => adi_matrix_gtxt('adi_no_expiry'),
		2 => adi_matrix_gtxt('adi_has_expiry'),
		3 => adi_matrix_gtxt('expired'),
	);

	// article status code translation
	$adi_matrix_statuses = array(
		1 => adi_matrix_gtxt('draft'),
		2 => adi_matrix_gtxt('hidden'),
		3 => adi_matrix_gtxt('pending'),
		4 => adi_matrix_gtxt('live'),
		5 => adi_matrix_gtxt('sticky'),
	);

	// article sort options
	$adi_matrix_sort_options = array(
		'posted' => adi_matrix_gtxt('posted'),
		'title' => adi_matrix_gtxt('title'),
		'id' => adi_matrix_gtxt('id'),
		'lastmod' => adi_matrix_gtxt('article_modified'),
		'expires' => adi_matrix_gtxt('expires'),
		'status' => adi_matrix_gtxt('status'),
		'keywords' => adi_matrix_gtxt('keywords'),
		'article_image' => adi_matrix_gtxt('article_image'),
		'category1' => adi_matrix_gtxt('category1'),
		'category2' => adi_matrix_gtxt('category2'),
		'section' => adi_matrix_gtxt('section'),
	);
	foreach ($adi_matrix_cfs as $index => $value) // add custom fields to sort options
		$adi_matrix_sort_options['custom_'.$index] = $adi_matrix_cfs[$index];

	// article sort direction
	$adi_matrix_sort_dir = array(
		'desc' => adi_matrix_gtxt('descending'),
		'asc' => adi_matrix_gtxt('ascending'),
	);

	// article sort type
	$adi_matrix_sort_type = array(
		'alphabetical' => adi_matrix_gtxt('adi_alphabetical'),
		'numerical' => adi_matrix_gtxt('adi_numerical'),
	);

	// article timestamp options
	$adi_matrix_timestamp_options = array(
		'any' => adi_matrix_gtxt('time_any'),
		'past' => adi_matrix_gtxt('time_past'),
		'future' => adi_matrix_gtxt('time_future'),
	);

	// validation errors
	$adi_matrix_validation_errors = array(
		0 => adi_matrix_gtxt('adi_invalid_timestamp'),
		1 => adi_matrix_gtxt('article_expires_before_postdate'),
		2 => adi_matrix_gtxt('adi_duplicate_url_title'),
		3 => adi_matrix_gtxt('adi_blank_url_title'),
	);

	// default preferences
	$adi_matrix_prefs = array(
		'adi_matrix_article_limit'			=> array('value' => '100', 'input' => 'text_input'),
		'adi_matrix_article_highlighting'	=> array('value' => '1', 'input' => 'yesnoradio'),
		'adi_matrix_article_tooltips'		=> array('value' => '1', 'input' => 'yesnoradio'),
		'adi_matrix_display_id'				=> array('value' => '0', 'input' => 'yesnoradio'),
		'adi_matrix_input_field_tooltips'	=> array('value' => '0', 'input' => 'yesnoradio'),
		'adi_matrix_jquery_ui'				=> array('value' => '../scripts/jquery-ui.js', 'input' => 'text_input'),
		'adi_matrix_jquery_ui_css'			=> array('value' => '../scripts/jquery-ui.css', 'input' => 'text_input'),
		'adi_matrix_tiny_mce'				=> array('value' => '0', 'input' => 'yesnoradio'),
		'adi_matrix_tiny_mce_type'			=> array('value' => 'custom', 'input' => 'text_input'),
 		'adi_matrix_tiny_mce_dir'			=> array('value' => '../scripts/tiny_mce', 'input' => 'text_input'),
		'adi_matrix_tiny_mce_config'		=> array('value' => 'see below', 'input' => 'text_area'),
	);
	$adi_matrix_prefs['adi_matrix_tiny_mce_config']['value'] = '
language : "en",
theme : "advanced",
plugins : "safari,pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
theme_advanced_buttons1 : "pagebreak,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
theme_advanced_buttons3 : "tablecontrols",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
extended_valid_elements: "style[*]",
width: "600",
height: "400",
';

	// tabs
	$adi_matrix_tabs = array(
		'content' => adi_matrix_gtxt('tab_content'),
		'start' => adi_matrix_gtxt('tab_start'),
	);

	// plugin options
	$adi_matrix_plugin_status = fetch('status','txp_plugin','name','adi_matrix',$adi_matrix_debug);
	if ($adi_matrix_plugin_status) { // proper install - options under Plugins tab
		add_privs('plugin_prefs.adi_matrix','1,2'); // defaults to priv '1' only
		register_callback('adi_matrix_options','plugin_prefs.adi_matrix');
	}
	else { // txpdev - options under Extensions tab
		add_privs('adi_matrix_options');
		register_tab('extensions','adi_matrix_options','Matrix Options');
		register_callback('adi_matrix_options','adi_matrix_options');
	}

	// glz_custom_fields stuff
	if ($adi_matrix_glz_cfs) {
		if (strstr($event, 'adi_matrix_matrix_')) {
			// date & time pickers
		    add_privs('glz_custom_fields_css_js', "1,2,3,4,5,6");
		    register_callback('glz_custom_fields_css_js', "admin_side", 'head_end');
			// TinyMCE
			if (adi_matrix_pref('adi_matrix_tiny_mce')) {
				register_callback('adi_matrix_tiny_mce_style','admin_side','head_end');
				register_callback('adi_matrix_tiny_mce_'.adi_matrix_pref('adi_matrix_tiny_mce_type'),'admin_side','footer');
			}
		}
	}

	// article matrix tabs
	$all_privs = '1,2,3,4,5,6'; // everybody
	$adi_matrix_list = array();
	if (adi_matrix_installed())
		$adi_matrix_list = adi_matrix_read_settings(adi_matrix_upgrade());
	foreach ($adi_matrix_list as $index => $matrix) {
		if ($matrix['user'])
			$user_ok = ($txp_user == $matrix['user']);
		else // open to all users
			$user_ok = TRUE;
		if ($matrix['privs'])
			$has_privs = has_privs('adi_matrix_'.$adi_matrix_groups[$matrix['privs']]);
		else // open to all privs
			$has_privs = TRUE;
		if (($user_ok && $has_privs) || has_privs('adi_matrix_admin')) {
			$myevent = 'adi_matrix_matrix_'.$index;
			$mytab = $matrix['name'];
			if ($matrix['privs'])
				add_privs($myevent,$adi_matrix_privs[$matrix['privs']]); // last line of defence if someone tries to access the matrix tag directly
			else
				add_privs($myevent,$all_privs); // everybody's welcome
			$tab = $matrix['tab'];
			if ($tab == 'start') // switch on Home tab
				add_privs('tab.start',$all_privs); // all privs
			register_tab($tab,$myevent,$mytab);
			register_callback("adi_matrix_matrix",$myevent);
		}
	}

	// style
	if (strstr($event,'adi_matrix_matrix_') || ($event == 'adi_matrix_admin'))
		register_callback('adi_matrix_style','admin_side','head_end');

	// script
	if (strstr($event,'adi_matrix_admin'))
		register_callback('adi_matrix_admin_script','admin_side','head_end');
	if (strstr($event,'adi_matrix_matrix_'))
		register_callback('adi_matrix_matrix_script','admin_side','head_end');
}

function adi_matrix_gtxt($phrase,$atts=array(),$suffix = '') {
// will check installed language strings before embedded English strings - to pick up Textpack
// - for TXP standard strings gTxt() & adi_matrix_gtxt() are functionally equivalent
	global $adi_matrix_gtxt,$prefs;

	// make sure $atts is an array (done automatically in TXP 4.5+)
	if (!is_array($atts)) $atts = array();
	// French colon-isation
	if ($suffix == ':' && (strpos($prefs['language'],'fr-') === 0))
		$suffix = sp.$suffix;
	if (gTxt($phrase, $atts) == $phrase) // no TXP translation found
		if (array_key_exists($phrase,$adi_matrix_gtxt)) // adi translation found
			return $adi_matrix_gtxt[$phrase].$suffix;
		else // last resort
			return $phrase.$suffix;
	else // TXP translation
		return gTxt($phrase,$atts).$suffix;
}

function adi_matrix_style() {
// some style

	echo '<style type="text/css">
			/* general */
			table#list td { padding-top:0.5em }
			.adi_matrix_button { margin:1em auto; text-align:center }
			/* general 4.5 */
			table.txp-list { width:auto; margin:0 auto }
			/* admin tab */
			.adi_matrix_admin input.radio { margin-left:0.5em }
			.adi_matrix_admin form { text-align:center }
			.adi_matrix_field { text-align:left }
			.adi_matrix_field label { display:block; float:left; width:8em }
			.adi_matrix_field label.adi_matrix_label2 { width:auto }
			.adi_matrix_field p { overflow:hidden; min-height:1.4em; }
			.adi_matrix_custom_field label { width:12em }
			.adi_matrix_multi_checkboxes { margin:0.3em 0 0.5em; height:5em; padding:0.2em; overflow:auto; border:1px solid #ccc }
			.adi_matrix_multi_checkboxes label { float:none; width:auto }
			.adi_matrix_prefs { margin-top:5em; text-align:center }
			.adi_matrix_prefs input { margin-left:1em }
			.adi_matrix_prefs input.checkbox { margin-left:0.5em }
			.adi_matrix_prefs input.smallerbox { margin-left:0 }
			.adi_matrix_prefs .adi_matrix_radio label { margin-right:1em }
			.adi_matrix_prefs .adi_matrix_radio input { margin-left:0.5em }
			/* matrix tabs */
			.adi_matrix_matrix h1 { margin-top:0; text-align:center; font-weight:bold }
			.adi_matrix_matrix table#list th.adi_matrix_noborder { border:0 }
			.adi_matrix_matrix table#list .adi_matrix_delete { width:4em; font-weight:normal; text-align:center }
			.adi_matrix_matrix .date input { margin-top:0 }
			.adi_matrix_none { margin-top:2em; text-align:center }
			.adi_matrix_timestamp { min-width:12.5em }
			.adi_matrix_future a { font-weight:bold }
			.adi_matrix_expired a { font-style:italic }
			.adi_matrix_error input { border-color:#b22222; color:#b22222 }
			.adi_matrix_matrix .adi_matrix_matrix_prefs { margin-top:4em; text-align:center }
			/* matrix tabs 4.5 */
			.txp-list .adi_matrix_timestamp .time input { margin-top:0.5em }
			.txp-list .adi_matrix_timestamp { min-width:11em }
			/* glz_custom_fields */
			html[xmlns] td.glz_custom_date-picker_field.clearfix { display:table-cell!important } /* override clearfix  - for date-picker field */
			.adi_matrix_matrix input.date-picker { float:left; width:7em }
			.adi_matrix_matrix td.glz_custom_date-picker_field { min-width:10em }
			.adi_matrix_matrix input.time-picker { width:4em }
			/* tinyMCE */
			.adi_matrix_matrix .glz_text_area_field div.tie_div { overflow-y:scroll; width:17.6em; height:5.6em; padding:0.2em; border:1px solid; border-color:#aaa #eee #eee #aaa; background-color: #eee }
			/* scrolling matrix */
			.adi_matrix_scroll table#list th:first-child,
			.adi_matrix_scroll table#list td:first-child { position:absolute; width:15em; left:0; top:auto; padding-right:1em; border-bottom-width:0 }
			.adi_matrix_scroll table#list thead th:first-child { border-bottom-width:1px }
			.adi_matrix_scroll div.scroll_box { width:80%; margin-left:17em; padding-bottom:1em;overflow-x:scroll; overflow-y:visible; border:solid #eee; border-width:0 1px }
			.adi_matrix_scroll table#list td:first-child a { display:block; height:1.3em; overflow:hidden }
			.adi_matrix_scroll table#list tfoot td:first-child { border-top:1px solid #ddd }
			.adi_matrix_scroll table#list tfoot td { border-bottom:0 }
			.adi_matrix_scroll table#list tfoot td:first-child a { display:inline }
			/* footer */
			table#list tfoot td { font-weight:bold }
			table#list tfoot td.desc a,
			table#list tfoot td.asc a { width:auto; background-color:transparent; background-image:url("./txp_img/arrowupdn.gif"); background-repeat: no-repeat; background-attachment: scroll; background-position: right -18px; padding-right: 14px; margin-right: 0pt; }
			table#list tfoot td.asc a { background-position: right 2px; }
			/* hive theme (pre-4.5) */
			#txp-content #list tr { border-bottom:1px solid #ddd }
			#txp-content #list tfoot td { border-right:1px solid #fff; background-color: #ddd; font-weight:normal }
			#txp-content #list tfoot td a { display:block }
			#txp-content #list tfoot td.asc a,
			#txp-content #list tfoot td.desc a { background:none }
			#txp-content #list tfoot td.asc,
			#txp-content #list tfoot td.desc { background-image: url("./theme/hive/color/default/twisty-arrow-list.gif"); background-repeat: no-repeat; background-attachment: scroll; background-position: right -4px }
			#txp-content #list tfoot td.desc { background-position: right -31px }
			#txp-content .adi_matrix_field label.adi_matrix_label2 { margin-right:1em }
/*			#txp-content .adi_matrix_timestamp .posted { min-width:15.5em }
			#txp-content .adi_matrix_timestamp .expires { min-width:11em }*/
			#txp-content .adi_matrix_matrix .time { margin-top:0.5em }
			#txp-content .adi_matrix_matrix .time input.checkbox { margin-left:0.5em; margin:right:0 }
			#txp-content .adi_matrix_multi_checkboxes { margin-bottom:1em }
		</style>';
}

function adi_matrix_admin_script() {
// jQuery magic for admin tab

	echo <<<END_SCRIPT
<script type="text/javascript">
	$(function(){
		$("#peekaboo").hide();
		$('input[name="adi_matrix_tiny_mce"][value="1"]:checked').each(function(){
			$("#peekaboo").show();
		});
		$('input[name="adi_matrix_tiny_mce"]:radio:eq(0)').change(function(){
			$("#peekaboo").show();
		});
		$('input[name="adi_matrix_tiny_mce"]:radio:eq(1)').change(function(){
			$("#peekaboo").hide();
		});
	});
</script>
END_SCRIPT;
}

function adi_matrix_matrix_script() {
// jQuery action

	// add class to <body>
	echo <<<END_SCRIPT
<script type="text/javascript">
	$(function(){
		$('body').addClass('adi_matrix');
	});
</script>
END_SCRIPT;
}

function adi_matrix_read_settings($just_the_basics=FALSE) {
// get matrix settings from database
	global $adi_matrix_cfs,$adi_matrix_debug;

	$rs = safe_rows_start('*','adi_matrix',"1=1");
	$matrix_list = array();
	if ($rs)
		while ($a = nextRow($rs)) {
			extract($a);
			// just enough to display matrix tab
			$matrix_list[$id]['name'] = $name;
			$matrix_list[$id]['user'] = $user;
			$matrix_list[$id]['privs'] = $privs;
			if (!isset($tab)) // tab introduced in v2.0, so may not be present during upgrade install
				$tab = 'content';
			$matrix_list[$id]['tab'] = $tab;
			// load in the rest
			if (!$just_the_basics) {
				$the_rest = array('sort','dir','sort_type','scroll','footer','title','publish','show_section','cf_links','criteria_section','criteria_category','criteria_descendent_cats','criteria_status','criteria_author','criteria_keywords','criteria_timestamp','criteria_expiry','criteria_condition','status','keywords','article_image','category1','category2','posted','expires','section');
				foreach ($the_rest as $item)
					$matrix_list[$id][$item] = $$item;
				// custom fields
				foreach ($adi_matrix_cfs as $index => $value) {
					$custom_x = 'custom_'.$index;
					if (isset($$custom_x)) // check that custom field is known to adi_matrix
						$matrix_list[$id][$custom_x] = $$custom_x;
				}
			}
		}
	return $matrix_list;
}

function adi_matrix_get_articles($criteria,$matrix_index,$sort,$dir,$sort_type) {
// read required articles from database and populate $adi_matrix_articles
// mostly ripped off from doArticles() in publish.php
	global $adi_matrix_debug,$adi_matrix_cfs,$adi_matrix_list,$adi_matrix_sort_options,$txp_user,$adi_matrix_timestamp_options,$adi_matrix_categories;

	extract($criteria);

	$excerpted = '';
	$month = '';
	$time = $timestamp;

	// categories
	$cats = array();
	if ($category == '!no_category!')
		$category = " and (Category1 = '' and Category2 = '')";
	else if ($category == '!any_category!')
		$category = " and (Category1 != '' or Category2 != '')";
	else if ($category == '!one_category!')
		$category = " and (Category1 != '' and Category2 = '') or  (Category1 = '' and Category2 != '')";
	else if ($category == '!two_categories!')
		$category = " and (Category1 != '' and Category2 != '')";
	else if ($category == '!any_parent_category!') {
		foreach ($adi_matrix_categories as $name => $this_cat)
			if ($this_cat['children'])
				$cats[] = $name;
		$category = implode(',',$cats);
		$category = implode("','", doSlash(do_list($category)));
		$category = (!$category) ? '' : " and (Category1 IN ('".$category."') or Category2 IN ('".$category."'))";
	}
	else if ($category == '!any_child_category!') {
		foreach ($adi_matrix_categories as $name => $this_cat)
			if ($this_cat['parent'] != 'root')
				$cats[] = $name;
		$category = implode(',',$cats);
		$category = implode("','", doSlash(do_list($category)));
		$category = (!$category) ? '' : " and (Category1 IN ('".$category."') or Category2 IN ('".$category."'))";
	}
	else { // single category (perhaps with optional descendents)
		if ($descendent_cats)
			$category .= ','.implode(',',$adi_matrix_categories[$category]['children']);
		$category = implode("','", doSlash(do_list($category)));
		$category = (!$category) ? '' : " and (Category1 IN ('".$category."') or Category2 IN ('".$category."'))";
	}

	$section   = (!$section) ? '' : " and Section IN ('".implode("','", doSlash(do_list($section)))."')";
	$excerpted = ($excerpted=='y') ? " and Excerpt !=''" : '';
	if ($author == '!logged_in_user!')
		$author = $txp_user;
	$author    = (!$author) ? '' : " and AuthorID IN ('".implode("','", doSlash(do_list($author)))."')";
	$month     = (!$month) ? '' : " and Posted like '".doSlash($month)."%'";

	// posted timestamp
	switch ($time) {
		case 'any':
			$time = "";
			break;
		case 'future':
			$time = " and Posted > now()";
			break;
		default:
			$time = " and Posted <= now()";
	}

	// expiry
	switch ($expiry) {
		case '1': // no expiry set
			$time .= " and Expires = ".NULLDATETIME;
			break;
		case '2': // has expiry set
			$time .= " and Expires != ".NULLDATETIME;
			break;
		case '3': // expired
			$time .= " and now() > Expires and Expires != ".NULLDATETIME;
			break;
	}

	$custom = ''; // MAY GET CONFUSING WITH criteria_condition
//	if ($customFields) {
//		foreach($customFields as $cField) {
//			if (isset($atts[$cField]))
//				$customPairs[$cField] = $atts[$cField];
//		}
//		if(!empty($customPairs)) {
//			$custom = buildCustomSql($customFields,$customPairs);
//		}
//	}

	if ($keywords) {
		$keys = doSlash(do_list($keywords));
		foreach ($keys as $key) {
			$keyparts[] = "FIND_IN_SET('".$key."',Keywords)";
		}
		$keywords = " and (" . implode(' or ',$keyparts) . ")";
	}

	if ($status)
		$statusq = ' and Status = '.intval($status);
	else // either blank or zero
		$statusq = ''; // all statuses

	if ($condition)
		$conditionq = ' and '.$condition;
	else
		$conditionq = '';

	$where = "1=1".$statusq.$time.$category.$section.$excerpted.$month.$author.$keywords.$custom.$conditionq;

	switch ($sort) { // map columns to sort query
		case 'posted':
			$sortq = 'Posted';
			break;
		case 'expires':
			$sortq = 'Expires';
			break;
		case 'lastmod':
			$sortq = 'LastMod';
			break;
		case 'title':
			$sortq = 'Title';
			break;
		case 'id':
			$sortq = 'ID';
			break;
		case 'status':
			$sortq = 'Status';
			break;
		case 'article_image':
			$sortq = 'Image';
			break;
		case 'category1':
			$sortq = 'Category1';
			break;
		case 'category2':
			$sortq = 'Category2';
			break;
		case 'section':
			$sortq = 'Section';
			break;
		default: // custom_x etc will fall through to here
			$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('textpattern')." LIKE '$sort'",$adi_matrix_debug); // find out if column (glz custom field probably) still exists
			$a = nextRow($rs);
			if (empty($a))
				$sortq = 'Posted';
			else
				$sortq = $sort;
	}

	// sort type
	if ($sort_type == 'numerical')
		$sort_typeq = ' + 0';
	else
		$sort_typeq = '';

	// sort it all out
	$sortq .= $sort_typeq.' '.$dir.', Posted desc'; // add direction ... & also secondary sort (pointless but harmless with ID, Posted & Expires)

	$limit = adi_matrix_pref('adi_matrix_article_limit'); // article limit defined in preferences
	$offset = 0;
	$pgoffset = $offset;

	// get the required articles from database
	$rs = safe_rows_start("*, unix_timestamp(Posted) as uPosted, unix_timestamp(Expires) as uExpires, unix_timestamp(LastMod) as uLastMod", 'textpattern', $where.' order by '.doSlash($sortq).' limit '.intval($pgoffset).', '.intval($limit), $adi_matrix_debug);
	$adi_matrix_articles = array();
	if ($rs) // populate $adi_matrix_articles
		while ($a = nextRow($rs)) {
			extract($a);
			$adi_matrix_articles[$ID] = array();
			$adi_matrix_articles[$ID]['title'] = html_entity_decode($Title, ENT_QUOTES, 'UTF-8');
			$adi_matrix_articles[$ID]['section'] = $Section;
			$adi_matrix_articles[$ID]['author'] = $AuthorID; // need author for article edit priv check
			$adi_matrix_articles[$ID]['status'] = $Status; // need status for article edit priv check
			$adi_matrix_articles[$ID]['keywords'] = $Keywords;
			$adi_matrix_articles[$ID]['article_image'] = $Image;
			$adi_matrix_articles[$ID]['category1'] = $Category1;
			$adi_matrix_articles[$ID]['category2'] = $Category2;
			foreach ($adi_matrix_cfs as $index => $cf_name) {
				$custom_x = 'custom_'.$index;
				$adi_matrix_articles[$ID][$custom_x] = $$custom_x;
			}
			$highlight = 0;
			$now = time();
			if (($now > $uExpires) && ($uExpires != 0)) // expired article
				$highlight = 1;
			if ($now < $uPosted) // future article
				$highlight = 2;
			$adi_matrix_articles[$ID]['posted'] = $Posted;
			$adi_matrix_articles[$ID]['expires'] = $Expires;
			$adi_matrix_articles[$ID]['highlight'] = $highlight;
		}
	return $adi_matrix_articles;
}

function adi_matrix_update_article($id,$data) {
// translate $_POSTED article data into query-speak
	global $adi_matrix_debug,$txp_user,$adi_matrix_cfs,$adi_matrix_articles,$vars,$prefs;

	include_once txpath.'/include/txp_article.php'; // to get textile_main_fields()

	// set up variables in the style of $vars
	$Title = $Title_plain = isset($data['title']) ? $data['title'] : '';
	$Status = isset($data['status']) ? $data['status'] : '';
	$Section = isset($data['section']) ? $data['section'] : '';
	$Keywords = isset($data['keywords']) ? trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $data['keywords'])), ', ') : '';
	$Image = isset($data['article_image']) ? $data['article_image'] : '';
	$Category1 = isset($data['category1']) ? $data['category1'] : '';
	$Category2 = isset($data['category2']) ? $data['category2'] : '';
	// posted
	if (isset($data['posted']['reset_time']))
		$publish_now = '1';
	else
		$publish_now = '';
	if (isset($data['posted'])) {
		extract($data['posted']);
		$Posted = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
	}
	else // force now
		$publish_now = '1';
	// expires
	if (isset($data['expires'])) {
		foreach ($data['expires'] as $index => $value) { // convert expiry vars ($year -> $exp_year) to align with $vars in txp_article.php
			$var = 'exp_'.$index;
			$$var = $value;
		}
		$Expires = $exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second;
	}
	else // force no expiry
		$exp_year = '0000';
	// custom Fields
	foreach ($adi_matrix_cfs as $index => $cf_name) {
		$custom_x = 'custom_'.$index;
		if (isset($data[$custom_x]))
			$$custom_x = $data[$custom_x];
		else
			$$custom_x = '';
	}
	// set the rest (not used by adi_matrix_update_article)
	$Body = '';
	$Body_html = '';
	$Excerpt = '';
	$Excerpt_html = '';
	$textile_body = $prefs['use_textile'];
	$textile_excerpt = $prefs['use_textile'];
	$Annotate = '0';
	$override_form = '';
	$AnnotateInvite = '';

	// package them all up
	$updates = compact($vars);

	if ($adi_matrix_debug) {
		echo '<b>Article '.$id.' data:</b>';
		dmp($updates);
	}

	// do some validation, textilation & slashing
	$incoming = array_map('assert_string',$updates);
	$incoming = textile_main_fields($incoming);	// converts ampersands to &amp; in titles
	extract(doSlash($incoming));
	if (isset($data['status']))
		extract(array_map('assert_int', array('Status' => $Status)));

	// title
	if (isset($data['title']))
		$titleq = "Title='$Title', ";
	else
		$titleq = '';
	// status
	$old_status = $new_status = $adi_matrix_articles[$id]['status'];
	if (isset($data['status'])) {
		$new_status = $Status;
		// tweak status according to privs
		if (!has_privs('article.publish') && $new_status >= STATUS_LIVE)
			$new_status = STATUS_PENDING;
		$statusq = 'Status='.doSlash($new_status).', ';
		if ($new_status >= STATUS_LIVE) // live & sticky articles only
			update_lastmod();
	}
	else
		$statusq = '';
	// section
	if (isset($data['section']))
		$sectionq = "Section='$Section', ";
	else
		$sectionq = '';
	// keywords
	if (isset($data['keywords']))
		$keywordsq = "Keywords='$Keywords', ";
	else
		$keywordsq = '';
	// article image
	if (isset($data['article_image']))
		$article_imageq = "Image='$Image', ";
	else
		$article_imageq = '';
	// categories
	if (isset($data['category1']))
		$categoryq = "Category1='$Category1', ";
	else
		$categoryq = '';
	if (isset($data['category2']))
		$categoryq .= "Category2='$Category2', ";
	else
		$categoryq .= '';
	// posted
	$postedq = '';
	if (isset($data['posted'])) {
		if ($publish_now)
			$postedq = "Posted=now(), ";
		else {
			$ts = strtotime($Posted);
			$date_error = ($ts === false || $ts === -1);
			if (!$date_error) {
				$when_ts = $ts - tz_offset($ts);
				$when = "from_unixtime($when_ts)";
				$postedq = "Posted=$when, ";
			}
		}
	}
	// expires
	$expiresq = '';
	if (isset($data['expires'])) {
		if ($exp_year == '0000')
			$expiry = 0;
		else {
			$ts = strtotime($Expires);
			$expiry = $ts - tz_offset($ts);
		}
		if ($expiry) {
			$date_error = ($ts === false || $ts === -1);
			if (!$date_error) {
				$expires = $ts - tz_offset($ts);
				$whenexpires = "from_unixtime($expires)";
				$expiresq = "Expires=$whenexpires, ";
			}
		}
		else
			$expiresq = "Expires=".NULLDATETIME.", ";
	}
	// custom fields
	$cfq = array();
	foreach($adi_matrix_cfs as $i => $cf_name) {
		$custom_x = "custom_{$i}";
		if (isset($data[$custom_x]))
			$cfq[] = "custom_$i = '".$$custom_x."'";
	}
	$cfq = implode(', ', $cfq);

	// update article in database
	$res = safe_update("textpattern",
	   $titleq.$sectionq.$statusq.$keywordsq.$article_imageq.$categoryq.$postedq.$expiresq.(($cfq) ? $cfq.', ' : '')."LastMod=now(), LastModID='$txp_user'",
		"ID=$id",
		$adi_matrix_debug
	);

	if ($new_status >= STATUS_LIVE && $old_status < STATUS_LIVE)
		do_pings();

	if ($new_status >= STATUS_LIVE || $old_status >= STATUS_LIVE)
		update_lastmod();

	return $res;
}

function adi_matrix_update_articles($updates,$matrix_index) {
// update articles

	$res = TRUE;
	if ($updates) {
		foreach ($updates as $id => $data)
			if ($id == 'new')
				$res = $res && adi_matrix_publish_article($data,$matrix_index);
			else
				$res = $res && adi_matrix_update_article($id,$data);
	}
	return $res;
}

function adi_matrix_article_defaults($matrix_index) {
// default values for new article, adjusted for specified matrix
	global $adi_matrix_debug,$adi_matrix_list,$prefs,$adi_matrix_cfs;

//	Article field	$defaults['xx']	Who determines						Default values
//	-------------	---------------	--------------						--------------
//	ID				-				MySQL								generated on publish
//	Posted			posted			adi_matrix_article_defaults,user	current date/time
//	Expires			expires			adi_matrix_article_defaults,user	blank (converted to 0000-00-00 00:00:00 by adi_matrix_validate_post_data)
//	AuthorID		-				adi_matrix_publish_article			current user
//	LastMod			-				adi_matrix_publish_article			generated on publish
//	LastModID		-				adi_matrix_publish_article			current user
//	Title			title			adi_matrix_article_defaults,user	blank
//	Title_html		-				adi_matrix_publish_article			blank
//	Body			-				adi_matrix_publish_article			blank
//	Body_html		-				adi_matrix_publish_article			blank
//	Excerpt			-				adi_matrix_publish_article			blank
//	Excerpt_html	-				adi_matrix_publish_article			blank
//	Image			article_image	adi_matrix_article_defaults,user	blank
//	Category1		category1		adi_matrix_article_defaults,user	criteria_category (if specific category set), blank
//	Category2		category2		adi_matrix_article_defaults,user	blank
//	Annotate		-				adi_matrix_publish_article			0
//	AnnotateInvite	-				adi_matrix_publish_article			blank
//	comments_count	-				MySQL								default
//	Status			status			adi_matrix_article_defaults,user	criteria_status (if set), "live"
//	textile_body	-				adi_matrix_publish_article			'use_textile' from $prefs
//	textile_excerpt	-				adi_matrix_publish_article			'use_textile' from $prefs
//	Section			section			adi_matrix_article_defaults			first section from criteria_section	(if set), 'default_section' from $prefs
//	override_form	-				adi_matrix_publish_article			blank
//	Keywords		keywords		adi_matrix_article_defaults,user	criteria_keywords (if set), blank
//	url_title		-				adi_matrix_publish_article			generated-from-title
//	custom_x		custom_x		adi_matrix_article_defaults,user	blank
//	uid				-				adi_matrix_publish_article			generated on publish
//	feed_time		-				adi_matrix_publish_article			generated on publish

	$defaults = array();

	// title - blank
	$defaults['title'] = '';
	// status - from criteria (or live if not set)
	if ($adi_matrix_list[$matrix_index]['criteria_status'])
		$defaults['status'] = $adi_matrix_list[$matrix_index]['criteria_status'];
	else
		$defaults['status'] = '4';
	// article image - blank
	$defaults['article_image'] = '';
	// keywords - from criteria
	$defaults['keywords'] = $adi_matrix_list[$matrix_index]['criteria_keywords'];
	// category1 - if specific category set - assign it to cat1, otherwise leave it up to user (i.e. blank)
	if (($adi_matrix_list[$matrix_index]['criteria_category']) && (strpos($adi_matrix_list[$matrix_index]['criteria_category'],'!') === FALSE))
		$defaults['category1'] = $adi_matrix_list[$matrix_index]['criteria_category'];
	else
		$defaults['category1'] = '';
	// category2 - leave blank
	$defaults['category2'] = '';
	// posted - now
	$defaults['posted'] = date("Y-m-d H:i:s");
	// expires - blank
	$defaults['expires'] = '';
	// section - $prefs default_section if blank, else first section on list if criteria set
	if ($adi_matrix_list[$matrix_index]['criteria_section']) {
		$sections = explode(',',$adi_matrix_list[$matrix_index]['criteria_section']);
		$defaults['section'] = $sections[0];
	}
	else
		$defaults['section'] = $prefs['default_section'];
	// custom fields - blank
	foreach ($adi_matrix_cfs as $index => $cf_name) {
		$custom_x = 'custom_'.$index;
		$defaults[$custom_x] = '';
	}

	return $defaults;
}

function adi_matrix_publish_article($data,$matrix_index) {
// new article - based on article_post() from txp_article.php
	global $adi_matrix_debug,$adi_matrix_cfs,$txp_user,$prefs,$vars,$step,$adi_matrix_list;

	include_once txpath.'/include/txp_article.php'; // to get article_validate() (TXP 4.5.0+), textile_main_fields()

	$defaults = adi_matrix_article_defaults($matrix_index);

	// translate adi_matrix stuff into article_post() stuff
	$Title = $Title_plain = $data['title'];
	$Body = '';
	$Body_html = '';
	$Excerpt = '';
	$Excerpt_html = '';
	$textile_body = $prefs['use_textile'];
	$textile_excerpt = $prefs['use_textile'];
	$Annotate = '0';
	$override_form = '';
	$AnnotateInvite = '';
	// article image
	if (isset($data['article_image']))
		$Image = $data['article_image'];
	else
		$Image = $defaults['article_image'];
	// keywords
	if (isset($data['keywords']))
		$Keywords = trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $data['keywords'])), ', ');
	else
		$Keywords = $defaults['keywords'];
	// status
	if (isset($data['status']))
		$Status = $data['status'];
	else
		$Status = $defaults['status'];
	// posted
	if (isset($data['posted']['reset_time']))
		$publish_now = '1';
	else
		$publish_now = '';
	if (isset($data['posted'])) {
		extract($data['posted']);
		$Posted = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
	}
	else // force now
		$publish_now = '1';
	// expires
	if (isset($data['expires'])) {
		foreach ($data['expires'] as $index => $value) { // convert expiry vars ($year -> $exp_year) to align with $vars in txp_article.php
			$var = 'exp_'.$index;
			$$var = $value;
		}
		$Expires = $exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second;
	}
	else // force no expiry
		$exp_year = '0000';
	// section
	if (isset($data['section']))
		$Section = $data['section'];
	else
		$Section = $defaults['section'];
	// categories
	$Category1 = isset($data['category1']) ? $data['category1'] : $defaults['category1'];
	$Category2 = isset($data['category2']) ? $data['category2'] : $defaults['category2'];
	// custom Fields
	foreach ($adi_matrix_cfs as $index => $cf_name) {
		$custom_x = 'custom_'.$index;
		if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) // check that custom field is known to adi_matrix
			if (isset($data[$custom_x]))
				$$custom_x = $data[$custom_x];
			else
				$$custom_x = '';
	}

	// package them all up
	$new = compact($vars);

	if ($adi_matrix_debug) {
		echo '<b>New article data:</b>';
		dmp($new);
	}

	// all fields are strings ...
	$incoming = array_map('assert_string',$new);

	// textilation (converts ampersands to &amp; in titles)
	$incoming = textile_main_fields($incoming);

	// slash attack
	extract(doSlash($incoming));

	// ... except some are more integer than string
	extract(array_map('assert_int', array('Status' => $Status, 'textile_body' => $textile_body, 'textile_excerpt' => $textile_excerpt)));
	$Annotate = (int) $Annotate;

	// set posted timestamp (already validated by adi_matrix_validate_post_data)
	if ($publish_now == 1) {
		$when = 'now()';
		$when_ts = time();
	}
	else {
		$ts = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
		$when_ts = $ts - tz_offset($ts);
		$when = "from_unixtime($when_ts)";
	}

	// and I quote: "Force a reasonable 'last modified' date for future articles, keep recent articles list in order"
	$lastmod = ($when_ts > time() ? 'now()' : $when);

	// set expiry timestamp (already validated/massaged by adi_matrix_get_post_data)
	if ($exp_year == '0000')
		$expires = 0;
	else {
		$ts = strtotime($exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second);
		$expires = $ts - tz_offset($ts);
	}
	if ($expires)
		$whenexpires = "from_unixtime($expires)";
	else
		$whenexpires = NULLDATETIME;

	// who's doing the doing?
	$user = doSlash($txp_user);

	$msg = '';

	// tweak status according to privs
	if (!has_privs('article.publish') && $Status >= STATUS_LIVE)
		$Status = STATUS_PENDING;

	// set url-title
	if (empty($url_title))
		$url_title = stripSpace($Title_plain, 1);

	// custom fields
	$cfq = array();
	$cfs = getCustomFields();
	foreach($cfs as $i => $cf_name) {
		$custom_x = "custom_{$i}";
		if (isset($$custom_x))
			$cfq[] = "custom_$i = '".$$custom_x."'";
	}
	$cfq = join(', ', $cfq);

	$rs = compact($vars);

	if ($adi_matrix_debug) {
		article_validate($rs, $msg);
		echo '<b>article_validate:</b>';
		dmp($msg);
	}

	if (article_validate($rs, $msg)) {
		$ok = safe_insert(
		   "textpattern",
		   "Title           = '$Title',
			Body            = '$Body',
			Body_html       = '$Body_html',
			Excerpt         = '$Excerpt',
			Excerpt_html    = '$Excerpt_html',
			Image           = '$Image',
			Keywords        = '$Keywords',
			Status          =  $Status,
			Posted          =  $when,
			Expires         =  $whenexpires,
			AuthorID        = '$user',
			LastMod         =  $lastmod,
			LastModID       = '$user',
			Section         = '$Section',
			Category1       = '$Category1',
			Category2       = '$Category2',
			textile_body    =  $textile_body,
			textile_excerpt =  $textile_excerpt,
			Annotate        =  $Annotate,
			override_form   = '$override_form',
			url_title       = '$url_title',
			AnnotateInvite  = '$AnnotateInvite',"
			.(($cfs) ? $cfq.',' : '').
			"uid            = '".md5(uniqid(rand(),true))."',
			feed_time       = now()"
			,$adi_matrix_debug
		);

		if ($ok) {
			if ($Status >= STATUS_LIVE) {
				do_pings();
				update_lastmod();
			}
			return TRUE;
		}
	}

	return FALSE;
}

function adi_matrix_delete_article($id) {
// delete an article - ripped off from list_multi_edit() in txp_list.php
	global $txp_user,$adi_matrix_debug;

	// keeping the multi-edit/array thing going - just in case
	$selected = array();
	$selected[] = $id;

	// is allowed?
	if (!has_privs('article.delete')) {
		$allowed = array();
		if (has_privs('article.delete.own'))
			$allowed = safe_column_num('ID', 'textpattern', 'ID in('.join(',',$selected).') and AuthorID=\''.doSlash($txp_user).'\'',$adi_matrix_debug);
		$selected = $allowed;
	}

	// in the bin
	foreach ($selected as $id) {
		if (safe_delete('textpattern', "ID = $id",$adi_matrix_debug))
			$ids[] = $id;
	}

	// housekeeping
	$changed = join(', ', $ids);
	if ($changed) {
		safe_update('txp_discuss', "visible = ".MODERATE, "parentid in($changed)",$adi_matrix_debug);
		return TRUE;
	}

	return FALSE;
}

function adi_matrix_glz_cfs_input($custom_x,$var,$val) {
// output custom field input according to glz_custom_fields format
	global $adi_matrix_debug;

	$row = safe_row('html','txp_prefs',"name = '".$custom_x."_set'"); // get html input type from prefs
	$html = $row['html'];

	$arr_custom_field_values = glz_custom_fields_MySQL("values", $custom_x."_set", '', array('custom_set_name' => $custom_x."_set"));
	$default_value = glz_return_clean_default(glz_default_value($arr_custom_field_values));
	if (is_array($arr_custom_field_values))
		array_walk($arr_custom_field_values, "glz_clean_default_array_values"); // from glz_custom_fields_replace()
	// glz radio reset - relies on name="field", which has to match for="field" in main label, which picks up for="field_value" in sub labels but adi_matrix don't have main label & needs name="article_xx[field_xx]" anyway - ne'er the twain shall neet! (would have to write our own reset jQuery I guess)
	// glz radio - uses an ID based on field & value i.e. field_value, but adi_matrix ends up as article_2[custom_3]_value which ain't valid (& don't work in jQuery anyway)
	// glz checkbox - uses an ID based on value only, so may get duplicate ID warnings on Write tab (& definitely on matrix tab)!!!
	if ($html == 'radio') { // create a clean ID prefix (i.e. without [ or ]) for radio buttons - to get rid of some error messages
		$glz_id_prefix = str_replace('[','_',$var);
		$glz_id_prefix = str_replace(']','_',$glz_id_prefix);
	}
	else
		$glz_id_prefix = '';
	$out = glz_format_custom_set_by_type($var,$glz_id_prefix,$html,$arr_custom_field_values,$val,$default_value);
	return $out; // html in $out[0], glz class in $out[1]
}

function adi_matrix_get_post_data($adi_matrix_articles,$matrix_index) {
// analyse submitted article data, massage if necessary, & create list of articles that need to be updated
	global $adi_matrix_list,$adi_matrix_cfs,$adi_matrix_glz_cfs,$adi_matrix_debug;

	// copy $_POST['article_xx'] to $adi_matrix_post[xx]
	$adi_matrix_post = array();
	foreach ($_POST as $index => $value) {
		$this_index = explode('_',$index);
		if ($this_index[0] == 'article') {
			if ($adi_matrix_glz_cfs) // tweak POSTED values to convert from array to bar|separated|list - based on glz_custom_fields_before_save()
				foreach ($value as $key => $val) {
					if (strstr($key, 'custom_') && is_array($val)) { // check for custom fields with multiple values e.g. arrays
						$val = implode($val, '|');
						$value[$key] = $val;
					}
				}
			$adi_matrix_post[$this_index[1]] = $value;
		}
	}

	// check "new" article
	if (isset($adi_matrix_post['new'])) {
		if (trim($adi_matrix_post['new']['title']) == '') // remove from the equation if title is blank
			unset($adi_matrix_post['new']);
		else { // make some times
			if (!isset($adi_matrix_post['new']['posted'])) {
				$ts = time();
				$adi_matrix_post['new']['posted'] = array();
				$adi_matrix_post['new']['posted']['year'] = date("Y",$ts);
				$adi_matrix_post['new']['posted']['month'] = date("m",$ts);
				$adi_matrix_post['new']['posted']['day'] = date("d",$ts);
				$adi_matrix_post['new']['posted']['hour'] = date("H",$ts);
				$adi_matrix_post['new']['posted']['minute'] = date("i",$ts);
				$adi_matrix_post['new']['posted']['second'] = date("s",$ts);
				$adi_matrix_post['new']['posted']['reset_time'] = '1';
			}
			if (!isset($adi_matrix_post['new']['expires'])) {
				$adi_matrix_post['new']['expires'] = array();
				$adi_matrix_post['new']['expires']['year'] = '0000';
				$adi_matrix_post['new']['expires']['month'] = '00';
				$adi_matrix_post['new']['expires']['day'] = '00';
				$adi_matrix_post['new']['expires']['hour'] = '00';
				$adi_matrix_post['new']['expires']['minute'] = '00';
				$adi_matrix_post['new']['expires']['second'] = '00';
			}
		}
	}

	// check for missing custom field values & fire blanks if necessary - required for checkboxes that have been completely unchecked
	// will also pick up radios & multiselects (though deselected multiselect are present in $_POST anyway)
	if ($adi_matrix_glz_cfs) {
		if ($adi_matrix_debug)
			echo '<b>Generating explicit blanks (glz_cf checkbox/radio/multiselect):</b>';
		foreach ($adi_matrix_articles as $id => $thisarticle) { // have to check all articles in matrix (article may be absent from POST if checkbox is the only data field & it's completely unticked)
			if ($adi_matrix_debug) echo "<br/>Article $id: ";
			foreach ($adi_matrix_cfs as $index => $title) { // check each custom field
				if ($adi_matrix_list[$matrix_index]['custom_'.$index]) { // only interested in custom field if it's visible in this matrix
					if (!array_key_exists($id,$adi_matrix_post)) { // article completely absent from POST
						$adi_matrix_post[$id]['custom_'.$index] = ''; // fire a blank
						if ($adi_matrix_debug) echo "custom_$index ";
					}
					else
						if (!array_key_exists('custom_'.$index,$adi_matrix_post[$id])) { // custom field absent from article in POST
							$adi_matrix_post[$id]['custom_'.$index] = ''; // fire a blank
						if ($adi_matrix_debug) echo "custom_$index ";
					}
				}
			}
		}
		if ($adi_matrix_debug) echo "<br/><br/>";
	}

	// expires - change all blanks to all zeroes
	foreach ($adi_matrix_post as $id => $this_article) { // check each article
		if (array_key_exists('expires',$this_article))
			if (($this_article['expires']['year'] == '') && ($this_article['expires']['month'] == '') && ($this_article['expires']['day'] == '') && ($this_article['expires']['hour'] == '') && ($this_article['expires']['minute'] == '') && ($this_article['expires']['second'] == '')) {
				$adi_matrix_post[$id]['expires']['year'] = '0000';
				$adi_matrix_post[$id]['expires']['month'] = $adi_matrix_post[$id]['expires']['day'] = $adi_matrix_post[$id]['expires']['hour'] = $adi_matrix_post[$id]['expires']['minute'] = $adi_matrix_post[$id]['expires']['second'] = '00';
			}
	}

	if ($adi_matrix_debug) {
		echo '<b>$_POST:</b>';
		dmp($_POST);
		echo '<b>$adi_matrix_post:</b>';
		dmp($adi_matrix_post);
	}

	return $adi_matrix_post;
}

function adi_matrix_get_updates($adi_matrix_post,$adi_matrix_articles) {
// compare submitted article data with database data & create $adi_matrix_updates[id][field] if changed
	global $adi_matrix_debug;

	$adi_matrix_updates = array();
	foreach ($adi_matrix_post as $id => $data) {
		foreach ($data as $index => $new_value) {
			if ($id == 'new') {
				$adi_matrix_updates[$id][$index] = $new_value;
			}
			else {
				$equal = TRUE;
				$old_value = $adi_matrix_articles[$id][$index];
				$test_value = $new_value;
				if ($index == 'keywords') // remove human friendly spaces after commas
					$test_value = str_replace(', ' ,',', $new_value);
				if (($index == 'posted') || ($index == 'expires'))
					if (array_key_exists('reset_time',$new_value))
						$equal = FALSE; // force inequality - actual "now" time will be set in database update
					else // convert date array to date/time string
						$test_value = $new_value['year'].'-'.$new_value['month'].'-'.$new_value['day'].' '.$new_value['hour'].':'.$new_value['minute'].':'.$new_value['second'];
	//			echo 'test_value='.$test_value,', old_value='.$old_value.'<br>';
				$equal = $equal && (strcmp($test_value,$old_value) == 0);
				if (!$equal)
					$adi_matrix_updates[$id][$index] = $new_value;
			}
		}
	}

	if ($adi_matrix_debug) {
		echo '<b>$adi_matrix_updates:</b>';
		dmp($adi_matrix_updates);
	}

	return $adi_matrix_updates;
}

function adi_matrix_validate_post_data($updates) {
// article data validation
	global $adi_matrix_debug,$adi_matrix_articles,$adi_matrix_validation_errors;

	$error_list = array(); // fields with errors indexed by article id

	// create array of empties indexed by $adi_matrix_validation_errors id
	$new_error_list = array();
	foreach ($adi_matrix_validation_errors as $i => $v)
		$new_error_list[$i] = array();

	foreach ($updates as $id => $data) {

		// add empty "error" slots for article
		foreach ($adi_matrix_validation_errors as $i => $v)
			$new_error_list[$i][$id] = array();

		// remember old timestamp values (existing articles only)
		if ($id != 'new') {
			$posted = $adi_matrix_articles[$id]['posted'];
			$expires = $adi_matrix_articles[$id]['expires'];
		}

		// iterate through $data (OTT but may change in the future)
		foreach ($data as $field => $value) {
			// do some date/time checking
			if (($field == 'posted') || ($field == 'expires')) {
				// record new (i.e. $_POSTed) timestamp values
				$$field = $value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minute'].':'.$value['second'];
				if ($field == 'posted')
					if (array_key_exists('reset_time',$value))
						$$field = date('Y-m-d H:i:s',time()); // have to predict the reset date/time (Article tab does it this way too!)
				// check it's a valid date/time
				$error = (!is_numeric($value['year']) || !is_numeric($value['month']) || !is_numeric($value['day']) || !is_numeric($value['hour'])  || !is_numeric($value['minute']) || !is_numeric($value['second']));
				$ts = strtotime($value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minute'].':'.$value['second']);
				$error = $error || ($ts === FALSE || $ts === -1);
				// special case - allow all blanks in expires
				if ($error && ($field == 'expires'))
					$error = !(empty($value['year']) && empty($value['month']) && empty($value['day']) && empty($value['hour']) && empty($value['minute']) && empty($value['second']));
				if ($error) {
					$error_list[$id]['fields'][] = $field;
					$error_list[$id]['errors'][] = $adi_matrix_validation_errors[0];
					if ($id != 'new')
						$$field = $adi_matrix_articles[$id][$field]; // restore old value (so it doesn't influence later "expires before posted" checking)
					$new_error_list['0'][$id][] = $field;
				}
			}
		}

		// check expires is not before posted (but only if expires is set)
		if ((strtotime($expires) < strtotime($posted)) && ($expires != '0000-00-00 00:00:00')) {
			$error_list[$id]['fields'][] = 'posted';
			$error_list[$id]['fields'][] = 'expires';
			$error_list[$id]['errors'][] = $adi_matrix_validation_errors[1];
			$new_error_list['1'][$id][] = 'posted';
			$new_error_list['1'][$id][] = 'expires';
		}

		// check URL-titles (duplicates & blanks)
		// title supplied if new or edited, get title if not supplied
		if (isset($data['title']))
			$title = $data['title'];
		else
			$title = $adi_matrix_articles[$id]['title'];
		$msg = 0;
		$url_title = stripSpace($title, 1);
		if (trim($url_title) =='') // blank
			$msg = 3;
		if ($msg) {
			$error_list[$id]['fields'][] = 'title';
			$error_list[$id]['errors'][] = $adi_matrix_validation_errors[$msg];
			$new_error_list[$msg][$id][] = 'url_title';
		}
		$url_title_count = safe_count('textpattern', "url_title = '$url_title'");
		if ($url_title_count > 1) // duplicate
			$msg = 2;
		if ($msg) {
			$error_list[$id]['fields'][] = 'title';
			$error_list[$id]['errors'][] = $adi_matrix_validation_errors[$msg];
			$new_error_list[$msg][$id][] = 'url_title';
		}

	}

	// lose the empties
	$new_error_list = array_filter(array_map('array_filter', $new_error_list));

	if ($adi_matrix_debug) {
		echo '<b>Invalid fields:</b>';
//		dmp($error_list);
		dmp($new_error_list);
	}

	return $new_error_list;
//	return $error_list;
}

function adi_matrix_remove_errors($updates,$errors) {
// remove fields with invalid data from article update list
	global $adi_matrix_debug;

//	foreach ($errors as $id => $this_error)
//		foreach ($this_error['fields'] as $field)
//			unset($updates[$id][$field]);
	foreach ($errors as $article)
		foreach ($article as $id => $fields)
			foreach ($fields as $field)
				unset($updates[$id][$field]);


	return $updates;
}

function adi_matrix_debug($adi_matrix_articles,$matrix_index) {
// plot in the title
	global $event,$step,$adi_matrix_cfs,$adi_matrix_list,$adi_matrix_glz_cfs,$adi_matrix_categories;

	echo "<p><b>Event:</b> ".$event.", <b>Step:</b> ".$step."</p>";
	echo '<b>This matrix:</b>';
	dmp($adi_matrix_list[$matrix_index]);
	echo '<b>$adi_matrix_cfs:</b>';
	dmp($adi_matrix_cfs);
	if ($adi_matrix_glz_cfs) {
		echo '<b>Custom field input types:</b><br/>';
		foreach ($adi_matrix_cfs as $index => $title) {
			$row = safe_row('html','txp_prefs',"name = 'custom_".$index."_set'"); // get html input type from prefs
			echo 'custom_'.$index.' - '.$row['html'].'<br/>';
		}
		echo '<br/>';
	}
	echo '<b>$adi_matrix_categories:</b><br/>';
	dmp($adi_matrix_categories);
	echo '<b>$adi_matrix_articles:</b>';
	dmp($adi_matrix_articles);
}

function adi_matrix_table_head($matrix_index,$type) {
// matrix <table> header stuff
	global $event,$adi_matrix_cfs,$adi_matrix_list;

	// get current sort settings
	list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));

	if ($type == 'header') {
		$tag = 'th';
		$wraptag = 'thead';
	}
	else {
		$tag = 'td';
		$wraptag = 'tfoot';
	}

	// Article id/title heading
	$field_list = array('id','title');
	foreach ($field_list as $field) {
		$var = $field.'_hcell';
		if ($field == $sort) // sort value matches field
			$dir == 'desc' ? $class = ' class="desc"' : $class = ' class="asc"'; // up/down arrow
		else
			$class = ''; // no arrow - sort set in admin
		$$var = tag(elink($event,'','sort',$field,adi_matrix_gtxt($field),'dir',($dir == 'asc' ? 'desc' : 'asc'),'','',''),$tag,$class); // column heading/toggle sort
	}

	// Standard field headings
	$field_list = array('status','article_image','keywords','category1','category2','posted','expires','section');
	foreach ($field_list as $field) {
		$var = $field.'_hcell';
		if ($field == $sort) // sort value matches field
			$dir == 'desc' ? $class = ' class="desc"' : $class = ' class="asc"'; // up/down arrow
		else
			$class = ''; // no arrow - sort set in admin
		$adi_matrix_list[$matrix_index][$field] ? $$var = tag(elink($event,'','sort',$field,adi_matrix_gtxt($field),'dir',($dir == 'asc' ? 'desc' : 'asc'),'','',''),$tag,$class) : $$var = ''; // column heading/toggle sort
	}

	// Custom field headings
	$cf_hcell = '';
	foreach ($adi_matrix_cfs as $index => $cf_name) {
		$custom_x = 'custom_'.$index;
		if ($custom_x == $sort) // sort value matches cf name
			$dir == 'desc' ? $class = ' class="desc"' : $class = ' class="asc"'; // up/down arrow
		else
			$class = ''; // no arrow - sort set in admin
		if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) // check that custom field is known to adi_matrix
			if ($adi_matrix_list[$matrix_index][$custom_x])
				$cf_hcell .= tag(elink($event,'','sort',$custom_x,$cf_name,'dir',($dir == 'asc' ? 'desc' : 'asc'),'','',''),$tag,$class);
	}

	// Delete heading
	$del_hcell = tag(gTxt('delete'),$tag,' class="adi_matrix_delete"');
//	$del_hcell = tag(sp,$tag);

	// Show section heading
	if ($sort == 'section') // sort value matches field
		$dir == 'desc' ? $class = ' class="desc"' : $class = ' class="asc"'; // up/down arrow
	else
		$class = ''; // no arrow - sort set in admin
	$show_section_hcell = tag(elink($event,'','sort','section',adi_matrix_gtxt('section'),'dir',($dir == 'asc' ? 'desc' : 'asc'),'','',''),$tag,$class);

	return tag(
				tr(
					(adi_matrix_pref('adi_matrix_display_id') ? $id_hcell : '')
					.$title_hcell
					.($adi_matrix_list[$matrix_index]['show_section'] ? $show_section_hcell : '') // THIS NEEDS SORTING OUT
					.($adi_matrix_list[$matrix_index]['section'] ? $section_hcell : '') // THIS NEEDS SORTING OUT
					.$status_hcell
					.$cf_hcell
					.$article_image_hcell
					.$keywords_hcell
					.$category1_hcell
					.$category2_hcell
					.$posted_hcell
					.$expires_hcell
					.($adi_matrix_list[$matrix_index]['publish'] ? $del_hcell : '')
				)
			,$wraptag);
}

function adi_matrix_table($adi_matrix_articles,$matrix_index,$errors=array(),$updates=array()) {
// generates matrix <table> and <form> for article data updates
	global $adi_matrix_debug,$adi_matrix_cfs,$adi_matrix_statuses,$adi_matrix_list,$txp_user,$txp_user,$adi_matrix_glz_cfs,$adi_matrix_validation_errors;

	$out = '';
	$out .= adi_matrix_table_head($matrix_index,'header');
	if ($adi_matrix_list[$matrix_index]['footer'])
		$out .= adi_matrix_table_head($matrix_index,'footer');
	$out .= '<tbody>';
	if ($adi_matrix_articles) {
		foreach ($adi_matrix_articles as $id => $data) {
			// set up validation error flags for this article
			$article_errors = array();
			foreach ($errors as $error_type)
				if (isset($error_type[$id]))
					$article_errors = array_merge($article_errors,$error_type[$id]);
			$article_errors = array_unique($article_errors);
			if ($adi_matrix_debug) {
				echo '<b>Validation errors #'.$id.':</b>';
				dmp($article_errors);
			}
			// based on standard save button in txp_article.php
			$Status = $data['status'];
			$AuthorID = $data['author'];
			$has_privs = // work out if user has a right to fiddle with article
				(($Status >= 4 and has_privs('article.edit.published'))
				or ($Status >= 4 and $AuthorID==$txp_user and has_privs('article.edit.own.published'))
				or ($Status <  4 and has_privs('article.edit'))
				or ($Status <  4 and $AuthorID==$txp_user and has_privs('article.edit.own')));
			$prefix = 'article_'.$id; // use array index 'article_id' rather than 'id' in POST data (clearer/safer?)
			$out .= '<tr>';
			$highlight = $data['highlight'];
			// article title link tooltip text
			// tooltip (&#10; = newline in non-Firefox tooltip)
			if (adi_matrix_pref('adi_matrix_article_tooltips')) {
				$title_text = '#'.$id.', '.adi_matrix_gtxt('posted').' '.$data['posted'];
				if ($data['expires'] != '0000-00-00 00:00:00')
					$title_text .= ', '.adi_matrix_gtxt('expires').' '.$data['expires'];
				if ($highlight == 1)
					$title_text .= ' ('.adi_matrix_gtxt('expired').')';
				if ($highlight == 2)
					$title_text .= ' ('.adi_matrix_gtxt('time_future').')';
				$title_text .= ', '.$data['section'];
				$title_text .= ', '.$AuthorID;
			}
			else
				$title_text = adi_matrix_gtxt('edit');
			$class = '';
			// highlighting for expired/future articles
			if (adi_matrix_pref('adi_matrix_article_highlighting')) {
				if ($highlight) {
					if ($highlight == 1) $class = ' class="adi_matrix_expired"';
					if ($highlight == 2) $class = ' class="adi_matrix_future"';
				}
			}
			// ID
			if (adi_matrix_pref('adi_matrix_display_id')) {
				$id_link = eLink('article','edit','ID',$id,$id);
				$out .= tag($id_link,'td');
			}
			// title
			$article_title = trim($data['title']);
			if ($article_title == '') // blank title
				$title_link = tag(eLink('article','edit','ID',$id,adi_matrix_gtxt('untitled'),'','',$title_text),'em');
			else
				$title_link = eLink('article','edit','ID',$id,$article_title,'','',$title_text);
			$title_link = '<span title="'.$title_text.'"'.$class.'>'.$title_link.'</span>';
			$arrow_link = sp.eLink('article','edit','ID',$id,'&rarr;','','',$title_text);
			if ($adi_matrix_list[$matrix_index]['title'] && adi_matrix_pref('adi_matrix_display_id')) // don't need arrow if got IDs
				$arrow_link = '';
			if ($adi_matrix_list[$matrix_index]['title']) {
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(finput("text",$prefix."[title]",$data['title'],'',(adi_matrix_pref('adi_matrix_input_field_tooltips') ?htmlspecialchars($data['title']):'')).$arrow_link) :
					$out .= tda($title_link);
			}
			else
				$out .= tag($title_link,'td');
			// section
			if ($adi_matrix_list[$matrix_index]['show_section'])
				$out .= tda($data['section']);
			if ($adi_matrix_list[$matrix_index]['section']) {
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(adi_matrix_section_popup($prefix."[section]",$data['section'],$adi_matrix_list[$matrix_index]['criteria_section'])) :
					$out .= ($data['section'] ? tda($data['section']) : tda('&nbsp;'));
			}
			// status
			if ($adi_matrix_list[$matrix_index]['status'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(selectInput($prefix.'[status]',$adi_matrix_statuses,$data['status'])) :
					$out .= tda($adi_matrix_statuses[$data['status']]);
			// custom fields
			foreach ($adi_matrix_cfs as $index => $cf_name) {
				$custom_x = 'custom_'.$index;
				if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) // check that custom field is known to adi_matrix
					if ($adi_matrix_list[$matrix_index][$custom_x])
						if ($has_privs) // decide if user gets input fields or not
							if ($adi_matrix_glz_cfs) {
								$glz_input_stuff = adi_matrix_glz_cfs_input($custom_x,$prefix."[$custom_x]",$data[$custom_x]);
								if ($glz_input_stuff[1] == 'glz_custom_radio_field') // don't apply glz_class coz can't handle glz reset function properly yet - see below
									$out .= tda($glz_input_stuff[0]);
								else
									$out .= tda($glz_input_stuff[0],' class="'.$glz_input_stuff[1].'"');
							}
							else
								$out .= tda(finput("text",$prefix."[$custom_x]",$data[$custom_x],'',(adi_matrix_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($data[$custom_x]):'')));
						else
							$out .= ($data[$custom_x] ? tda($data[$custom_x]) : tda('&nbsp;')); // make sure the table cell stretches if no data
			}
			// article image
			if ($adi_matrix_list[$matrix_index]['article_image']) {
				$arrow_link = '';
				if (trim($data['article_image']) && ($adi_matrix_list[$matrix_index]['cf_links'] == 'article_image')) {
					$image_ids = explode(',',$data['article_image']);
					$image_id = $image_ids[0];
					if (safe_count('txp_image',"id=$image_id",$adi_matrix_debug))
						$arrow_link = sp.eLink('image','image_edit','id',$image_id,'&rarr;','','',adi_matrix_gtxt('edit_image').' #'.$image_id);
					else // image not found
						$arrow_link = sp.'?';
				}
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(finput("text",$prefix."[article_image]",$data['article_image'],'',(adi_matrix_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($data['article_image']):'')).$arrow_link) :
					$out .= ($data['article_image'] ? tda($data['article_image'].$arrow_link) : tda('&nbsp;')); // make sure the table cell stretches if no data
			}
			// keywords
			if ($adi_matrix_list[$matrix_index]['keywords'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda('<textarea name="'.$prefix."[keywords]".'" cols="18" rows="5" class="mceNoEditor">'.htmlspecialchars(str_replace(',' ,', ', $data['keywords'])).'</textarea>') :
					$out .= ($data['keywords'] ? tda($data['keywords']) : tda('&nbsp;'));
			// category1
			if ($adi_matrix_list[$matrix_index]['category1'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(adi_matrix_category_popup($prefix."[category1]",$data['category1'],FALSE)) :
					$out .= ($data['category1'] ? tda($data['category1']) : tda('&nbsp;'));
			// category2
			if ($adi_matrix_list[$matrix_index]['category2'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(adi_matrix_category_popup($prefix."[category2]",$data['category2'],FALSE)) :
					$out .= ($data['category2'] ? tda($data['category2']) : tda('&nbsp;'));
			// posted
			$class = 'adi_matrix_timestamp';
//			if (array_key_exists($id,$errors)) {
//				if (array_search('posted',$errors[$id]['fields']) !== FALSE) {
//					$class .= ' adi_matrix_error';
//				}
//			}
			if (array_search('posted',$article_errors) !== FALSE)
				$class .= ' adi_matrix_error';
			if ($adi_matrix_list[$matrix_index]['posted'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(adi_matrix_timestamp_input($prefix."[posted]",$data['posted'],'posted'),' class="'.$class.'"') :
					$out .= ($data['posted'] ? tda($data['posted']) : tda('&nbsp;'));
			// expires
			$class = 'adi_matrix_timestamp';
//			if (array_key_exists($id,$errors)) {
//				if (array_search('expires',$errors[$id]['fields']) !== FALSE) {
//					$class .= ' adi_matrix_error';
//				}
//			}
			if (array_search('expires',$article_errors) !== FALSE)
				$class .= ' adi_matrix_error';
			if ($adi_matrix_list[$matrix_index]['expires'])
				$has_privs ? // decide if user gets input fields or not
					$out .= tda(adi_matrix_timestamp_input($prefix."[expires]",$data['expires'],'expires'),' class="'.$class.'"') :
					$out .= ($data['expires'] ? tda($data['expires']) : tda('&nbsp;'));
			// delete button
			if ($adi_matrix_list[$matrix_index]['publish']) { // got publish? - might delete!
				 // a closer look at credentials - override delete priv OR (delete own priv and it's yours to delete)
				if (has_privs('article.delete') || (has_privs('article.delete.own') && ($AuthorID == $txp_user))) {
					$event = 'adi_matrix_matrix_'.$matrix_index;
					$step = 'delete';
					$url = '?event='.$event.a.'step='.$step.a.'id='.$id;
					$button =
							'<a href="'
							.$url
							.'" class="dlink" title="Delete?" onclick="return verify(\''
							.addslashes(htmlentities($data['title']))
							.' - '
							.adi_matrix_gtxt('confirm_delete_popup')
							.'\')">&#215;</a>';
					$out .= tda($button,' class="adi_matrix_delete"');
				}
				else
					$out .= tda('&nbsp;');
			}

			$out .= '</tr>';
		}
	}

	if ($adi_matrix_list[$matrix_index]['publish'] && has_privs('article'))
		$out .= adi_matrix_new_article($matrix_index);

	$out .= '</tbody>';
	return $out;
}

function adi_matrix_new_article($matrix_index) {
// data input fields for new article
	global $adi_matrix_debug,$adi_matrix_cfs,$adi_matrix_statuses,$adi_matrix_list,$adi_matrix_glz_cfs;

	$defaults = adi_matrix_article_defaults($matrix_index);
	if ($adi_matrix_debug) {
		echo '<b>New article defaults:</b>';
		dmp($defaults);
	}
	$prefix = 'article_new';
	$out = '';
	$out .= '<tr>';
	// ID placeholder
	if (adi_matrix_pref('adi_matrix_display_id'))
		$out .= tag('+','td');
	// title
	$out .= tda(finput("text",$prefix."[title]",$defaults['title']));
	// section
	if ($adi_matrix_list[$matrix_index]['show_section'])
		$out .= tda($defaults['section']);
	else if ($adi_matrix_list[$matrix_index]['section'])
		$out .= tda(adi_matrix_section_popup($prefix."[section]",$defaults['section'],$adi_matrix_list[$matrix_index]['criteria_section']));
	// status
	if ($adi_matrix_list[$matrix_index]['status'])
		$out .= tda(selectInput($prefix.'[status]',$adi_matrix_statuses,$defaults['status']));
	// custom fields
	foreach ($adi_matrix_cfs as $index => $cf_name) {
		$custom_x = 'custom_'.$index;
		if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) // check that custom field is known to adi_matrix
			if ($adi_matrix_list[$matrix_index][$custom_x])
				if ($adi_matrix_glz_cfs) {
					$glz_input_stuff = adi_matrix_glz_cfs_input($custom_x,$prefix."[$custom_x]",$defaults[$custom_x]);
					if ($glz_input_stuff[1] == 'glz_custom_radio_field') // don't apply glz_class coz can't handle glz reset function properly yet - see below
						$out .= tda($glz_input_stuff[0]);
					else
						$out .= tda($glz_input_stuff[0],' class="'.$glz_input_stuff[1].'"');
				}
				else
					$out .= tda(finput("text",$prefix."[$custom_x]",$defaults[$custom_x],'',(adi_matrix_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($defaults[$custom_x]):'')));
	}
	// article image
	if ($adi_matrix_list[$matrix_index]['article_image'])
		$out .= tda(finput("text",$prefix."[article_image]",$defaults['title']));
	// keywords
	if ($adi_matrix_list[$matrix_index]['keywords'])
		$out .= tda('<textarea name="'.$prefix."[keywords]".'" cols="18" rows="5" class="mceNoEditor">'.htmlspecialchars(str_replace(',' ,', ', $defaults['keywords'])).'</textarea>');
	// category1
	if ($adi_matrix_list[$matrix_index]['category1'])
		$out .= tda(adi_matrix_category_popup($prefix."[category1]",$defaults['category1'],FALSE));
	// category2
	if ($adi_matrix_list[$matrix_index]['category2'])
		$out .= tda(adi_matrix_category_popup($prefix."[category2]",$defaults['category2'],FALSE));
	// posted
	if ($adi_matrix_list[$matrix_index]['posted'])
		$out .= tda(adi_matrix_timestamp_input($prefix."[posted]",$defaults['posted'],'posted'),' class="adi_matrix_timestamp"');
	// expires
	if ($adi_matrix_list[$matrix_index]['expires'])
		$out .= tda(adi_matrix_timestamp_input($prefix."[expires]",$defaults['expires'],'expires'),' class="adi_matrix_timestamp"');

	// Delete placeholder
	if ($adi_matrix_list[$matrix_index]['publish'])
		$out .= tag(sp,'td',' class="adi_matrix_delete"');

	$out .= '</tr>';

	return $out;
}

function adi_matrix_matrix($event,$step) {
// a matrix tab
	global $prefs,$adi_matrix_debug,$adi_matrix_list,$adi_matrix_articles,$adi_matrix_cfs,$adi_matrix_sort_dir,$adi_matrix_sort_type,$adi_matrix_sort_options,$adi_matrix_validation_errors;

	// extract matrix index from event (e.g. adi_matrix_matrix_0 => 0)
	$matrix_index = str_replace('adi_matrix_matrix_','',$event);

	// bomb out if upgrade needed
	$upgrade_required = adi_matrix_upgrade();
	if ($upgrade_required) {
		pagetop($adi_matrix_list[$matrix_index]['name'],array(adi_matrix_gtxt('adi_upgrade_required'),E_WARNING));
		return;
	}

	// current sort settings (read from user pref, default to matrix settings)
	list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));

	// user sort changes
	$new_sort = doStripTags(gps('sort'));
	$new_dir = doStripTags(gps('dir'));
	$new_sort_type = doStripTags(gps('sort_type'));
	$reset_sort = doStripTags(gps('reset_sort'));
	// sort it all out
	if ($new_sort || $new_dir || $new_sort_type || $reset_sort) {
		if ($new_sort && $new_dir) // column heading clicked
			adi_matrix_pref($event.'_sort',$new_sort.','.$new_dir.','.$sort_type,TRUE); // update user pref with sort & dir
		else if ($new_sort_type) // sort_type change
			adi_matrix_pref($event.'_sort',$sort.','.$dir.','.$new_sort_type,TRUE); // update user pref with sort_type
		else if ($reset_sort) { // reset sort to default
			safe_delete('txp_prefs',"name = '".$event."_sort'",$adi_matrix_debug); // delete user pref
			$prefs = get_prefs();
		}
		// reread user pref, defaulting to matrix settings
		list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));
	}

	if ($adi_matrix_debug) {
		echo 'Current sort = '.get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']);
	}

	// article selection criteria
	$criteria = array();
	$criteria['section'] = $adi_matrix_list[$matrix_index]['criteria_section'];
	$criteria['category'] = $adi_matrix_list[$matrix_index]['criteria_category'];
	$criteria['descendent_cats'] = $adi_matrix_list[$matrix_index]['criteria_descendent_cats'];
	$criteria['status'] = $adi_matrix_list[$matrix_index]['criteria_status'];
	$criteria['author'] = $adi_matrix_list[$matrix_index]['criteria_author'];
	$criteria['keywords'] = $adi_matrix_list[$matrix_index]['criteria_keywords'];
	$criteria['timestamp'] = $adi_matrix_list[$matrix_index]['criteria_timestamp'];
	$criteria['expiry'] = $adi_matrix_list[$matrix_index]['criteria_expiry'];
	$criteria['condition'] = $adi_matrix_list[$matrix_index]['criteria_condition'];

	// initialise some bits
	$message = '';
	$adi_matrix_articles = adi_matrix_get_articles($criteria,$matrix_index,$sort,$dir,$sort_type);
	$updates = $errors = array();

	// $step aerobics
	if ($step == 'update') {
		$post_data = adi_matrix_get_post_data($adi_matrix_articles,$matrix_index);
		$errors = adi_matrix_validate_post_data($post_data);
		$updates = adi_matrix_get_updates(adi_matrix_remove_errors($post_data,$errors),$adi_matrix_articles);
		if ($updates) {
			$ok = adi_matrix_update_articles($updates,$matrix_index);
			$ok ? $message = adi_matrix_gtxt('adi_articles_saved') : $message = array(adi_matrix_gtxt('adi_article_update_fail'),E_WARNING);
			$adi_matrix_articles = adi_matrix_get_articles($criteria,$matrix_index,$sort,$dir,$sort_type);
		}
		else
			$message = adi_matrix_gtxt('adi_articles_not_modified');
		if ($errors) {
			$message .= '. '.adi_matrix_gtxt('adi_matrix_validation_error','',':');
//			foreach ($errors as $id => $article_errors)
//				$message .= ' '.$id.' ('.implode(',',array_unique($article_errors['errors'])).')';
			foreach ($errors as $i => $v)
				$message .= ' '.$adi_matrix_validation_errors[$i].' ('.implode(',',array_keys($v)).')';
			$message = array($message,E_WARNING);
		}
	}
	else if ($step == 'delete') {
		$id = gps('id');
		if (isset($adi_matrix_articles[$id])) {
			$ok = adi_matrix_delete_article($id);
			$message = adi_matrix_gtxt('article_deleted').' ('.$id.')';
		}
		else {
			$message = array(adi_matrix_gtxt('adi_article_delete_fail'),E_ERROR);
		}
		$adi_matrix_articles = adi_matrix_get_articles($criteria,$matrix_index,$sort,$dir,$sort_type);
	}

	// generate page
	pagetop($adi_matrix_list[$matrix_index]['name'],$message);

	// output matrix table & input form
	$table = adi_matrix_table($adi_matrix_articles,$matrix_index,$errors,$updates);
	$tags = array('<input', '<textarea', '<select'); // tags which indicate that a save button is deserved
	$save_button = FALSE;
	foreach ($tags as $tag)
		$save_button = $save_button || strpos($table,$tag);
	$class = 'adi_matrix_matrix';
	if ($adi_matrix_list[$matrix_index]['scroll'])
		$class .= ' adi_matrix_scroll';
	$class .= ' txp-list';
	echo form(
		tag($adi_matrix_list[$matrix_index]['name'],'h1')
		.'<div class="scroll_box">'
		.startTable('list','',$class)
		.$table
		.endTable()
		.'</div>'
		.(empty($adi_matrix_articles) ?
			graf(tag(adi_matrix_gtxt('no_articles_recorded'),'em'),' class="adi_matrix_none"')
			: ''
		)
		.($save_button ?
			tag(
				hInput('count',count($adi_matrix_articles)) // secret count of articles for checking later (e.g. with $_POST size limit problems)
				.fInput("submit", "do_something", adi_matrix_gtxt('save'), "publish")
				.eInput("adi_matrix_matrix_".$matrix_index).sInput("update"),
				'div',
				' class="adi_matrix_button"'
			)
			: ''
		)
		.tag(
			graf(
				adi_matrix_gtxt('adi_default_sort','',':')
				.sp
				.elink(
					$event
					,''
					,'reset_sort'
					,1
					,$adi_matrix_sort_options[$adi_matrix_list[$matrix_index]['sort']]
						.', '
						.$adi_matrix_sort_dir[$adi_matrix_list[$matrix_index]['dir']]
						.', '
						.$adi_matrix_sort_type[$adi_matrix_list[$matrix_index]['sort_type']]
					,'','','' // to override TXP 4.5 default title "Edit"
				)
				.br
				.adi_matrix_gtxt('adi_sort_type','',':')
				.sp
				.adi_matrix_gtxt('adi_'.$sort_type)
				.' ['
				.elink(
					$event
					,''
					,'sort_type'
					,($sort_type == 'numerical' ? 'alphabetical' : 'numerical')
					,adi_matrix_gtxt(($sort_type == 'numerical' ? 'adi_alphabetical' : 'adi_numerical'))
					,'','','' // to override TXP 4.5 default title "Edit"
				)
				.']'
			)
			,'div',' class="adi_matrix_matrix_prefs"')
		,''
		,''
		,'post'
		,$class
	);

	// flashing message
	if ($errors) {
		echo <<<END_SCRIPT
			<script type="text/javascript">
			<!--
			$(document).ready( function(){
						$('#messagepane').fadeOut(800).fadeIn(800);
						$('#messagepane').fadeOut(800).fadeIn(800);
					} )
			// -->
			</script>
END_SCRIPT;
	}

	if ($adi_matrix_debug)
		adi_matrix_debug($adi_matrix_articles,$matrix_index);
}

function adi_matrix_installed($table='adi_matrix') {
// test if database table is present

	$rs = safe_query("SHOW TABLES LIKE '".safe_pfx($table)."'");
	$a = nextRow($rs);
	if ($a)
		return TRUE;
	else
		return FALSE;
}

function adi_matrix_install() {
// install adi_matrix table in database
	global $adi_matrix_debug,$adi_matrix_cfs;

	$cfq = '';
	foreach ($adi_matrix_cfs as $index => $value) {
		$cfq .= ', `custom_'.$index.'` TINYINT(1) DEFAULT 0 NOT NULL';
	}

	$res = safe_query(
		"CREATE TABLE IF NOT EXISTS "
		.safe_pfx('adi_matrix')
		." (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`name` VARCHAR(255) NOT NULL,
		`sort` VARCHAR(32) NOT NULL DEFAULT 'posted',
		`sort_type` VARCHAR(32) NOT NULL DEFAULT 'alphabetical',
		`dir` VARCHAR(32) NOT NULL DEFAULT 'desc',
		`user` VARCHAR(64) NOT NULL DEFAULT '',
		`privs` VARCHAR(16) NOT NULL DEFAULT '',
		`scroll` TINYINT(1) DEFAULT 0 NOT NULL,
		`footer` TINYINT(1) DEFAULT 0 NOT NULL,
		`title` TINYINT(1) DEFAULT 0 NOT NULL,
		`publish` TINYINT(1) DEFAULT 0 NOT NULL,
		`show_section` TINYINT(1) DEFAULT 0 NOT NULL,
		`cf_links` VARCHAR(128) NOT NULL DEFAULT '',
		`tab` VARCHAR(16) NOT NULL DEFAULT 'content',
		`criteria_section` VARCHAR(128) NOT NULL DEFAULT '',
		`criteria_category` VARCHAR(128) NOT NULL DEFAULT '',
		`criteria_status` INT(2) NOT NULL DEFAULT '4',
		`criteria_author` VARCHAR(64) NOT NULL DEFAULT '',
		`criteria_keywords` VARCHAR(255) NOT NULL DEFAULT '',
		`criteria_timestamp` VARCHAR(16) NOT NULL DEFAULT 'any',
		`criteria_expiry` INT(2) NOT NULL DEFAULT '0',
		`criteria_descendent_cats` TINYINT(1) DEFAULT 0 NOT NULL,
		`criteria_condition` VARCHAR(255) NOT NULL DEFAULT '',
		`status` TINYINT(1) DEFAULT 0 NOT NULL,
		`keywords` TINYINT(1) DEFAULT 0 NOT NULL,
		`article_image` TINYINT(1) DEFAULT 0 NOT NULL,
		`category1` VARCHAR(128) NOT NULL DEFAULT '',
		`category2` VARCHAR(128) NOT NULL DEFAULT '',
		`posted` TINYINT(1) DEFAULT 0 NOT NULL,
		`expires` TINYINT(1) DEFAULT 0 NOT NULL,
		`section` TINYINT(1) DEFAULT 0 NOT NULL
		"
		.$cfq
		.");"
		,$adi_matrix_debug);
 	return $res;
}

function adi_matrix_uninstall() {
// uninstall adi_matrix
	global $adi_matrix_debug;

	// delete table
	$res = safe_query("DROP TABLE ".safe_pfx('adi_matrix').";",$adi_matrix_debug);
	// delete preferences
	$res = $res && safe_delete('txp_prefs',"name LIKE 'adi_matrix_%'",$adi_matrix_debug);
	return $res;
}

function adi_matrix_lifecycle($event,$step) {
// a matter of life & death
// $event:	"plugin_lifecycle.adi_matrix"
// $step:	"installed", "enabled", disabled", "deleted"
// TXP 4.5:	upgrade/reinstall only triggers "installed" event (now have to manually detect whether upgrade required)
	global $adi_matrix_debug;

	$result = '?';
	// set upgrade flag if upgrading/reinstalling in TXP 4.5+
	$upgrade = (($step == "installed") && adi_matrix_installed());
	if ($step == 'enabled') {
		$result = $upgrade = adi_matrix_install();
	}
	else if ($step == 'deleted')
		$result = adi_matrix_uninstall();
	if ($upgrade)
		$result = $result && adi_matrix_upgrade(TRUE);
	if ($adi_matrix_debug)
		echo "Event=$event Step=$step Result=$result Upgrade=$upgrade";
}

function adi_matrix_upgrade($do_upgrade=FALSE) {
// check/perform upgrade
	global $adi_matrix_debug;

	$upgrade_required = FALSE;
	// version 0.2
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'article_image'",$adi_matrix_debug); // find out if column exists
	$a = nextRow($rs);
	$v0_2 = empty($a);
	$upgrade_required = $upgrade_required || $v0_2;
	// version 0.3
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'criteria_timestamp'",$adi_matrix_debug);
	$a = nextRow($rs);
	$v0_3t = empty($a);
	$upgrade_required = $upgrade_required || $v0_3t;
	// version 0.3
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'criteria_expiry'",$adi_matrix_debug);
	$a = nextRow($rs);
	$v0_3e = empty($a);
	$upgrade_required = $upgrade_required || $v0_3e;
	// version 1.0
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'scroll'",$adi_matrix_debug);
	$a = nextRow($rs);
	$v1_0 = empty($a);
	$upgrade_required = $upgrade_required || $v1_0;
	// version 1.1
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'footer'",$adi_matrix_debug);
	$a = nextRow($rs);
	$v1_1 = empty($a);
	$upgrade_required = $upgrade_required || $v1_1;
	// version 2.0
	$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'title'",$adi_matrix_debug);
	$a = nextRow($rs);
	$v2_0 = empty($a);
	$upgrade_required = $upgrade_required || $v2_0;

	if ($do_upgrade && $upgrade_required) {
		$res = TRUE;
		if ($v0_2)
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `article_image` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
		if ($v0_3t)
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_timestamp` VARCHAR(16) NOT NULL DEFAULT 'any'",$adi_matrix_debug);
		if ($v0_3e)
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_expiry` INT(2) NOT NULL DEFAULT '0'",$adi_matrix_debug);
		if ($v1_0) {
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `scroll` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `category1` VARCHAR(128) NOT NULL DEFAULT ''",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `category2` VARCHAR(128) NOT NULL DEFAULT ''",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `dir` VARCHAR(32) NOT NULL DEFAULT '1'",$adi_matrix_debug);
			// convert old `sort` to new `sort` & `dir`
			//	OLD sort=1 (Posted desc) 	->  NEW sort=1, dir=1
			//	OLD sort=2 (Posted asc) 	->  NEW sort=1, dir=2
			//	OLD sort=3 (Title)			->  NEW sort=2, dir=''
			//	OLD sort=4 (LastMod desc)	->  NEW sort=3, dir=1
			//	OLD sort=5 (LastMod asc)	->  NEW sort=3, dir=2
			$res = $res && safe_update('adi_matrix',"`dir` = '1'","`sort` IN ('1','4')",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`dir` = '2'","`sort` IN ('2','5')",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = '1'","`sort` = '2'",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = '2'","`sort` = '3'",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = '3'","`sort` IN ('4','5')",$adi_matrix_debug);
		}
		if ($v1_1) {
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `footer` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `sort_type` VARCHAR(32) NOT NULL DEFAULT 'alphabetical'",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `posted` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `expires` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_descendent_cats` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			// one day sort will be sorted
			//	OLD sort=1, NEW sort='posted'
			//	OLD sort=2, NEW sort='title',
			//	OLD sort=3, NEW sort='lastmod',
			//	OLD sort=4, NEW sort='expires',
			$res = $res && safe_update('adi_matrix',"`sort` = 'posted'","`sort` = '1'",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = 'title'","`sort` = '2'",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = 'lastmod'","`sort` = '3'",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort` = 'expires'","`sort` = '4'",$adi_matrix_debug);
			// sort_type "+ 0 asc/desc" now "numerical"
			$res = $res && safe_update('adi_matrix',"`sort_type` = 'alphabetical'","`dir` IN ('1','2')",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`sort_type` = 'numerical'","`dir` IN ('3','4')",$adi_matrix_debug);
			// sort direction
			$res = $res && safe_update('adi_matrix',"`dir` = 'desc'","`dir` IN ('1','4')",$adi_matrix_debug);
			$res = $res && safe_update('adi_matrix',"`dir` = 'asc'","`dir` IN ('2','3')",$adi_matrix_debug);
		}
		if ($v2_0) {
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `title` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `publish` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `show_section` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `cf_links` VARCHAR(128) NOT NULL DEFAULT ''",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `tab` VARCHAR(16) NOT NULL DEFAULT 'content'",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `section` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
			$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_condition` VARCHAR(255) NOT NULL DEFAULT ''",$adi_matrix_debug);
		}
		return $res;
	}
	else // report back only
		return $upgrade_required;
}

function adi_matrix_downgrade() {
// downgrade to previous version - 2.0 to 1.1/1.2 only
	global $adi_matrix_debug;

	$res = TRUE;
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `title`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `publish`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `show_section`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `cf_links`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `tab`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `section`",$adi_matrix_debug);
	$res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `criteria_condition`",$adi_matrix_debug);
	return $res;
}

function adi_matrix_cat_tree($list,$parent='root') {
// generate a tree of parent/child relationships
	$return = array();
	foreach ($list as $cat)
		if ($cat['parent'] == $parent)
			$return[$cat['name']] = adi_matrix_cat_tree($list,$cat['name']);
	return $return;
}

function adi_matrix_cat_descendents($tree,$parent=NULL,$found=FALSE) {
// find all descendents of a given parent
	$return = array();
	foreach ($tree as $name => $children) {
		if ($found)
			$return[] = $name;
		$return = array_merge($return,adi_matrix_cat_descendents($children,$parent,(($name == $parent) OR ($found))));
	}
	return $return;
}

function adi_matrix_categories($getTree_array) {
// create adi_matrix_categories array, indexed by cat name containing pertinent information
	global $adi_matrix_debug;

	// $getTree_array is array of arrays:
	//    'id' => 'xx',
	//    'name' =>	'category-name',
	//    'title' => 'Category Title',
	//    'level' => 0,  (in category hierarchy)
	//    'children' => 2,	(no. of children)
	//    'parent' => 'root',	(name of parent)

	$cat_tree = adi_matrix_cat_tree($getTree_array);

	$categories = array();
	foreach ($getTree_array as $this_cat) {
		$categories[$this_cat['name']]['parent'] = $this_cat['parent'];
		$categories[$this_cat['name']]['children'] = adi_matrix_cat_descendents($cat_tree,$this_cat['name']);
	}

	return $categories;
}

function adi_matrix_section_popup($select_name,$value,$list='') {
// generate section popup list for admin settings table
// where 'TRUE' not supported on MySQL 4.0.27 (OK in MySQL 5+), so use 1=1

	$blank_allowed = TRUE;
	$where = "name != 'default'";

	// set up where condition if section list supplied
	if ($list) {
		$a = explode(',',$list);
		foreach ($a as $i => $v)
			$a[$i] = "'$v'";
		$where .= 'AND name in ('.implode(',',$a).')';
		$blank_allowed = FALSE;
	}

	$rs = safe_column('name', 'txp_section', $where);
	if ($rs)
		return selectInput($select_name, $rs, $value, $blank_allowed);

	return false;
}

function adi_matrix_section_checkboxes($field_name,$value) {
// generate section checkboxes

	$section_list = explode(',',$value);
	$out = '';
	$rs = safe_column('name', 'txp_section', "name != 'default'");
	if ($rs) {
		foreach ($rs as $section)
			$out .= tag(checkbox($field_name.'[]',$section,(array_search($section,$section_list) !== FALSE ? '1' : '0')).$section,'label');
		return $out;
	}
	return FALSE;
}

function adi_matrix_category_popup($select_name,$value,$admin=TRUE) {
// generate category popup list for admin settings table

	$rs = getTree('root','article');
	if ($rs) {
		if ($admin) { /* create wildcards (wildcats?) */
			$wildcard_list = array('no_category','any_category','one_category','two_categories','any_parent_category','any_child_category');
			foreach (array_reverse($wildcard_list) as $wildcard)
				/* add to front of array & renumber */
				array_unshift($rs,array('id' => '0', 'name' => '!'.$wildcard.'!', 'title' => adi_matrix_gtxt('adi_'.$wildcard), 'level' => '0', 'children' => '0', 'parent' => 'root'));
		}
		return treeSelectInput($select_name,$rs,$value,'',35);
	}
	return tag(adi_matrix_gtxt('no_categories_exist'),'em');
}

function adi_matrix_status_popup($select_name,$value) {
// generate status popup list for admin settings table
	global $adi_matrix_statuses;

	return selectInput($select_name, $adi_matrix_statuses, $value, TRUE);
}

function adi_matrix_timestamp_popup($select_name,$value) {
// generate timestamp popup list for admin settings table
	global $adi_matrix_timestamp_options;

	return selectInput($select_name, $adi_matrix_timestamp_options, $value, FALSE);
}

function adi_matrix_expiry_popup($select_name,$value) {
// generate timestamp popup list for admin settings table
	global $adi_matrix_expiry_options;

	return selectInput($select_name, $adi_matrix_expiry_options, $value, FALSE);
}

function adi_matrix_user_popup($select_name,$value,$wildcard=FALSE) {
// generate user/author popup list for admin settings table
	global $adi_matrix_statuses;

	$rs = safe_column('name', 'txp_users', '1=1');
	if ($rs) {
		if ($wildcard) { /* create wildcard */
			$logged_in_user = array('!logged_in_user!' => adi_matrix_gtxt('adi_logged_in_user'));
			$rs = $logged_in_user + $rs; /* add to front of array */
		}
		return selectInput($select_name, $rs, $value, TRUE);
	}
	return false;
}

function adi_matrix_privs_popup($select_name,$value) {
// generate privs popup list for admin settings table
	global $adi_matrix_groups;

	return selectInput($select_name, $adi_matrix_groups, $value, TRUE);
}

function adi_matrix_tab_popup($select_name,$value) {
// generate tab popup list for admin settings table
	global $adi_matrix_tabs;

	return selectInput($select_name, $adi_matrix_tabs, $value);
}

function adi_matrix_timestamp_input($name,$datetime,$type='posted') {
// date/time input fields - code stolen from include/txp_article.php

	if ($datetime == '0000-00-00 00:00:00')
		$ts = 0;
	else
		$ts = strtotime($datetime);
	$class = ' '.$type;
	if ($type == 'posted')
		$class .= ' created';
	$out =
		tag(
			adi_matrix_tsi($name.'[year]','%Y',$ts,'',$type)
			.' /'
			.adi_matrix_tsi($name.'[month]','%m',$ts,'',$type)
			.' /'
			.adi_matrix_tsi($name.'[day]','%d',$ts,'',$type)
			,'div'
			,' class="date'.$class.'"'
		)
		.tag(
			adi_matrix_tsi($name.'[hour]','%H',$ts,'',$type)
			.' :'
			.adi_matrix_tsi($name.'[minute]','%M',$ts,'',$type)
			.' :'
			.adi_matrix_tsi($name.'[second]','%S',$ts,'',$type)
			.($type == 'posted' ? br.tag(adi_matrix_gtxt('adi_reset'),'label',' class="reset_time-now"').sp.checkbox($name.'[reset_time]','1','0') : '')
			,'div'
			,' class="time'.$class.'"'
		);
	return $out;
}

function adi_matrix_tsi($name,$datevar,$time,$tab='',$type='') {
// date/time item input - adapted from txplib_forms.php

	preg_match_all('/.*\[(.*)\]$/',$name,$matches); // to get 'year' etc from article_x[posted][year]
	$short_name = $matches[1][0];
	if ($type == 'expires')
		$short_name = 'exp_'.$short_name;
	$size = ($short_name == 'year' or $short_name == 'exp_year') ? 4 : 2;
	$s = ($time == 0) ? '' : safe_strftime($datevar, $time);
	return
		n
		.'<input type="text" name="'.$name
		.'" value="'
		.$s
		.'" size="'
		.$size
		.'" maxlength="'
		.$size
		.'" class="edit '
		.$short_name
		.'"'
		.(empty($tab) ? '' : ' tabindex="'.$tab.'"')
		.' title="'
		.adi_matrix_gtxt('article_'.$short_name)
		.'" />';
	}

function adi_matrix_delete_button($matrix_list,$matrix_index) {
// matrix delete button [X]

	$event = 'adi_matrix_admin';
	$step = 'delete';
	$url = '?event='.$event.a.'step='.$step.a.'matrix='.$matrix_index;
	if ($matrix_index == 'new') // don't want delete button
		return '&nbsp;';
	else
		return
			'<a href="'
			.$url
			.'" class="dlink" title="Delete?" onclick="return verify(\''
			.$matrix_list[$matrix_index]['name']
			.' - '
			.adi_matrix_gtxt('confirm_delete_popup')
			.'\')">&#215;</a>';
}

function adi_matrix_delete($matrix_index) {
// stick a matrix in the bin
	global $adi_matrix_debug;

	$res = safe_delete('adi_matrix', "`id`=$matrix_index", $adi_matrix_debug);
	return $res;
}

function adi_matrix_update_settings() {
// analyse $_POST & update settings
	global $adi_matrix_debug,$adi_matrix_cfs;

	$res = FALSE;
	foreach ($_POST as $index => $value) {
		$data = doArray($value,'doStripTags'); // strip out monkey business
		$this_index = explode('_',$index);
		if ($this_index[0] == 'matrix') {
			$matrix_index = $this_index[1];

			// adjustments
			if ($data['publish']) // if publish, then get title edit for free
				$data['title'] = '1';
			if (isset($data['section'])) // if section selected as data, then switch off show_section
				$data['show_section'] = '0';

			// sort
			if ($data['sort'] == '')
				$sortq = "sort='desc', ";
			else
				$sortq = "sort='".doSlash($data['sort'])."', ";
			// section
			if (array_key_exists('criteria_section',$data))
				$criteria_sectionq = "criteria_section='".implode(',',$data['criteria_section'])."', ";
			else
				$criteria_sectionq = "criteria_section='', ";
			// status
			if (array_key_exists('status',$data))
				$statusq = 'status=1, ';
			else
				$statusq = 'status=0, ';
			// keywords
			if (array_key_exists('keywords',$data))
				$keywordsq = 'keywords=1, ';
			else
				$keywordsq = 'keywords=0, ';
			// article image
			if (array_key_exists('article_image',$data))
				$article_imageq = 'article_image=1, ';
			else
				$article_imageq = 'article_image=0, ';
			// category
			if (array_key_exists('category1',$data))
				$categoryq = 'category1=1, ';
			else
				$categoryq = 'category1=0, ';
			if (array_key_exists('category2',$data))
				$categoryq .= 'category2=1, ';
			else
				$categoryq .= 'category2=0, ';
			// posted
			if (array_key_exists('posted',$data))
				$postedq = 'posted=1, ';
			else
				$postedq = 'posted=0, ';
			// expires
			if (array_key_exists('expires',$data))
				$expiresq = 'expires=1, ';
			else
				$expiresq = 'expires=0, ';
			// title
			if (array_key_exists('title',$data))
				$titleq = 'title=1, ';
			else
				$titleq = 'title=0, ';
			// section
			if (array_key_exists('section',$data))
				$sectionq = 'section=1, ';
			else
				$sectionq = 'section=0, ';
			// custom field
			$cfq = '';
			foreach ($adi_matrix_cfs as $index => $cf_name) {
				$custom_x = 'custom_'.$index;
				if (array_key_exists($custom_x,$data))
					$cfq .= "custom_".$index."='1', ";
				else
					$cfq .= "custom_".$index."='0', ";
			}
			// category
			if (!array_key_exists('criteria_category',$data)) // in case there're no categories defined
				$data['criteria_category'] = '';
			if (preg_match('/!.*!/',$data['criteria_category'])) // disable descendent cats option with wildcards
				unset($data['criteria_descendent_cats']);
			if (array_key_exists('criteria_descendent_cats',$data))
				$criteria_descendent_catsq = 'criteria_descendent_cats=1, ';
			else
				$criteria_descendent_catsq = 'criteria_descendent_cats=0, ';

			$matrix_sql_set =
				"name='".doSlash($data['name'])."', "
				.$sortq
				."dir='".doSlash($data['dir'])."', "
				."sort_type='".doSlash($data['sort_type'])."', "
				."user='".doSlash($data['user'])."', "
				."privs='".doSlash($data['privs'])."', "
				."scroll='".doSlash($data['scroll'])."', "
				."footer='".doSlash($data['footer'])."', "
				."publish='".doSlash($data['publish'])."', "
				."show_section='".doSlash($data['show_section'])."', "
				."cf_links='".doSlash($data['cf_links'])."', "
				."tab='".doSlash($data['tab'])."', "
				.$criteria_sectionq
				."criteria_status='".doSlash($data['criteria_status'])."', "
				."criteria_author='".doSlash($data['criteria_author'])."', "
				."criteria_keywords='".doSlash($data['criteria_keywords'])."', "
				."criteria_timestamp='".doSlash($data['criteria_timestamp'])."', "
				."criteria_expiry='".doSlash($data['criteria_expiry'])."', "
				."criteria_condition='".doSlash($data['criteria_condition'])."', "
				.$titleq
				.$statusq
				.$sectionq
				.$keywordsq
				.$article_imageq
				.$postedq
				.$expiresq
				.$cfq
				.$categoryq
				.$criteria_descendent_catsq
				."criteria_category='".doSlash($data['criteria_category'])."' ";
			if ($matrix_index == 'new') {  // add new matrix to the mix
				if (!empty($data['name'])) { // but don't add a blank one
					$res = safe_insert(
						'adi_matrix',
						$matrix_sql_set
						,$adi_matrix_debug
					);
				}
			}
			else { // update existing matrix
				$res = safe_upsert(
					'adi_matrix',
					$matrix_sql_set
					,"id='$matrix_index'"
					,$adi_matrix_debug
				);
			}
		}
	}
	return $res;
}

function adi_matrix_admin_table_head($adi_matrix_cfs) {
// <table> header stuff

	// Custom field headings
	$data_span = 2; // [status/keywords] plus [custom fields]
	return '<thead>'.tr(
		hcell(adi_matrix_gtxt('adi_matrix'))
		.hcell() // spacer for View link
		.hcell(adi_matrix_gtxt('adi_article_selection'))
		.hcell(adi_matrix_gtxt('adi_article_data'),'',' colspan="'.$data_span.'"')
		.hcell('','',' class="adi_matrix_noborder"') // spacer for Delete button
	).'</thead>';
}

function adi_matrix_admin_table($matrix_list,$matrix_cfs) {
// generate form fields for existing & new matrixes
	global $adi_matrix_cfs,$adi_matrix_sort_options,$adi_matrix_sort_dir,$txp_user,$adi_matrix_sort_type,$prefs;

	$out = '';
	$out .= adi_matrix_admin_table_head($matrix_cfs);

	// tack 'new' index onto end of $matrix_list (field defaults for adding new matrix)
	$matrix_list['new'] = array(
		'name' => '',
		'sort' => 'posted',
		'dir' => 'desc',
		'sort_type' => 'alphabetical',
		'user' => $txp_user,
		'privs' => '',
		'scroll' => '0',
		'footer' => '0',
		'title' => '0',
		'publish' => '0',
		'show_section' => '0',
		'cf_links' => '',
		'tab' => '0',
		'criteria_section' => '',
		'criteria_category' => '',
		'criteria_descendent_cats' => '0',
		'criteria_status' => '0',
		'criteria_author' => '',
		'criteria_keywords' => '',
		'criteria_timestamp' => '',
		'criteria_expiry' => '',
		'criteria_condition' => '',
		'status' => '0',
		'keywords' => '0',
		'article_image' => '0',
		'category1' => '0',
		'category2' => '0',
		'posted' => '0',
		'expires' => '0',
		'section' => '0'
	);
	foreach ($adi_matrix_cfs as $index => $value) // add custom fields to $matrix_list['new']
		$matrix_list['new']['custom_'.$index] = '0';

	// existing matrixes followed by empty fields for a new one
	foreach ($matrix_list as $matrix_index => $matrix) {
		$cf_checkboxes = '';
		foreach ($matrix_cfs as $index => $cf_name) {
			$custom_x = 'custom_'.$index;
			$colon = ':';
			if (strpos($prefs['language'],'fr-') === 0) // normally adi_matrix_gtxt takes care of this
				$colon = ' '.$colon;
			$cf_checkboxes .= graf(tag($cf_name.$colon,'label').checkbox("matrix_".$matrix_index."[$custom_x]",1,$matrix[$custom_x]));
		}
		$cf_td = tda($cf_checkboxes,' class="adi_matrix_field adi_matrix_custom_field"');
		$url = '?event=adi_matrix_matrix_'.$matrix_index;
		if ($matrix_index == 'new')
			$view_link = ' ';
		else
			$view_link = '<a href="?event=adi_matrix_matrix_'.$matrix_index.'">'.adi_matrix_gtxt('view').'</a>';
		$out .= tr(
				// matrix settings
				tda(
					graf(tag(adi_matrix_gtxt('name','',':'),'label').finput("text","matrix_".$matrix_index."[name]",$matrix['name']))
					.graf(tag(adi_matrix_gtxt('sort','',':'),'label').selectInput("matrix_".$matrix_index."[sort]",$adi_matrix_sort_options,$matrix['sort'],FALSE))
					.graf(tag(adi_matrix_gtxt('sort_direction','',':'),'label').selectInput("matrix_".$matrix_index."[dir]",$adi_matrix_sort_dir,$matrix['dir'],FALSE))
					.graf(tag(adi_matrix_gtxt('adi_sort_type','',':'),'label').selectInput("matrix_".$matrix_index."[sort_type]",$adi_matrix_sort_type,$matrix['sort_type'],FALSE))
					.graf(tag(adi_matrix_gtxt('adi_user','',':'),'label').adi_matrix_user_popup("matrix_".$matrix_index."[user]",$matrix['user']))
					.graf(tag(adi_matrix_gtxt('privileges','',':'),'label').adi_matrix_privs_popup("matrix_".$matrix_index."[privs]",$matrix['privs']))
					.graf(
						tag(adi_matrix_gtxt('adi_scroll','',':'),'label')
						.adi_matrix_gtxt('yes')
						.radio("matrix_".$matrix_index."[scroll]",'1',($matrix['scroll'] == '1'))
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("matrix_".$matrix_index."[scroll]",'0',($matrix['scroll'] == '0'))
					)
					.graf(
						tag(adi_matrix_gtxt('adi_footer','',':'),'label')
						.adi_matrix_gtxt('yes')
						.radio("matrix_".$matrix_index."[footer]",'1',($matrix['footer'] == '1'))
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("matrix_".$matrix_index."[footer]",'0',($matrix['footer'] == '0'))
					)
					.graf(
						tag(adi_matrix_gtxt('adi_show_section','',':'),'label')
						.adi_matrix_gtxt('yes')
						.radio("matrix_".$matrix_index."[show_section]",'1',($matrix['show_section'] == '1'))
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("matrix_".$matrix_index."[show_section]",'0',($matrix['show_section'] == '0'))
					)
					.graf(
						tag(adi_matrix_gtxt('adi_cf_links','',':'),'label')
						.adi_matrix_gtxt('yes')
						.radio("matrix_".$matrix_index."[cf_links]",'article_image',(!empty($matrix['cf_links']))) // will be comma list one day
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("matrix_".$matrix_index."[cf_links]",'',(empty($matrix['cf_links']))) // will be comma list one day
					)
					.graf(
						tag(adi_matrix_gtxt('publish','',':'),'label')
						.adi_matrix_gtxt('yes')
						.radio("matrix_".$matrix_index."[publish]",'1',($matrix['publish'] == '1'))
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("matrix_".$matrix_index."[publish]",'0',($matrix['publish'] == '0'))
					)
					.graf(tag(adi_matrix_gtxt('adi_tab','',':'),'label').adi_matrix_tab_popup("matrix_".$matrix_index."[tab]",$matrix['tab']))
					,' class="adi_matrix_field"'
				)
				.tda($view_link)
				// article selection
				.tda(
					adi_matrix_gtxt('section','',':').br
					.tag(adi_matrix_section_checkboxes("matrix_".$matrix_index."[criteria_section]",$matrix['criteria_section']),'div',' class="adi_matrix_multi_checkboxes"')
					.graf(tag(adi_matrix_gtxt('category','',':'),'label').adi_matrix_category_popup("matrix_".$matrix_index."[criteria_category]",$matrix['criteria_category']))
					.graf(tag(adi_matrix_gtxt('adi_include_descendent_cats','',':'),'label',' class="adi_matrix_label2"').checkbox("matrix_".$matrix_index."[criteria_descendent_cats]",1,$matrix['criteria_descendent_cats']))
					.graf(tag(adi_matrix_gtxt('status','',':'),'label').adi_matrix_status_popup("matrix_".$matrix_index."[criteria_status]",$matrix['criteria_status']))
					.graf(tag(adi_matrix_gtxt('author','',':'),'label').adi_matrix_user_popup("matrix_".$matrix_index."[criteria_author]",$matrix['criteria_author'],TRUE))
					.graf(tag(adi_matrix_gtxt('keywords','',':'),'label').finput("text","matrix_".$matrix_index."[criteria_keywords]",$matrix['criteria_keywords']))
					.graf(tag(adi_matrix_gtxt('timestamp','',':'),'label').adi_matrix_timestamp_popup("matrix_".$matrix_index."[criteria_timestamp]",$matrix['criteria_timestamp']))
					.graf(tag(adi_matrix_gtxt('adi_expiry','',':'),'label').adi_matrix_expiry_popup("matrix_".$matrix_index."[criteria_expiry]",$matrix['criteria_expiry']))
					.graf(tag(adi_matrix_gtxt('adi_custom_condition','',':'),'label').finput("text","matrix_".$matrix_index."[criteria_condition]",$matrix['criteria_condition']))
					,' class="adi_matrix_field"'
				)
				// article data
				.tda(
					graf(tag(adi_matrix_gtxt('status','',':'),'label').checkbox("matrix_".$matrix_index."[status]",1,$matrix['status']))
					.graf(tag(adi_matrix_gtxt('keywords','',':'),'label').checkbox("matrix_".$matrix_index."[keywords]",1,$matrix['keywords']))
					.graf(tag(adi_matrix_gtxt('article_image','',':'),'label').checkbox("matrix_".$matrix_index."[article_image]",1,$matrix['article_image']))
					.graf(tag(adi_matrix_gtxt('category1','',':'),'label').checkbox("matrix_".$matrix_index."[category1]",1,$matrix['category1']))
					.graf(tag(adi_matrix_gtxt('category2','',':'),'label').checkbox("matrix_".$matrix_index."[category2]",1,$matrix['category2']))
					.graf(tag(adi_matrix_gtxt('posted','',':'),'label').checkbox("matrix_".$matrix_index."[posted]",1,$matrix['posted']))
					.graf(tag(adi_matrix_gtxt('expires','',':'),'label').checkbox("matrix_".$matrix_index."[expires]",1,$matrix['expires']))
					.graf(tag(adi_matrix_gtxt('title','',':'),'label').checkbox("matrix_".$matrix_index."[title]",1,$matrix['title']))
					.graf(tag(adi_matrix_gtxt('section','',':'),'label').checkbox("matrix_".$matrix_index."[section]",1,$matrix['section']))
					,' class="adi_matrix_field"'
				)
				.$cf_td
				.tda(adi_matrix_delete_button($matrix_list,$matrix_index),' class="adi_matrix_delete"')
		);
	}
	return $out;
}

function adi_matrix_pref($name,$value=NULL,$private=FALSE) {
// read or set pref
	global $prefs,$adi_matrix_prefs;

	if ($value === NULL)
		return get_pref($name,$adi_matrix_prefs[$name]['value']);
	else {
		if (array_key_exists($name,$adi_matrix_prefs))
			$html = $adi_matrix_prefs[$name]['input'];
		else
			$html = 'text_input';
		$res = set_pref($name,$value,'adi_matrix_admin',2,$html,0,$private);
		$prefs = get_prefs();
		return $res;
	}
}

function adi_matrix_tiny_mce_custom() {
// TinyMCE implementation in a modal window
	global $adi_matrix_cfs;

	$jquery_ui = adi_matrix_pref('adi_matrix_jquery_ui');
	$tiny_mce_dir = adi_matrix_pref('adi_matrix_tiny_mce_dir');
	$adi_matrix_tiny_mce_config = adi_matrix_pref('adi_matrix_tiny_mce_config');

	$title = adi_matrix_gtxt('edit');
	$ok = adi_matrix_gtxt('adi_ok');
	$cancel = adi_matrix_gtxt('adi_cancel');

	$script = <<<END_SCRIPT
<script src="$jquery_ui" type="text/javascript"></script>
<script type="text/javascript" src="$tiny_mce_dir/tiny_mce.js"></script>
<script type="text/javascript">
//<![CDATA[
	$(document).ready(function(){

		var i = 0;
		$('.adi_matrix_matrix .glz_text_area_field textarea').each(function(){
			// hide textareas
			$(this).css({display:"none"});
			// assign unique class
			i++;
			$(this).addClass('mceNoEditor tie' + i);
			// create div containing textarea contents (with same unique class)
			var text = $(this).val();
			$(this).after('<div class="tie_div tie' + i + '">' + text + '<\/div>');

		});

		$('.adi_matrix_matrix div.tie_div').click(function(){

			// get unique class (actually the last class - potentially dodgy?)
			var thisClass = $(this).attr('class').split(' ').slice(-1);
			// get corresponding textarea
			var thisTextarea = $('textarea.' + thisClass);
			// get textarea name attribute (e.g. article_2[custom_6])
			// WILL NEED TO SET THIS INFO IN THE DOM IN ADVANCE - TEXTAREA TITLE ATTR?
			// var thisName = $(thisTextarea).attr('name');

			$('#dialog').remove();
			$('body').append('<div id="dialog" \/>');
			$('#dialog').dialog({
				autoOpen: false,
				bgiframe: true,
				resizable: false,
				width: 700,
				position: ['center',35],
				overlay: { backgroundColor: '#000', opacity: 0.6 },
				open: function(e, ui){

				},
				beforeclose: function(event, ui) {
					tinyMCE.get('editor').remove();
					$('#editor').remove();
				}

			});

			$('#dialog').dialog('option', 'title', '$title');
			$('#dialog').dialog('option', 'modal', true);
			$('#dialog').dialog('option', 'buttons', {
				$cancel: function() {
							$(this).dialog('close');
				},
				$ok: function() {
						var content = tinyMCE.get('editor').getContent();
						// feed edited contents into textarea
						$(thisTextarea).val(content);
						// feed edited contents into div
						$('div.' + thisClass).html(content);
						$(this).dialog('close');
				}
			});

			$('#dialog').html('<textarea name="editor" id="editor"><\/textarea>');
			$('#dialog').dialog('open');
			tinyMCE.init({
				mode : "specific_textareas",
				editor_deselector : "mceNoEditor",
				// start of user configurable options
				$adi_matrix_tiny_mce_config
				// end of user-configurable options
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						tinyMCE.get('editor').setContent($(thisTextarea).val());
						tinyMCE.execCommand('mceRepaint');
					});
				}

		 	});
			return false;
		});
	});
//]]></script>
END_SCRIPT;

	echo $script;
}

function adi_matrix_tiny_mce_style() {
// jQuery UI CSS for TinyMCE modal window
	$jquery_ui_css = adi_matrix_pref('adi_matrix_jquery_ui_css');
	echo '<link href="'.$jquery_ui_css.'" type="text/css" rel="stylesheet" />';
}

function adi_matrix_admin($event, $step) {
// adi_matrix admin tab
	global $adi_matrix_debug,$adi_matrix_cfs,$adi_matrix_prefs,$adi_matrix_url,$prefs,$textarray,$adi_matrix_privs,$adi_matrix_groups,$txp_permissions,$adi_matrix_glz_cfs,$adi_matrix_sort_options;

	$message = '';
	$installed = adi_matrix_installed();

	if ($installed) {
		$upgrade_required = adi_matrix_upgrade();
		if ($upgrade_required)
			$message = array(adi_matrix_gtxt('adi_upgrade_required'),E_WARNING);
		else { // custom field musical chairs
			$cfs_fiddled = FALSE;
			// add additional custom fields that may have suddenly appeared (glz_cfs: custom_11+)
			foreach ($adi_matrix_cfs as $index => $value) {
				$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'custom_$index'",$adi_matrix_debug); // find out if column exists
				$a = nextRow($rs);
				if (empty($a)) {
					safe_query("ALTER TABLE ".safe_pfx('adi_matrix')." ADD `custom_$index` TINYINT(1) DEFAULT 0 NOT NULL",$adi_matrix_debug);
					$cfs_fiddled = TRUE;
				}
			}
			// remove custom fields that may have been deleted in glz_cfs
			$rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'custom%'",$adi_matrix_debug);
			$current_cfs = array();
			while ($a = nextRow($rs)) { // get list of cf indexes from adi_matrix
				$index = substr($a['Field'],7); // strip 'custom_' from 'custom_x'
				$current_cfs[$index] = TRUE;
			}
			foreach ($current_cfs as $index => $value)
				if (!array_key_exists($index,$adi_matrix_cfs)) {
					safe_query("ALTER TABLE ".safe_pfx('adi_matrix')." DROP COLUMN `custom_$index`",$adi_matrix_debug);
					$cfs_fiddled = TRUE;
				}
			if ($cfs_fiddled)
				$message = adi_matrix_gtxt('adi_matrix_cfs_modified');
		}
	}
	else
		$message = array(adi_matrix_gtxt('adi_not_installed'),E_ERROR);

	// admin $step aerobics
	if ($step == 'update') {
		$result = adi_matrix_update_settings();
		$result ? $message = adi_matrix_gtxt('adi_matrix_updated') : $message = array(adi_matrix_gtxt('adi_matrix_update_fail'),E_ERROR);
	}
	else if ($step == 'delete') {
		$matrix_index = gps('matrix');
		$result = adi_matrix_delete($matrix_index);
		$result ? $message = adi_matrix_gtxt('adi_matrix_deleted') : $message = array(adi_matrix_gtxt('adi_matrix_delete_fail'),E_ERROR);
	}
	else if ($step == 'update_prefs') {
		$result = TRUE;
		foreach ($adi_matrix_prefs as $name => $data) {
			if (array_key_exists($name,$_POST))
				$value = $_POST[$name];
			else if ($data['input'] == 'yesnoradio')
				$value = '0';
			else
				$value = $data['value'];
			// some values not allowed to be blank, reset to default
			$non_blanks = array('adi_matrix_tiny_mce_dir','adi_matrix_jquery_ui','adi_matrix_jquery_ui_css');
			foreach ($non_blanks as $non_blank)
				if (($name == $non_blank) && (trim($value) == ''))
					$value = $adi_matrix_prefs[$non_blank]['value'];
			$result = $result && adi_matrix_pref($name,$value);
		}
		$result ? $message = adi_matrix_gtxt('preferences_saved') : $message = array(adi_matrix_gtxt('adi_pref_update_fail'),E_ERROR);
	}

	// generate page
	pagetop(adi_matrix_gtxt('adi_matrix_admin'),$message);

	$installed = adi_matrix_installed();
	if ($installed && !$upgrade_required) {
		$adi_matrix_list = adi_matrix_read_settings();
		// output table & input form
		echo form(
			startTable('list','','txp-list')
			.adi_matrix_admin_table($adi_matrix_list,$adi_matrix_cfs)
			.endTable()
			.tag(
				fInput("submit", "do_something", adi_matrix_gtxt('adi_update_matrix'), "smallerbox").
				eInput("adi_matrix_admin").sInput("update"),
				'div',
				' class="adi_matrix_button"'
			)
			,''
			,''
			,'post'
			,'adi_matrix_admin'
		);
		// preferences
		echo form(
			tag(
				tag(adi_matrix_gtxt('edit_preferences'),'h2')
				// article limit
				.graf(
					tag(adi_matrix_gtxt('adi_article_limit','',':'),'label')
					.finput("text",'adi_matrix_article_limit',adi_matrix_pref('adi_matrix_article_limit'),'','','',6)
				)
				// display article id
				.graf(
					tag(adi_matrix_gtxt('adi_display_article_id','',':'),'label')
					.checkbox2("adi_matrix_display_id",adi_matrix_pref('adi_matrix_display_id'))
					.sp.sp
					.tag(adi_matrix_gtxt('adi_article_highlighting','',':'),'label')
					.checkbox2("adi_matrix_article_highlighting",adi_matrix_pref('adi_matrix_article_highlighting'))
				)
				// article tooltips
				.graf(
					tag(adi_matrix_gtxt('adi_article_tooltips','',':'),'label')
					.checkbox2("adi_matrix_article_tooltips",adi_matrix_pref('adi_matrix_article_tooltips'))
					.sp.sp
					.tag(adi_matrix_gtxt('adi_matrix_input_field_tooltips','',':'),'label')
					.checkbox2("adi_matrix_input_field_tooltips",adi_matrix_pref('adi_matrix_input_field_tooltips'))
				)
				.( $adi_matrix_glz_cfs ?
					// tinymce
					graf(
						tag(adi_matrix_gtxt('adi_tiny_mce').'? ','label')
						.adi_matrix_gtxt('yes')
						.radio("adi_matrix_tiny_mce",'1',(adi_matrix_pref('adi_matrix_tiny_mce') == '1'))
						.sp.sp
						.adi_matrix_gtxt('no')
						.radio("adi_matrix_tiny_mce",'0',(adi_matrix_pref('adi_matrix_tiny_mce') == '0'))
						,' class="adi_matrix_radio"'
					)
					.'<div id="peekaboo">'
					// tinymce dir
					.graf(
						tag(adi_matrix_gtxt('adi_tiny_mce_dir_path','',':'),'label')
						.finput("text",'adi_matrix_tiny_mce_dir',adi_matrix_pref('adi_matrix_tiny_mce_dir'),'','','',40)
					)
					// jquery ui
					.graf(
						tag(adi_matrix_gtxt('adi_jquery_ui').':','label')
						.finput("text",'adi_matrix_jquery_ui',adi_matrix_pref('adi_matrix_jquery_ui'),'','','',40)
					)
					// jquery ui css
					.graf(
						tag(adi_matrix_gtxt('adi_jquery_ui_css').':','label')
						.finput("text",'adi_matrix_jquery_ui_css',adi_matrix_pref('adi_matrix_jquery_ui_css'),'','','',40)
					)
					// tinymce config
					.graf(
						tag(adi_matrix_gtxt('adi_tiny_mce_config').':','label')
					)
					.graf(
						text_area('adi_matrix_tiny_mce_config',300,600,adi_matrix_pref('adi_matrix_tiny_mce_config'))
					)
					.'</div>'
				: '')
				.fInput("submit", "do_something", adi_matrix_gtxt('adi_update_prefs'), "smallerbox")
				.eInput("adi_matrix_admin").sInput("update_prefs")
				,'div'
				,' class="adi_matrix_prefs"'
				)
		);
	}

	if ($adi_matrix_debug) {
		echo "<p><b>Event:</b> ".$event.", <b>Step:</b> ".$step."</p>";
		echo '<b>$_POST:</b>';
		dmp($_POST);
		if ($installed) {
			echo '<b>Prefs:</b><br/>';
			foreach ($adi_matrix_prefs as $name => $this_pref)
				echo $name.' = '.adi_matrix_pref($name).'<br/>';
			echo '<br/>';
			echo '<b>$adi_matrix_list:</b>';
			dmp(adi_matrix_read_settings());
			echo '<b>$adi_matrix_privs:</b>';
			dmp($adi_matrix_privs);
			echo '<b>adi added privs:</b><br/>';
			foreach ($txp_permissions as $index => $value) {
				$chunks = explode('_',$index);
				if ($chunks[0] == 'adi')
					echo $index.' = '.$value.'<br/>';
			}
			echo '<br/>';
			echo '<b>$adi_matrix_sort_options:</b>';
			dmp($adi_matrix_sort_options);
			echo '<b>$adi_matrix_groups:</b>';
			dmp($adi_matrix_groups);
			echo '<b>$adi_matrix_cfs:</b>';
			dmp($adi_matrix_cfs);
			echo '<b>glz_custom_fields:</b> is';
			if (!$adi_matrix_glz_cfs)
				echo ' NOT';
			echo ' installed<br/>';
		}
	}

}

function adi_matrix_options($event,$step) {
// plugin options page
	global $adi_matrix_debug,$adi_matrix_url,$adi_matrix_plugin_status,$textarray;

	$message = '';

	$installed = adi_matrix_installed();
	if ($installed) {
		$upgrade_required = adi_matrix_upgrade();
		if ($upgrade_required)
			$step = 'upgrade';
	}

	// dance steps
	if ($step == 'textpack') {
		if (function_exists('install_textpack')) {
			$adi_textpack = file_get_contents($adi_matrix_url['textpack']);
			if ($adi_textpack) {
				$result = install_textpack($adi_textpack);
				$message = adi_matrix_gtxt('textpack_strings_installed', array('{count}' => $result));
				$textarray = load_lang(LANG); // load in new strings
			}
			else
				$message = array(adi_matrix_gtxt('adi_textpack_fail'),E_ERROR);
		}
	}
	else if ($step == 'upgrade') {
		$result = adi_matrix_upgrade(TRUE);
		$result ? $message = adi_matrix_gtxt('adi_upgraded') : $message = array(adi_matrix_gtxt('adi_upgrade_fail'),E_ERROR);
	}
	else if ($step == 'downgrade') {
		$result = adi_matrix_downgrade();
		$result ? $message = adi_matrix_gtxt('adi_downgraded') : $message = array(adi_matrix_gtxt('adi_downgrade_fail'),E_ERROR);
	}
	else if ($step == 'tweak') { // for development updates
		$result = safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `cf_links` VARCHAR(255) NOT NULL DEFAULT ''",$adi_matrix_debug);
		$result ? $message = 'Tweaked' : $message = array('Tweak failed',E_ERROR);
	}
	else if ($step == 'install') {
		$result = adi_matrix_install();
		$result ? $message = adi_matrix_gtxt('adi_installed') : $message = array(adi_matrix_gtxt('adi_install_fail'),E_ERROR);
	}
	else if ($step == 'uninstall') {
		$result = adi_matrix_uninstall();
		$result ? $message = adi_matrix_gtxt('adi_uninstalled') : $message = array(adi_matrix_gtxt('adi_uninstall_fail'),E_ERROR);
	}

	// generate page
	pagetop('adi_matrix - '.adi_matrix_gtxt('plugin_prefs'),$message);

	$install_button =
		tag(
			form(
				fInput("submit", "do_something", adi_matrix_gtxt('install'), "publish","",'return verify(\''.adi_matrix_gtxt('are_you_sure').'\')')
				.eInput($event).sInput("install")
				,'','','post','adi_matrix_nstall_button'
			)
			,'div'
			,' style="text-align:center"'
		);
	$uninstall_button =
		tag(
	    	form(
				fInput("submit", "do_something", adi_matrix_gtxt('adi_uninstall'), "publish","",'return verify(\''.adi_matrix_gtxt('are_you_sure').'\')')
				.eInput($event).sInput("uninstall")
				,'','','post','adi_matrix_nstall_button adi_matrix_uninstall_button'
			)
			,'div'
			,' style="margin-top:5em"');

	if ($adi_matrix_plugin_status) // proper plugin install, so lifecycle takes care of install/uninstall
		$install_button = $uninstall_button = '';

	$installed = adi_matrix_installed();
	if ($installed) {
		// options
		echo tag(
			tag('adi_matrix '.adi_matrix_gtxt('plugin_prefs'),'h2')
			// textpack links
			.graf(href(adi_matrix_gtxt('install_textpack'),'?event='.$event.'&amp;step=textpack'))
			.graf(href(adi_matrix_gtxt('adi_textpack_online'),$adi_matrix_url['textpack_download']))
			.graf(href(adi_matrix_gtxt('adi_textpack_feedback'),$adi_matrix_url['textpack_feedback']))
			.$uninstall_button
			,'div'
			,' style="text-align:center"'
		);
	}
	else // install button
	    echo $install_button;

	if ($adi_matrix_debug) {
		echo "<p><b>Event:</b> ".$event.", <b>Step:</b> ".$step."</p>";
		echo '<b>$adi_textpack ('.$adi_matrix_url['textpack'].'):</b>';
		$adi_textpack = file_get_contents($adi_matrix_url['textpack']);
		dmp($adi_textpack);
	}

}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1><strong>adi_matrix</strong> &#8211; Multi-article update tabs</h1>

<p>This plugin provides a way of viewing and updating multiple articles from a single <span class="caps">TXP</span> admin tab.</p>

<p>Matrixes give you a summary view of multiple articles, where you can make changes to selected data &amp; update them all in one go.</p>

<p>Two new tabs do the work:</p>

	<ul>
		<li>adi_matrix admin tab under Extensions</li>
		<li>article matrix tab(s) under Contents</li>
	</ul>

<p>The admin tab defines the matrixes and the article matrix tabs display the required articles with their data.</p>

<h2><strong>Installation</strong></h2>

<p><i>Installation of <strong>adi_matrix</strong> adds a new table to your Textpattern database which should not interfere with anything else. That said, if you are of a cautious frame of mind then I can thoroughly recommend <a href="http://forum.textpattern.com/viewtopic.php?id=10395" rel=" rel="1"">rss_admin_db_manager</a> to do database backups before installation.</i></p>

<p>As of version 1.0, the database tables are automatically added and/or updated when you install &amp; enable the plugin.</p>

<h2><strong>Upgrading from a previous version</strong></h2>

<p>Sometimes changes to the <strong>adi_matrix</strong> database table are required.  These is done automatically when you enable the plugin.</p>

<h2><strong>adi_matrix admin tab</strong></h2>

<p>This is where you set up the article matrixes.  There are three aspects to this:</p>

<h3>Matrix appearance</h3>

<p>Here you can:</p>

	<ul>
		<li>give the matrix a name (which will be used to list it under the Contents tab)</li>
		<li>specify the order in which articles should be listed</li>
		<li>specify whether a single user or all users are to get access to the matrix</li>
		<li>specify whether access to the matrix is based on privileges or not</li>
		<li>specify which tab to display the matrix under</li>
		<li>permit users to add/delete articles</li>
		<li>define how the matrix is laid out</li>
	</ul>

<h3>Article selection criteria</h3>

<p>By default all articles will be listed in the matrix, but you can narrow it down according to:</p>

	<ul>
		<li>section</li>
		<li>category</li>
		<li>article status</li>
		<li>author</li>
		<li>keywords</li>
		<li>posted &amp; expires timestamps</li>
		<li>custom <span class="caps">WHERE</span> clause condition</li>
	</ul>

<h3>Article data display</h3>

<p>This is where you define what data the user can see and change. Article that can be viewed &amp; updated in matrixes:</p>

	<ul>
		<li>article status</li>
		<li>custom fields</li>
		<li>article image</li>
		<li>keywords</li>
		<li>categories</li>
		<li>posted &amp; expires timestamps</li>
		<li>title</li>
		<li>section</li>
	</ul>

<h3>Preferences</h3>

	<ul>
		<li>maximum number of articles to be listed</li>
		<li>display article ID</li>
		<li>article title highlighting (indicate future or expired articles)</li>
		<li>article title tooltips (show ID, posted &amp; expires timestamps in tooltip)</li>
		<li>input field tooltips (show contents of input field in a tooltip)</li>
	</ul>

<h2><strong>Getting started</strong></h2>

<p>A new matrix can be added in <strong>adi_matrix</strong> admin tab simply by entering its details into the blank spaces.  As a minimum, the new matrix&#8217;s name needs to be provided.</p>

<p>Once a matrix has been defined, it&#8217;s settings can be changed at any time.  The new matrix will seen under the Contents tab after you have visited at least one other <span class="caps">TXP</span> tab (a hop is required so that the Contents tab menu gets updated).</p>

<h2><strong>Article matrixes</strong></h2>

<p>The matrix tabs under Contents show a number of articles, with their associated &#8220;data&#8221;. If you are the article author or have sufficient overriding privileges then you can make changes to the data &amp; update all articles with a single click.</p>

<p>Note that only articles where you have actually changed anything will be updated &#8211; together with their <i>Last Modified Date</i> and <i>Author</i>.</p>

<p><strong>adi_matrix</strong> respects all the standard restrictions on who can make changes to articles &#8211; based on authorship &amp; privilege level.</p>

<h2><strong>Textpack</strong></h2>

<p>To install the Textpack, go to the plugin&#8217;s options page and click on &#8220;Install textpack&#8221;.  This will copy &amp; install it from a remote server. The number of language strings installed for your language will be displayed.</p>

<p>If the Textpack installation fails (possibly due to an error accessing the remote site), the alternative is to click the <a href="http://www.greatoceanmedia.com.au/textpack" rel=" rel="1"">Textpack also available online</a> link.  This will take you to a website where the Textpack can be manually copied &amp; pasted into the <span class="caps">TXP</span> Admin &#8211; Language tab.</p>

<p>Updates and corrections to the Textpack are welcome &#8211; please use the <a href="http://www.greatoceanmedia.com.au/textpack/?plugin=adi_matrix" rel=" rel="1"">Textpack feedback</a> form.</p>

<h2><strong>glz_custom_fields</strong></h2>

<p><strong>adi_matrix</strong> will automatically detect if <strong>glz_custom_fields</strong> is installed and should play nicely.</p>

<h2><strong>TinyMCE</strong></h2>

<p>If <strong>glz_custom_fields</strong> is installed you have the opportunity to use <a href="http://www.tinymce.com/" rel=" rel="1"">TinyMCE</a> to edit textarea custom fields.  Note that TinyMCE must be installed seperately.  To use it with <strong>adi_matrix</strong>, switch it on in the admin tab and fill in the configuration details.</p>

<h2><strong>Uninstalling adi_matrix</strong></h2>

<p>To uninstall <strong>adi_matrix</strong>, simply go to the Plugins tab and delete it.  No articles will be harmed in the process.</p>

<h2><strong>Additional information</strong></h2>

<p>Support and further information can be obtained from the <a href="http://forum.textpattern.com/viewtopic.php?id=35972" rel=" rel="1"">Textpattern support forum</a>. A copy of this help is also available <a href="http://www.greatoceanmedia.com.au/txp/?plugin=adi_matrix" rel=" rel="1"">online</a>.  More adi_plugins can be found <a href="http://www.greatoceanmedia.com.au/txp/" rel=" rel="1"">here</a>.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>