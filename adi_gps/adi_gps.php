<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'adi_gps';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Adi Gilbert';
$plugin['author_uri'] = 'http://www.greatoceanmedia.com.au/';
$plugin['description'] = 'Extract GET & POST variables';

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

/*
	adi_gps - Extract GET & POST variables

	Written by Adi Gilbert

	Released under the GNU General Public License

	Version history:
	0.1		- initial release

*/

function adi_gps($atts) {

	extract(lAtts(array(
		'name'			=> '',	// a single GET/POST var
		'new'			=> '',	// new variable name
		'txpvar'		=> '1',	// set as a TXP variable or not
		'post'			=> '0',	// extract POST vars or not
		'quiet'			=> '0',	// return value or not
		'global'		=> '1', // make vars global or not
		'escape'		=> '',	// escape HTML entities or not
		'decode'		=> '0',	// perform a urldecode or not
		'list'			=> '0', // list variable names and values
		'debug'			=> '0',
	), $atts));

	if ($name == '') { // no single var specified, so all vars extracted
		$quiet = 1; // force quiet mode
		$new = ''; // disable var rename
		$get_list = array_keys($_GET);
		$post ?
			$post_list = array_keys($_POST) : $post_list = array();
		$name_list = array_merge($get_list,$post_list);
	}
	else
		$name_list[] = $name;

	$debug_list = 'adi_gps variables';
	foreach ($name_list as $index => $name) {
		$value = gps($name); // extract value
		if ($decode) // convert %chars
			$value = urldecode($value);
		if ($escape == 'html') // encode chars (e.g. ampersand becomes &amp;)
			$value = htmlentities($value);
		if ($new) // rename var (single var mode only)
			$name = $new;
		if ($txpvar) // create TXP variable
			parse('<txp:variable name="'.$name.'" value="'.$value.'"/>');
		if ($global) // make the variable global
			$GLOBALS[$name] = $value;
		if ($list)
			$debug_list .= ":$name=$value";
	}

	if ($debug) {
		echo 'SUPPLIED ATTRIBUTES:<br/>';
		dmp($atts);
		echo "GET VARS:<br/>";
		dmp($_GET);
		echo "POST VARS:<br/>";
		dmp($_POST);
		echo "ADI_GPS VAR LIST:<br/>";
		dmp($name_list);
		echo "ALL TXP VARS:<br/>";
		dmp($GLOBALS['variable']);
	}

	if ($list)
		return $debug_list.'<br/>';
	else
		if ($quiet) // don't report value
			return '';
		else // return value
			return $value;
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1><strong>adi_gps</strong> &#8211; Extract <span class="caps">GET</span> &amp; <span class="caps">POST</span> variables</h1>

	<p>This plugin extracts <span class="caps">GET</span>/POST variables from the <span class="caps">URL</span> and assigns them to <span class="caps">TXP</span> variables.</p>

	<h2><strong>Attributes</strong></h2>

	<p><code>name="var name"</code></p>

	<p>- a single <span class="caps">GET</span>/POST variable to extract. Default = &#8220;&#8221; (extract all <span class="caps">GET</span> variables). See <code>post</code> attribute for extracting <span class="caps">POST</span> variables.</p>

	<p><code>new="var name"</code></p>

	<p>- rename the variable. Default = &#8220;&#8221; (no rename). Only applies when retrieving a single <span class="caps">GET</span>/POST variable.</p>

	<p><code>txpvar="boolean"</code></p>

	<p>- duplicate the <span class="caps">GET</span>/POST variable as a <span class="caps">TXP</span> variable of the same name. Default = &#8220;1&#8221; (Yes). Requires Textpattern 4.0.7. Switch off if using versions prior to this.</p>

	<p><code>post="boolean"</code></p>

	<p>- extract <span class="caps">POST</span> variables as well. Default = &#8220;0&#8221; (No). Only applies in &#8220;extract all&#8221; mode.</p>

	<p><code>quiet="boolean"</code></p>

	<p>- don&#8217;t display variable value. Default = &#8220;0&#8221; (display variable value). Only applies when retrieving a single <span class="caps">GET</span>/POST variable. Quiet mode automatically switched on with &#8220;extract all&#8221; mode.</p>

	<p><code>global="boolean"</code></p>

	<p>- make the variable <i>global</i> in scope. Default = &#8220;1&#8221; (Yes).</p>

	<p><code>escape="mode"</code></p>

	<p>- escape <span class="caps">HTML</span> entities in variable value. Default = &#8220;&#8221; (no escape).  Use <code>escape="html"</code> to switch on.</p>

	<p><code>decode="boolean"</code></p>

	<p>- decodes any remaining %## encoding in the variable values. Default = &#8220;0&#8221; (No). Not normally required but may be needed if <span class="caps">URL</span> has been processed by htaccess.</p>

	<p><code>list="boolean"</code></p>

	<p>- outputs a list of the extracted variables &amp; their values. Handy for debugging. Default = &#8220;0&#8221; (No list).</p>

	<h2><strong>Examples</strong></h2>

	<p><code>&lt;txp:adi_gps /&gt;</code></p>

	<p>- extract all <span class="caps">GET</span> variables and duplicate them as <span class="caps">TXP</span> variables.</p>

	<p><code>&lt;txp:adi_gps post="1" /&gt;</code></p>

	<p>- extract all <span class="caps">GET</span> <i>and</i> <span class="caps">POST</span> variables and duplicate them as <span class="caps">TXP</span> variables.</p>

	<p><code>&lt;txp:adi_gps name="myvar1" /&gt;</code></p>

	<p>- extract a variable called <strong>myvar1</strong> from <span class="caps">URL</span>, create a <span class="caps">TXP</span> variable of the same name and display it&#8217;s value.</p>

	<p><code>&lt;txp:adi_gps name="myvar2" txpvar="0" /&gt;</code></p>

	<p>- extract a variable called <strong>myvar2</strong> from <span class="caps">URL</span> and display it&#8217;s value. No <span class="caps">TXP</span> variable is created (suitable for use with Textpattern versions prior to 4.0.7).</p>

	<p><code>&lt;txp:adi_gps name="myvar3" quiet="1" /&gt;</code></p>

	<p>- extract a variable called <strong>myvar3</strong> from <span class="caps">URL</span>, create a <span class="caps">TXP</span> variable of the same name but don&#8217;t display it&#8217;s value.</p>

	<h2><strong>Using the <span class="caps">URL</span> variables in pages or forms</strong></h2>

	<p>Because, by default, <strong>adi_gps</strong> duplicates the extracted <span class="caps">URL</span> variables as <span class="caps">TXP</span> variables you can use all the standard <span class="caps">TXP</span> variable tags to process them:</p>

	<ul>
		<li><code>&lt;txp:variable /&gt;</code></li>
		<li><code>&lt;txp:if_variable /&gt;</code></li>
	</ul>

	<p>For example:</p>

<pre><code>&lt;txp:adi_gps name="myvar" quiet="1" /&gt;
&lt;txp:if_variable name="myvar" value="some_value"&gt;
... do something different ...
&lt;txp:else /&gt;
... the same old thing ...
&lt;/txp:if_variable&gt;
</code></pre>

	<h2><strong>Using <span class="caps">URL</span> variables in articles</strong></h2>

	<p>You can also use <code>&lt;txp:adi_gps /&gt;</code> within articles.  For example:</p>

	<blockquote>
		<p><i>The value of myvar is <code>&lt;txp:adi_gps name="myvar" /&gt;</code> &#8230;</i></p>
	</blockquote>

	<p>or if you&#8217;ve already extracted the variable elsewhere:</p>

	<blockquote>
		<p><i>The value of myvar is <code>&lt;txp:variable name="myvar" /&gt;</code> &#8230;</i></p>
	</blockquote>

	<p>No escaping from Textile is required (e.g. using <code>notextile.</code> or <code>== ... ==</code>).</p>

	<h2><strong>Accessing the extracted <span class="caps">GET</span>/POST variables in <span class="caps">PHP</span></strong></h2>

	<p>By default, <strong>adi_gps</strong> makes extracted <span class="caps">URL</span> variables available in the global scope. For example if $myvar1, $myvar2 &amp; $myvar3 have been extracted previously they can be accessed in <span class="caps">PHP</span> snippets as follows:</p>

<pre><code>&lt;txp:php&gt;
global $myvar1,$myvar2,$myvar3;
echo "$myvar1 $myvar2 $myvar3";
&lt;/txp:php&gt;
</code></pre>
# --- END PLUGIN HELP ---
-->
<?php
}
?>