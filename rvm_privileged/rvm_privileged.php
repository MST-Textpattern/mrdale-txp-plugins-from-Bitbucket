<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rvm_privileged';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.4';
$plugin['author'] = 'Ruud van Melick';
$plugin['author_uri'] = 'http://vanmelick.com/';
$plugin['description'] = 'Use TXP admin user privileges on public side';

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

function rvm_privileged($atts)
{
  extract(lAtts(array(
    'name' => '',
    'level'  => ''
  ), $atts));

  if (!rvm_is_privileged($name, $level))
  {
    ob_clean();
    txp_die('Forbidden', 403);
  }
}


function rvm_if_privileged($atts, $thing = '')
{
  extract(lAtts(array(
    'name' => '',
    'level' => ''
  ), $atts));

  return parse(EvalElse($thing, rvm_is_privileged($name, $level)));
}


function rvm_is_privileged($name, $level)
{
  static $ili = 0;

  if ($ili === 0) $ili = is_logged_in();

  if (!$ili) return FALSE;

  $isname  = ($name and in_array($ili['name'], do_list($name)));
  $islevel = ($level and in_array($ili['privs'], do_list($level)));

  if ($name)
  {
    if ($level)
    {
      return ($isname and $islevel);
    }
    else
    {
      return $isname;
    }
  }
  elseif($level)
  {
    return $islevel;
  }
  else
  {
    return TRUE;
  }
}


function rvm_privileged_user($atts)
{
  extract(lAtts(array(
    'type' => 'RealName'
  ), $atts));

  static $ili = 0;

  if ($ili === 0) $ili = is_logged_in();

  $types = array('RealName', 'name', 'email', 'privilege', 'level');

  if (!$ili or !in_array($type, $types)) return;

  $levels = array('none', 'publisher', 'managing editor', 'copy editor', 'staff writer', 'freelancer', 'designer');

  switch ($type)
  {
    case 'privilege': $out = $levels[$ili['privs']]; break;
    case 'level'    : $out = $ili['privs']; break;
    default         : $out = $ili[$type];
  }

  return htmlspecialchars($out);
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<style>
   li code {font-weight: bold;}
   pre {padding: 0.5em 1em; background: #eee; border: 1px dashed #ccc;}
   h1, h2, h3, h3 code {font-family: sans-serif; font-weight: bold;}
   h1, h2, h3 {margin-left: -1em;}
   h2, h3 {margin-top: 2em;}
   h1 {font-size: 3em;}
   h2 {font-size: 2em;}
   h3 {font-size: 1.5em;}
   li a code {font-weight: normal;}
   .required, .warning {color:red;}
 </style>

	<h1>Privileged</h1>

	<p>Requires <span class="caps">TXP</span> 4.0.6 or higher.</p>

	<p>This plugin provides the following tags:
	<ul>
		<li><a href="#rvm_privileged"> <code>&lt;txp:rvm_privileged /&gt;</code> </a></li>
		<li><a href="#rvm_if_privileged"> <code>&lt;txp:rvm_if_privileged /&gt;</code> </a></li>
		<li><a href="#rvm_privileged_user"> <code>&lt;txp:rvm_privileged_user /&gt;</code> </a></li>
	</ul></p>

	<h3 id="rvm_privileged"><code>&lt;txp:rvm_privileged /&gt;</code></h3>

	<p>When a non-privileged user visits a page containing this tag, a <span class="caps">HTTP</span> 403 Forbidden error is shown.</p>

	<p>The error page can be customized in two ways:
	<ol>
		<li>modify the <code>error_default</code> template page.</li>
		<li>create a new template page called <code>error_403</code> (recommended!)</li>
	</ol></p>

	<h4>Attributes</h4>

	<ul>
		<li><code>name=&quot;comma-separated values&quot;</code> <br />
List of allowed <span class="caps">TXP</span> login names. Default is to accept any logged in user.</li>
		<li><code>level=&quot;comma-separated values&quot;</code> <br />
List of allowed Privilege levels<sup class="footnote"><a href="#fn1318659219513a6caa55548">1</a></sup>. Default is to accept logged in users regardless of privilege level.</li>
	</ul>

	<h4>Example</h4>

	<p>Allow access to user &#8216;ruud&#8217; and anyone with privilege levels 2,3 and 5:</p>

<pre><code>&lt;txp:rvm_privileged name=&quot;ruud&quot; level=&quot;2,3,5&quot; /&gt;
</code></pre>

	<h3 id="rvm_if_privileged"><code>&lt;txp:rvm_if_privileged /&gt;</code></h3>

	<p>Can be used to display different content to privileged users.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>name=&quot;comma-separated values&quot;</code> <br />
List of allowed <span class="caps">TXP</span> login names. Default is to accept any logged in user.</li>
		<li><code>level=&quot;comma-separated values&quot;</code> <br />
List of allowed Privilege levels<sup class="footnote"><a href="#fn1318659219513a6caa55548">1</a></sup>. Default is to accept logged in users regardless of privilege level.</li>
	</ul>

	<h4>Example</h4>

<pre><code>&lt;txp:rvm_if_privileged&gt;
  You are logged in!
&lt;txp:else /&gt;
  You are NOT logged in!
&lt;/txp:rvm_if_privileged&gt;
</code></pre>

	<h3 id="rvm_privileged_user"><code>&lt;txp:rvm_privileged_user /&gt;</code></h3>

	<p>Display various properties of the logged in user.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>type=&quot;name|email|privilege|level&quot;</code> <br />
 Select one of: login name (name), email address (email), privilege (privilege) or numeric privilege (level). By default the RealName of the logged in user is shown.</li>
	</ul>

	<h4>Examples</h4>

	<p>Show the real name of the logged in user:</p>

<pre><code>&lt;txp:rvm_privileged_user /&gt;
</code></pre>

	<p>Show the email address of the logged in user:</p>

<pre><code>&lt;txp:rvm_privileged_user type=&quot;email&quot;&gt;
</code></pre>

	<h3>Footnotes</h3>

	<p id="fn1318659219513a6caa55548" class="footnote"><sup>1</sup> Privilege levels:
	<ol>
		<li>Publisher</li>
		<li>Managing Editor</li>
		<li>Copy Editor</li>
		<li>Staff Writer</li>
		<li>Freelancer</li>
		<li>Designer</li>
	</ol></p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>