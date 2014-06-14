<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rah_nocache';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.4.0';
$plugin['author'] = 'Jukka Svahn';
$plugin['author_uri'] = 'http://rahforum.biz';
$plugin['description'] = 'Prevent client-side caching on admin-side panels';

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


/**
 * Rah_nocache plugin for Textpattern CMS
 *
 * @author  Jukka Svahn
 * @date    2009-
 * @license GNU GPLv2
 * @link    http://rahforum.biz/plugins/rah_nocache
 *
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

class rah_nocache
{
	/**
	 * Constructor.
	 */

	public function __construct()
	{
		$this->send_headers();
	}

	/**
	 * Send no-cache HTTP headers.
	 */

	public function send_headers()
	{
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		header('Expires: Sat, 24 Jul 2003 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Pragma: no-cache');
	}
}

new rah_nocache();
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>rah_nocache</h1>

<p><a href="http://rahforum.biz/plugins/rah_nocache" rel=" rel="1"">Project page</a> | <a href="http://twitter.com/gocom" rel=" rel="1"">Twitter</a> | <a href="https://github.com/gocom/rah_nocache" rel=" rel="1"">GitHub</a> | <a href="http://forum.textpattern.com/viewtopic.php?id=30963" rel=" rel="1"">Support forum</a> | <a href="http://rahforum.biz/donate/rah_nocache" rel=" rel="1"">Donate</a></p>

<p>Rah_nocache is a simple plugin for <a href="http://www.textpattern.com" rel=" rel="1"">Textpattern <span class="caps">CMS</span></a> which tries to prevent client-side and proxy caching of admin-side editor forms by sending no-cache <span class="caps">HTTP</span> response headers. The plugin tries to remove all unwanted browser and proxy caching that could break admin-side panels functionality by redirecting requests to your local cache instead of showing the live pages. Usually important control panels and dynamically changing, informative pages shouldn&#8217;t be cached, but should contain the most up-to-date information.</p>

<p>Getting rah_nocache up and running is super simple. Just upload the plugin to your Textpattern installation and activate it. Zero configuration or extra steps required.</p>

<h2>Requirements</h2>

<p>Rah_nocache&#8217;s minimum requirements:</p>

	<ul>
		<li>Textpattern 4.5.0 or newer.</li>
	</ul>

<h2>Installing</h2>

<p>Rah_nocache&#8217;s installation follows the standard plugin installation steps.</p>

	<ol>
		<li>Download the plugin installation code.</li>
		<li>Copy and paste the installation code into the <em>Install plugin</em> box of your Textpattern Plugin pane.</li>
		<li>Run the automated setup.</li>
		<li>After the setup is done, activate the plugin. Done.</li>
	</ol>

<p>The plugin is now installed and activated, and is doing its job.</p>

<h2>Changelog</h2>

<h3>Version 0.4.0 &#8211; 2013/04/17</h3>

	<ul>
		<li>Improved: HTML5 validity.</li>
		<li>Improved: Prevents caching on Ajax, asynchronous and other script responses.</li>
		<li>Now requires Textpattern 4.5.0 or newer.</li>
	</ul>

<h3>Version 0.3 &#8211; 2012/07/12</h3>

	<ul>
		<li>Improved: Some minor clean up, and updated to new source structure.</li>
	</ul>

<h3>Version 0.2 &#8211; 2011/10/30</h3>

	<ul>
		<li>Added: License to the header comment block.</li>
		<li>Added: Some comments to the code.</li>
		<li>Updated: Help file.</li>
	</ul>

<h3>Version 0.1 &#8211; 2009/06/03</h3>

	<ul>
		<li>Initial release.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>