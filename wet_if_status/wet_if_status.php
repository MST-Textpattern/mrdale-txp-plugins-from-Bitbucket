<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'wet_if_status';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Robert Wetzlmayr';
$plugin['author_uri'] = 'http://awasteofwords.com/article/wet_if_status-testing-article-stati-with-a-textpattern-plugin';
$plugin['description'] = 'Tests for various article stati';

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

function wet_if_live($atts, $thing)
{
	global $thisarticle;
	assert_article();
	$id = $thisarticle['thisid'];
	return parse(EvalElse($thing, 4 == safe_field('Status', 'textpattern', "ID = '$id'")));
}

function wet_if_sticky($atts, $thing)
{
	global $thisarticle;
	assert_article();
	$id = $thisarticle['thisid'];
	return parse(EvalElse($thing, 5 == safe_field('Status', 'textpattern', "ID = '$id'")));
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h3>Tests for various article stati</h3>

	<h4>Usage:</h4>

	<p>This particular plugin contains two conditional tags which check for an article&#8217;s status being either &#8220;live&#8221; or &#8220;sticky&#8221;, respectively. Usage is plain and simple:</p>

	<p><code>&lt;txp:wet_if_live&gt;...&lt;/txp:wet_if_live&gt;</code></p>

	<p><code>&lt;txp:wet_if_sticky&gt;...&lt;/txp:wet_if_sticky&gt;</code></p>

	<p>Both tags are bound to be used in an article context, either as part of an article form or inside the body/excerpt.</p>

	<h4>Licence</h4>

	<p>This plugin is released under the <a href="http://www.gnu.org/licenses/gpl.txt" rel="nofollow">Gnu General Public Licence</a>.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>