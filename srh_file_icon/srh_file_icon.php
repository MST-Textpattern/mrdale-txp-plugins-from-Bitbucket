<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'srh_file_icon';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Shaun Humphreys';
$plugin['author_uri'] = 'http://www.srhcommunications.com/misc/srh_file_icon_v0.1.txt';
$plugin['description'] = 'Displays either the file extension or an icon (if present) for a file';

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
function srh_file_icon($atts)
	{
		global $thisfile;

        extract(lAtts(array(
		  'displaytype' => 'text',
		  'imagetype' => 'png',
	    ), $atts));

        $ext = substr(strrchr($thisfile['filename'], "."), 1);
        $capext=strtoupper($ext);
		
        if ($displaytype == "image") {
                     return "<img src='/images/".$ext.".".$imagetype."' />";
            } else {
                     return $capext;
	    }
      }
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h3>srh_file_icon</h3>
<p><strong>srh_file_icon</strong> displays either the file extension (eg PDF, DOC, ZIP) or an icon image for a file. Icon images must be present in the /images directory for each file type required (eg pdf.png, doc.png -- filenames should be lowercase). The icon image type (eg png, gif) can also be specified.</p>

<p>Use: <em>&#60;txp:srh_file_icon /&#62;</em><br />
attributes:
<ul>
<li><strong>displaytype</strong> - 'text' or 'image' defaults to 'text'</li>
<li><strong>imagetype</strong> - the type of image file used for the icon, defaults to png</li>
</ul>

<p>You can get your file icons here:</p>

<p><a href="http://www.zap.org.au/documents/icons/file-icons/sample.html">http://www.zap.org.au/documents/icons/file-icons/sample.html</a></p>

<p>v0.1</p>
<p>This plugin was a hack from mrz_file_preview (which did a lot more and was written by someone who knows PHP).</p>
<p>If I keep reading my PHP book, I might get around to adding some other obvious attributes like <em>class, width & height, alt-text</em> etc.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>