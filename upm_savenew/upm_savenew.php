<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'upm_savenew';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.4.2';
$plugin['author'] = 'Mary Fredborg';
$plugin['author_uri'] = 'http://utterplush.com/txp-plugins/upm-savenew';
$plugin['description'] = '\"Save New\" button for articles and forms.';

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
if (txpinterface == 'admin')
	{
		// link to javascript
		add_privs('upm_savenew_js_link_load', '1,2,3,4,5,6');
		register_callback('upm_savenew_js_link_load', 'article', 'edit', 1);
		register_callback('upm_savenew_js_link_load', 'form', '', 1);

		// load javascript
		add_privs('upm_savenew_js', '1,2,3,4,5,6');
		register_callback('upm_savenew_js', 'upm_savenew_js', '', 1);
	}

// -------------------------------------------------------------

	function upm_savenew_js_link_load()
	{
		ob_start('upm_savenew_js_link');
	}

	function upm_savenew_js_link($buffer)
	{
		$find = '</head>';
		$replace = n.n.t.t.'<script type="text/javascript" src="index.php?event=upm_savenew_js"></script>'.n.t;

		return str_replace($find, $replace.$find, $buffer);
	}

// -------------------------------------------------------------

	function upm_savenew_js()
	{
		while (@ob_end_clean());

		$save_new = gTxt('save_new');

		header("Content-type: text/javascript");

		echo <<<js
/*
upm_savenew
*/

	$(document).ready(function() {
		// create new article submit button
		$('#page-article input[name="save"]').eq(0).
			after(' <input type="submit" name="publish" value="$save_new" class="publish" />');

		// article save new button
		$('#page-article input[name="save"] + input[name="publish"]').
			// onclick...
			click(function(){
				// check reset time checkbox
				$('#reset_time').attr({
					name: 'publish_now',
					checked: true
				});

				// empty URL-only title
				$('#url-title').attr('value', '');
// set article status to 1
$("#status-1").attr('checked','checked');
// unbind the JS Listener from the HTML From
$('#article_form').unbind();
			});

		// create new form submit button
		$('#page-form input[name="save"]').
			after(' <input type="submit" name="savenew" value="$save_new" class="publish" />');

		// forms save new button
		$('#page-form input[name="save"] + input[name="savenew"]').
			// onclick...
			click(function(){
				// change form name from original to original_copy
				$('input[name="name"]').attr('value', $('input[name="name"]').attr('value') + '_copy');
			});
	});

js;
		exit(0);
	}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>upm_savenew</h1>

	<p>Once installed and activated, you&#8217;re ready to go.</p>

	<p>This plugin started as <a href="http://forum.textpattern.com/viewtopic.php?id=2586" rel="nofollow">a hack created by <strong>grapeice925</strong></a> of the Textpattern forum. I converted it into an admin-side plugin, and extended it to include a &#8220;Save New&#8221; button for forms.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>