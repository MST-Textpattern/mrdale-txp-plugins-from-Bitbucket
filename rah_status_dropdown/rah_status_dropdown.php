<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rah_status_dropdown';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.6.2';
$plugin['author'] = 'Jukka Svahn';
$plugin['author_uri'] = 'http://rahforum.biz';
$plugin['description'] = 'Changes Write panel\'s status radio buttons to a select field';

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
$plugin['type'] = '4';

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


/**
 * Rah_status_dropdown plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @date    2008-
 * @license GNU GPLv2
 * @link    http://rahforum.biz/plugins/rah_status_dropdown
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	register_callback('rah_status_dropdown', 'article_ui', 'status');

/**
 * Replaces status radio buttons with a &lt;select&gt; input.
 *
 * @param  string $event
 * @param  string $step
 * @param  string $default
 * @param  array  $rs
 * @return string HTML
 */

	function rah_status_dropdown($event, $step, $default, $rs)
	{
		global $statuses;

		return 
			preg_replace(
				'/<ul[^>]*?>[\s\S]*?<\/ul>/',
				graf(selectInput('Status', doArray($statuses, 'strip_tags'), !$rs['Status'] ? 4 : $rs['Status']), ' class="status"'),
				$default
			);
	}


# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>rah_status_dropdown</h1>

<p><a href="http://rahforum.biz/plugins/rah_status_dropdown" rel=" rel="nofollow"">Project page</a> | <a href="http://twitter.com/gocom" rel=" rel="nofollow"">Twitter</a> | <a href="https://github.com/gocom/rah_status_dropdown" rel=" rel="nofollow"">GitHub</a> | <a href="http://forum.textpattern.com/viewtopic.php?id=28281" rel=" rel="nofollow"">Support forum</a> | <a href="http://rahforum.biz/donate/rah_status_dropdown" rel=" rel="nofollow"">Donate</a></p>

<p>Rah_status_dropdown is a visual admin-side extension for <a href="http://www.textpattern.com" rel=" rel="nofollow"">Textpattern <span class="caps">CMS</span></a>. The plugin changes <em>Write</em> panel&#8217;s <em>status</em> radio buttons to a select field. Changing the list of statuses to a dropdown will save some space, and hides the extra article statuses that might have no real use to a content author.</p>

<h2>Requirements</h2>

<p>Rah_status_dropdown&#8217;s minimum requirements:</p>

	<ul>
		<li>Textpattern v4.5.0 or newer.</li>
	</ul>

<h2>Installing</h2>

<p>Rah_status_dropdown&#8217;s installation follows the standard plugin installation steps.</p>

	<ol>
		<li>Download the plugin installation code.</li>
		<li>Copy and paste the installation code into the <em>Install plugin</em> box of your Textpattern Plugin pane.</li>
		<li>Run the automated setup.</li>
		<li>After the setup is done, activate the plugin. Done.</li>
	</ol>

<p>The plugin is now in use and the article status show up as a select field.</p>

<h2>Changelog</h2>

<h3>Version 0.6.2 &#8211; 2012/10/31</h3>

	<ul>
		<li>Reverts 0.6.1.</li>
	</ul>

<h3>Version 0.6.1 &#8211; 2012/10/31</h3>

	<ul>
		<li>Casts statuses as strings.</li>
	</ul>

<h3>Version 0.6 &#8211; 2012/08/27</h3>

	<ul>
		<li>Changed plugin type to 4, which allows the plugin to load correctly on Textpattern v4.5.0.</li>
		<li>Now requires Textpattern 4.5.0 or newer.</li>
		<li>Is now fully compatible with Textpattern v4.5.0 release version.</li>
	</ul>

<h3>Version 0.5 &#8211; 2012/07/30</h3>

	<ul>
		<li>Added: The select field is now wrapped in a paragraph.</li>
		<li>Changed: Client-side dependency. Uses the pluggable_ui and server-side <span class="caps">PHP</span> instead of JavaScript.</li>
		<li>Is now compatible with Textpattern v4.5.0 (r3650 and newer).</li>
	</ul>

<h3>Version 0.4 &#8211; 2011/08/06</h3>

	<ul>
		<li>Now the plugin doesn&#8217;t use <span class="caps">PHP</span> tie-in, and the all the action is powered by JavaScript.</li>
	</ul>

<h3>Version 0.3 &#8211; 2011/06/29</h3>

	<ul>
		<li>Fixed: Don&#8217;t spit out JavaScript when the browser&#8217;s JavaScript support is disabled.</li>
	</ul>

<h3>Version 0.2 &#8211; 2010/08/08</h3>

	<ul>
		<li>Moved the JavaScript to <code>&lt;head&gt;</code>.</li>
		<li>Now gets the active status with plain JavaScript, not with <span class="caps">PHP</span> from the saved article.</li>
		<li>Because of the above change, the code is now shorter and less intensive.</li>
		<li>Now requires Textpattern version 4.0.7 or newer.</li>
	</ul>

<h3>Version 0.1 &#8211; 2008/08/31</h3>

	<ul>
		<li>Initial release.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>