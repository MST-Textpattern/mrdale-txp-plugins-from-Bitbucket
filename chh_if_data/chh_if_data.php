<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'chh_if_data';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.10';
$plugin['author'] = 'Coke Harrington';
$plugin['author_uri'] = 'http://www.cokesque.com/';
$plugin['description'] = 'Show a block of text only if enclosed Txp tags produce some output.';

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

function chh_if_data ($atts, $thing='') {
    $atts = lAtts(array('debug' => 0), $atts);

    $f = '/<txp:(\S+)\b(.*)(?:(?<!br )(\/))?'.chr(62).'(?(3)|(.+)<\/txp:\1>)/sU';
    $iftext = EvalElse($thing, true);
    $thresh = 1 + strlen(preg_replace($f, '', $iftext));

    $parsed = parse($iftext);
    $parsed_len = strlen($parsed);

    $empty = 'Data';
    if (strlen($parsed) < $thresh) { #or !preg_match('/\S/', $parsed)) {
        $parsed = parse(EvalElse($thing, false));
        $empty = 'No Data';
    }
    return $atts['debug']
           ? "<!-- $empty -- Threshhold: $thresh Length: $parsed_len -->" . $parsed
           : $parsed;
}

// Deprecated due to poor naming
function chh_unless_empty ($atts, $thing='') {
    return chh_if_data($atts, $thing);
}
function chh_if_not_empty ($atts, $thing='') {
    return chh_if_data($atts, $thing);
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h2>chh_if_data</h2>

	<p>This tag is used to determine whether some contained Txp tags output any data, allowing the conditional output of extra markup, like a header or a "no articles found" message.</p>

<pre>
&lt;txp:chh_if_data&gt;
    &lt;h3&gt;Here's an article list&lt;/h3&gt;
    &lt;txp:article_custom category="Nogo" /&gt;
&lt;txp:else /&gt;
    &lt;p&gt;article_custom had nothing to say.&lt;/p&gt;
&lt;/txp:chh_if_data&gt;</pre>
<pre>
&lt;txp:chh_if_data&gt;
    Static text will NEVER be shown.
    Drop a Txp tag in here!<br />
&lt;/txp:chh_if_data&gt;<br />
</pre>

	<p>If the enclosed chunk of text contains several Txp tags, <code>chh_if_data</code> will evaluate to true when <em>any</em> of the tags returns data.</p>

	<p>"Data," in this case, mean any output at all, including whitespace.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>