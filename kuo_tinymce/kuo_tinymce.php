<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'kuo_tinymce_cdn';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Petri Ikonen';
$plugin['author_uri'] = 'http://kuopassa.net/';
$plugin['description'] = 'Fetching TinyMCE from CDN and activating it in the Write section.';

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
$plugin['type'] = '5';

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
if (@txpinterface == 'admin') {
register_callback('kuo_tinymce_head','admin_side','head_end');
}

function kuo_tinymce_head() {

if ($GLOBALS['event'] === 'article') {
echo '<script type="text/javascript" src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
	entity_encoding:"raw",
	selector:"textarea#body,textarea#excerpt",
	menubar :false,
	removed_menuitems:"newdocument",
	        toolbar_items_size: "small",
	            toolbar: "bold italic underline | formatselect | link image media | alignleft aligncenter alignright | bullist numlist outdent indent | searchreplace spellchecker | fullscreen code",
	plugins:["code,spellchecker,searchreplace,link,image,media"]
});

$(document).ready(function(){
$("select#markup-body option[value=0]").attr("selected","selected");
$("select#markup-excerpt option[value=0]").attr("selected","selected");
$("select#markup-body").css("border","1px solid #690");
$("select#markup-excerpt").css("border","1px solid #690");
$($("select#markup-body")).change(function(){
if ($(this).val() == 0) {
$("select#markup-body").css("border","1px solid #690");
}
else {
$("select#markup-body").css("border","1px solid #c00");
}
});
$($("select#markup-excerpt")).change(function(){
if ($(this).val() == 0) {
$("select#markup-excerpt").css("border","1px solid #690");
}
else {
$("select#markup-excerpt").css("border","1px solid #c00");
}
});

if ($("form.async")) {
   $("form.async").on("click", "input[type=submit]", function (evt) {
        tinyMCE.triggerSave();
   });
}

});
</script>';
}

}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style>#briefly { padding: 3%; font-size: 1.2em; -moz-box-shadow: 0 0 20px #ccc; -webkit-box-shadow: 0 0 20px #ccc; box-shadow: 0 0 20px #ccc; }#notice { padding: 1%; margin: 1% 0; background: #ffc; border: 1px dashed #fcc; } li span { color: #666; } .large code { font-size: 1.3em; padding: 1%; border: 1px solid #690; } hr { border: 3px solid #ccc; font: 1em Arial, Helvetica, sans-serif;}#links { display: table; height: 100%; width: 100%; clear: both; padding: 0; margin: 0 0 5px 0; list-style: none; }#links li { float: left; display: inline; } #links a { float: left; margin-right: 5px; background: #8AC6ED; color: #333; padding: 5px 10px; text-decoration: none; } #links li:last-child a { background: #9DBB78; }#links li:last-child a:hover,#links li:last-child a:focus,#links li:last-child a:active {background: #E2826B; }</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<ul id="links">
<li id="kuo_tinymce_cdn"><a href="#kuo_tinymce_cdn">kuo_tinymce_cdn</a></li>
<li id="external"><a href="http://kuopassa.net/txp/" rel="external">kuopassa.net/txp</a></li>
</ul>
<div id="briefly">
<h2>Briefly about this plugin</h2>
<p>Fetching TinyMCE 4.1 from a content delivery network (<code>tinymce.cachefly.net/4.1/tinymce.min.js</code>), inserting it to both of the <code>textarea</code>s (body and excerpt) at the Write tab.</p>
<p id="notice">This plugin is suitable for sites where ALL articles are to be handled with TinyMCE.</p>
If the plugin is activated, it's activated in all of the articles&hellip; No toggling on and off per article, at least not yet. Also:</p>
<ul>
<li>Please leave text untouched for articles (Textile off).</li>
<li>TinyMCE is loaded in English.</li>
<li>Use at your own risk.</li>
</ul>
<hr />
<h4>Special thanks to</h4>
<ul>
<li>photonomad for <code>tinyMCE.triggerSave();</code></li>
</ul>
</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>