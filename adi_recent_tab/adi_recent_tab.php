<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'adi_recent_tab';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.4';
$plugin['author'] = 'Adi Gilbert';
$plugin['author_uri'] = 'http://www.greatoceanmedia.com.au/';
$plugin['description'] = 'Recent Items Tab';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '1';

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
	adi_recent_tab - Recent Items Tab

	Written by Adi Gilbert

	Released under the GNU General Public License

	Version history:
	0.4		- TXP 4.5-ified (particularly to cope with new Sections tab & new-style Links tab)
			- fix: record new links properly
			- fix: losing image (file) name if both alt & caption blank
	0.3		- style improvements to cope with long titles
			- fix: style for latest version of Hive theme (thanks Uli)
			- fix: error message in images & files search result page (thanks Uli)
	0.2		- fix: cope with image replace & file replace properly
			- fix: cope with style create/save/delete properly
			- new pref: include article/image/link/file IDs
	0.1		- initial release

*/

global $event,$step,$txp_user,$textarray,$prefs,$adi_recent_tab_debug,$adi_recent_tab_event_gtxt,$adi_recent_tab_include,$adi_recent_tab_exclude_combo,$adi_recent_tab_include_combo,$adi_recent_tab_gtxt,$adi_recent_tab_url,$adi_recent_tab_prefs,$adi_recent_tab_plugin_status;

if (@txpinterface == 'admin') {

	$adi_recent_tab_debug = 0;

	// using plugin options/lifecycle (4.2.0), Textpack (4.3.0), Home tab (4.3.0), sanitizeForPage() (4.4.0) so depart with great alacrity should it be pertinent
	if (!version_compare(txp_version,'4.4.0','>=')) return;

	$adi_recent_tab_txp450 = (version_compare(txp_version,'4.4.1','>'));


	// plugin lifecycle
	register_callback('adi_recent_tab_lifecycle','plugin_lifecycle.adi_recent_tab');

	// plugin options
	$adi_recent_tab_plugin_status = fetch('status','txp_plugin','name','adi_recent_tab',$adi_recent_tab_debug);
	if ($adi_recent_tab_plugin_status) { // proper install - options under Plugins tab
		add_privs('plugin_prefs.adi_recent_tab','1,2,6');
		register_callback('adi_recent_tab_options','plugin_prefs.adi_recent_tab');
	}
	else { // txpdev - options under Extensions tab
		add_privs('adi_recent_tab_options','1,2,6');
		register_tab('extensions','adi_recent_tab_options','Recent Items Options');
		register_callback('adi_recent_tab_options','adi_recent_tab_options');
	}

	// Textpack
	$adi_recent_tab_url = array(
		'textpack' => 'http://www.greatoceanmedia.com.au/files/adi_textpack.txt',
		'textpack_download' => 'http://www.greatoceanmedia.com.au/textpack/download',
		'textpack_feedback' => 'http://www.greatoceanmedia.com.au/textpack/?plugin=adi_recent_tab',
	);
	if (strpos($prefs['plugin_cache_dir'],'adi') !== FALSE) // use Adi's local version
		$adi_matrix_url['textpack'] = $prefs['plugin_cache_dir'].'/adi_textpack.txt';

# --- BEGIN PLUGIN TEXTPACK ---
	$adi_recent_tab_gtxt = array(
		'adi_include_ids' => 'Include IDs?',
		'adi_install_fail' => 'Unable to install',
		'adi_installed' => 'Installed',
		'adi_pref_update_fail' => 'Preference update failed',
		'adi_recent_items' => 'Recent items',
		'adi_recent_tab' => 'Recent',
		'adi_recent_tab_max_items' => 'Maximum number of items',
		'adi_tab' => 'Tab',
		'adi_textpack' => 'Textpack',
		'adi_textpack_fail' => 'Textpack installation failed',
		'adi_textpack_feedback' => 'Textpack feedback',
		'adi_textpack_online' => 'Textpack also available online',
		'adi_uninstall' => 'Uninstall',
		'adi_uninstall_fail' => 'Unable to uninstall',
		'adi_uninstalled' => 'Uninstalled',
		'adi_update_prefs' => 'Update preferences',
	);
# --- END PLUGIN TEXTPACK ---

	/*
		Events & Steps - A minefield of inconsistencies ...

						Step			other vars
	Event=article
		Write			create			-			(empty create tab)
		Edit			edit			ID
		Save			edit			ID
		Publish			create -> edit	$GLOBALS['ID']

	Event=image
		Images list		-
		Image upload	image_insert	$GLOBALS['ID']
		Image edit		image_edit		id
		Image save		image_edit		id
		Image replace	image_replace	id

	Event=page
		Pages/default	-
		Page edit		-				name
		Save			page_save		name
		Copy			page_save		name, newname
		Create page		page_save		newname

	Event=form
		Forms/default	-
		Form edit		form_edit		name
		Form save		form_save

	Event=link
		Links list		- -> link_edit
		Link edit		link_edit		id
		Link save		link_save		id
		Link create		link_post		$GLOBALS['ID']	(pre-4.5.0)
		Link create		link_save		$GLOBALS['ID']	(post-4.5.0)

	Event=file
		Files list		-
		Upload			file_insert		$GLOBALS['ID']
		Save			file_save		?
		File edit		file_edit		id
		File replace	file_replace	id

	Event=css
		Style/default	-
		Style edit		-				name
		Save			css_save		name
		New style		pour			newname
		Copy style		css_save		name

	Event=section
		Section list	-
		Section edit	section_edit	name
		Section create	section_edit	-		(empty create tab)
??		Section save		link_save		id
??		Section create		link_save		$GLOBALS['ID']	(post-4.5.0)
	*/

	// the privileged few ...
	$adi_recent_tab_include = array('article','image','page','form','link','file','css'); // if you're not on the list, you're not getting in
	if ($adi_recent_tab_txp450)
		$adi_recent_tab_include[] = 'section'; // new kid on the block

	// it's what we call 'em
	$adi_recent_tab_event_gtxt = array(
		'article'	=> 'tab_list',
		'css'		=> 'tab_style',
		'file'		=> 'tab_file',
		'form'		=> 'tab_forms',
		'image'		=> 'tab_image',
		'link'		=> 'tab_link',
		'page'		=> 'tab_pages',
		'section'	=> 'tab_sections',
	);

	// not so fast ...
	$adi_recent_tab_exclude_combo[] = array('event' => '^article$', 'step' => '^$'); // empty create article tab - event="article", step=blank
	$adi_recent_tab_exclude_combo[] = array('event' => '^image$', 'step' => '^$'); // image list tab - event="image", step=blank
	$adi_recent_tab_exclude_combo[] = array('event' => '^image$', 'step' => 'image_multi_edit'); // image list multi-edit - event="image", step contains image_multi_edit
	$adi_recent_tab_exclude_combo[] = array('event' => '^image$', 'step' => 'image_replace'); // image replace - event="image", step contains image_multi_edit
	$adi_recent_tab_exclude_combo[] = array('event' => '^image$', 'step' => 'image_list'); // image replace - event="image", step contains image_list (search results)
	$adi_recent_tab_exclude_combo[] = array('event' => '^page$', 'step' => 'page_new'); // new page tab - event="page", step contains page_new
	$adi_recent_tab_exclude_combo[] = array('event' => '^form$', 'step' => 'form_multi_edit'); // form list multi-edit - event="form", step contains form_multi_edit
	$adi_recent_tab_exclude_combo[] = array('event' => '^link$', 'step' => 'link_edit', 'id' => '^$'); // link list tab
	$adi_recent_tab_exclude_combo[] = array('event' => '^link$', 'id' => '^$'); // TXP 4.5 link list tab
	$adi_recent_tab_exclude_combo[] = array('event' => '^link$', 'step' => 'link_multi_edit'); // link list tab - event="link", step contains link_multi_edit
	$adi_recent_tab_exclude_combo[] = array('event' => '^file$', 'step' => '^$'); // file list tab - event="file", step=blank
	$adi_recent_tab_exclude_combo[] = array('event' => '^file$', 'step' => 'file_replace'); // file replace - event="image", step contains file_replace
	$adi_recent_tab_exclude_combo[] = array('event' => '^file$', 'step' => 'file_list'); // image replace - event="image", step contains image_list (search results)
	$adi_recent_tab_exclude_combo[] = array('event' => '^css$', 'step' => 'pour'); // style list tab - event="css", step=pour (create style)
	$adi_recent_tab_exclude_combo[] = array('event' => '^section$', 'step' => 'section_edit', 'name' => '^$'); // empty section create tab (TXP 4.5.0)
	$adi_recent_tab_exclude_combo[] = array('event' => '.*', 'step' => 'save'); // any step containing "save" (e.g. page_save, form_save, link_save)
	$adi_recent_tab_exclude_combo[] = array('event' => '.*', 'step' => 'create'); // any step containing "create" (e.g. new article create, form create)
	$adi_recent_tab_exclude_combo[] = array('event' => '.*', 'step' => 'apply'); // any step containing "apply" - just in case!
	$adi_recent_tab_exclude_combo[] = array('event' => '.*', 'step' => 'update'); // any step containing "update" - just in case!
	$adi_recent_tab_exclude_combo[] = array('event' => '.*', 'step' => 'delete'); // any step containing "delete" - just in case!
	// don't log initial page/form/style tab visit (i.e. default by default)
	$adi_recent_tab_exclude_combo[] = array('event' => '^page$', 'step' => '^$', 'name' => '^$'); // default page tab (i.e. edit default)
	$adi_recent_tab_exclude_combo[] = array('event' => '^form$', 'step' => '^$', 'name' => '^$'); // default form tab (i.e. edit default)
	$adi_recent_tab_exclude_combo[] = array('event' => '^css$', 'step' => '^$', 'name' => '^$'); // default style tab (i.e. edit default)
	$adi_recent_tab_exclude_combo[] = array('event' => '^section$', 'step' => '^$', 'name' => '^$'); // default section list tab (TXP 4.5.0)

	// oh, go on then ...
	$adi_recent_tab_include_combo[] = array('event' => 'page', 'step' => 'page_save'); // create new page
	$adi_recent_tab_include_combo[] = array('event' => 'form', 'step' => 'form_save'); // create new form
	$adi_recent_tab_include_combo[] = array('event' => 'css', 'step' => 'css_save'); // create new form
	$adi_recent_tab_include_combo[] = array('event' => 'link', 'step' => 'link_post'); // create new link (pre-4.5.0)
	$adi_recent_tab_include_combo[] = array('event' => 'link', 'step' => 'link_save'); // create new link (post-4.5.0)
	$adi_recent_tab_include_combo[] = array('event' => 'section', 'step' => 'section_save'); // create new section (TXP 4.5.0+)

	// preferences & defaults
	$adi_recent_tab_prefs = array(
		'adi_recent_tab_max_items'	=> array('value' => '5', 'input' => 'text_input'),
		'adi_recent_tab_list'		=> array('value' => implode(',',$adi_recent_tab_include), 'input' => 'text_input'),
		'adi_recent_tab_ids'		=> array('value' => '0', 'input' => 'yesnoradio'),
	);
	foreach ($adi_recent_tab_prefs as $adi_recent_tab_pref => $this_pref) {
		$get_value = get_pref($adi_recent_tab_pref,'?'); // returns '?' if not set (but beware of cacheing)
		if ($get_value != '?')
			$adi_recent_tab_prefs[$adi_recent_tab_pref]['value'] = $get_value;
	}

	// do the business
	if (adi_recent_tab_installed()) {
		if ($adi_recent_tab_debug)
			echo 'Event='.$event.', Initial Step='.$step;
		// style
		register_callback('adi_recent_tab_menu_style','admin_side','head_end'); // menu style for all
		if ($event == "adi_recent_tab")
			register_callback('adi_recent_tab_style','admin_side','head_end');
		// adi_recent_tab tab
		add_privs('adi_recent_tab','1,2,3,4,5,6'); // all privs for adi_recent_tab
		register_callback("adi_recent_tab","adi_recent_tab");
//		add_privs('tab.start','1,2,3,4,5,6'); // all privs for Home tab
//		register_tab('start','adi_recent_tab',adi_recent_tab_gtxt('adi_recent_items'));
		// separate Recent items menu
		add_privs('tab.recent','1,2,3,4,5,6'); // all privs for recent items tab
		register_tab('recent','adi_recent_tab',adi_recent_tab_gtxt('adi_recent_items'));
		$textarray['tab_recent'] = adi_recent_tab_gtxt('adi_recent_tab'); // to get something meaningful into tab
		// put logger in the footer - to catch the elusive newly created article/image IDs etc
		register_callback('adi_recent_tab_logger','admin_side','footer');
		// inject markup to create recent items menu
		adi_recent_tab_menu();
	}
}

function adi_recent_tab_style() {
// style for tab
	echo
		'<style type="text/css">
			#adi_recent_tab { width:685px; margin:0 auto }
			#adi_recent_tab table { width:100% }
			#adi_recent_tab td { width:50%; padding-left:0 }
			#adi_recent_tab ul { list-style:none; margin:0.5em 0 }
			#adi_recent_tab li { margin:0.2em 0 }
			#adi_recent_tab li a span { font-weight:bold }
			#adi_recent_tab h1 { font-weight:bold }
			#adi_recent_tab h2 { margin:0.5em 0 0 }
			#adi_recent_tab h2 span { margin-left:0.5em; font-size:0.7em }
			#adi_recent_tab h3 { margin:5em 0 1em; padding-top:0.5em; border-top:1px solid #ccc; font-weight:bold }
			/* Hive adjustments - recent tab page given class="recent" which is Hive styling for Recent Articles! */
			#adi_recent_tab li { list-style:none; margin-left:0 }
			#txp-nav li { list-style:none; margin-left:0 }
			/* Hive TXP 4.5 adjustments - recent tab page given class="recent" which is Hive styling for Recent Articles! */
			#adi_recent_tab ul { padding:0 }
			.txp-nav li { margin-left:0 }
		</style>';
}

function adi_recent_tab_menu_style() {
// style for recent dropdown menu
	echo
		'<style type="text/css">
			/* Remora */
			#nav .adi_recent_tab_menu_title { font-weight:bold }
			#nav a[href="?event=adi_recent_tab"] + ul { width:270px; padding-top:0.5em }
			#nav a[href="?event=adi_recent_tab"] + ul li { width:auto; height:auto }
			#nav a[href="?event=adi_recent_tab"] + ul li a { width:250px; height:auto; line-height:1.2; padding-top:0.3em; padding-bottom:0.3em }
			#nav a[href="?event=adi_recent_tab"] + ul li a span { font-weight:bold }
			#nav a[href="?event=adi_recent_tab"] + ul li:first-child { display:none }
			/* Remora TXP 4.5*/
			.txp-header #nav a[href="?event=adi_recent_tab"] + ul { width:25em }
			.txp-header #nav a[href="?event=adi_recent_tab"] + ul li a { width:23em }
			.txp-header #nav a[href="?event=adi_recent_tab"] + ul { padding-bottom:0.5em }
			/* Hive */
			#txp-nav .adi_recent_tab_menu_item { font-weight:normal }
			#txp-nav .adi_recent_tab_menu_title { font-weight:bold }
			#txp-nav a[href="?event=adi_recent_tab"] + ul li a { line-height:1.2; padding-top:3px; padding-bottom:3px }
			#txp-nav a[href="?event=adi_recent_tab"] + ul li a span { font-weight:bold }
			#txp-nav a[href="?event=adi_recent_tab"] + ul { width:270px; padding-top:0.5em; padding-bottom:0.5em }
			#txp-nav a[href="?event=adi_recent_tab"] + ul li a { width:250px }
			#txp-nav a[href="?event=adi_recent_tab"] + ul li:first-child { display:none }
			/* Hive TXP 4.5*/
			.txp-nav .adi_recent_tab_menu_title { font-weight:bold }
			.txp-nav a[href="?event=adi_recent_tab"] + ul li:first-child { display:none }
			.txp-nav a[href="?event=adi_recent_tab"] + ul { width:27em }
			.txp-nav a[href="?event=adi_recent_tab"] + ul li a { width:25em }
			.txp-nav a[href="?event=adi_recent_tab"] + ul { padding-bottom:0.5em }
		</style>';
}

function adi_recent_tab_logger() {
// monitor each TXP admin page visit & decide if it's to be recorded by adi_recent_tab
	global $adi_recent_tab_debug,$event,$step,$txp_user,$adi_recent_tab_include,$adi_recent_tab_exclude_combo,$adi_recent_tab_include_combo;

	if (!adi_recent_tab_installed()) // in case it's just been uninstalled
		return;

	$id = gps('id'); // image id
	$name = gps('name'); // page/form/image name

	if ($adi_recent_tab_debug) {
		echo 'Event='.$event.', Final Step='.$step.', supplied id='.$id.', supplied name='.$name.'<br/>';
		echo 'Include event list:';
		dmp($adi_recent_tab_include);
		echo 'Exclude combo list:';
		dmp($adi_recent_tab_exclude_combo);
		echo 'Include combo list:';
		dmp($adi_recent_tab_include_combo);
	}

	// process included & excluded events/steps
	// events of interest
	$exclude = array_search($event,$adi_recent_tab_include) === FALSE;
	if ($adi_recent_tab_debug && $exclude)
		echo '($adi_recent_tab_include) EXCLUDE EVENT: '.$event.'<br/>';
	// sort the men from the boys
	if (!$exclude) {
		// event/step combos to be discounted
		$exclude = adi_recent_tab_filter($adi_recent_tab_exclude_combo,$event,$step,$id,$name);
		if ($adi_recent_tab_debug && $exclude)
			echo '($adi_recent_tab_exclude_combo) '."EXCLUDED EVENT/STEP/ID/NAME: $event,$step,$id,$name<br/>";

		// explicitly include these combos
		$include = adi_recent_tab_filter($adi_recent_tab_include_combo,$event,$step,$id,$name);
		if ($adi_recent_tab_debug && $include)
			echo '($adi_recent_tab_include_combo) '."INCLUDED EVENT/STEP/ID/NAME: $event,$step,$id,$name<br/>";
		if ($include)
			$exclude = FALSE;
	}

	if (!$exclude) { // massage stuff
		// ID
		if (empty($id)) // try finding an article ID
			$id = gps('ID');
		if (empty($id) && ($event == 'article')) // i.e. a newly created article
			$id = $GLOBALS['ID'];
		if (empty($id) && ($event == 'image')) // i.e. a newly created image
			$id = $GLOBALS['ID'];
		if (empty($id) && ($event == 'link')) // i.e. a newly created link
			$id = $GLOBALS['ID'];
		if (empty($id) && ($event == 'file')) // i.e. a newly uploaded file
			$id = $GLOBALS['ID'];
		// NAME
		if ((($event == 'page') || ($event == 'form')) && empty($name)) // fill in the "default" blank - needed if logging initial page/form tab visit
			$name = 'default';
		if ($event == 'image') // don't want to store image name coz we've got the ID (& the name might change)
			$name = '';
		$newname = doSlash(sanitizeForPage(gps('newname'))); // create/copy page
		if ($newname)
			$name = $newname;
		// STEP
		$this_step = $step;
		if (($event== 'image') && ($step == 'image_insert')) // tweak step - new image
			$this_step = 'image_edit';
		if (($event== 'page') && ($step == 'page_save')) // new/save page
			$this_step = '';
		if (($event== 'form') && ($step == 'form_save')) // new/save form
			$this_step = 'form_edit';
		if (($event== 'css') && ($step == 'css_save')) // new style/save style
			$this_step = '';
		if (($event== 'form') && ($step == '')) // tweak step - click between forms
			$this_step = 'form_edit';
		if (($event== 'link') && ($step == 'link_post')) // new link (pre-4.5.0)
			$this_step = 'link_edit';
		if (($event== 'link') && ($step == 'link_save')) // new link (post-4.5.0)
			$this_step = 'link_edit';
		if (($event== 'section') && ($step == 'section_save')) // new section (post-4.5.0)
			$this_step = 'section_edit';
		if (($event== 'file') && ($step == 'file_insert')) // new file
			$this_step = 'file_edit';
		if ($adi_recent_tab_debug)
			echo 'Event='.$event.', this_step='.$this_step.', massaged id='.$id.', massaged name='.$name.'<br/>';

		// update adi_recent_tab DB table
		$table = 'adi_recent_tab';
		$set = 'timestamp=now(), user="'.doSlash($txp_user).'", event="'.doSlash($event).'", step="'.doSlash($this_step).'", id="'.doSlash($id).'", name="'.doSlash($name).'"';
		$where = 'user="'.$txp_user.'" AND event="'.$event.'" AND step="'.$this_step.'" AND id="'.$id.'" AND name="'.$name.'"';
		// it's me own safe_upsert ...
		$r = safe_update($table,$set,$where,$adi_recent_tab_debug);
		if (!($r and (mysql_affected_rows() or safe_count($table,$where,$adi_recent_tab_debug))))
			safe_insert($table,$set,$adi_recent_tab_debug);
	}

	// tidy up
	adi_recent_tab_housekeeping();
}

function adi_recent_tab_filter($pattern_list,$event,$step,$id='',$name='') {
// look for combination matches
	$match = FALSE;
	foreach ($pattern_list as $pair) {
		$this_match = TRUE;
		foreach ($pair as $var => $pattern)
			$this_match = $this_match && preg_match('/'.$pattern.'/',$$var);
		$match = $match || $this_match;
	}
	return $match;
}

function adi_recent_tab_gtxt($phrase,$atts=array()) {
// will check installed language strings before embedded English strings - to pick up Textpack
// - for TXP standard strings gTxt() & adi_recent_tab_gtxt() are functionally equivalent
	global $adi_recent_tab_gtxt;

	if (strpos(gTxt($phrase,$atts),$phrase) !== FALSE) { // no TXP translation found
		if (array_key_exists($phrase,$adi_recent_tab_gtxt)) // adi translation found
			return strtr($adi_recent_tab_gtxt[$phrase],$atts);
		else // last resort
			return $phrase;
		}
	else // TXP translation
		return gTxt($phrase,$atts);
}

function adi_recent_tab_options($event,$step) {
// plugin options page
	global $adi_recent_tab_debug,$adi_recent_tab_url,$textarray,$adi_recent_tab_prefs,$adi_recent_tab_include,$adi_recent_tab_event_gtxt,$adi_recent_tab_plugin_status;

	$message = '';

	// step-tastic
	if ($step == 'textpack') {
		if (function_exists('install_textpack')) {
			$adi_textpack = file_get_contents($adi_recent_tab_url['textpack']);
			if ($adi_textpack) {
				$result = install_textpack($adi_textpack);
				$message = gTxt('textpack_strings_installed', array('{count}' => $result));
				$textarray = load_lang(LANG); // load in new strings
			}
			else
				$message = array(adi_recent_tab_gtxt('adi_textpack_fail'),E_ERROR);
		}
	}
	else if ($step == 'install') {
		$result = adi_recent_tab_install();
		$result ? $message = adi_recent_tab_gtxt('adi_installed') : $message = array(adi_recent_tab_gtxt('adi_install_fail'),E_ERROR);
	}
	else if ($step == 'uninstall') {
		$result = adi_recent_tab_uninstall();
		$result ? $message = adi_recent_tab_gtxt('adi_uninstalled') : $message = array(adi_recent_tab_gtxt('adi_uninstall_fail'),E_ERROR);
	}

	// generate page
	pagetop('adi_recent_tab - '.gTxt('plugin_prefs'),$message);

	$install_button =
		tag(
			form(
				fInput("submit", "do_something", gTxt('install'), "publish","",'return verify(\''.gTxt('are_you_sure').'\')')
				.eInput($event).sInput("install")
				,'','','post','adi_recent_tab_nstall_button'
			)
			,'div'
			,' style="text-align:center"'
		);
	$uninstall_button =
		tag(
	    	form(
				fInput("submit", "do_something", adi_recent_tab_gtxt('adi_uninstall'), "publish","",'return verify(\''.gTxt('are_you_sure').'\')')
				.eInput($event).sInput("uninstall")
				,'','','post','adi_recent_tab_nstall_button adi_recent_tab_uninstall_button'
			)
			,'div'
			,' style="margin-top:5em"');
	if ($adi_recent_tab_plugin_status) // proper plugin install, so lifecycle takes care of install/uninstall
		$install_button = $uninstall_button = '';
	$installed = adi_recent_tab_installed();
	if ($installed) {
		// options
		echo tag(
			tag('adi_recent_tab - '.gTxt('plugin_prefs'),'h2')
			.tag(
				tag(adi_recent_tab_gtxt('adi_textpack'),'h2')
				// textpack links
				.graf(href(gTxt('install_textpack'),'?event='.$event.'&amp;step=textpack'))
				.graf(href(adi_recent_tab_gtxt('adi_textpack_online'),$adi_recent_tab_url['textpack_download']))
				.graf(href(adi_recent_tab_gtxt('adi_textpack_feedback'),$adi_recent_tab_url['textpack_feedback']))
				,'div'
				,' style="margin-top:3em"')
			// uninstall button
			.$uninstall_button
			,'div'
			,' style="text-align:center"'
		);
	}
	else // install button
	    echo $install_button;

	if ($adi_recent_tab_debug) {
		echo '<b>$adi_textpack ('.$adi_recent_tab_url['textpack'].'):</b>';
		$adi_textpack = file_get_contents($adi_recent_tab_url['textpack']);
		dmp($adi_textpack);
	}
}

function adi_recent_tab_installed($table='adi_recent_tab') {
// test if table is present
	$rs = safe_query("SHOW TABLES LIKE '".safe_pfx($table)."'");
	$a = nextRow($rs);
	if ($a)
		return TRUE;
	else
		return FALSE;
}

function adi_recent_tab_install() {
// install adi_recent_tab table in database
	global $adi_recent_tab_debug;

	$res = safe_query(
		// 'id' is a VARCHAR (rather than an INT) coz I need it to be blank sometimes
		"CREATE TABLE IF NOT EXISTS ".safe_pfx('adi_recent_tab')
		."(	`timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
			`user` VARCHAR(64) NOT NULL DEFAULT '',
			`event` VARCHAR(64) NOT NULL DEFAULT '',
			`step` VARCHAR(64) NOT NULL DEFAULT '',
			`id` VARCHAR(12) NOT NULL DEFAULT '',
			`name` VARCHAR(64) NOT NULL DEFAULT ''
		);"
		,$adi_recent_tab_debug);
 	return $res;
}

function adi_recent_tab_uninstall() {
// uninstall adi_recent_tab
	global $adi_recent_tab_debug,$adi_recent_tab_prefs;

	// delete table
	$res = safe_query("DROP TABLE ".safe_pfx('adi_recent_tab').";",$adi_recent_tab_debug);
	// delete preferences
	foreach ($adi_recent_tab_prefs as $this_pref => $value)
		$res = $res && safe_delete('txp_prefs',"name = '$this_pref'",$adi_recent_tab_debug);
	return $res;
}

function adi_recent_tab_lifecycle($event,$step) {
// a matter of life & death
// $event:	"plugin_lifecycle.adi_recent_tab"
// $step:	"installed", "enabled", disabled", "deleted"
	global $adi_recent_tab_debug;

	$result = '?';
	if ($step == 'enabled')
		$result = adi_recent_tab_install();
	else if ($step == 'deleted')
		$result = adi_recent_tab_uninstall();
	if ($adi_recent_tab_debug)
		echo "Event=$event Step=$step Result=$result";
}

function adi_recent_update_prefs() {
// trawl $_POST & update adi_recent_tab preferences
	global $adi_recent_tab_prefs,$adi_recent_tab_debug;

	if ($adi_recent_tab_debug)
		dmp($_POST);

	$per_user_prefs = TRUE;

	$res = TRUE;
	foreach ($adi_recent_tab_prefs as $adi_recent_tab_pref => $this_pref) {
		if (array_key_exists($adi_recent_tab_pref,$_POST))
			$new_value = $_POST[$adi_recent_tab_pref];
		else
			$new_value = '';
		if ($adi_recent_tab_pref == 'adi_recent_tab_list')
			if ($new_value)
				$new_value = implode(',',array_keys($new_value));
		$new_value = strip_tags($new_value);
		$res = $res && set_pref($adi_recent_tab_pref,$new_value,'adi_recent_admin',2,$adi_recent_tab_prefs[$adi_recent_tab_pref]['input'],0,$per_user_prefs);
		$adi_recent_tab_prefs[$adi_recent_tab_pref]['value'] = $new_value;
	}
	return $res;
}

function adi_recent_tab_housekeeping() {
// delete oldest recents that exceed max count; make sure items still exist in DB
	global $adi_recent_tab_debug,$txp_user,$adi_recent_tab_include,$adi_recent_tab_prefs;

	$max_recent = $adi_recent_tab_prefs['adi_recent_tab_max_items']['value'];

	$sort = ' ORDER BY timestamp desc';
	$default_where = " AND user='$txp_user'";

	if ($adi_recent_tab_debug)
		echo '<br/>Housekeeping (deceased entries):<br/>';

	// delete entries that don't exist ... the recently deceased
	foreach ($adi_recent_tab_include as $recent) {
		$table = 'txp_'.$recent;
		if ($recent == 'article')
			$table = 'textpattern';
		$rs = safe_rows('*','adi_recent_tab',"event='".$recent."'".$default_where.$sort,$adi_recent_tab_debug);
		foreach ($rs as $index => $row) {
			extract($row);
			// "id" entries
			if (($recent == 'article') || ($recent == 'image') || ($recent == 'link') || ($recent == 'file')) {
				$row = safe_row('*',$table,"id='$id'",$adi_recent_tab_debug);
				if (empty($row)) {
					$where = 'user="'.$txp_user.'" AND event="'.$event.'" AND step="'.$step.'" AND id="'.$id.'" AND name="'.$name.'"';
					safe_delete('adi_recent_tab',$where,$adi_recent_tab_debug);
				}
			}
			// "name" entries
			if (($recent == 'page') || ($recent == 'form') || ($recent == 'css') || ($recent == 'section')) {
				$row = safe_row('*',$table,"name='$name'",$adi_recent_tab_debug);
				if (empty($row)) {
					$where = 'user="'.$txp_user.'" AND event="'.$event.'" AND step="'.$step.'" AND id="'.$id.'" AND name="'.$name.'"';
					safe_delete('adi_recent_tab',$where,$adi_recent_tab_debug);
				}
			}
		}
	}

	if ($adi_recent_tab_debug)
		echo 'Housekeeping (remove surplus):<br/>';

	// lose the surplus
	foreach ($adi_recent_tab_include as $recent) {
		$rs = safe_rows('*','adi_recent_tab',"event='".$recent."'".$default_where.$sort,$adi_recent_tab_debug);
		if (count($rs) > $max_recent)
			foreach ($rs as $index => $row) {
				extract($row);
				if (!($index < $max_recent)) {
					$where = 'user="'.$txp_user.'" AND event="'.$event.'" AND step="'.$step.'" AND id="'.$id.'" AND name="'.$name.'"'; // BIG GLOBAL FOR THIS WHERE???
					safe_delete('adi_recent_tab',$where,$adi_recent_tab_debug);
				}
			}
	}

}

function adi_recent_tab_links($event,$ul=TRUE) {
// return a <ul><li> or <li> of all the "recent" links
	global $adi_recent_tab_debug,$adi_recent_tab_prefs,$txp_user;

//	$sort_method = 'timestamp';
	$sort_method = 'alphanumeric';

	$default_where = " AND user='$txp_user'";

	if ($sort_method == 'timestamp') {
		$sort = ' ORDER BY timestamp desc';
		$rs = safe_rows('*','adi_recent_tab',"event='$event'".$default_where.$sort,$adi_recent_tab_debug);
	}
	else if ($sort_method == 'alphanumeric') {
		if ($event == 'article') {
			$sort = ' ORDER BY Title';
			$rs = safe_rows('Title,textpattern.ID,adi_recent_tab.*','textpattern, adi_recent_tab',"event='$event' AND textpattern.ID = adi_recent_tab.id".$default_where.$sort,$adi_recent_tab_debug);
		}
		else if ($event == 'image') {
			$sort = ' ORDER BY alt,txp_image.name';
			// note that actual name is last in list of fields - image name not stored by adi_recent_tab, so don't want blank to override proper name
			$rs = safe_rows('adi_recent_tab.*,alt,txp_image.name','txp_image, adi_recent_tab',"event='$event' AND txp_image.id = adi_recent_tab.id".$default_where.$sort,$adi_recent_tab_debug);
		}
		else if ($event == 'file') {
			$sort = ' ORDER BY title,filename';
			$rs = safe_rows('title,filename,adi_recent_tab.*','txp_file, adi_recent_tab',"event='$event' AND txp_file.id = adi_recent_tab.id".$default_where.$sort,$adi_recent_tab_debug);
		}
		else if ($event == 'link') {
			$sort = ' ORDER BY linkname,url';
			$rs = safe_rows('linkname,url,adi_recent_tab.*','txp_link, adi_recent_tab',"event='$event' AND txp_link.id = adi_recent_tab.id".$default_where.$sort,$adi_recent_tab_debug);
		}
		else { // page, form, style, section
			$sort = ' ORDER BY name';
			$rs = safe_rows('*','adi_recent_tab',"event='$event'".$default_where.$sort,$adi_recent_tab_debug);
		}
	}

	$out = '';
	if ($adi_recent_tab_debug)
		dmp($rs);
	foreach ($rs as $index => $row) {
		$item = '';
		extract($row);
		// link text fiddling
		if ($event == 'article')
			if (empty($Title)) // something's better than nothing
				$link_text = gTxt('untitled');
			else
				$link_text = $Title;
		else if ($event == 'image') // use alt text, or name if blank
			if (empty($alt))
				$link_text = $name;
			else
				$link_text = $alt;
		else if ($event == 'link') // use link name, or url if blank
			if (empty($linkname))
				$link_text = $url;
			else
				$link_text = $linkname;
		else if ($event == 'file') // use title, or filename if blank
			if (empty($title))
				$link_text = $filename;
			else
				$link_text = $title;
		else if ($event == 'section') // use name
				$link_text = $name;
		// include IDs?
		if ($adi_recent_tab_prefs['adi_recent_tab_ids']['value'])
			if ($id != '')
				$link_text .= ' ('.tag($id,'span').')';
		// links to items
		if ($event == 'article')
			$item .= '<a href="?event='.$event.a.'step='.$step.a.'ID='.$id.'">'.$link_text.'</a>';
		else if (($event == 'image') || ($event == 'link') || ($event == 'file'))
			$item .= '<a href="?event='.$event.a.'step='.$step.a.'id='.$id.'">'.$link_text.'</a>';
		else if ($event == 'section')
			$item .= '<a href="?event='.$event.a.'step='.$step.a.'name='.$name.'">'.$link_text.'</a>';
		else
			$item .= '<a href="?event='.$event.a.'name='.$name.'">'.$name.'</a>';
		$out = $out.tag($item,'li',' class="adi_recent_tab_menu_item"');
	}
	if ($ul)
		return tag($out,'ul');
	else
		return $out;
}

function adi_recent_tab($event,$step) {
// the page that displays all the "recent" links
	global $adi_recent_tab_debug,$adi_recent_tab_event_gtxt,$adi_recent_tab_include,$adi_recent_tab_prefs;

	$message= '';
	if ($step == 'update_prefs') {
		$result = adi_recent_update_prefs();
		$result ? $message = gTxt('preferences_saved') : $message = array(adi_recent_tab_gtxt('adi_pref_update_fail'),E_ERROR);
	}

	// generate page
	pagetop('adi_recent_tab '.gTxt('adi_recent_tab'),$message);

	echo '<div id="adi_recent_tab">';
	echo tag(adi_recent_tab_gtxt('adi_recent_items'),'h1');
	echo '<table summary="adi_recent_tab">';

	// generate table of recents
	$tab_list = explode(',',$adi_recent_tab_prefs['adi_recent_tab_list']['value']);
	$padding = count($tab_list) % 2; // need extra <td> coz there's an odd number
	end($tab_list);
	$last_index = key($tab_list);
	foreach ($tab_list as $index => $this_event) {
		if (!($index % 2)) // even
			echo '<tr>';
		$tab_event = $this_event;
		if ($this_event == 'article')
			$tab_event = 'list';
		echo tag(
			tag(adi_recent_tab_gtxt($adi_recent_tab_event_gtxt[$this_event]).'<span>[<a href="?event='.$tab_event.'">'.adi_recent_tab_gtxt('adi_tab').'</a>]</span>','h2')
			.adi_recent_tab_links($this_event)
			,'td'
		);
		if ($padding && ($index == $last_index)) // need a filler
			echo '<td>&nbsp;</td>';
		if ($index % 2) // odd
			echo '</tr>';
	}

	// tab visibility
	$checkboxes = '';
	$tab_list = explode(',',$adi_recent_tab_prefs['adi_recent_tab_list']['value']);
	foreach ($adi_recent_tab_include as $tab_name) {
		$checked = array_search($tab_name,$tab_list) !== FALSE;
		$checkboxes .=
			adi_recent_tab_gtxt($adi_recent_tab_event_gtxt[$tab_name])
			.sp
			.checkbox("adi_recent_tab_list[$tab_name]", TRUE, $checked)
			.sp.sp.sp;
	}

	// preferences
	echo '<tr><td colspan="2">';
	echo tag(
    	form(
			tag(gTxt('edit_preferences'),'h3')
			// visibility
			.graf(
				$checkboxes
			)
			// max number of items
			.graf(
				tag(adi_recent_tab_gtxt('adi_recent_tab_max_items').':','label')
				.sp
				.finput("text",'adi_recent_tab_max_items',$adi_recent_tab_prefs['adi_recent_tab_max_items']['value'],'','','',2)
			)
			// include IDs?
			.graf(
				tag(adi_recent_tab_gtxt('adi_include_ids'),'label')
				.sp.sp
				.gTxt('yes')
				.sp
				.radio('adi_recent_tab_ids','1',($adi_recent_tab_prefs['adi_recent_tab_ids']['value'] == '1'))
				.sp.sp
				.gTxt('no')
				.sp
				.radio('adi_recent_tab_ids','0',($adi_recent_tab_prefs['adi_recent_tab_ids']['value'] == '0'))
			)
			.fInput("submit", "do_something", adi_recent_tab_gtxt('adi_update_prefs'), "smallerbox")
			.eInput($event).sInput("update_prefs")
			,'','','post',''
		)
		,'div'
	);

	echo '</td></tr>';
	echo '</table>';
	echo '</div>';

	if ($adi_recent_tab_debug)
		echo adi_recent_tab_db_dump();
}

function adi_recent_tab_menu() {
// the old-fashioned magic that gets the recent items into adi_recent_tab's menu
	ob_start('adi_recent_tab_menu_inject');
}

function adi_recent_tab_menu_inject($buffer) {
// another bit of arcane magic
	global $DB;

	if(!isset($DB)) $DB = new db;
//	$pattern = '#id="page_form".*</form>#sU';
	$pattern = '#'.adi_recent_tab_gtxt('adi_recent_items').'</a></li>#sU';
	$insert = 'adi_recent_tab_menu_markup';
	$buffer = preg_replace_callback($pattern, $insert, $buffer);
	return $buffer;
}

function adi_recent_tab_menu_markup($matches) {
// generate markup for adi_recent_tab recent item lists
	global $adi_recent_tab_event_gtxt,$adi_recent_tab_include,$adi_recent_tab_prefs;

	$out = '';
	$tab_list = explode(',',$adi_recent_tab_prefs['adi_recent_tab_list']['value']);
	foreach ($tab_list as $this_event)
		$out .=
			'<li><a class="adi_recent_tab_menu_title" href="?event='.$this_event.'">'
			.adi_recent_tab_gtxt($adi_recent_tab_event_gtxt[$this_event])
			.'</a></li>'
			.adi_recent_tab_links($this_event,FALSE);
	return $matches[0].$out;
}

function adi_recent_tab_db_dump($table='adi_recent_tab') {
// print out contents of database table
	$result = mysql_query("SELECT * FROM {$table}");
	$fields_num = mysql_num_fields($result);
	$out = "<br/><table><tr>";
	// table headers
	for ($i = 0; $i < $fields_num; $i++) {
	    $field = mysql_fetch_field($result);
	    $out .= "<td><b>{$field->name}</b>&nbsp;</td>";
	}
	$out .= "</tr>";
	// table rows
	while($row = mysql_fetch_row($result)) {
	    $out .= "<tr>";
	    foreach($row as $cell)
	        $out .= "<td>$cell&nbsp;</td>";
	    $out .= "</tr>";
	}
	$out .= '</table><br/>';
	return $out;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1><strong>adi_recent_tab</strong> &#8211; Recent Items</h1>

	<p>To help speed up workflow &#8211; especially during website construction &#8211; this plugin gives you a menu of recently accessed <span class="caps">TXP</span> items.</p>

	<h2><strong>Usage</strong></h2>

	<p>Install &amp; activate the plugin in the normal way. A new top level <span class="caps">TXP</span> admin tab &#8211; <strong>Recent</strong> &#8211; will then be available.</p>

	<p>The <strong>Recent</strong> tab dropdown menu shows a list of articles/images/links/pages/forms etc that you have recently visited in the admin interface.</p>

	<p>This menu contains items that you have <em>visited</em> &#8211; as opposed to the Recent Articles list in the Article tab which only contains articles that have been recently modified.</p>

	<h2><strong>The Menu</strong></h2>

	<p>The Recent menu gives you direct access to:</p>

	<ul>
		<li>recently visited items</li>
		<li>and their tabs</li>
	</ul>

	<h2><strong>The Tab</strong></h2>

	<p>The recent items are also available on a standalone page &#8211; simply click on the <strong>Recent</strong> tab itself.</p>

	<p>Here you will also find preferences to:</p>

	<ul>
		<li>specify which recently visited items you want to record</li>
		<li>set the number of items to remember</li>
		<li>choose whether to have article/image/link/file IDs included in the menu</li>
	</ul>

	<h2><strong>Textpack</strong></h2>

	<p>To install the Textpack, go to the plugin&#8217;s Options tab and click on &#8220;Install textpack&#8221;.  This will copy &amp; install it from a remote server. The number of language strings installed for your language will be displayed.</p>

	<p>If the Textpack installation fails (possibly due to an error accessing the remote site), the alternative is to click the <a href="http://www.greatoceanmedia.com.au/textpack" rel="nofollow">Textpack also available online</a> link.  This will take you to a website where the Textpack can be manually copied &amp; pasted into the <span class="caps">TXP</span> Admin &#8211; Language tab.</p>

	<p>Additions and corrections to the Textpack are welcome &#8211; please use the <a href="http://www.greatoceanmedia.com.au/textpack/?plugin=adi_recent_tab" rel="nofollow">Textpack feedback</a> form.</p>

	<h2><strong>Additional information</strong></h2>

	<p>Support and further information can be obtained from the <a href="http://forum.textpattern.com/viewtopic.php?id=36928" rel="nofollow">Textpattern support forum</a>. A copy of this help is also available <a href="http://www.greatoceanmedia.com.au/txp/?plugin=adi_recent_tab" rel="nofollow">online</a>.  More adi_plugins can be found <a href="http://www.greatoceanmedia.com.au/txp/" rel="nofollow">here</a>.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>