<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_postmaster_library';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '1.0.10';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Helper functions for the Postmaster plugin. This is a branch of Ben Bruce\'s Postmaster Plugin.';

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
// ==== POSTMASTER LIBRARY ====
// =========================================================

if( !defined('bab_pm_prefix') )
    define( 'bab_pm_prefix' , 'bab_pm' );

if (!defined('BAB_CUSTOM_FIELD_COUNT'))
	define('BAB_CUSTOM_FIELD_COUNT', 20);

function bab_pm_styles()
{

	$css_encoded = safe_field('css', 'txp_css', "name LIKE 'mem_postmaster'");

	if ($css_encoded)
	{
		$css = base64_decode($css_encoded);

		if ($css === false)
			$css = $css_encoded;

		echo n . '<style type="text/css">' . n
			. $css
			. n . '</style>' . n;
	}
	else
	{
		echo $bab_pm_styles = <<<bab_pm_styles
<style type="text/css">
#bab_pm_master {

}
#bab_pm_master fieldset{
	padding:10px;
}
#bab_pm_master legend{
	font: small-caps bold 12px Georgia; color: black;
}
#bab_pm_nav {
	width:100%;
}
#bab_pm_content {
	width:600px;
	margin-right:auto;
	margin-left:auto;
	margin-top:-20px;
}
.bab_pm_alerts {
	color:red;
	padding:10px;
	margin-top:10px;
	margin-bottom:10px;
	border:1pt dotted red;
	text-align:center;
}

.stuff { display:none; }
.csv_form {margin-left:200px;}

/* mrdale's */

#bab_pm_edit p {
	float:left;
	clear:left;
	width:122px;
	height:22px;
	background:#eee;
	margin:3px;
/*	line-height:200%; */
	text-align:right;
	padding-right:5px;
}
#bab_pm_edit textarea {
	float:right;
	clear:none;
	border:1px inset gray;
	background:#fff;
	padding-left:3px;
}

.bab_pm_input_group {
	margin-bottom: 10px;
	float: right;
	clear: none;
	padding-left: 3px;
	width: 75%;
	height: 100%;
}
dl.bab_pm_form_input {
	width: 100%;
	clear: left;
}
dl.bab_pm_form_input dt {
	width: 120px;
	text-align: right;
	clear: left;
	float: left;
	position: relative;
}
dl.bab_pm_form_input dd {
	margin-left: 125px;
	clear: right;
}
dl.bab_pm_form_input dd input[type=text] {
	width: 300px;
}

#bab_pm_edit input.smallerbox {
	margin:5px 0;
	float:right
}

.pm_prog_bar {
	background: white url(/images/percentImage_back.png) no-repeat top left;
	padding: 0;
	margin: 5px 0 0 0;
	background-position: 0 0;
}
</style>
bab_pm_styles;
	}
}

function bab_pm_poweredit()
{
	$lists = safe_rows('listID, listName', 'bab_pm_list_prefs', '1 order by listName');

	$list_options = '';
	foreach ($lists as $l)
	{
		$list_options .= '<option value="'.doSlash($l['listID']).'">'.htmlspecialchars($l['listName']).'</option>';
	}

	echo <<<EOJS
<script type="text/javascript">
<!--
		function poweredit(elm)
		{
			var something = elm.options[elm.selectedIndex].value;

			// Add another chunk of HTML
			var pjs = document.getElementById('js');

			if (pjs == null)
			{
				var br = document.createElement('br');
				elm.parentNode.appendChild(br);

				pjs = document.createElement('P');
				pjs.setAttribute('id','js');
				elm.parentNode.appendChild(pjs);
			}

			if (pjs.style.display == 'none' || pjs.style.display == '')
			{
				pjs.style.display = 'block';
			}

			switch (something)
			{
				case 'add_to_list':
				case 'remove_from_list':
					var lists = '<select name=\"selected_list_id\" class=\"list\">{$list_options}</select>';
					pjs.innerHTML = '<span>List: '+lists+'</span>';
					break;
				default:
					pjs.innerHTML = '';
					break;
			}

			return false;
		}
-->
</script>
EOJS;
}

#===========================================================================
#	Strings for internationalisation...
#===========================================================================
global $_bab_pm_l18n;
$_bab_pm_l18n = array(
	'bab_pm'				=> 'Postmaster plugin',
	# --- the following are used as labels for PM prefs...
	'subscriberFirstName'	=> 'First Name',
	'subscriberLastName'	=> 'Last Name',
	'subscriberEmail'	=> 'Email',
	'subscriberLists'	=> 'Lists',
	'subscribers_per_page'	=> 'Subscribers per page',
	'emails_per_batch'		=> 'Emails per batch',
	'email_batch_delay'		=> 'Batch delay (seconds)',
	'form_select_prefix'	=> 'Form Select Prefix',
	'default_unsubscribe_url'	=> 'Default Unsubscribe URL',
	# --- the following are used in the PM interface...
	'add_to_list'		=> 'Add to List',
	'remove_from_list'	=> 'Remove from List',
	'add_all_to_list'	=> 'Add everyone to List',
	'remove_all_from_list'	=> 'Remove everyone from List',
	);

for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
{
	$_bab_pm_l18n["subscriberCustom{$i}"] = "Custom field {$i} name";
}

#-------------------------------------------------------------------------------
#	String support routines...
#-------------------------------------------------------------------------------
register_callback( 'bab_pm_enumerate_strings' , 'l10n.enumerate_strings' );
function bab_pm_enumerate_strings($event , $step='' , $pre=0)
{
	global $_bab_pm_l18n;
	$r = array	(
				'owner'		=> 'bab_pm',			#	Change to your plugin's name
				'prefix'	=> bab_pm_prefix,		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'public',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $_bab_pm_l18n,		#	The strings themselves.
				);
	return $r;
}
function bab_pm_gTxt($what,$args = array())
{
	global $_bab_pm_l18n, $textarray;
	$key = strtolower( bab_pm_prefix . '-' . $what );
	if (isset($textarray[$key]))
		$str = $textarray[$key];
	else
	{
		$key = strtolower($what);

		if (isset($_bab_pm_l18n[$key])) {
			$str = $_bab_pm_l18n[$key];
		} else if (isset($_bab_pm_l18n[$what])) {
			$str = $_bab_pm_l18n[$what];
		} else if (isset($textarray[$key])) {
			$str = $textarray[$key];
		} else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}

#===========================================================================
#	Plugin preferences...
#===========================================================================
global $_bab_pm_prefs;
$_bab_pm_prefs = array
	(
	'subscribers_per_page'	=> array( 'type'=>'text_input' , 'val'=>'20', 'position' => 50 ) ,
	'emails_per_batch'		=> array( 'type'=>'text_input' , 'val'=>'50', 'position' => 60 ) ,
	'email_batch_delay'		=> array( 'type'=>'text_input' , 'val'=>'3', 'position' => 61 ) ,
	'form_select_prefix'	=> array( 'type'=>'text_input' , 'val'=>'newsletter-', 'position' => 70 ) ,
	'default_unsubscribe_url'	=> array( 'type' => 'text_input', 'val' => '', 'position' => 100),
	);

for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
{
	$_bab_pm_prefs["subscriberCustom{$i}"] = array('type' => 'text_input', 'val' => "Custom {$i}:", 'position' => $i + 19);
}

#-------------------------------------------------------------------------------
#	Pref support routines...
#-------------------------------------------------------------------------------
if( @txpinterface === 'admin' )
{
    register_callback( '_bab_pm_handle_prefs_pre' , 'prefs' , 'advanced_prefs' , 1 );
    register_callback( '_bab_pm_handle_prefs_pre' , 'prefs' , 'advanced_prefs_save' , 1 );
    register_callback( '_bab_pm_handle_prefs_pre' , 'postmaster' , 'prefs' , 1 );
}
function _bab_prefix_key($key)
{
    return bab_pm_prefix.'-'.$key;
}
function _bab_pm_install_pref($key, $value, $type, $position=0)
{
    global $prefs , $textarray , $_bab_pm_l18n;

    $k = _bab_prefix_key( $key );
    if( !array_key_exists( $k , $prefs ) )
    {
        set_pref( $k , $value , bab_pm_prefix , 1 , $type , $position );
        $prefs[$k] = $value;
    }
    # Insert the preference strings for non-mlp sites...
	$k = strtolower($k);
    if( !array_key_exists( $k , $textarray ) )
        $textarray[$k] = $_bab_pm_l18n[$key];
}
function _bab_pm_remove_prefs()
{
    safe_delete( 'txp_prefs' , "`event`='".bab_pm_prefix."'" );
}
function _bab_pm_handle_prefs_pre( $event , $step )
{
    global $prefs, $_bab_pm_prefs;

	if (!empty($prefs['plugin_cache_dir']))
		{
		$dir = rtrim($prefs['plugin_cache_dir'], DS) . DS;
		# in case it's a relative path
		if (!is_dir($dir))
			$dir = rtrim(realpath(txpath.DS.$dir), DS) . DS;
		$filename = $dir.'postmaster'.DS.'overrides.php';
		if (is_file($filename))
			{
			#	Bring in the preference overrides from the file...
			@include_once( $filename );
			}
		}

    if( version_compare( $prefs['version'] , '4.0.6' , '>=' ) )
        {
        foreach( $_bab_pm_prefs as $key=>$data )
            _bab_pm_install_pref( $key , $data['val'] , $data['type'], $data['position'] );
        }
    else
        _bab_pm_remove_prefs();
}


function bab_pm_preferences($what)
{
	$lang = array(

		// ---- subscriber-related preferences

		'subscriberFirstName'	  => 'First Name:',
		'subscriberLastName'	  => 'Last Name:',
		'subscriberEmail'         => 'Email:',
		'subscriberLists'           => 'Lists:',

		// ---- list-related preferences

		'listName'	  => 'List Name:',
		'listDescription'         => 'Description:',
		'listAdminEmail'         => 'Admin Email:',
		'listUnsubscribeUrl'           => 'Unsubscribe Url:',
		'listEmailForm'           => 'Form:',
		'listSubjectLine'           => 'Subject Line:',

		// ---- alert text

		'subscriber_add'			=> 'Added subscriber.',
		'subscriber_edit'		=> 'Updated subscriber information.',
		'subscriber_delete'			=> 'Deleted subscriber.',
		'subscribers_delete'			=> 'Deleted subscribers.',
		'subscribers_add_to_list'	=> 'Selected subscribers added to list',
		'subscribers_remove_from_list'	=> 'Selected subscribers removed from list',

		'subscribers_delete_lists'	=> 'Deleted selected lists',
		'subscribers_add_all_to_list'	=> 'Add everyone to selected lists',
		'subscribers_remove_all_from_list'	=> 'Removed everyone from selected lists',

		'list_add'			=> 'Added list.',
		'list_edit'		=> 'Updated list information.',
		'list_delete'			=> 'Deleted list.',
		'lists_delete'			=> 'Deleted lists.',
		'uploaded'			=> 'Uploaded from file.',
		'prefs_saved'	=> 'Preferences saved.',

		// ---- miscellaneous preferences

		'edit_fields_width'           => '440',
		'edit_fields_height'           => '14',
		'zemDoSubscribe_no'           => 'No',
		'unsubscribe_error'           => 'That is not a valid unsubscription. Please contact the list administrator or website owner. ',
		'aggregate_field'           => 'zemSubscriberAggregate',

	);

	$result = @$lang[$what];
	if( !$result )
	{
		global $prefs;
		$key = _bab_prefix_key( $what );
		$result = $prefs[$key];
	}

	return $result;
}

// ----------------------------------------------------------------------------
// bab_pm_file_upload_form --> should move to the Library

function bab_pm_file_upload_form($label,$pophelp,$step,$id='')
{
	global $file_max_upload_size;
	if (!$file_max_upload_size || intval($file_max_upload_size)==0) $file_max_upload_size = 2*(1024*1024);
	$max_file_size = (intval($file_max_upload_size) == 0) ? '': intval($file_max_upload_size);

	$label_id = (@$label_id) ? $label_id : 'postmaster-upload';

	return '<form method="post" enctype="multipart/form-data" action="index.php">'
		. '<div>'
		. (!empty($max_file_size)? n.hInput('MAX_FILE_SIZE', $max_file_size): '')
		. eInput('postmaster')
		. sInput('import')
		. graf(
			'<label for="'.$label_id.'">'.$label.'</label>'.sp.
				fInput('file', 'thefile', '', 'edit', '', '', '', '', $label_id).sp.
				fInput('submit', '', gTxt('upload'), 'smallerbox')
		)
		. '<br /><input type="checkbox" name="dump_first" /> Empty subscribers list before import'
		. '</div></form>';
		;
} // end bab_pm_file_upload_form

// ----------------------------------------------------------------------------
// Import from the old Newsletter Manager plugin
// move to library

function bab_pm_importfromnm()
	{
		$step= gps('step');
		echo '<P class=bab_pm_subhed>IMPORT FROM NEWSLETTER MANAGER</P>';
		echo '<fieldset id="bab_pm_importfromnm"><legend><span class="bab_pm_underhed">Import Subscribers</span></legend>';
		$bab_txp_subscribers_table = safe_pfx('txp_subscribers');
		$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');
		$result = safe_query("UPDATE $bab_pm_SubscribersTable SET flag = '' ");
		$subscribers = getRows("select * from $bab_txp_subscribers_table");
		foreach($subscribers as $subscriber) {
			$subscriberName = $subscriber['name'];
			$subscriberEmail = $subscriber['email'];
			$subscriberCustom1 = $subscriber['nl1'];
			$subscriberCustom2 = $subscriber['nl2'];
			$subscriberCustom3 = $subscriber['nl3'];
			$subscriberCustom4 = $subscriber['nl4'];
			$subscriberCustom5 = $subscriber['nl5'];
			$subscriber_prefs = $subscriber['subscriber_prefs'];
			$oldCatchall = $subscriber['catchall'];
			//insert old subs into new db table

			$md5 = md5(uniqid(rand(),true));
			$strSQL = safe_query("INSERT INTO $bab_pm_SubscribersTable values (NULL,'$subscriberName','$subscriberEmail','default','$subscriberCustom1','$subscriberCustom2','$subscriberCustom3','$subscriberCustom4','$subscriberCustom5','$subscriber_prefs','$oldCatchall','','','','latest','','$md5')");
		}
		echo 'Check out your new subscribers <a href="?event=postmaster&step=subscriberlist">here</a>.<div class=bab_pm_alerts>NOTE: The old Newsletter Manager tables will remain in your database until you remove them.</div>';
		echo '</fieldset>';
	} // end bab_pm_importfromnm

// ---- MAIL CODA ------------------------------------------------

function bab_pm_mail_coda() // this is the coda, after mailing is complete
{
	echo '<div class=bab_pm_alerts>

		<img src="/images/percentImage.png" alt="complete" style="background-position: 0% 0%;" class="pm_prog_bar" /><br />

<p style="padding-top:10px;text-align:center;">Your mailing is complete. You may now close this window.</p>
<p style="padding-top:10px;text-align:center;"><a href="?event=article">Return to Content > Write</a></p>
</div>';
	exit;
}

// ---- CREATE TABLES ------------------
// ---- This function creates two tables:
// ---- the postMasterPrefs table (which handles the admin preferences)
// ---- the subscribers table (which holds all of your subscriber information)

function bab_pm_createTables()
{
	global $txpcfg, $bab_pm_PrefsTable , $bab_pm_SubscribersTable, $bab_pm_mapTable;
	//function to create database
	$version = mysql_get_server_info();
	$dbcharset = "'".$txpcfg['dbcharset']."'";
	//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
	$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version))
		? " ENGINE=MyISAM "
		: " TYPE=MyISAM ";
	// On 4.1 or greater use utf8-tables
	if ( isset($dbcharset) && (intval($version[0]) >= 5 || preg_match('#^4\.[1-9]#',$version))) {
		$tabletype .= " CHARACTER SET = $dbcharset ";
		if (isset($dbcollate))
			$tabletype .= " COLLATE $dbcollate ";
		mysql_query("SET NAMES ".$dbcharset);
	}
	$create_sql[] = safe_query("CREATE TABLE IF NOT EXISTS $bab_pm_PrefsTable (
		`listID` int(4) NOT NULL auto_increment,
		`listName` varchar(100) NOT NULL default '',
		`listDescription` longtext NOT NULL default '',
		`listAdminEmail` varchar(100) NOT NULL default '',
		`listUnsubscribeUrl` varchar(100) NOT NULL default '',
		`listEmailForm` varchar(100) NOT NULL default '',
		`listSubjectLine` varchar(128) NOT NULL default '',
		`catchall` longtext NOT NULL default '',
		PRIMARY KEY  (`listID`)
	) $tabletype ");

	$custom_fields = '';
	for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
	{
		$custom_fields .= "`subscriberCustom{$i}` longtext NOT NULL default \'\'," . n;
	}

	$create_sql[] = safe_query("CREATE TABLE IF NOT EXISTS $bab_pm_SubscribersTable (
		`subscriberID` int(4) NOT NULL auto_increment,
		`subscriberFirstName` varchar(30) NOT NULL default '',
		`subscriberLastName` varchar(30) NOT NULL default '',
		`subscriberEmail` varchar(100) NOT NULL default '',
		{$custom_fields}
		`subscriberCatchall` longtext NOT NULL default '',
		`flag` varchar(100) NOT NULL default '',
		`unsubscribeID` varchar(100) NOT NULL default '',
		PRIMARY KEY  (`subscriberID`),
		UNIQUE (subscriberEmail)
	) $tabletype ");

	$bab_pm_subscribers_list = safe_pfx('bab_pm_subscribers_list');
	$create_sql[] = safe_query("CREATE TABLE IF NOT EXISTS $bab_pm_subscribers_list (
		`id` int(4) NOT NULL auto_increment,
		`list_id` int(4) NOT NULL,
		`subscriber_id` int(4) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE (`list_id`, `subscriber_id`)
	) $tabletype ");

	$create_sql[] = safe_query("ALTER TABLE $bab_pm_subscribers_list ADD INDEX `subscriber_id` ( `subscriber_id` )");

//--- insert initial row in prefs table -------------------------------

	$create_sql[] = safe_query("INSERT INTO $bab_pm_PrefsTable values ('1','default','All subscribers','','','','Notification: A new article has been posted at &lt;txp:site_url /&gt;','')");

//--- insert initial row in subs table -------------------------------

	$md5 = md5(uniqid(rand(),true));
	$create_sql[] = safe_query("INSERT INTO $bab_pm_SubscribersTable (subscriberFirstName, subscriberLastName, subscriberEmail, subscriberCustom1, subscriberCustom10, unsubscribeID) values ('Test','User','test@test','custom1','custom10','$md5')");

	safe_insert('bab_pm_list_prefs', "list_id=1, subscriber_id=1");
	return;
} // end create tables

function bab_pm_addCustomFields($columns=null)
{
	global $txpcfg, $bab_pm_PrefsTable , $bab_pm_SubscribersTable, $bab_pm_mapTable;

	for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
	{
		$n = "subscriberCustom{$i}";
		if (empty($columns) || (is_array($columns) && !in_array($n, $columns)))
		{
			safe_query("ALTER TABLE {$bab_pm_SubscribersTable} ADD COLUMN `{$n}` longtext NOT NULL default ''");
		}
	}
}


function bab_pm_create_subscribers_list()
{
	global $txpcfg, $bab_pm_PrefsTable , $bab_pm_SubscribersTable, $bab_pm_mapTable;

	$lists_table = @getThings('describe `'.PFX.'bab_pm_subscribers_list`');
	if ($lists_table)
	{
		return;
	}

	//function to create database
	$version = mysql_get_server_info();
	$dbcharset = "'".$txpcfg['dbcharset']."'";
	//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
	$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version))
		? " ENGINE=MyISAM "
		: " TYPE=MyISAM ";
	// On 4.1 or greater use utf8-tables
	if ( isset($dbcharset) && (intval($version[0]) >= 5 || preg_match('#^4\.[1-9]#',$version))) {
		$tabletype .= " CHARACTER SET = $dbcharset ";
		if (isset($dbcollate))
			$tabletype .= " COLLATE $dbcollate ";
		mysql_query("SET NAMES ".$dbcharset);
	}

	$bab_pm_subscribers_list = safe_pfx('bab_pm_subscribers_list');

	$sql[] = safe_query("CREATE TABLE IF NOT EXISTS $bab_pm_subscribers_list (
		`id` int(4) NOT NULL auto_increment,
		`list_id` int(4) NOT NULL,
		`subscriber_id` int(4) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE (`list_id`, `subscriber_id`)
	) $tabletype ");

	// get lists
	$lists = safe_rows('listID, listName', 'bab_pm_list_prefs', '1=1');

	// loop over subscribers
	$rs = safe_rows_start('subscriberID, subscriberLists', 'bab_pm_subscribers', '1=1');

	if ($rs)
	{
		while ($row = nextRow($rs))
		{
			extract($row);

			foreach($lists as $list)
			{
				extract($list);
				if (stripos($subscriberLists, $listName) !== false)
				{
					safe_insert('bab_pm_subscribers_list',
						"list_id = $listID, subscriber_id = $subscriberID");
				}
			}
		}
	}
}

// ---- BAB_PM_UNSUBSCRIBE ------------------------------------------------.

function bab_pm_unsubscribe()
{
	$unsubscribeID = gps('uid');
	$unsubscribeID = doSlash($unsubscribeID);
	if (safe_delete("bab_pm_subscribers", "unsubscribeID='$unsubscribeID'")) {
		safe_delete("bab_pm_subscribers_list", "subscriber_id = '$unsubscribeID'");
		return '';
	}
	return bab_pm_preferences('unsubscribe_error');
}


// -------------------------------------------------------------
function subscriberlist_searching_form($crit,$method)
{
	global $prefs;

	$methods = 	array(
		'name' => gTxt('Subscriber Name'),
		'email' => gTxt('Subscriber Email'),
		'lists' => gTxt('Subscriber List')
	);
	$page_url = page_url(array());

	for ($i=1; $i <= 10; $i++)
	{
		$field = 'subscriberCustom' . $i;
		$key = 'bab_pm-' . $field;
		if (!empty($prefs[$key]))
			$methods[$field] = $prefs[$key];
	}

	$selection = selectInput('method',$methods,$method);

	$search_form = <<<search_form
<form action="$page_url" method="POST" id="subscriber_edit_form" style="text-align:center;padding-bottom:10px;">
Search by $selection : <input type="text" name="crit" value="$crit" class="edit" size="15">
<input type="submit" value="Go" class="smallerbox">
</form>
search_form;

	return $search_form;
/*	return
	form(
		graf(gTxt('Search by').sp.selectInput('method',$methods,$method). ' : ' .
			fInput('text','crit',$crit,'edit','','','15').
			eInput("postmaster").sInput('subscribers').sp.
			fInput("submit","search",gTxt('go'),"smallerbox"),' align="center"')
	);*/
}

// -------------------------------------------------------------
function listlist_searching_form($crit,$method)
{
	$methods = 	array(
		'name' => gTxt('List Name'),
		'admin email' => gTxt('Admin Email'),
	);
$atts['type'] = 'request_uri';
		$page_url = page_url($atts);
$selection = selectInput('method',$methods,$method);

$search_form = <<<search_form
<form action="$page_url" method="POST" id="subscriber_edit_form" style="text-align:center;padding-bottom:10px;">
Search by $selection : <input type="text" name="crit" value="$crit" class="edit" size="15">
<input type="submit" value="Go" class="smallerbox">
</form>
search_form;

	return $search_form;
	/*
	return
	form(
		graf(gTxt('Search by').sp.selectInput('method',$methods,$method). ' : ' .
			fInput('text','crit',$crit,'edit','','','15').
			eInput("postmaster").sInput('lists').sp.
			fInput("submit","search",gTxt('go'),"smallerbox"),' align="center"')
	); */
}

// -------------------------------------------------------------
function subscriberlist_nav_form($page, $numPages, $sort, $dir='', $crit='', $method='')
{
	$nav[] = ($page > 1)
	?	bab_pm_PrevNextLink("postmaster",$page-1,gTxt('prev'),'prev',$sort, $dir, $crit, $method)
	:	'';
	$nav[] = sp.small($page. '/'.$numPages).sp;
	$nav[] = ($page != $numPages)
	?	bab_pm_PrevNextLink("postmaster",$page+1,gTxt('next'),'next',$sort, $dir, $crit, $method)
	:	'';
	if ($nav) return graf(join('',$nav),' align="center"');
}

// -------------------------------------------------------------
function subscriberlist_multiedit_form()
{
	return event_multiedit_form('postmaster','','','','','','');
}

// -------------------------------------------------------------
function subscriberlist_multi_edit()
{
	if (ps('selected') and !has_privs('postmaster')) {
		$ids = array();
		if (has_privs('postmaster')) {
			foreach (ps('selected') as $subscriberID) {
				$subscriber = safe_field('subscriberID', 'bab_pm_subscribers', "ID='".doSlash($id)."'");
			}
			$_POST['selected'] = $ids;
		}
		$deleted = event_multi_edit('bab_pm_subscribers','subscriberID');
		if(!empty($deleted)){
			$method = ps('method');
			return bab_pm_subscriberlist(messenger('postmaster',$deleted,(($method == 'delete')?'deleted':'modified')));
		}
		return bab_pm_subscriberlist();
	}
}

// ---- copy of column_head function to allow for different $step value

function bab_pm_column_head($value, $sort='', $current_event='', $islink='', $dir='')
{
	$o = '<th class="small"><strong>';
	if ($islink) {
		$o.= '<a href="index.php';
		$o.= ($sort) ? "?sort=$sort":'';
		$o.= ($dir) ? a."dir=$dir":'';
		$o.= ($current_event) ? a."event=$current_event":'';
		$o.= a.'step=subscribers">';
	}
	$o .= gTxt($value);
	if ($islink) { $o .= "</a>"; }
		$o .= '</strong></th>';
		return $o;
	}

// ---- copy of column_head function to allow for different $step value

function bab_pm_list_column_head($value, $sort='', $current_event='', $islink='', $dir='')
{
	$o = '<th class="small"><strong>';
	if ($islink) {
		$o.= '<a href="index.php';
		$o.= ($sort) ? "?sort=$sort":'';
		$o.= ($dir) ? a."dir=$dir":'';
		$o.= ($current_event) ? a."event=$current_event":'';
		$o.= a.'step=lists">';
	}
	$o .= gTxt($value);
	if ($islink) { $o .= "</a>"; }
		$o .= '</strong></th>';
		return $o;
	}

// ---- copy of PrevNextLink function to allow for different $step value

function bab_pm_PrevNextLink($event,$topage,$label,$type,$sort='',$dir='',$crit='',$method='')
{
          return join('',array(
              '<a href="?event='.$event.a.'step=subscribers'.a.'page='.$topage,
              ($sort) ? a.'sort='.$sort : '',
              ($dir) ? a.'dir='.$dir : '',
              ($crit) ? a.'crit='.$crit : '',
              ($method) ? a.'method='.$method : '',
             '" class="navlink">',
              ($type=="prev") ? '&#8249;'.sp.$label : $label.sp.'&#8250;',
              '</a>'
          ));
}


//custom data tag

function bab_pm_data($atts) {

	global $row, $rs, $thisarticle;

	extract(lAtts(array(
		'display' => 'Body',
		'strip_html' => 'no',
	),$atts));

	global $$display; // contents of $display becomes the variable name

	if(is_array($rs)) extract($rs); // article data
	extract($row); // list data

/* need to update documentation, because now the options for display="" are the actual column names (in order to make $$display work) */

// ---- article-related

	if ($display == 'link') {
		$link = "<txp:permlink />";
		$parsed_link = parse($link);
		return $parsed_link;
	}
	if ($display == 'Body_html') {
		if (!$Body_html) { return; } else {
			if ($strip_html == 'yes') {
				$Body_html = strip_tags(deGlyph($Body_html));
			}
			return $Body_html;
		}
	}
	if ($display == 'Excerpt_html') {
		if (!$Excerpt_html) { return; } else {
			if ($strip_html == 'yes') {
				$Excerpt_html = strip_tags(deGlyph($Excerpt_html));
			}
			return $Excerpt_html;
		}
	} else {
		return $$display;
	}
}//end tag


// ------------------------------------------------------------
function bab_pm_mime($atts)
{

// if you're coming here, that means it's HTML -- no need to check

// make $headers global, so you can update the variable that was set above
	global $headers, $mime_boundary, $listAdminEmail;

// extract the attributes for the tag (to determine which mime they want)
	extract(lAtts(array(
	'type' => 'text',
	),$atts));

// build mimes

	$top_mime = <<<top_mime
Content-Type: multipart/alternative; boundary="$mime_boundary"

top_mime;

	$text_mime = <<<text_mime
--$mime_boundary
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

text_mime;

	$html_mime = <<<html_mime
--$mime_boundary
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

html_mime;

	$end_mime = <<<end_mime
--$mime_boundary--
end_mime;

	// overwrite default headers with new headers:

	$sep = (!is_windows()) ? "\n" : "\r\n";
	$headers = "From: $listAdminEmail".
                   $sep.'X-Mailer: Textpattern/Postmaster'.
                   $sep.'MIME-Version: 1.0'.
                   $sep.'Content-Transfer-Encoding: 8bit'.
                   $sep.'Content-Type: multipart/alternative; boundary="'.$mime_boundary.'"'.
                   $sep;

	if ($type == 'text') {
		if (!$text_mime) { return; } else {
			return $text_mime;
		}
	}
	if ($type == 'html') {
		if (!$html_mime) { return; } else {
			return $html_mime;
		}
	}
	if ($type == 'end') {
		if (!$end_mime) { return; } else {
			return $end_mime;
		}
	}
}

// ------------------------------------------------------------
function bab_pm_unsubscribeLink($atts, $thing='')
{
	global $bab_pm_unsubscribeLink, $prefs;

	extract(lAtts(array(
		'type' => 'text',
	),$atts));

	$default_url = $prefs[_bab_prefix_key('default_unsubscribe_url')];

	$url = empty($bab_pm_unsubscribeLink) ? $url = $default_url : $bab_pm_unsubscribeLink;

	if ($type == 'html')
	{
		$url = $bab_pm_unsubscribeLink = "<a href=\"$url\">$url</a>";
	}

	return $url;
}

function bab_unsubscribe_url($atts, $thing='')
{
	return bab_pm_unsubscribeLink($atts,$thing);
}

//-------------------------------------------------------------
function bab_pm_upgrade_db()
{
	global $bab_pm_SubscribersTable;

//test for existence of first and last name columns
	// $sql = "SHOW COLUMNS FROM {$bab_pm_SubscribersTable} LIKE '%name%'";
	//
	// $rs = safe_rows($sql);
	// if(numRows($rs) < 2) {
		//we'll assume that 2+ name columns means the upgrade's already run
			$sql = <<<END
				ALTER TABLE {$bab_pm_SubscribersTable} ADD COLUMN subscriberLastName varchar(30) NOT NULL default '' AFTER subscriberID,
				ADD COLUMN subscriberFirstName varchar(30) NOT NULL default '' AFTER subscriberID
END;

		$rs = safe_query($sql);
	// }

}

// ===========================

function deGlyph($text)
{
	$glyphs = array (
		'&#8217;',   //  single closing
		'&#8216;',  //  single opening
		'&#8220;',                 //  double closing
		'&#8222;',              //  double opening
		'&#8230;',              //  ellipsis
		'&#8212;',                   //  em dash
		'&#8211;',             //  en dash
		'&#215;',             //  dimension sign
		'&#8482;',          //  trademark
		'&#174;',               //  registered
		'&#169;',             //  copyright
		'&#160;',             //  non-breaking space numeric
		'&#nbsp;',             //  non-breaking space named
		'&#38;',             //  ampersand numeric
		'&amp;'             //  ampersand named
	);

	$deGlyphs = array (
		"'",           //  single closing
		"'",           //  single opening
		'"',           //  double closing
		'"',           //  double opening
		'...',         //  ellipsis
		' -- ',        //  em dash
		' - ',         //  en dash
                ' x ',         //  dimension sign
		'T',          //  trademark
		'R',          //  registered
		'(c)',        //  copyright
		' ',          //  non-breaking space numeric
		' ',          //  non-breaking space named
		'&',          //  ampersand numeric
		'&'           //  ampersand named
	);

	$text = str_replace($glyphs, $deGlyphs, $text);
//	return $text;
// changed to try and remove white space on deglyphed text
	return trim($text);
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<p>See the <a href="http://www.benbruce.com/postmanual">Postmanual</a> for help.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>