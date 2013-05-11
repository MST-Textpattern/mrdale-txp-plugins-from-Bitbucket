<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'adi_form_links';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Adi Gilbert';
$plugin['author_uri'] = 'http://www.greatoceanmedia.com.au/';
$plugin['description'] = 'Admin-side form links';

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
/*
	adi_form_links - Admin-side form links

	Written by Adi Gilbert

	Released under the GNU General Public License

	Version history:
	0.1		- initial release (from an idea by Edoardo @wornout & thoroughly tested by Uli)

*/

global $event,$step,$adi_form_links_debug,$adi_form_links_message,$adi_form_links_gtxt,$adi_form_links_url,$adi_form_links_prefs;

if (@txpinterface == 'admin') {

	$adi_form_links_debug = 0;

	// using plugin options/lifecycle (4.2.0), Textpack (4.3.0) & plugin options (4.2.0)
	if (!version_compare(txp_version,'4.3.0','>=')) return;

	// plugin lifecycle
	register_callback('adi_form_links_lifecycle','plugin_lifecycle.adi_form_links');

	// callbacks - inject markup into Pages/Forms tab
	register_callback('adi_form_links_list','page','',1);
	register_callback('adi_form_links_list','form','',1);

	// plugin options
	$adi_form_links_plugin_status = fetch('status','txp_plugin','name','adi_form_links',$adi_form_links_debug);
	if ($adi_form_links_plugin_status) { // proper install - options under Plugins tab
		add_privs('plugin_prefs.adi_form_links','1,2,6');
		register_callback('adi_form_links_options','plugin_prefs.adi_form_links');
	}
	else { // txpdev - options under Extensions tab
		add_privs('adi_form_links_options','1,2,6');
		register_tab('extensions','adi_form_links_options','Form Link Options');
		register_callback('adi_form_links_options','adi_form_links_options');
	}

	// Textpack
	$adi_form_links_url = array(
		'textpack' => 'http://www.greatoceanmedia.com.au/files/adi_textpack.txt',
		'textpack_download' => 'http://www.greatoceanmedia.com.au/textpack/download',
		'textpack_feedback' => 'http://www.greatoceanmedia.com.au/textpack/?plugin=adi_form_links',
	);
	if (isset($prefs['plugin_cache_dir']))
		if (!strpos($prefs['plugin_cache_dir'],'adi') === FALSE) // use Adi's local version
			$adi_form_links_url['textpack'] = $prefs['plugin_cache_dir'].'/adi_textpack.txt';

# --- BEGIN PLUGIN TEXTPACK ---
	$adi_form_links_gtxt = array(
		'adi_edit_form' => 'Edit form',
		'adi_forms_referenced' => 'Forms referenced',
		'adi_forms_used' => 'Forms used',
		'adi_link_list' => 'Link list',
		'adi_list_format' => 'List format',
		'adi_none_found' => 'None found',
		'adi_pref_update_fail' => 'Preference update failed',
		'adi_textpack_fail' => 'Textpack installation failed',
		'adi_textpack_feedback' => 'Textpack feedback',
		'adi_textpack_online' => 'Textpack also available online',
		'adi_update_prefs' => 'Update preferences',
	);
# --- END PLUGIN TEXTPACK ---

	// preferences & defaults
	$adi_form_links_prefs = array(
		'adi_form_links_type'		=> array('value' => 'list', 'input' => 'radio'), // 'list' or 'popup'
	);
	foreach ($adi_form_links_prefs as $adi_form_links_pref => $this_pref) {
		$get_value = get_pref($adi_form_links_pref,'?'); // returns '?' if not set (but beware of cacheing)
		if ($get_value != '?')
			$adi_form_links_prefs[$adi_form_links_pref]['value'] = $get_value;
	}

	// some action
	if (($event == "page") || ($event == 'form')) { // 'ere we go

		if ($adi_form_links_debug) {
			echo 'Event='.$event.',Step='.$step.',$_GET=';
			dmp($_GET);
			echo '$_POST=';
			dmp($_POST);
		}

		// style
		register_callback('adi_form_links_style','admin_side','head_end');
	}
}

function adi_form_links_style() {
// practical & stylish
	echo
		'<style type="text/css">
			#adi_form_links { margin-top:1em }
			#adi_form_links label { width:auto; float:left; margin:0 }
			#adi_form_links select { float:left; margin:0 0 0 0.5em }
			#adi_form_links input.smallerbox { margin:0 0 0 0.5em }
			#adi_form_links .adi_form_links_disabled { color:#777 } /* explicitly grey out disabled options (for IE6/7) - still no good for iOS Safari */
			#adi_form_links ul { list-style:none; margin:0; padding:0 0 1em }
			#adi_form_links li { margin:0; padding:0 }
			#adi_form_links li.adi_form_links_new a,
			#adi_form_links option.adi_form_links_new { color:#777 }
		</style>';
}

function adi_form_links_gtxt($phrase,$atts=array()) {
// will check installed language strings before embedded English strings - to pick up Textpack
// - for TXP standard strings gTxt() & adi_form_links_gtxt() are functionally equivalent
	global $adi_form_links_gtxt;

	if (strpos(gTxt($phrase,$atts),$phrase) !== FALSE) { // no TXP translation found
		if (array_key_exists($phrase,$adi_form_links_gtxt)) // adi translation found
			return strtr($adi_form_links_gtxt[$phrase],$atts);
		else // last resort
			return $phrase;
		}
	else // TXP translation
		return gTxt($phrase,$atts);
}

function adi_form_links_options($event,$step) {
// plugin options page
	global $adi_form_links_debug,$adi_form_links_url,$textarray,$adi_form_links_prefs;

	$message = '';

	// step-tastic
	if ($step == 'textpack') {
		if (function_exists('install_textpack')) {
			$adi_textpack = file_get_contents($adi_form_links_url['textpack']);
			if ($adi_textpack) {
				$result = install_textpack($adi_textpack);
				$message = gTxt('textpack_strings_installed', array('{count}' => $result));
				$textarray = load_lang(LANG); // load in new strings
			}
			else
				$message = adi_form_links_gtxt('adi_textpack_fail');
		}
	}
	if ($step == 'update_prefs') {
		$result = adi_form_links_update_prefs();
		$result ? $message = gTxt('preferences_saved') : $message = adi_form_links_gtxt('adi_pref_update_fail');
	}

	// generate page
	if (!empty($message))
		$message = '<strong>'.$message.'</strong>';
	pagetop('adi_form_links '.gTxt('plugin_prefs'),$message);

	// options
	echo tag(
		tag('Form Links '.gTxt('plugin_prefs'),'h2')
 		// preferences
 	  	.form(
			tag(gTxt('edit_preferences'),'h3')
			.graf(
				tag(adi_form_links_gtxt('adi_list_format'),'label',' for="adi_form_links_type"')
				.': '
				.adi_form_links_gtxt('adi_link_list')
				.sp
				.radio('adi_form_links_type','list',($adi_form_links_prefs['adi_form_links_type']['value'] == 'list'))
				.sp.sp
				.adi_form_links_gtxt('tag_popup')
				.sp
				.radio('adi_form_links_type','popup',($adi_form_links_prefs['adi_form_links_type']['value'] == 'popup'))
			)
			.graf(fInput("submit", "do_something", adi_form_links_gtxt('adi_update_prefs'), "smallerbox"))
			.eInput($event).sInput("update_prefs")
			,'','','post',''
		)
		// textpack links
		.graf(href(gTxt('install_textpack'),'?event='.$event.'&amp;step=textpack'))
		.graf(href(adi_form_links_gtxt('adi_textpack_online'),$adi_form_links_url['textpack_download']))
		.graf(href(adi_form_links_gtxt('adi_textpack_feedback'),$adi_form_links_url['textpack_feedback']))
		,'div'
		,' style="text-align:center"'
	);

	if ($adi_form_links_debug) {
		echo '<b>$adi_textpack ('.$adi_form_links_url['textpack'].'):</b>';
		$adi_textpack = file_get_contents($adi_form_links_url['textpack']);
		dmp($adi_textpack);
	}

}

function adi_form_links_lifecycle($event,$step) {
// from cradle to grave
// $event:	"plugin_lifecycle.adi_form_links"
// $step:	"installed", "enabled", disabled", "deleted"
	global $adi_form_links_debug,$adi_form_links_prefs;

	$result = '?';
	if ($step == 'deleted') {
		// delete preferences
		$res = TRUE;
		foreach ($adi_form_links_prefs as $this_pref => $value)
			$res = $res && safe_delete('txp_prefs',"name = '$this_pref'",$adi_form_links_debug);
		$result = $res;
	}
	if ($adi_form_links_debug)
		echo "Event=$event Step=$step Result=$result";
}

function adi_form_links_update_prefs() {
// trawl $_POST & update adi_form_links preferences
	global $adi_form_links_prefs;

	$per_user_prefs = TRUE;

	$res = TRUE;
	foreach ($adi_form_links_prefs as $adi_form_links_pref => $this_pref) {
		if (array_key_exists($adi_form_links_pref,$_POST))
			$new_value = $_POST[$adi_form_links_pref];
		else
			$new_value = '';
		$res = $res && set_pref($adi_form_links_pref,$new_value,'adi_form_links',2,$adi_form_links_prefs[$adi_form_links_pref]['input'],0,$per_user_prefs);
		$adi_form_links_prefs[$adi_form_links_pref]['value'] = $new_value;
	}
	return $res;
}

function adi_form_links_forms($name) {
// find the forms & generate the markup
	global $event,$step,$adi_form_links_debug,$adi_form_links_prefs;

	if ($event == 'page') {
		/*							$step		$name		$newname
			Initial Page tab visit:	-			-			-			(== default)
			Edit page:				-			abc			-
			Save page:				page_save	abc			-
			Create page:			page_new	-			-
			Save new page:			page_save	-			xyz
			Delete page:			page_delete	abc			-
			Copy page:				page_save	abc			xyz			(it's OK to retrieve from source page in this case!)
		*/
		if (empty($step) && empty($name)) // initial page tab visit
			$name = 'default';
		if ($step == 'page_new')
			return ''; // otherwise it'll list default's form
		if ($step == 'page_save') // save page or add new page
			if (empty($name)) // add new page
				$name = gps('newname');
		if ($step == 'page_delete') // page delete, so default to default
			$name = 'default';
		// retrieve page contents from DB
		$row = safe_row('user_html','txp_page'," name='".$name."'");
		$data = $row['user_html'];
	}
	else if ($event == 'form') {
		/*							$step			$name		$oldname
			Initial Form tab visit:	-				-			-			(== default)
			Edit form:				form_edit		abc			-
			Save form:				form_save		abc			abc
			Create form:			form_create		-			-
			Save new form:			form_save		abc			-
			Delete form:			form_multi_edit	-			-
			Rename form:			form_save		xyc			abc
		*/
		if (empty($step) && empty($name)) // initial form tab visit
			$name = 'default';
		if ($step == 'form_multi_edit') // form delete, so default to default
			$name = 'default';
		$row = safe_row('Form','txp_form'," name='".$name."'");
		$data = $row['Form'];
	}

	// get all tags from current page/form
	$all_tags = adi_form_links_parse($data);

	// sort out wheat from chaff
	$forms = array();
	$debug = '<br/><br/>TAGS, ATTS & VALUES:<br/>';
	foreach ($all_tags as $taginfo) {
		$atts = adi_form_links_splat($taginfo['atts']); // get array of atts & values
		if ($atts)
			foreach ($atts as $att => $value) {
				$this_tag = array();
				if (preg_match("/.*form$/i",$att)) { // * form attribute is a good start
					$debug .= '*';
					if (!preg_match("/[<>]/i",$value)) { // ** no embedded tags is the business
						if ($value) { // form must have a name!
							$debug .= '*';
							$this_tag['tag'] = $taginfo['tag'];
							$this_tag['att'] = $att;
							$this_tag['form'] = htmlentities($value);
							$forms[] = $this_tag;
						}
					}
				}
				$debug .= $taginfo['tag'].' ('.$att.'='.htmlentities($value).')'.'<br/>';
			}
		else // no attributes
			$debug .= $taginfo['tag'].'<br/>';
	}

	if (!$adi_form_links_debug) $debug = '';

	// generate select list & link list markup
	$select_list = '';
	$link_list = '<ul>';
	foreach ($forms as $this_tag) {
		$link_text = $this_tag['form'];
		if (safe_row('name','txp_form'," name='".$this_tag['form']."'",$adi_form_links_debug)) {
			$option_class = '';
			$link_list .= '<li>'.elink('form','form_edit','name',$this_tag['form'],$link_text).' ('.$this_tag['tag'].')'.'</li>';
		}
		else {
			$option_class = ' class="adi_form_links_new"';
			$link_list .= '<li class="adi_form_links_new">'.elink('form','form_create','name',$this_tag['form'],$link_text).' ('.$this_tag['tag'].')'.'</li>';
		}
		$select_list .= '<option'.$option_class.' value="'.$this_tag['form'].'">'.$this_tag['form'].' ('.$this_tag['tag'].')</option>';
	}
	$link_list .= '</ul>';

	if ($forms)
		if ($adi_form_links_prefs['adi_form_links_type']['value'] == 'list')
			return $debug.$link_list;
		else // popup
			return $debug.tag('<option value="">&nbsp;</option>'.$select_list,'select',' id="adi_form_links_forms" name="name"');
	else
		return $debug;
}

function adi_form_links_parse($thing) {
// get all tags & their attributes
// ripped off (& modified) from publish.php

	$f = '@(</?txp:\w+(?:\s+\w+\s*=\s*(?:"(?:[^"]|"")*"|\'(?:[^\']|\'\')*\'|[^\s\'"/>]+))*\s*/?'.chr(62).')@s';
	$t = '@:(\w+)(.*?)/?.$@s';

	$parsed = preg_split($f, $thing, -1, PREG_SPLIT_DELIM_CAPTURE);

	$level  = 0;
	$out    = array();
	$inside = '';
	$istag  = FALSE;

	foreach ($parsed as $chunk)
	{
		if ($istag)
		{
			if ($level === 0) {
				preg_match($t, $chunk, $tag);

				if (substr($chunk, -2, 1) === '/')
				{ # self closing
					TRUE; // do nothing but keep for reference
				}
				else
				{ # opening
					$level++;
				}
				// $tag[1] = the tag, $tag[2] = the attributes
				$out[] = array('tag' => trim($tag[1]), 'atts' => trim($tag[2]));
				$out = array_merge($out,adi_form_links_parse($tag[2])); // parse tags within tags
			}
			else
			{
				if (substr($chunk, 1, 1) === '/')
				{ # closing
					if (--$level === 0)
					{
						$out = array_merge($out,adi_form_links_parse($inside)); // parse contained tags
						$inside = '';
					}
					else
					{
						$inside .= $chunk;
					}
				}
				elseif (substr($chunk, -2, 1) !== '/')
				{ # opening inside open
					++$level;
					$inside .= $chunk;
				}
				else
				{
					$inside .= $chunk;
				}
			}
		}
		else
		{
			if ($level)
			{
				$inside .= $chunk;
			}
		}

		$istag = !$istag;
	}

	return $out;
}

function adi_form_links_splat($text) {
// get tag attributes
// ripped off (& modified) from txplib_misc.php
	$atts  = array();

	if (preg_match_all('@(\w+)\s*=\s*(?:"((?:[^"]|"")*)"|\'((?:[^\']|\'\')*)\'|([^\s\'"/>]+))@s', $text, $match, PREG_SET_ORDER))
	{
		foreach ($match as $m)
		{
			switch (count($m))
			{
				case 3:
					$val = str_replace('""', '"', $m[2]);
					break;
				case 4:
					$val = str_replace("''", "'", $m[3]);
					break;
				case 5:
					$val = $m[4];
					break;
			}

			$atts[strtolower($m[1])] = $val;
		}

	}

	return $atts;
}

function adi_form_links_list() {
// arcane magic that gets adi_form_links's list into the standard TXP tab
	ob_start('adi_form_links_inject');
}

function adi_form_links_inject($buffer) {
// another bit of the magic that gets adi_form_links's section popup into the standard TXP tab
	global $DB,$event;

	if(!isset($DB)) $DB = new db;
	if ($event == 'page')
		$pattern = '#id="page_form".*</form>#sU';
	else if ($event == 'form')
		$pattern = '#id="form_form".*</form>#sU';
	else
		return '';
	$insert = 'adi_form_links_markup';
	$buffer = preg_replace_callback($pattern, $insert, $buffer);
	return $buffer;
}

function adi_form_links_markup($matches) {
// generate markup
	global $adi_form_links_prefs;

	$name = gps('name');

	$forms_found = adi_form_links_forms($name);

	if ($forms_found)
		if ($adi_form_links_prefs['adi_form_links_type']['value'] == 'list') // link list
			$markup = graf(adi_form_links_gtxt('adi_forms_referenced').': ').$forms_found;
		else // popup
			$markup =
				form(
					tag(
						tag(adi_form_links_gtxt('adi_forms_referenced').': ','label',' for="adi_form_links_forms"')
						.$forms_found
						.fInput("submit","edit_form",adi_form_links_gtxt('adi_edit_form'),"smallerbox")
						.eInput("form")
						.sInput("form_edit")
					,'p')
				);
	else // none found
		$markup = adi_form_links_gtxt('adi_forms_referenced').': '.adi_form_links_gtxt('adi_none_found');

	return $matches[0] // the admin page chunk, plus:
		.tag(
			$markup
			,'div'
			,' id="adi_form_links"'
		);
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1><strong>adi_form_links</strong> &#8211; Admin-side form links</h1>

	<p>This plugin is an enhancement to the standard <span class="caps">TXP</span> Page and Form tabs designed to help speed up workflow.  It lists forms that are referenced within the current page or form. From an <a href="http://forum.textpattern.com/viewtopic.php?id=36961" rel="nofollow">idea by Edoardo</a>.</p>

	<h2><strong>Usage</strong></h2>

	<p>After installing &amp; activating the plugin, go to the Pages or Forms tab and you&#8217;ll find a list of forms referenced by the current page or form.</p>

	<p>The list can either be a simple list of links or a popup &#8211; choose your preference in the plugin options.</p>

	<p>Forms are listed in the order they&#8217;re found and their &#8220;owner&#8221; tag is shown also.</p>

	<p>Forms shown in grey don&#8217;t exist in the database.</p>

	<p>Clicking or selecting a form in the list will take you to the Form Edit tab for that form. If the form doesn&#8217;t exist then you&#8217;ll be taken to a Form Create tab.</p>

	<h2><strong>Textpack</strong></h2>

	<p>To install the Textpack, go to the plugin&#8217;s Options tab and click on &#8220;Install textpack&#8221;.  This will copy &amp; install it from a remote server. The number of language strings installed for your language will be displayed.</p>

	<p>If the Textpack installation fails (possibly due to an error accessing the remote site), the alternative is to click the <a href="http://www.greatoceanmedia.com.au/textpack" rel="nofollow">Textpack also available online</a> link.  This will take you to a website where the Textpack can be manually copied &amp; pasted into the <span class="caps">TXP</span> Admin &#8211; Language tab.</p>

	<p>Additions and corrections to the Textpack are welcome &#8211; please use the <a href="http://www.greatoceanmedia.com.au/textpack/?plugin=adi_form_links" rel="nofollow">Textpack feedback</a> form.</p>

	<h2><strong>Additional information</strong></h2>

	<p>Support and further information can be obtained from the <a href="http://forum.textpattern.com/viewtopic.php?id=37006" rel="nofollow">Textpattern support forum</a>. A copy of this help is also available <a href="http://www.greatoceanmedia.com.au/txp/?plugin=adi_form_links" rel="nofollow">online</a>.  More adi_plugins can be found <a href="http://www.greatoceanmedia.com.au/txp/" rel="nofollow">here</a>.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>