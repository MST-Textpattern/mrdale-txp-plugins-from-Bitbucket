<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'soo_plugin_pref';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2.2';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Plugin preference manager';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '1';

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

// event handler called by other plugins on plugin_lifecycle and plugin_prefs events
function soo_plugin_pref( $event, $step, $defaults ) {
	preg_match('/^(.+)\.(.+)/', $event, $match);
	list( , $type, $plugin) = $match;
	$message = $step ? soo_plugin_pref_query($plugin, $step, $defaults): '';
	if ( $type == 'plugin_prefs' )
		soo_plugin_pref_ui($plugin, $defaults, $message);
}

// user interface for preference setting (plugin_prefs events)
function soo_plugin_pref_ui( $plugin, $defaults, $message = '' ) {
	$cols = 2;
	$align_rm = ' style="text-align:right;vertical-align:middle"';
	$prefs = soo_plugin_pref_query($plugin, 'select');

	// install prefs if necessary
	if ( $defaults and ! $prefs ) {
		soo_plugin_pref_query($plugin, 'enabled', $defaults);
		$prefs = soo_plugin_pref_query($plugin, 'select');
	}

	pagetop(gTxt('edit_preferences') . " &#8250; $plugin", $message);
	echo n.t.'<div class="txp-layout-grid">'
		.n.'<div class="txp-layout-textbox">'
		.hed(gTxt('plugin') .' '. gTxt('edit_preferences') . ": $plugin", 1)
		.n.'<form method="post" name="soo_plugin_pref_form">'	
		.n. startTable('list','','txp-list soo_plugin_pref_form');

	foreach ( $prefs as $pref ) {
		extract($pref);
		$name = str_replace("$plugin.", '', $name);
		$input = $html == 'yesnoradio' ?
 			yesnoRadio($name, $val) :
 			fInput('text', $name, $val, 'edit', '', '', 20);
 		echo
 			n. tr(
 			n.t. tda($defaults[$name]['text'], $align_rm) .
 			n. td($input)
 		);
 	}

	echo
		n. sInput('update') .
		n. eInput("plugin_prefs.$plugin") .
		n. tr(n. tdcs(fInput('submit', 'soo_plugin_pref_update',
			gTxt('save'), 'publish'), $cols)) .
		endTable() . '</form>' .
		n;
}

// preference CRUD
function soo_plugin_pref_query( $plugin, $action, $defaults = array() ) {

	if ( $action == 'select' )
		return safe_rows(
			'name, val, html, position',
			'txp_prefs',
			"name like '$plugin.%' order by position asc"
		);

	elseif ( $action == 'update' ) {
		$post = doSlash(stripPost());
		$allowed = array_keys(soo_plugin_pref_vals($plugin));
		foreach ( $post as $name => $val )
			if ( in_array($name, $allowed) )
				if ( ! set_pref("$plugin.$name", $val) )
					$error = true;
		return empty($error) ? gTxt('preferences_saved') : '';
	}

	elseif ( $action == 'enabled' ) {
		$prefs = soo_plugin_pref_vals($plugin);
		$add = array_diff_key($defaults, $prefs);
		$remove = array_diff_key($prefs, $defaults);
		foreach ( $add as $name => $pref )
			set_pref(
				$plugin . '.' . $name,
				$pref['val'],
				'plugin_prefs',
				2,
				$pref['html']
			);
		foreach ( $remove as $name => $val )
			safe_delete('txp_prefs', "name = '$plugin.$name'");

		// update position values
		foreach ( array_keys($defaults) as $i => $name )
			safe_update('txp_prefs',
				"position = $i",
				"name = '$plugin.$name'");
	}

	elseif ( $action == 'deleted' )
		safe_delete('txp_prefs', "name like '$plugin.%'");
}

// get a plugin's prefs; return as associative name:value array
function soo_plugin_pref_vals( $plugin ) {
	$rs = soo_plugin_pref_query($plugin, 'select');
	foreach ( $rs as $r ) {
		extract ($r);
		$name = str_replace("$plugin.", '', $name);
		$out[$name] = $val;
	}
	return isset($out) ? $out : array();
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<style type="text/css">
div#sed_help pre {padding: 0.5em 1em; background: #eee; border: 1px dashed #ccc;}
div#sed_help h1, div#sed_help h2, div#sed_help h3, div#sed_help h3 code {font-family: sans-serif; font-weight: bold;}
div#sed_help h1, div#sed_help h2, div#sed_help h3 {margin-left: -1em;}
div#sed_help h2, div#sed_help h3 {margin-top: 2em;}
div#sed_help h1 {font-size: 2.4em;}
div#sed_help h2 {font-size: 1.8em;}
div#sed_help h3 {font-size: 1.4em;}
div#sed_help h4 {font-size: 1.2em;}
div#sed_help h5 {font-size: 1em;margin-left:1em;font-style:oblique;}
div#sed_help h6 {font-size: 1em;margin-left:2em;font-style:oblique;}
div#sed_help li {list-style-type: disc;}
div#sed_help li li {list-style-type: circle;}
div#sed_help li li li {list-style-type: square;}
div#sed_help li a code {font-weight: normal;}
div#sed_help li code:first-child {background: #ddd;padding:0 .3em;margin-left:-.3em;}
div#sed_help li li code:first-child {background:none;padding:0;margin-left:0;}
div#sed_help dfn {font-weight:bold;font-style:oblique;}
div#sed_help .required, div#sed_help .warning {color:red;}
div#sed_help .default {color:green;}
</style>
 <div id="sed_help">

	<h1>soo_plugin_pref</h1>

 <div id="toc">

	<h2>Contents</h2>

	<ul>
		<li><a href="#overview">Overview</a></li>
		<li><a href="#usage">Usage</a></li>
		<li><a href="#authors">Info for plugin authors</a>
	<ul>
		<li><a href="#config">Configuration</a></li>
		<li><a href="#limitations">Limitations</a></li>
	</ul></li>
		<li><a href="#history">History</a></li>
	</ul>

 </div>

	<h2 id="overview">Overview</h2>

	<p>This is an admin-side plugin for managing plugin preferences.</p>

	<p>For users, it provides a consistent admin interface to set preferences for <strong>soo_plugin_pref</strong>-compatible plugins, and automatically installs or removes those preferences from the database as appropriate. Of course, when you upgrade a plugin your existing preference values are retained.</p>

	<p>For plugin authors, it allows you to add preference settings to your plugins without having to create the user interface or the preference-handling functions.</p>

	<p>It uses the plugin prefs/lifecycle features introduced in Txp 4.2.0, so <span class="required">Txp 4.2.0 or greater is required</span>.</p>

	<h2 id="usage">Usage</h2>

	<p><strong>soo_plugin_pref</strong> only works with plugins that are designed to use it. (See <a href="http://forum.textpattern.com/viewtopic.php?id=31732">support forum thread</a> for a list of compatible plugins.) As of version 0.2.1, you can install plugins in any order. A compatible plugin&#8217;s preferences will be installed the first time you <a href="http://textbook.textpattern.net/wiki/index.php?title=Plugins#Panel_layout_.26_controls">activate it or click its <strong>Options</strong> link in the plugin list</a> while <strong>soo_plugin_pref</strong> is active. Its preferences will be removed from the database when you delete it while <strong>soo_plugin_pref</strong> is active.</p>

	<p>To set a plugin&#8217;s preferences, <a href="http://textbook.textpattern.net/wiki/index.php?title=Plugins#Panel_layout_.26_controls">click its <strong>Options</strong> link in the plugin list</a>.</p>

	<h2 id="authors">Info for plugin authors</h2>

	<p><strong>If you are not a plugin author, you can safely ignore the rest of this help text.</strong></p>

	<h3 id="config">Configuration</h3>

	<p>To configure a plugin to work with <strong>soo_plugin_pref</strong>:</p>

	<p>In the plugin manifest (i.e., the <code>$plugin</code> array at the top of the file):</p>

	<ul>
		<li>Ensure the plugin will load on the admin side (i.e., if <code>type</code> is 0, change it to 1)</li>
		<li>Set the plugin flags (using <a href="http://textpattern.googlecode.com/svn/development/4.x-plugin-template/">http://textpattern.googlecode.com/svn/development/4.x-plugin-template/</a> all you need to do is uncomment the <code>$plugin[&#39;flags&#39;]</code> line):</li>
	</ul>

	<p>In the plugin code section (substituting your plugin&#8217;s name for <code>abc_my_plugin</code>, and whatever name you choose for the callback function):</p>

<pre>@require_plugin(&#39;soo_plugin_pref&#39;);	// optional
if ( @txpinterface == &#39;admin&#39; )
{
	add_privs(&#39;plugin_prefs.abc_my_plugin&#39;,&#39;1,2&#39;);
	add_privs(&#39;plugin_lifecycle.abc_my_plugin&#39;,&#39;1,2&#39;);
	register_callback(&#39;abc_my_plugin_prefs&#39;, &#39;plugin_prefs.abc_my_plugin&#39;);
	register_callback(&#39;abc_my_plugin_prefs&#39;, &#39;plugin_lifecycle.abc_my_plugin&#39;);
}
</pre>

	<p>Define your plugin&#8217;s preference defaults. I like to do this with a function, with the option to output the multi-level array required by <code>soo_plugin_pref()</code> or a simple key:value array:</p>

<pre>function abc_my_plugin_defaults( $vals_only = false ) {
	$defaults = array(
		&#39;foo&#39; =&gt; array(
			&#39;val&#39;	=&gt; &#39;foo&#39;,
			&#39;html&#39;	=&gt; &#39;text_input&#39;,
			&#39;text&#39;	=&gt; &#39;Helpful description&#39;,
		),
		&#39;bar&#39; =&gt; array(
			&#39;val&#39;	=&gt; 1,
			&#39;html&#39;	=&gt; &#39;yesnoradio&#39;,
			&#39;text&#39;	=&gt; &#39;Equally helpful description&#39;,
		),
	);
	if ( $vals_only )
		foreach ( $defaults as $name =&gt; $arr )
			$defaults[$name] = $arr[&#39;val&#39;];
	return $defaults;
}
</pre>

	<p>For each preference, <code>val</code> is the default value, and <code>html</code> is the type of <span class="caps">HTML</span> input element used to display the preference in the admin interface; these go to the corresponding columns in <code>txp_prefs</code>. <code>text</code> is the label that will appear in the admin interface; it is not stored in the database.</p>

	<p>Preference names in the database are in the format &#8220;plugin_name.key&#8221;, where &#8220;plugin_name&#8221; is your plugin&#8217;s name, and &#8220;key&#8221; is the array key from the <code>$defaults</code> array.</p>

	<p>Each preference will be assigned a position value corresponding to its position in the defaults array, starting at 0. This determines its relative order in the admin interface.</p>

	<p>Other <code>txp_prefs</code> columns are set as follows:
	<ul>
		<li><code>event</code> is always set to &#8220;plugin_prefs&#8221;</li>
		<li><code>prefs_id</code> is always set to <code>1</code></li>
		<li><code>type</code> is always set to <code>2</code> (hidden from main Prefs page)</li>
	</ul></p>

	<p>Add the prefs callback:</p>

<pre>function abc_my_plugin_prefs( $event, $step ) {
	if ( function_exists(&#39;soo_plugin_pref&#39;) )
		soo_plugin_pref($event, $step, abc_my_plugin_defaults());
	else {
		// any custom preference handling goes here
	}
}
</pre>

	<p>If nothing else, you should display a message for a <code>plugin_prefs.abc_my_plugin</code> event if <strong>soo_plugin_pref</strong> is not installed. The version below checks the event type, then attempts to cobble together a meaningful message using <code>gTxt()</code> fragments:</p>

<pre>function abc_my_plugin_prefs( $event, $step ) {
	if ( function_exists(&#39;soo_plugin_pref&#39;) )
		return soo_plugin_pref($event, $step, abc_my_plugin_defaults());
	if ( substr($event, 0, 12) == &#39;plugin_prefs&#39; ) {
		$plugin = substr($event, 13);
		$message = &#39;&lt;p&gt;&lt;br /&gt;&lt;strong&gt;&#39; . gTxt(&#39;edit&#39;) . &quot; $plugin &quot; .
			gTxt(&#39;edit_preferences&#39;) . &#39;:&lt;/strong&gt;&lt;br /&gt;&#39; .
			gTxt(&#39;install_plugin&#39;) . &#39; &lt;a
			href=&quot;http://ipsedixit.net/txp/92/soo_plugin_pref&quot;&gt;soo_plugin_pref&lt;/a&gt;&lt;/p&gt;&#39;;
		pagetop(gTxt(&#39;edit_preferences&#39;) . &quot; &amp;#8250; $plugin&quot;, $message);
	}
}
</pre>

	<p>Finally, fetch the plugin preferences for use by the rest of the plugin code. I usually put preferences into a global array:</p>

<pre>global $abc_my_plugin;
$abc_my_plugin = function_exists(&#39;soo_plugin_pref_vals&#39;) ?
	array_merge(abc_my_plugin_defaults(true), soo_plugin_pref_vals(&#39;abc_my_plugin&#39;))
	: abc_my_plugin_defaults(true);
</pre>

	<p>Note the use of <code>soo_plugin_pref_vals()</code>, which returns your plugin&#8217;s preferences as an associative array.</p>

	<p>There are various ways you can code the above requirements, depending on your plugin&#8217;s exact needs. Some working examples:</p>

	<ul>
		<li><a href="http://ipsedixit.net/txp/101/soo_required_files-source-code">soo_required_files</a>, a public-side plugin</li>
		<li><a href="http://ipsedixit.net/txp/125/source-code">soo_editarea</a>, an admin-side plugin</li>
	</ul>

	<h3 id="limitations">Limitations</h3>

	<ul>
		<li>Currently the only allowed values for <code>html</code> are <code>text_input</code> and <code>yesnoradio</code>.</li>
		<li><strong>soo_plugin_pref</strong> only handles global preferences. If your plugin has a mix of global and per-user preferences, you will have to code all the handling of the per-user preferences.</li>
	</ul>

	<h2 id="history">Version History</h2>

	<h3>0.2.2 (9/28/2009)</h3>

	<p>Fixed bug in pref position re-indexing</p>

	<h3>0.2.1 (9/26/2009)</h3>

	<ul>
		<li>Pre-installing <strong>soo_plugin_pref</strong> is no longer required for automatic preference installation</li>
		<li>Each preference is now assigned a position value that determines relative order in the admin interface, and that corresponds to its position in the defaults array</li>
	</ul>

	<h3>0.2 (9/17/2009)</h3>

	<p>This version uses a different identifying scheme for preferences and hence is not compatible with the previous version. The plugin name is no longer stored in the <code>event</code> column, so there is no longer any restriction on plugin name length.</p>

	<h3>0.1 (9/5/2009)</h3>

	<p>Basic plugin preference management:</p>

	<ul>
		<li>Automatic installation/removal of preferences on plugin install/deletion</li>
		<li>Simple admin interface for setting preferences</li>
	</ul>

 </div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>