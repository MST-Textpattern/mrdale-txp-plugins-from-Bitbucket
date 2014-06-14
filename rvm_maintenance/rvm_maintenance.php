<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rvm_maintenance';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.5';
$plugin['author'] = 'Ruud van Melick';
$plugin['author_uri'] = 'http://vanmelick.com/';
$plugin['description'] = 'Maintenance Mode';

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
$plugin['type'] = '0';

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

register_callback('rvm_maintenance_init', 'pretext');

function rvm_maintenance_init()
{
  if (txpinterface == 'public' and !gps('txpcleantest') and !is_logged_in())
  {
    $_GET = $_POST = $_REQUEST = array();
    register_callback('rvm_maintenance', 'pretext_end');
  }
}

function rvm_maintenance()
{
    txp_die('Site maintenance in progress. Please check back later.', 503);
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>Maintenance Mode</h1>

	<p>This plugin, when activated, will show a maintenance page to all visitors who are not logged in on the admin side of Textpattern.</p>

	<p>After enabling the plugin, click the <em>View Site</em> tab. This will allow you to see what your website will look like to visitors once the maintenance mode is deactivated.</p>

	<p>There are two ways to customize the maintenance mode error page<sup class="footnote"><a href="#fn299886085513a6c957ade7">1</a></sup>:
	<ol>
		<li>modify the <em>error_default</em> template page.</li>
		<li>create a new template page called <em>error_503</em> (recommended!)</li>
	</ol></p>

	<p>To disable maintenance mode, simply deactivate the plugin. This will make your entire site viewable to all visitors.</p>

	<p>This plugin requires Textpattern 4.0.7 or higher.</p>

	<p id="fn299886085513a6c957ade7" class="footnote"><sup>1</sup> Maintenance mode uses a <em>503 Service Unavailable</em> <span class="caps">HTTP</span> status header.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>