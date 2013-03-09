<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rah_replace';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.4';
$plugin['author'] = 'Jukka Svahn';
$plugin['author_uri'] = 'http://rahforum.biz';
$plugin['description'] = 'Search and replace';

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


/**
 * Rah_replace plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2009-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_replace
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	function rah_replace($atts, $thing) {
		
		extract(lAtts(array(
			'from' => '',
			'to' => '',
			'delimiter' => ','
		), $atts));
		
		if($delimiter !== '') {
			$from = explode($delimiter, $from);
			
			if(strpos($to, $delimiter) !== FALSE) {
				$to = explode($delimiter, $to);
			}
		}
		
		return str_replace($from, $to, parse($thing));
	}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>rah_replace</h1>

<p>A tiny <a href="http://www.textpattern.com" rel=" rel="nofollow"">Textpattern</a> plugin that returns contained content with all searched occurrences replaced with given replacements. This ideally works in the same manner as <span class="caps">PHP</span>&#8217;s <a href="http://php.net/manual/en/function.str-replace.php" rel=" rel="nofollow"">str_replace</a> function, but with a Textpattern&#8217;s <span class="caps">XML</span> tag.</p>

<h2>Basics</h2>

<p>The plugin, rah_replace, introduces a new container tag to Textpattern&#8217;s arsenal. The tag returns wrapped content with all found occurrences replaced with given replacements. A tag supports multiple searched occurrences and replacements.</p>

<pre><code>&lt;txp:rah_replace from=&quot;value1, value2, ...&quot; to=&quot;value1, value2, ...&quot;&gt;
	Searched content
&lt;/txp:rah_replace&gt;
</code></pre>

<h2>Attributes</h2>

<p>The tag is a container, <code>&lt;txp:rah_replace&gt; ...contained statement... &lt;/txp:rah_replace&gt;</code>, and attributes for it follow.</p>

<p><strong>from</strong><br />
Strings that will be searched and replaced with <code>to</code> attribute&#8217;s values. Separate multiple values with a comma (or <code>delimiter</code> if changed).<br />
Default: <code>from=&quot;&quot;</code> Example: <code>&quot;dog,cat,house&quot;</code></p>

<p><strong>to</strong><br />
Replacements that will be used to replace <code>from</code> attribute&#8217;s values. Comma (or <code>delimiter</code>) separated if multiple.<br />
Default: <code>to=&quot;&quot;</code> Example: <code>&quot;ship,home,hat&quot;</code></p>

<p><strong>delimiter</strong><br />
Sets the delimiter used in <code>from</code> and <code>to</code> to separate multiple values. Default is a comma.<br />
Default: <code>delimiter=&quot;,&quot;</code> Example: <code>&quot;|&quot;</code></p>

<h2>Examples</h2>

<h3>Replaces a <em>dog</em> with a <em>cat</em></h3>

<pre><code>&lt;txp:rah_replace from=&quot;dog&quot; to=&quot;cat&quot;&gt;
	My favorite animal is a dog.
&lt;/txp:rah_replace&gt;
</code></pre>

<p>Returns: <code>My favorite animal is a cat.</code></p>

<h3>Replace multiple needles with different replacements</h3>

<pre><code>&lt;txp:rah_replace from=&quot;house,dog,Mike&quot; to=&quot;boat,friend,wife&quot;&gt;
	I live in a house with my dog and Mike.
&lt;/txp:rah_replace&gt;
</code></pre>

<p>Returns: <code>I live in a boat with my friend and wife.</code></p>

<h3>Replace multiple needles with a one replacement</h3>

<pre><code>&lt;txp:rah_replace from=&quot;Mike,dad&quot; to=&quot;I&quot;&gt;
	I remember when dad and Mike did go to fishing.
&lt;/txp:rah_replace&gt;
</code></pre>

<p>Returns: <code>I remember when I and I did go to fishing.</code></p>

<h3>Using a different delimiter</h3>

<p>By default any comma is treated as a delimiter and can not be used as actual value. To use a comma (<code>,</code>) as a needle or a replacement, you would have to change the delimiter to something else. Like for instance to a vertical bar:</p>

<pre><code>&lt;txp:rah_replace from=&quot;.|,&quot; to=&quot;!&quot; delimiter=&quot;|&quot;&gt;
	A, B, C.
&lt;/txp:rah_replace&gt;
</code></pre>

<p>Returns: <code>A! B! C!</code></p>

<h2>Changelog</h2>

<h3>Version 0.4 &#8211; 2012/07/12</h3>

	<ul>
		<li>Performance optimization.</li>
	</ul>

<h3>Version 0.3 &#8211; 2011/04/22</h3>

	<ul>
		<li>Performance optimization.</li>
	</ul>

<h3>Version 0.2 &#8211; 2009/04/16</h3>

	<ul>
		<li>Added a new attribute: <code>delimiter</code>.</li>
	</ul>

<h3>Version 0.1 &#8211; 2009/04/16</h3>

	<ul>
		<li>Initial release.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>