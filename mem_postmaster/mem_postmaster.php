<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_postmaster';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '1.0.15';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Simple email-on-post/newsletter manager for Textpattern';

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
$plugin['type'] = '5';

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
$plugin['textpack'] = <<<EOT
#@admin
bab_pm_email_to_subscribers => Email to subscribers
bab_pm_label_list_to_email => List to email
bab_pm_label_send_from => Send from
bab_pm_send_to_list => Send to List
bab_pm_send_to_test => Send to Test
EOT;
// End of textpack

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/*
Postmaster
by Ben Bruce
Help documentation: http://www.benbruce.com/postmaster
Support forum thread: http://forum.textpattern.com/viewtopic.php?id=19510
*/

@include_plugin('mem_postmaster_library'); // postmaster library functions
register_callback('bab_pm_zemcontact_submit','zemcontact.submit'); // plugs into zem_contact, public-side
if (@txpinterface == 'admin') {
	add_privs ('prefs.bab_pm', '1,2');
	add_privs('postmaster', '1,2'); // see help for details
	register_tab('extensions', 'postmaster', 'Postmaster');
	register_callback('bab_postmaster', 'postmaster');
	register_callback('bab_pm_zemcontact_submit','zemcontact.submit');
//	register_callback("bab_pm_eop", 'article' , 'edit', '0');
//	register_callback("bab_pm_eop", 'article' , 'save', '0');
//	register_callback("bab_pm_eop", 'article' , 'publish', '0');
//	register_callback("bab_pm_writetab", 'article' , ''); // default is "only while editing"
	register_callback("bab_pm_writetab", 'article' , 'edit');
}

// ----------------------------------------------------------------------------
// "Content > Write" tab

function bab_pm_writetab($evt, $stp) {
	global $app_mode;

	if ($app_mode === 'async') {
		return '';
	}

	// Get available subscriber lists to create dropdown
	$bab_pm_PrefsTable = safe_pfx('bab_pm_list_prefs');
	$bab_pm_lists = safe_rows('*', $bab_pm_PrefsTable, '1=1');

	$options = array();
	foreach ($bab_pm_lists as $row) {
		$options[$row['listName']] = $row['listName'];
	}

	$selection = selectInput('listToEmail', $options, '', false, '', 'listToEmail');
	$label = gTxt('bab_pm_email_to_subscribers');
	$content = n. '<label for="listToEmail">'.gTxt('bab_pm_label_list_to_email').'</label>'.
		n. $selection.
		br. '<label for="postmaster-sendFrom">'.gTxt('bab_pm_label_send_from').'</label>'.
		n. fInput('text', 'sendFrom', '', '', '', '', '', '', 'postmaster-sendFrom').
		br. fInput('button', 'send_to_list', gTxt('bab_pm_send_to_list'), 'bab_pm_button publish').
		n. fInput('button', 'send_to_test', gTxt('bab_pm_send_to_test'), 'bab_pm_button publish');

	if (function_exists('wrapRegion')) {
		$out = wrapRegion('bab_postmaster', $content, 'bab_pm', $label, 'bab_pm');
	} else {
		$out = n.n.'<fieldset id="bab_pm">'.
			n. '<legend>'.$label.'</legend>'.
			n.graf($content).
			n.'</fieldset>';
	}

	$out = escape_js($out);

	echo script_js(<<<EOJS
		jQuery(function() {
			jQuery('#dates_group').after('{$out}');
			jQuery('.bab_pm_button').click(function() {
				var btn = jQuery(this).attr('name');
				var lst = jQuery('#listToEmail').val();
				var snd = jQuery('#postmaster-sendFrom').val();
				var art = jQuery("input[name='ID']").val();
				var url = 'index.php?event=postmaster&step=initialize_mail&radio='+btn+'&list='+lst+'&sendFrom='+snd+'&artID='+art;
				location.href = url;
			});
		});
EOJS
	);
	
} // end bab_pm_writetab

// ----------------------------------------------------------------------------
// "Admin > Postmaster" tab

function bab_postmaster($event, $step='')
{
	global $bab_pm_PrefsTable, $bab_pm_SubscribersTable;

	if ($event == 'postmaster' && $step == 'export')
	{
		bab_pm_export();
		return;
	}

	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");

	// session_start();
	pagetop('Postmaster','');

	// define the users table names (with prefix)

	$bab_pm_PrefsTable = safe_pfx('bab_pm_list_prefs');
	$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

	// set up script for hiding add sections

	echo $jshas = <<<jshas

<script type="text/javascript">
$(document).ready(function(){
$("a.show").toggle(
function(){
$(".stuff").show('fast');
}, function(){
$(".stuff").hide('slow');
});
});

</script>
jshas;
	// check tables. if not exist, create tables

	$check_prefsTable = @getThings('describe `'.PFX.'bab_pm_list_prefs`');
	$check_subscribersTable = @getThings('describe `'.PFX.'bab_pm_subscribers`');

	if (!$check_prefsTable or !$check_subscribersTable) {
		bab_pm_createTables();
	}

	if (!in_array('subscriberCustom' . BAB_CUSTOM_FIELD_COUNT, $check_subscribersTable))
	{
		bab_pm_addCustomFields($check_subscribersTable);
	}

	$sql = "SHOW COLUMNS FROM {$bab_pm_SubscribersTable} LIKE '%name%'";

	$rs = safe_query($sql);

	if(numRows($rs) < 2) {
		//upgrade the db
		bab_pm_upgrade_db();
	}
	// define postmaster styles

	bab_pm_create_subscribers_list();

	bab_pm_styles();

	bab_pm_poweredit();

	// masthead / navigation

	// fix this hack

	$step = gps('step');
	if (!$step) {
		$step = 'subscribers';
	}
	//assign all down state
	$a_subscribers = $a_lists = $a_importexport = $a_formsend = $a_prefs = '<a class="navlink"';

	$active_tab_var = 'a_' . $step;
	$$active_tab_var = '<a class="navlink-active"';

	$pm_nav = <<<pm_nav

	<p class="nav-tertiary" align="center">

				$a_subscribers href="?event=postmaster&step=subscribers"  class="plain">Subscribers</a>
				$a_lists href="?event=postmaster&step=lists"  class="plain">Lists</a>
				$a_importexport href="?event=postmaster&step=importexport"  class="plain">Import/Export</a>
				$a_formsend href="?event=postmaster&step=formsend"  class="plain">Direct Send</a>
				$a_prefs href="?event=postmaster&step=prefs"  class="plain">Preferences</a>
</p>
pm_nav;

	echo '<div id="bab_pm_master" class="txp-layout-textbox">';
	echo $pm_nav;
	echo '<div id="bab_pm_content">';
	bab_pm_ifs(); // deal with the "ifs" (if delete button pushed, etc)

	include_once txpath.'/publish.php'; // testing page_url

	$handler  = "bab_pm_$step";
	if(!function_exists($handler)) {
		$handler = "bab_pm_subscribers";
	}
	$handler();

	echo '</div>'; // end bab_pm_content
	echo '</div>'; // end master_bab_pm
}

// ----------------------------------------------------------------------------
// "Admin > Postmaster > Subscribers" tab

function bab_pm_makeform()
	{
		global $prefs;

		$page_url = page_url(array());
		$bab_hidden_input = '';
		$event = gps('event');
		$step = gps('step');
		if (!$event) { $event = 'postmaster'; }
		if (!$step) { $step = 'subscribers'; }

		if ($step == 'subscribers') {
			$bab_columns = array(
				'subscriberFirstName',
				'subscriberLastName',
				'subscriberEmail',
				'subscriberLists',
			);

			for($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
			{
				$bab_columns[] = "subscriberCustom{$i}";
			}

			$bab_submit_value = 'Add Subscriber';
			$bab_prefix = 'new';

			$subscriberToEdit = gps('subscriber');
			if ($subscriberToEdit)
			{
				$bab_hidden_input = '<input type="hidden" name="editSubscriberId" value="' . doSpecial($subscriberToEdit) . '">';
				$bab_prefix = 'edit';
				$bab_submit_value = 'Update Subscriber Information';

				$row = safe_row('*', 'bab_pm_subscribers', "subscriberID=" . doSlash($subscriberToEdit));
				$subscriber_lists = safe_rows('*', 'bab_pm_subscribers_list', "subscriber_id = " . doSlash($row['subscriberID']));
				$fname = doSpecial($row['subscriberFirstName']);
				$lname = doSpecial($row['subscriberLastName']);
				echo "<fieldset id=bab_pm_edit><legend><span class=bab_pm_underhed>Editing Subscriber: $fname $lname</span></legend>";
			}
			else
			{
				$subscriber_lists = array();
			}
			$lists = safe_rows('*', 'bab_pm_list_prefs', '1=1 order by listName');
		}
		if ($step == 'lists') {
			$bab_columns = array('listName', 'listAdminEmail','listDescription','listUnsubscribeUrl','listEmailForm','listSubjectLine');
			$bab_submit_value = 'Add List';
			$bab_prefix = 'new';

			if ($listToEdit = gps('list')) {
				$bab_hidden_input = '<input type="hidden" name="editListID" value="' . $listToEdit . '">';
				$bab_prefix = 'edit';
				$bab_submit_value = 'Update List Information';
				$bab_prefix = 'edit';

				$row = safe_row('*', 'bab_pm_list_prefs', "listID=" . doSlash($listToEdit));
				echo "<fieldset id=bab_pm_edit><legend>Editing List: $row[listName]</legend>";
			}

			$form_prefix = $prefs[_bab_prefix_key('form_select_prefix')];

			$forms = safe_column('name', 'txp_form',"name LIKE '". doSlash($form_prefix) . "%'");
			$form_select = selectInput($bab_prefix.ucfirst('listEmailForm'), $forms, @$row['listEmailForm']);
			// replace class
			$form_select = str_replace('class="list"', 'class="bab_pm_input"', $form_select);
		}

		// build form

		echo '<form action="' . $page_url . '" method="POST" id="subscriber_edit_form">';

		foreach ($bab_columns as $column) {
			echo '<span class="bab_pm_form_input"><label>'.bab_pm_preferences($column).'</label>';
			$bab_input_name = $bab_prefix . ucfirst($column);

			switch ($column)
			{
				case 'listEmailForm':
					echo $form_select;
					break;
				case 'listSubjectLine':
					$checkbox_text = 'Use Article Title for Subject';
				case 'listUnsubscribeUrl':
					if (empty($checkbox_text))
						$checkbox_text = 'Use Default';

					$checked = empty($row[$column]) ? 'checked="checked" ' : '';
					$js = <<<eojs
<script>
$(document).ready(function () {
	$('#{$column}_checkbox').change(function(){
		if ($(this).is(':checked')) {
			$('input[name={$bab_input_name}]').attr('disabled', true).val('');
		}
		else {
			$('input[name={$bab_input_name}]').attr('disabled', false);
		}
	});
});
</script>

eojs;

					echo $js . '<input id="'.$column.'_checkbox" type="checkbox" class="bab_pm_input" ' . $checked . '/>'.$checkbox_text.'</span>' .
						'<input type="text" name="' . $bab_input_name . '" value="' . doSpecial(@$row[$column]) . '"' .
							(!empty($checked) ? ' disabled="disabled"' : '') . ' />' .
						'</dd>';
					break;
				case 'subscriberLists':
					foreach ($lists as $list)
					{
						$checked = '';
						foreach ($subscriber_lists as $slist)
						{
							if ($list['listID'] == $slist['list_id'])
							{
								$checked = 'checked="checked" ';
								break;
							}
						}

						echo '<input type="checkbox" name="'. $bab_input_name .'[]" value="'.$list['listID'].'"' . $checked . '/>'
							. doSpecial($list['listName']) . "<br>";
					}
					break;
				default:
					echo '<input type="text" name="' . $bab_input_name . '" value="' . doSpecial(@$row[$column]) . '" class="bab_pm_input">';
					break;
			}
			echo '</span>';
		}

		echo $bab_hidden_input;
		echo '<input type="submit" value="' . doSpecial($bab_submit_value) . '" class="publish">';
		echo '</form>';
	}

function bab_pm_formsend()
{
	//test for incoming send request - nevermind...we'll just fire off the mail function
	$bab_pm_PrefsTable = safe_pfx('bab_pm_list_prefs');
	$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

	$bab_pm_radio = ps('bab_pm_radio'); // this is whether to mail or not, or test
	if ($bab_pm_radio == 'Send to Test')
		$bab_pm_radio = 2;
	if ($bab_pm_radio == 'Send to List')
		$bab_pm_radio = 1;

	if ($bab_pm_radio != 0) { // if we have a request to send, start the ball rolling....
		// email from override
		$sendFrom = gps('sendFrom');

		$listToEmail = (!empty($_REQUEST['listToEmail'])) ? gps('listToEmail') : gps('list');
		// $listToEmail = gps('listToEmail'); // this is the list name
		$subject = gps('subjectLine');
		$form = gps('override_form');

		// ---- scrub the flag column for next time:
		$result = safe_query("UPDATE $bab_pm_SubscribersTable SET flag = NULL");

		//time to fire off initialize
		// bab_pm_initialize_mail();

		$path = "?event=postmaster&step=initialize_mail&radio=$bab_pm_radio&list=$listToEmail&artID=$artID";
		if (!empty($sendFrom)) $path .= "&sendFrom=" . urlencode($sendFrom);
		if(!empty($subject)) $path .= "&subjectLine=" . urlencode($subject);
		if($_POST['use_override'] && !empty($form)) $path .= "&override_form=$form&use_override=1";
		header("HTTP/1.x 301 Moved Permanently");
		header("Status: 301");
		header("Location: ".$path);
		header("Connection: close");

	}

			$options = '';
			$form_select = '';
			// get available lists to create dropdown menu

			$bab_pm_lists = safe_query("select * from $bab_pm_PrefsTable");
			while($row = @mysql_fetch_row($bab_pm_lists)) {
				$options .= "<option>$row[1]</option>";
			}
			$selection = '<select id="listToEmail" name="listToEmail">' . $options . '</select>';

			$form_list = safe_column('name', 'txp_form',"name like 'newsletter-%'");
			if(count($form_list) > 0) {
				foreach($form_list as $form_item)
				{
					$form_options[] = "<option>$form_item</option>";
				}
				$form_select = '<select name="override_form">' . join($form_options,"\n") . '</select>';
				$form_select .= checkbox('use_override', '1', '').'Use override?';
			}
			if(isset($form_select) && !empty($form_select)) {
					$form_select = <<<END
						<div style="margin-top:5px">
							Override form [optional]: $form_select
						</div>
END;
			}
				echo <<<END
				<form action="" method="post" accept-charset="utf-8">
					<fieldset id="bab_pm_formsend">
						<legend><span class="bab_pm_underhed">Form-based Send</span></legend>
							<div style="margin-top:5px">
								<label for="listToEmail" class="listToEmail">Select list:</label> $selection
							</div>
							$form_select
							<label for="sendFrom" class="sendFrom">Send From:</label><input type="text" name="sendFrom" value="" id="sendFrom" /><br />
							<label for="subjectLine" class="subjectLine">Subject Line:</label><input type="text" name="subjectLine" value="" id="subjectLine" /><br />

							<p><input type="submit" name="bab_pm_radio" value="Send to Test" class="publish" />
							&nbsp;&nbsp;
							<input type="submit" name="bab_pm_radio" value="Send to List" class="publish" /></p>
					</fieldset>
				</form>
END;

}

function bab_pm_subscribers()
{
	$page_url = page_url(array());
	$step = gps('step');
	if ($subscriberToEdit= gps('subscriber')) {
		bab_pm_makeform();
	} else {
		// total subscribers

		$lvars = array('page','sort','dir','crit','method');
		extract(gpsa($lvars));

		$total = getCount('bab_pm_subscribers',"1");
		echo '<fieldset id="bab_pm_total-lists"><legend><span class="bab_pm_underhed">Total Subscribers</span></legend><br />' . $total . '</fieldset>';

		// add a subscriber

		echo '<section role="region" id="bab_pm_add-subscriber" class="txp-details" aria-labelledby="bab_pm_add-subscriber-label">
		<h3 id="bab_pm_list-of-subscribers-label" class="lever txp-summary expanded"><a href="#" role="button" class="show" aria-controls="name-of-details" aria-pressed="true">Add Subscriber</a></h3>
		<div role="group" id="add-subscriber" class="stuff toggle" aria-expanded="true">';
		bab_pm_makeform();

		echo '</div></section>';

		// subscriber list


		if (empty($method))
		{
			$method = gps('search_method');
		}

		if (!$sort)
		{
			$sort = "subscriberLastName";
		}
		//$dir = $dir == 'desc' ? 'desc' : 'asc';
		if ($crit)
		{
			$slash_crit = doSlash($crit);
			$critsql = array(
				'name'  => "(subscriberLastName rlike '$slash_crit' or subscriberFirstName rlike '$slash_crit')",
				'email' => "subscriberEmail rlike '$slash_crit'",
				'lists' => "T.subscriberLists rlike '$slash_crit'"
			);
			if (array_key_exists($method, $critsql))
				$criteria = $critsql[$method];
			else
			{
				if (strncmp($method, 'subscriberCustom', 16) == 0)
				{
					$custom_criteria = "(S.{$method} rlike '{$slash_crit}')";
				}
			}
			//$limit = 100;
		}

		if (empty($criteria))
		{
			$criteria = '1';
		}

		if (empty($custom_criteria))
			$custom_criteria = '1';

		$q = "";

		$list_table = safe_pfx('bab_pm_list_prefs');
		$map_table = safe_pfx('bab_pm_subscribers_list');
		$sub_table = safe_pfx('bab_pm_subscribers');

		$q = <<<EOSQL
SELECT COUNT(*) FROM (
SELECT S.subscriberID, S.subscriberFirstName, S.subscriberLastName, S.subscriberEmail,
GROUP_CONCAT(L.listName ORDER BY L.listName SEPARATOR ', ') as subscriberLists
FROM `$sub_table` as S left join `$map_table` as MAP ON S.subscriberID=MAP.subscriber_id
left join `$list_table` as L ON MAP.list_id=L.listID
WHERE $custom_criteria
GROUP BY S.subscriberID) as T
WHERE $criteria
EOSQL;
		$search_results = $total = getThing($q);


		$limit = bab_pm_preferences('subscribers_per_page');
		if (!$limit) $limit = 20;
		$numPages = ceil($total/$limit);
		$page = (!$page) ? 1 : $page;
		$offset = ($page - 1) * $limit;


		$q = <<<EOSQL
SELECT * FROM (
SELECT S.subscriberID, S.subscriberFirstName, S.subscriberLastName, S.subscriberEmail,
GROUP_CONCAT(L.listName ORDER BY L.listName SEPARATOR ', ') as subscriberLists
FROM `$sub_table` as S left join `$map_table` as MAP ON S.subscriberID=MAP.subscriber_id
left join `$list_table` as L ON MAP.list_id=L.listID
WHERE $custom_criteria
GROUP BY S.subscriberID) as T
WHERE $criteria
ORDER BY $sort
LIMIT $offset, $limit
EOSQL;

		$gesh = startRows($q);
		echo '<fieldset id="bab_pm_list-of-subscribers"><legend><span class="bab_pm_underhed">List of Subscribers</span></legend><br />';
		echo subscriberlist_nav_form($page, $numPages, $sort, $dir, $crit, $method);
		echo subscriberlist_searching_form($crit, $method);

		if ($gesh) {
			echo '<form action="' . $page_url . '" method="post" name="longform" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">',
				startTable('','','txp-list'),
				'<thead>'
				.'<tr>',
				bab_pm_column_head('First Name', 'subscriberFirstName', 'postmaster', 1, ''),
				bab_pm_column_head('Last Name', 'subscriberLastName', 'postmaster', 1, ''),
				bab_pm_column_head('Email', 'subscriberEmail', 'postmaster', 1, ''),
				bab_pm_column_head('Lists', 'subscriberLists', 'postmaster', 0, ''),
				bab_pm_column_head(NULL),
		  	'</tr>'
		  	. '</thead>';
			while ($a = nextRow($gesh)) {
				extract(doSpecial($a));

				$modbox = fInput('checkbox','selected[]',$subscriberID,'','','','','','subscriberid_'. $subscriberID);
				if(empty($subscriberFirstName) && empty($subscriberLastName)) { $subscriberFirstName = 'Edit' ;}
				$editLinkFirst = '&nbsp;<a href="?event=postmaster&step=subscribers&subscriber=' . $subscriberID . ' ">' . $subscriberFirstName . '</a>&nbsp;';
				$editLinkLast = '&nbsp;<a href="?event=postmaster&step=subscribers&subscriber=' . $subscriberID . ' ">' . $subscriberLastName . '</a>&nbsp;';
				echo "<tr>".n,
					td($editLinkFirst,125),
					td($editLinkLast,125),
					td($subscriberEmail,250),
					td($subscriberLists,125),
					td($modbox),
				'</tr>'.n;
			}
			echo "<tr>".n,
				tda(
					select_buttons().
					event_multiedit_form('postmaster', array(
						'add_to_list' => bab_pm_gTxt('add_to_list'),
						'remove_from_list' => bab_pm_gTxt('remove_from_list'),
						'delete' => bab_pm_gTxt('delete'),
					), $page, $sort, $dir, $crit, $method)
					, ' colspan="5" style="text-align: right; border: none;"'
				),
			'</tr>'.n;
			echo "</table></form>";
			unset($sort);
		}

		echo '</fieldset>';
		echo '<fieldset><legend>Search Results</legend>' . @$search_results . '</fieldset>';
	}// end if/else
} // end bab_pm_subscribers

// ----------------------------------------------------------------------------
// "Admin > Postmaster > Lists" tab

function bab_pm_lists()
	{
		$step= gps('step');

		if ($listToEdit = gps('list')) {
			bab_pm_makeform();
		} else {

			// total lists

			$lvars = array('page','sort','dir','crit','method');
			extract(gpsa($lvars));
			$total = getCount('bab_pm_list_prefs',"1");
			echo '<fieldset id="bab_pm_total-lists"><legend><span class="bab_pm_underhed">Total Lists</span></legend><br />' . $total . '</fieldset>';

			// add lists

			echo '<fieldset id="bab_pm_edit"><legend><span class="bab_pm_underhed"><a href="#" class="show">Add a List</a></span></legend><br /><div class="stuff">';
			bab_pm_makeform();
			echo '</div></fieldset>';

			// manage lists
			if (empty($method))
			{
				$method = gps('search_method');
			}

			if (!$sort) $sort = "listName";
			$dir = $dir == 'desc' ? 'desc' : 'asc';
			if ($crit) {
				$critsql = array(
					'name' => "listName rlike '".doSlash($crit)."'",
					'admin email'     => "listAdminEmail rlike '".doSlash($crit)."'",
				);
				$criteria = $critsql[$method];
				$limit = 500;
			}
			if (empty($criteria))
			{
				$criteria = '1';
			}
			$gesh = safe_rows_start(
				"*",
				"bab_pm_list_prefs",
				"$criteria order by $sort"
			);
			echo '<fieldset id="bab_pm_list-of-lists"><legend><span class="bab_pm_underhed">List of Lists</span></legend>';
			echo listlist_searching_form($crit,$method);
			if ($gesh) {
				echo '<form action="' . @$page_url . '" method="post" name="longform" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">',
				startTable('','','txp-list'),
				'<thead><tr>',
					// hCell(gTxt('Edit')),
					bab_pm_list_column_head('Name', 'listName', 'postmaster', 1, ''),
					bab_pm_list_column_head('Admin Email', 'listAdminEmail', 'postmaster', 1, ''),
					hCell(gTxt('Description')),
					hCell(gTxt('List Form')),
					bab_pm_column_head(NULL),
				'</tr></thead>';
				while ($a = nextRow($gesh)) {
					extract($a);
					$modbox = fInput('checkbox','selected[]',$listID,'','','','','',$listID);
					$editLink = '<a href="?event=postmaster&step=lists&list=' . $listID . ' ">' . $listName . '</a>';
					$formLink = '<a href="?event=form&step=form_edit&name=' . $listEmailForm . ' ">' . $listEmailForm . '</a>';
					echo "<tr>".n,
						td($editLink,75),
						td($listAdminEmail,170),
						td($listDescription,230),
						td($formLink,170),
						td($modbox),
					'</tr>'.n;
				}
				echo "<tr>".n,
					tda(
						select_buttons().
						event_multiedit_form('postmaster', array(
							'add_all_to_list' => bab_pm_gTxt('add_all_to_list'),
							'remove_all_from_list' => bab_pm_gTxt('remove_all_from_list'),
							'delete_lists' => bab_pm_gTxt('delete'),
						), $page, $sort, $dir, $crit, $method)
						, ' colspan="5" style="text-align: right; border: none;"'
					),
				'</tr>';
				echo "</table></form>";
				unset($sort);
			}
			echo '</fieldset>';
		} // end if/else
	} // end bab_pm_listlist

// ----------------------------------------------------------------------------
// "Admin > Postmaster > Import/Export" tab

function bab_pm_importexport()
{
	echo '<fieldset id="bab_pm_add-subscriber"><legend><span class="bab_pm_underhed">Import Subscribers</span></legend>'
		. bab_pm_file_upload_form(gTxt('upload_file'), 'upload', 'import')
		. '</fieldset>';

	echo '<fieldset id="bab_pm_export-subscribers"><legend><span class="bab_pm_underhed">Export Subscribers</span></legend><div>';

	echo $final = <<<final
<p><a href="?event=postmaster&amp;step=export">Export all subscribers</a></p>
<p><a href="?event=postmaster&amp;step=export&amp;include_header=1">Export all subscribers</a> (include CSV header)</p>
final;

	echo '</div></fieldset>';
}

function bab_pm_quote($str)
{
	// prep field for CSV
	return '"' . str_replace('"', '""', $str) . '"';
}

function bab_pm_import()
{
	global $prefs;

	if (gps('dump_first') == 'on')
	{
		safe_delete('bab_pm_subscribers', '1=1');
		safe_delete('bab_pm_subscribers_list', '1=1');
	}
	else
	{
		/* Scrub the subscriberCatchall column: when bulkadd runs, it flags each email as "latest".
		this is so that if there is some error, we can run a cleanup and delete them. but now we're
		about to run bulkadd again, so presumably, there WERE no errors last time -- so we set a clean slate */

		safe_update('bab_pm_subscribers', "subscriberCatchall = ''", '1');
	}

	$skip_existing = ps('skip_existing') == 'on' ? true : false;

	$file = $_FILES['thefile'];

	@ini_set('auto_detect_line_endings', '1');

	$fh = fopen($file['tmp_name'], 'r');

	if ($fh)
	{
		$added  = 0;
		$lists = safe_rows('listID, listName', 'bab_pm_list_prefs', '1');
		$existing = safe_column('subscriberEmail', 'bab_pm_subscribers', '1');

		while ($row = fgetcsv($fh))
		{
			$email = $row[2];
			$sub_lists = $row[3];

			$row = doSlash(array_pad($row, BAB_CUSTOM_FIELD_COUNT + 4, ''));


			if (count($row) < 3 || empty($email))
			{
				continue;
			}

			$is_existing = in_array($email, $existing);
			if ($is_existing)
			{
				$skipped[] = $email;
			}
			else
			{
				$custom_fields = '';
				for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
				{
					$custom_fields .= "subscriberCustom{$i} = '" . $row[($i + 3)] . "',";
				}

				$md5 = md5(uniqid(rand(),true));
				$subscriber_id = safe_insert('bab_pm_subscribers', "
						subscriberFirstName = '{$row[0]}',
						subscriberLastName = '{$row[1]}',
						subscriberEmail = '{$row[2]}',
						{$custom_fields}
						subscriberCatchall = 'latest',
						unsubscribeID = '$md5'");

				if ($subscriber_id)
				{
					$added++;

					$listids = array();
					foreach (split(',', $sub_lists) as $l)
					{
						$l = trim($l);
						if (empty($l))
						{
							continue;
						}
						foreach($lists as $list)
						{
							if (strcasecmp($list['listName'], $l)==0)
							{
								safe_insert('bab_pm_subscribers_list',
									"list_id = {$list['listID']}, subscriber_id = $subscriber_id");
								break;
							}
						}
					}
				}
				else
				{
					// failed to insert subscriber
				}
			}

		} // end while

		$skip_count = count($skipped);

		echo '<div class="bab_pm_alerts">'
			. "<p>Inserted {$added} addresses.</p>"
			. ($skip_count > 0 ?
				"<p>The following $skip_count addresses already exist in the database and were skipped: "
				. join(', ', $skipped)
				. '</p>'
				:
				''
			)
			. '</div>';

	}

	return bab_pm_importexport();
}

function bab_pm_export()
{
	global $prefs;

	ob_end_clean();

	$date = date("YmdHis");
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="pm_export_'.$date.'.csv"');

	$list_table = safe_pfx('bab_pm_list_prefs');
	$map_table = safe_pfx('bab_pm_subscribers_list');
	$sub_table = safe_pfx('bab_pm_subscribers');

	$custom_fields = '';
	for ($i=1; $i < BAB_CUSTOM_FIELD_COUNT; $i++)
	{
		$custom_fields .= "S.subscriberCustom{$i}, ";
	}

	// get subscribers data, build file
			$q = <<<EOSQL
SELECT S.subscriberID, S.subscriberFirstName, S.subscriberLastName, S.subscriberEmail,
	{$custom_fields}
	GROUP_CONCAT(L.listName ORDER BY L.listName SEPARATOR ', ') as subscriberLists
FROM `$sub_table` as S left join `$map_table` as MAP ON S.subscriberID=MAP.subscriber_id
	left join `$list_table` as L ON MAP.list_id=L.listID
GROUP BY S.subscriberID
ORDER BY S.subscriberID
EOSQL;

	$subscribers = getRows($q);

	if (gps('include_header') == 1)
	{
		$fields = array(
			bab_pm_gTxt('subscriberFirstName'),
			bab_pm_gTxt('subscriberLastName'),
			bab_pm_gTxt('subscriberEmail'),
			bab_pm_gTxt('subscriberLists')
		);

		for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
		{
			$fields[] = $prefs[_bab_prefix_key("subscriberCustom{$i}")];
		}

		echo str_replace(':', '', implode(',', $fields)) . "\n";
	}

	foreach ($subscribers as $subscriber) {
		extract($subscriber);

		$row = array(
			bab_pm_quote($subscriberFirstName),
			bab_pm_quote($subscriberLastName),
			bab_pm_quote($subscriberEmail),
			bab_pm_quote($subscriberLists)
		);

		for ($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
		{
			$n = 'subscriberCustom' . $i;
			$row[] = bab_pm_quote(@$$n);
		}

		echo implode(',', $row) . "\n";
	}

	exit();
}


//$event = isset($event) ? $event : gps('event');

function bab_pm_pref_func($func, $name, $val, $size = '')
{
	if ($func == 'text_input')
	{
		// name mangle to prevent errors in other porrly coded plugins
		$func = 'bab_pm_text_input';
	}
	else
	{
		$func = (is_callable('pref_'.$func) ? 'pref_'.$func : $func);
	}

	return call_user_func($func, $name, $val, $size);
}

function bab_pm_text_input($name, $val, $size = '')
{
	return fInput('text', $name, $val, 'edit', '', '', $size, '', $name);
}

function bab_pm_prefs()
{
	echo n.n.'<form method="post" action="index.php">'.
		n.n.startTable('','','txp-list').
    '<thead>'.
		n.tr(
			tdcs(
				hed(bab_pm_gTxt('Preferences'), 1)
			, 3)
		).'</thead>';

	$rs = safe_rows_start('*', 'txp_prefs', "prefs_id = 1 and event = 'bab_pm' order by position");

	while ($a = nextRow($rs))
	{
		$label_name = bab_pm_gTxt(str_replace('bab_pm-', '', $a['name']));

		$label = ($a['html'] != 'yesnoradio') ? '<label for="'.$a['name'].'" class="'.$a['name'].'">'.$label_name.'</label>' : $label_name;

		$out = tda($label, ' style="text-align: right; vertical-align: middle;"');

		if ($a['html'] == 'text_input')
		{
			$out.= td(
				bab_pm_pref_func('text_input', $a['name'], $a['val'], 20)
			);
		}

		else
		{
			$out.= td(bab_pm_pref_func($a['html'], $a['name'], $a['val']));
		}

		echo tr($out);
	}

	echo n.n.tr(
		tda(
			fInput('submit', 'Submit', bab_pm_gTxt('save_button'), 'publish').
			n.sInput('prefs_save').
			n.eInput('postmaster').
			n.hInput('prefs_id', '1')
		, ' colspan="3" class="noline"')
	).
	n.n.endTable().n.n.'</form>';
}


function bab_pm_prefs_save()
{
	$prefnames = safe_column("name", "txp_prefs", "prefs_id = 1 and event = 'bab_pm'");

	$post = doSlash(stripPost());

	foreach($prefnames as $prefname) {
		if (isset($post[$prefname])) {
			if ($prefname == 'siteurl')
			{
				$post[$prefname] = str_replace("http://",'',$post[$prefname]);
				$post[$prefname] = rtrim($post[$prefname],"/ ");
			}

			safe_update(
				"txp_prefs",
				"val = '".$post[$prefname]."'",
				"name = '".doSlash($prefname)."' and prefs_id = 1"
			);
		}
	}

	update_lastmod();

	$alert = bab_pm_preferences('prefs_saved');
	echo "<div class=\"bab_pm_alerts\">$alert</div>";

	bab_pm_prefs();
}

// ----------------------------------------------------------------------------
// Bulk Mail, in two parts (Initialize, Mail)

// ----------------------------------------------------------------------------
// Initialize

function bab_pm_initialize_mail()
{
	// no need to check radio (checked in eop)
	@session_start();

	global $listAdminEmail, $headers, $mime_boundary, $bab_pm_PrefsTable, $bab_pm_SubscribersTable, $row, $rs, $thisarticle; // $row (list), $rs (article) are global for bab_pm_data

	$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

	$bab_pm_radio = (!empty($_REQUEST['bab_pm_radio'])) ? gps('bab_pm_radio') : gps('radio');

	if ($bab_pm_radio == 'send_to_test')
		$bab_pm_radio = 2;
	if ($bab_pm_radio == 'send_to_list')
		$bab_pm_radio = 1;

  	$sep = (!is_windows()) ? "\n" : "\r\n"; // is_windows line break

	include_once txpath.'/publish.php'; // this line is required

	// get list data (this is so we only perform the query once)

	$listToEmail = (!empty($_REQUEST['listToEmail'])) ? gps('listToEmail') : gps('list');
	$row = safe_row('*', 'bab_pm_list_prefs', "listname = '".doSlash($listToEmail)."'");

	extract($row); // go ahead and do it because you need several of the variables in initialize

	// get article data here, so we only do query one time
	$artID = gps('artID');
	if(!empty($artID)) {
		// bypass if this is called from the send screen
		$rs = safe_row(
			"*, unix_timestamp(Posted) as sPosted,
			unix_timestamp(LastMod) as sLastMod",
			"textpattern",
			"ID=".doSlash($artID)
		);
@	 	@populateArticleData($rs); // builds $thisarticle (for article context error)

	 	// if no subject line, use article title
	 	if (empty($listSubjectLine))
	 	{
	 		$listSubjectLine = $rs['Title'];
	 	}
	}


	$newSubject = gps('subjectLine');
	$subjectLineSource = (!empty($newSubject)) ? 'newSubject' : 'listSubjectLine';

	$sendFrom = urlencode(gps('sendFrom'));
	$email_from = empty($sendFrom) ? $listAdminEmail : $sendFrom;

	$subject = parse($$subjectLineSource);

	// set TOTAL number of subscribers in list (for progress bar calculation)
	if (isset($listID))
	{
		$map_table = safe_pfx('bab_pm_subscribers_list');
		$sub_table = safe_pfx('bab_pm_subscribers');

		$q = <<<EOSQL
SELECT COUNT(*)
FROM `$sub_table` as S inner join `$map_table` as MAP ON S.subscriberID=MAP.subscriber_id
WHERE MAP.list_id = $listID
EOSQL;
		$bab_pm_total = getThing($q);
		$bab_pm_total = $bab_pm_total ? $bab_pm_total : 0;
	}
	else
	{
		$bab_pm_total = 0;
	}

	// set header variables, so that only happens once

	$headers = "From: $email_from".
		$sep."Reply-To: $email_from".
		$sep.'X-Mailer: Textpattern/Postmaster'.
		$sep.'MIME-Version: 1.0'.
		$sep.'Content-Transfer-Encoding: 8bit'.
		$sep.'Content-Type: text/plain; charset="UTF-8"'.
		$sep;

	// set mime boundary, so that only happens once

	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

	// if use override is selected, then overwrite the listEmailForm variable
	if(gps('use_override')) {
		$listEmailForm = gps('override_form');
	}

	//trim the form name
	$listEmailForm = trim($listEmailForm);

	// set email template, so that only happens once

	if (!empty($listEmailForm)) {
		$template = fetch('Form','txp_form','name',"$listEmailForm");
	}

	// test to confirm that we actually have a form, otherwise use default
	if (!$template || empty($template))
	{
$template = <<<eop_form
<txp:author /> has posted a new article at <txp:site_url />.
Read article at: <txp:bab_pm_data display="link" />
Unsubscribe: <txp:bab_pm_unsubscribeLink />
eop_form;

	}
	//echo $template;

	// Scrub the flag column if the request came from the Write panel
	// A yucky hack, but we don't want to flush the flag if the initialize_mail
	// event is called manually (e.g. after browser crash or accidentally clicking
	/// away from the scree) so the mailer can continue where it left off.
	$referrer = serverSet('HTTP_REFERER');
	if (strpos($referrer, 'event=article') !== false && strpos($referrer, 'step=edit') !== false) {
		safe_update($bab_pm_SubscribersTable, 'flag = NULL', '1=1');
	}

	// send all our initialized to bab_pm_bulk_mail

	bab_pm_bulk_mail($bab_pm_total, $bab_pm_radio, $subject, @$thisarticle, $template); // send all info to mail through function
} // end initialize

// ----------------------------------------------------------------------------
// Mail

function bab_pm_bulk_mail($bab_pm_total, $bab_pm_radio, $subject, $thisarticle, $template)
	{
		global $prefs;

		echo '<P class=bab_pm_subhed>BULK MAIL</p>';
		echo '<P>Currently mailing: ' . $subject . '</p>';

 		// ----- set globals for library funcs

		global $headers, $mime_boundary, $bab_pm_unsubscribeLink, $bab_pm_PrefsTable, $bab_pm_SubscribersTable, $row, $rs;
		global $subscriberName, $subscriberFirstName, $subscriberLastName, $subscriberEmail, $subscriberLists;

		$unsubscribe_url = trim($prefs[_bab_prefix_key('default_unsubscribe_url')]);
		if (empty($unsubscribe_url)){
			$unsubscribe_url = trim($row['listUnsubscribeUrl']);
		}


		for($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
		{
			$n = "subscriberCustom{$i}";
			global $$n; // required (extracted in foreach, then sent to bab_pm_data)
		}

		$sep = (!is_windows()) ? "\n" : "\r\n"; // is_windows line break

		// prep Title, Body and Excerpt
		@extract($rs);
		parse(@$Title);
		parse(@$Body);
		$Body = str_replace("\r\n", "\n", @$Body);
		$Body = str_replace("\r", "\n", $Body);
		$Body = str_replace("\n", $sep, $Body);
		parse(@$Excerpt);
		$Excerpt = str_replace("\r\n", "\n", @$Excerpt);
		$Excerpt = str_replace("\r", "\n", $Excerpt);
		$Excerpt = str_replace("\n", $sep, $Excerpt);

		extract($row); // need list data to parse template

		// ----- check bab_pm_radio

		if ($bab_pm_radio == 1)
		{ // set $subscribers to be chosen list of subscribers

			$map_table = safe_pfx('bab_pm_subscribers_list');
			$sub_table = safe_pfx('bab_pm_subscribers');

			$subsQuery = <<< EOSQL
SELECT S.*
FROM `$sub_table` as S inner join `$map_table` as MAP ON S.subscriberID=MAP.subscriber_id
WHERE MAP.list_id = $listID AND flag != 'mailed'
EOSQL;
			$subscribers = getRows("$subsQuery");
			$sub_total = count($subscribers);

			if (!$subscribers) { // if there are NO subscribers NOT mailed, go to coda
				bab_pm_mail_coda();
			}
			$sent = ($bab_pm_total-$sub_total);
			$remaining = round((1-($sent/$bab_pm_total))*100);

			echo $status_report = <<<status_report

<div class=bab_pm_alerts>
	<img src="/images/percentImage.png" alt="$remaining" style="background-position: {$remaining}% 0%;" class='pm_prog_bar' /><br />

			$sub_total left ...

</div>

<p style="padding:10px;text-align:center;">Please don't close this window until mailing is complete. You will receive a written message when it's done.</p>

status_report;

		}
		if ($bab_pm_radio == 2) { // set $subscribers to be an an array of an array of one
			$testSubscriber = array(
	 			'subscriberID'        => '0',
	 			'subscriberFirstName' => 'Test',
				'subscriberLastName'  => 'User',
	 			'subscriberEmail'     => $listAdminEmail,
	 			'unsubscribeID'       => '12345'
			);
			$subscribers = array($testSubscriber);
		}

		// begin batch

		$email_batch = bab_pm_preferences('emails_per_batch');
		if (empty($email_batch) or !is_numeric($email_batch))
		{
			$email_batch = 10;
		}

		$i=1; // set internal counter
		foreach($subscribers as $subscriber) {

			if ($i <= $email_batch) {
				@extract($subscriber);
				if (empty($subscriberLastName) && empty($subscriberFirstName))
				{
					$subscriberName = "Subscriber";
				} else {
					$subscriberName = @$subscriberFirstName . ' ' . @$subscriberLastName;
				}
				$url = $unsubscribe_url;
				if (!empty($url)) {
					$bab_pm_unsubscribeLink = $url . (strrchr($url, '?') ? '&' : '?') . 'uid=' . urlencode($unsubscribeID);
				}
				else {
					$bab_pm_unsubscribeLink = '';
				}
				// all necessary variables now defined, parse email template
				$email = @parse($template);
				$email = str_replace("\r\n", "\n", $email);
				$email = str_replace("\r", "\n", $email);
				$email = str_replace("\n", $sep, $email);
				// finally, mail
				mail($subscriberEmail, $subject, $email, $headers);
				$i++; // ---- update internal counter
				// mark address as "mailed"
				$result = safe_update('bab_pm_subscribers', "flag = 'mailed'", "subscriberEmail='".doSlash($subscriberEmail)."'");

				echo "<p>Mail sent to $subscriberEmail</p>";
			}
			else  { break; }
		} // end foreach
		if ($bab_pm_radio == 1)
		{
			$email_batch_delay = $prefs[_bab_prefix_key('email_batch_delay')];
			if (empty($email_batch_delay) || !is_numeric($email_batch_delay))
			{
				$email_batch_delay = 3;
			}

			header("Cache-Control: no-store");
			header("Refresh: ".$email_batch_delay.";");
			exit;
		}
		if ($bab_pm_radio == 2) {
			echo '<div class=bab_pm_alerts>Your mailing is complete.</div>';
		}
	} // end bulk mail

// ----------------------------------------------------------------------------
// Navigation

function bab_pm_mastheadNavigation()
	{
		echo '<center><P class="bab_pm_hed">POSTMASTER</p>';
	// http://www.stokedtohost.com/textpattern/?event=postmaster&step=subscriberlist
$layout = <<<layout

<td class="navlink2">hello</td><td class="navlink2"><a href="?event=postmaster&step=listlist"  class="plain">Lists</a></td><td class="navlink2"><a href="?event=postmaster&step=add"  class="plain">Add</a></td><td class="navlink2"><a href="?event=postmaster&step=importexport"  class="plain">Import/Export</a></td>
 </tr></table><Br>
 <table width="600" class="bab_pm_contenttable"><tr><td valign="top">
layout;

		echo $layout;
	}

// ----------------------------------------------------------------------------
// Zem_Contact_Reborn

function bab_pm_zemcontact_submit()
{
	global $zem_contact_values;
	extract(doSlash($zem_contact_values));
	$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

	$zemSubscriberEmail = @trim($zemSubscriberEmail);

	// check if this IS a Postmaster zem_submit; if not, return
	if (!$zemSubscriberEmail) {
		return;
	}
	// check if the zemDoSubscribe option is included; if so, does it say no? if no, return
	if ($zemDoSubscribe && $zemDoSubscribe == 'No')  {
		return;
	}

	// check if zemUnSubscribe is included; if so, does it say "on"? if "on", unsubscribe and return
	$is_unsubscribe = strtolower($zemUnSubscribe);
	$is_unsubscribe = $is_unsubscribe == 'on' || $is_unsubscribe == 'yes';

	$subscriber_id = safe_field('subscriberID', 'bab_pm_subscribers', "subscriberEmail='".$zemSubscriberEmail."'");

	if ($is_unsubscribe)
	{
		if ($subscriber_id)
		{
			// remove from lists
			safe_delete('bab_pm_subscribers_list', "subscriber_id=$subscriber_id");
			// delete subscription record
			if (safe_delete('bab_pm_subscribers', "subscriberEmail='".$zemSubscriberEmail."'")) {
				return '';
			}
		}

		return "There was an error. Please contact the administrator of this website. ";
	}

	$fields = array('FirstName', 'LastName', 'Email');

	for($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
	{
		$fields[] = "Custom{$i}";
	}

	$set = array("unsubscribeID = '". md5(uniqid(rand(),true)) ."'");

	foreach($fields as $f)
	{
		$var = 'zemSubscriber' . $f;
		$val = $$var;
		// ignore empty values on update
		if (!empty($val) || !$subscriber_id)
		{
			$set[] = 'subscriber' . $f . " = '" . $val . "'";
		}
	}

	if (!$subscriber_id)
	{
		// add new subscriber
		$subscriber_id = safe_insert('bab_pm_subscribers', implode(', ', $set));

		$subscriptions = false;
	}
	else
	{
		// update existing subscriber details
		safe_update('bab_pm_subscribers', implode(', ', $set), "subscriberID = $subscriber_id");

		// get current list memberships
		$subscriptions = safe_rows('list_id', 'bab_pm_subscribers_list', "subscriber_id = $subscriber_id");
	}

	if ($subscriber_id)
	{
		if (!is_array($subscriptions))
			$subscriptions = array();

		// get lists
		$lists = safe_rows('listID, listName', 'bab_pm_list_prefs', '1=1');

		$sub_lists = explode(',',$zemSubscriberLists);

		foreach ($sub_lists as $slist)
		{
			foreach($lists as $l)
			{
				$list_id = $l['listID'];
				$list_name = trim($l['listName']);
				// if the list name is valid and not already scubscribed
				if (strcasecmp($list_name, $slist) == 0 && !in_array($list_id, $subscriptions))
				{
					// subscribe
					safe_insert('bab_pm_subscribers_list',
						"list_id = $list_id, subscriber_id = $subscriber_id");
				}

			}
		}
	}
}

// ----------------------------------------------------------------------------
// ZCR form, date input

function bab_pm_zemTime($atts)
	{
		$today = date("F j, Y, g:i a");
		extract(lAtts(array(
			'custom_field' => '',
		),$atts));

$form_line = <<<yawp
<input type="hidden" name="$custom_field" value="$today" >
yawp;
		return $form_line;
	}

// ----------------------------------------------------------------------------
// email on post -- this is called after you click "Save"
// TODO: Remove
function bab_pm_eop()
{
	$bab_pm_PrefsTable = safe_pfx('bab_pm_list_prefs');
	$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

	$bab_pm_radio = ps('bab_pm_radio'); // this is whether to mail or not, or test
	$s2l = ps('send_to_list');
	$s2t = ps('send_to_test');
	if ($s2t)
		$bab_pm_radio = 2;
	if ($s2l)
		$bab_pm_radio = 1;
	$listToEmail = ps('listToEmail'); // this is the list name
	$artID=ps('ID');
	$sendFrom=ps('sendFrom');

	// ---- scrub the flag column for next time:
	if ($bab_pm_radio)
	{
		$result = safe_query("UPDATE $bab_pm_SubscribersTable SET flag = NULL");

		$path = "?event=postmaster&step=initialize_mail&radio=$bab_pm_radio&list=$listToEmail&artID=$artID";
		if (!empty($sendFrom)) $path .= '&sendFrom='.urlencode($sendFrom);
		header("HTTP/1.x 301 Moved Permanently");
		header("Status: 301");
		header("Location: ".$path);
		header("Connection: close");
	}
} // end bab_pm_eop

// ----------------------------------------------------------------------------
// the ifs (if the delete button is clicked, etc)

function bab_pm_ifs()
	{

		$bab_pm_PrefsTable = safe_pfx('bab_pm_list_prefs');
		$bab_pm_SubscribersTable = safe_pfx('bab_pm_subscribers');

		// if the "add subscriber" button has been clicked

		$if_add = ps('newSubscriberFirstName') or ps('newSubscriberLastName') or ps('newSubscriberEmail') or ps('newSubscriberLists');

		$fields_array = array('newSubscriberFirstName', 'newSubscriberLastName', 'newSubscriberEmail');
		for($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
		{
			$n = "newSubscriberCustom{$i}";
			$fields_array[] = $n;
			$if_add |= ps($n);
		}

		if ($if_add)
		{
			extract(doSlash(gpsa($fields_array)));

			$md5 = md5(uniqid(rand(),true));

			$sql_fields = '';
			foreach($fields_array as $field)
			{
				$field_name = 's' . substr($field, 4);
				$sql_fields .= "`{$field_name}` = '" . $$field . "', ";
			}
			$sql_fields .= "`unsubscribeID` = '{$md5}'";

			$subscriber_id = safe_insert('bab_pm_subscribers', $sql_fields);

			$lists = gps('newSubscriberLists');

			if (is_array($lists))
			{
				foreach($lists as $l)
				{
					$list_id = doSlash($l);
					safe_insert('bab_pm_subscribers_list',
						"list_id = $list_id, subscriber_id = $subscriber_id");
				}
			}

			$alert = bab_pm_preferences('subscriber_add');
			echo "<div class=bab_pm_alerts>$alert</div>";
		}

		// if the "add list" button has been clicked

		if (ps('newListName') or ps('newListDescription') or ps('newListAdminEmail') or ps('newListUnsubscribeUrl') or ps('newListEmailForm') or ps('newListSubjectLine'))
		{
			$addNewListArray = array(doSlash(ps('newListName')), doSlash(ps('newListDescription')), doSlash(ps('newListAdminEmail')), doSlash(ps('newListUnsubscribeUrl')), doSlash(ps('newListEmailForm')), doslash(ps('newListSubjectLine')));
			$strSQL = safe_query("INSERT INTO $bab_pm_PrefsTable values (NULL,'$addNewListArray[0]','$addNewListArray[1]','$addNewListArray[2]','$addNewListArray[3]','$addNewListArray[4]','$addNewListArray[5]','')");
			$alert = bab_pm_preferences('list_add');
			echo "<div class=bab_pm_alerts>$alert</div>";
		}

		if (ps('step') == 'postmaster_multi_edit')
		{
			// multiedit
			$selected = ps('selected');
			$method = ps('edit_method');

			$selected_list_id = ps('selected_list_id');
			$list_id = doSlash($selected_list_id);

			if ($selected)
			{

				foreach ($selected as $s)
				{
					$sid = doSlash($s);

					if ($method == 'delete')
					{
						// delete subscriber
						safe_delete('bab_pm_subscribers', "subscriberID = $sid");
						// delete subscriber list map
						safe_delete('bab_pm_subscribers_list', "subscriber_id = $sid");
					}
					else if ($method == 'add_to_list')
					{
						// ignore error. It'll most likely be from unique constraint
						@safe_insert('bab_pm_subscribers_list', "subscriber_id = $sid, list_id = $list_id");
					}
					else if ($method == 'remove_from_list')
					{
						safe_delete('bab_pm_subscribers_list', "subscriber_id = $sid AND list_id = $list_id");
					}
					else if ($method == 'delete_lists' || $method == 'remove_all_from_list')
					{
						// unsubscribe all from list
						safe_delete('bab_pm_subscribers_list', "list_id = $sid");

						if ($method == 'delete_lists')
						{
							// remove list
							safe_delete('bab_pm_list_prefs', "listID = $sid");
						}
					}
					else if ($method == 'add_all_to_list')
					{
						// remove all subs for list to prevent constraint violation
						safe_delete('bab_pm_subscribers_list', "list_id = $sid");
						// add everyone to list
						safe_query('INSERT INTO '.PFX.'bab_pm_subscribers_list (list_id, subscriber_id)'
							. " SELECT $sid as list_id, subscriberID as subscriber_id FROM ".PFX."bab_pm_subscribers");
					}
				}

				$alert = bab_pm_preferences('subscribers_'.$method);
			}
			else
			{
				$alert = 'You must select at least 1 checkbox to do that';
			}

			echo '<div class="bab_pm_alerts">'
				. $alert
				. '</div>';
		}

		$editSubscriberId = ps('editSubscriberId');

		// if the "edit subscriber" button has been clicked
		if ($editSubscriberId)
		{
			$edit_fields = array('editSubscriberFirstName', 'editSubscriberLastName', 'editSubscriberEmail');
			for($i=1; $i <= BAB_CUSTOM_FIELD_COUNT; $i++)
			{
				$edit_fields[] = "editSubscriberCustom{$i}";
			}

			extract(doSlash(gpsa($edit_fields)));

			$sql_fields = array();
			foreach($edit_fields as $field)
			{
				$n = 's' . substr($field, 5);
				$sql_fields[] = "`{$n}` = '" . $$field . "'";
			}
			$sql_fields = implode(', ', $sql_fields);

			safe_update('bab_pm_subscribers', $sql_fields, "`subscriberID` = {$editSubscriberId}");

			$lists = ps('editSubscriberLists');

			safe_delete('bab_pm_subscribers_list', "subscriber_id = $editSubscriberId");

			if (is_array($lists))
			{
				foreach($lists as $l)
				{
					$list_id = doSlash($l);
					safe_insert('bab_pm_subscribers_list',
						"subscriber_id = $editSubscriberId, list_id = $list_id");
				}
			}

			$alert = bab_pm_preferences('subscriber_edit');
			echo "<div class=bab_pm_alerts>$alert</div>";
		}

		// if the "edit list" button has been clicked

		if (ps('editListName') or ps('editListDescription') or ps('editListAdminEmail') or ps('editListUnsubscribeUrl') or ps('editListEmailForm') or ps('editListSubjectLine')) {
			$editListArray = array(doSlash(ps('editListName')), doSlash(ps('editListDescription')), doSlash(ps('editListAdminEmail')), doSlash(ps('editListUnsubscribeUrl')), doSlash(ps('editListEmailForm')), doSlash(ps('editListID')), doslash(ps('editListSubjectLine')));
			$strSQL = safe_query("update $bab_pm_PrefsTable set
	listName='$editListArray[0]', listDescription='$editListArray[1]', listAdminEmail='$editListArray[2]', listUnsubscribeUrl='$editListArray[3]', listEmailForm='$editListArray[4]', listSubjectLine='$editListArray[6]' where listID=$editListArray[5]");
$alert = bab_pm_preferences('list_edit');
			echo "<div class=bab_pm_alerts>$alert</div>";
		}
	}


# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<p><style><br />
.note {
	border:1px solid gray;padding:10px;background-color:#f5f5f5;color:red;font-size:smaller;margin-bottom:20px;margin-top:20px;<br />
}<br />
</style><br />
<p>Whether this is your first time using Postmaster, or you?ve come to solve a problem, your first step is to follow the tutorial (for people resolving an issue, this provides a baseline to work from):</p></p>

<p>First things first:</p>

<ul>
	<li>Is Postmaster successfully installed and set to &#8220;Active&#8221;?</li>
	<li>Is the Postmaster Library successfully installed and set to &#8220;Active&#8221;?</li>
	<li>Do you have the Zem_Contact_Reborn plugin installed and set to &#8220;Active&#8221;? Remember that Zem_Contact_Reborn requires a separate language plugin to work properly.</li>
	<li>Is your browser set to accept Javascript?</li>
</ul>

<p>All set? OK, then. First things first&#8212;Click on the Postmaster tab under the Extensions tab in your Textpattern Admin. You will see three sub-tabs: Subscribers, Lists and Import/Export.</p>

<div class="note"><span class="caps">NOTE</span>: The first time you click the Postmaster tab, the plugin automatically creates the two database tables needed to store information (one for lists, and one for subscribers). It will also enter a &#8220;default&#8221; list and a &#8220;test&#8221; subscriber to each table.</div>

<p>Click on the Lists sub-tab for now. This opens a &#8220;list of lists&#8221; page, with the total number of lists displayed at the top, a link to &#8220;Add a List,&#8221; a search form and a table displaying a list of all lists you have entered.</p>

<p>You can re-order the list of lists using the &#8220;Name&#8221; or &#8220;Admin Email&#8221; column headers. There&#8217;s a checkbox next to each list and a separate &#8220;Check All&#8221; box (you can select some or all lists and click the &#8220;Delete&#8221; button to delete). Currently there is only one list, &#8220;default,&#8221; which Postmaster entered for you. You&#8217;ll have to make some adjustments to &#8220;default&#8221; before we can send any mail so click the list name (&#8220;default&#8221;).</p>

<p>That brings up the Editing List page, which displays <i>all</i> the fields of data for each list: List Name, List Description, Admin Email, Unsubscribe <span class="caps">URL</span>, List Form and Subject Line.</p>

<p>You may change or edit any of these fields at any time, and then click the &#8220;Update List Information&#8221; button at the bottom to save your changes. For now, leave everything alone except the Admin Email field, in which you should enter a real email address. After you&#8217;ve entered the email address, click the &#8220;Update List Information&#8221; button.</p>

<p>A list isn&#8217;t really a list without a subscriber&#8212;so click the Subscribers sub-tab now. This opens a &#8220;list of subscribers&#8221; page much like the &#8220;list of lists&#8221; page. There is only one subscriber listed, &#8220;test,&#8221; with a faulty email address entered. Click on the subscriber name to update your subscriber&#8217;s information.</p>

<p>That brings up an Editing Subscriber page, much like the &#8220;Editing List&#8221; page you already saw. This page has quite a few more fields: Subscriber Name, Subscriber Email, Subscriber Lists and Subscriber Custom 1 through Subscriber Custom 10.</p>

<div class="note"><span class="caps">NOTE</span>: The Subscriber Lists field should contain the word &#8220;default&#8221;&#8212;that means subscriber &#8220;Test&#8221; is a member of list &#8220;default.&#8221;</div>

<p>You may change or edit any of these fields at any time, and then click <span class="caps">UPDATE</span> <span class="caps">SUBSCRIBER</span> <span class="caps">INFORMATION</span> at the bottom to save your changes. For now, leave everything alone except the <span class="caps">EMAIL</span>, in which you should enter a real email address. After you&#8217;ve entered the email address, click <span class="caps">UPDATE</span> <span class="caps">SUBSCRIBER</span> <span class="caps">INFORMATION</span>.</p>

<p>Now that you have an updated list and subscriber, it&#8217;s time to mail your first email!</p>

<p>Click on the <span class="caps">WRITE</span> tab in your Textpattern Admin. Write a bit of content&#8212;give it title &#8220;newsletter test&#8221; and in the body, write &#8220;Hey diddy diddy, there&#8217;s a kiddy in the middle.&#8221; Assign the new article to a section that won&#8217;t go public, and click <span class="caps">PUBLISH</span>.</p>

<p>Once the page refreshes, you&#8217;ll see a new module underneath the <span class="caps">SAVE</span> button, called &#8220;Email to subscribers?&#8221; There is a dropdown menu and a set of radio buttons. Use the dropdown menu to select a list (currently you only have one choice) and fill a radio button other than &#8220;No&#8221; if you&#8217;d like to send a mail (&#8220;Test&#8221; sends mail to the admin email address of the selected list; &#8220;Yes&#8221; sends mail to the entire selected list).</p>

<p>Once you&#8217;ve selected your list, and the correct radio button (you selected <span class="caps">TEST</span> for your first one, right?), click the <span class="caps">SAVE</span> button again. You will be taken to the Bulk Mail screen, which shows your mail status. Wait there until you receive the all clear (if you selected Test, this is immediate).</p>

<p>Go to the inbox of whatever email you entered&#8212;you should have received an email called &#8220;Notification: &#8230;&#8221; Congratulations!</p>

<div class="note"><span class="caps">NOTE</span>: if you have any problems up to this point, you need to come to the <txp:bab_link id="26" linktext="support forum thread" class="support_forum_thread" /> for help.</div>

<p>Lots more documentation is available in the <a href="http://www.benbruce.com/postmanual">Postmanual</a>.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>