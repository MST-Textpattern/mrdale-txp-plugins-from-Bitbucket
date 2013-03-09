<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'zem_nth';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'zem';
$plugin['author_uri'] = 'http://vigilant.tv/';
$plugin['description'] = 'Display content every n-th step';

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


	function zem_nth($atts, $thing) {
		global $zem_nth_count;
		$step = (empty($atts["step"]) ? 2 : $atts["step"]);
		# aside: can you believe PHP has no INT_MAX equivalent?
		$of = (empty($atts["of"]) ? 1000000 : $atts["of"]);

		# parse a list of the form "1, 2, 3-7, 8" into an array of integers
		$range = array();
		$r = explode(",", $step);
		foreach ($r as $i) {
			if (strpos($i, "-")) {
				list($low, $high) = explode("-", $i, 2);
				$range = array_merge($range, range($low, $high));
			}
			else {
				$range[] = (int)$i;
			}
		}

		# Keep separate counters for each zem_nth tag
		$id = md5($step. $thing . $of);

		if (!isset($zem_nth_count[$id]))
			$zem_nth_count[$id] = 0;

		$result = NULL;
		if (in_array($zem_nth_count[$id] + 1, $range))
			$result = parse($thing);

		$zem_nth_count[$id] = ($zem_nth_count[$id] +1) % $of;

		return $result;
	}



# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<p>&lt;txp:zem_nth&gt; conditionally displays content every "n-th" step.  Example uses:</p>

<ul>
<li>Alternate or cycle colours and styles</li>
<li>Include a break only at specific positions in a list</li>
<li>Provide "glue" between articles in a list, without an extraneous element at the beginning or end</li>
</ul>

Attributes:

<dl>
<dt>step</dt><dd>Step(s) on which to display.  May be a single integer, a comma-separated list, a range ("1-5"), or a combination ("1-3, 5, 8, 11-16").</dd>
<dt>of</dt><dd>Cycle period, as an integer.  Optional.  "step=1 of=3" will trigger on every third item (step 1, 4, 7, 10, etc).</dd>
</dl>

Examples for use in an article form:

<dl>
<dt>&lt;txp:zem_nth step=2 of=2&gt;&lt;hr /&gt;&lt;/txp:zem_nth&gt;</dt>
<dd>Show a HR every second article.</dd>
<dt>&lt;txp:zem_nth step=1 of=2&gt;&lt;div class="red" /&gt;&lt;/txp:zem_nth&gt;<br />
&lt;txp:zem_nth step=2 of=2&gt;&lt;div class="blue" /&gt;&lt;/txp:zem_nth&gt;<br />
</dt>
<dd>Alternate colours.</dd>
<dt>&lt;txp:zem_nth step=1 &gt;&lt;hr /&gt;&lt;/txp:zem_nth&gt;</dt>
<dd>Show a HR after the first article only.</dd>
<dt>&lt;txp:zem_nth step="1-9" &gt;&lt;hr /&gt;&lt;/txp:zem_nth&gt;</dt>
<dd>Show a HR after the first nine articles, and nothing after subsequent articles.</dd>
<dt>&lt;txp:zem_nth step=6 &gt;&lt;/tr&gt;&lt;tr&gt;&lt;/txp:zem_nth&gt;</dt>
<dd>Break a table into two columns at the 6th item.</dd>
</dl>
# --- END PLUGIN HELP ---
-->
<?php
}
?>