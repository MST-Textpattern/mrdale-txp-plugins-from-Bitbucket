<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'zem_contact_lang';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '4.0.3.6';
$plugin['author'] = 'TXP Community';
$plugin['author_uri'] = 'http://forum.textpattern.com/viewtopic.php?id=21144';
$plugin['description'] = 'Language plug-in for Zem Contact Reborn';

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

function zem_contact_gTxt($what, $var1 = '', $var2 = '')
{
	$lang = array(
		'checkbox'		=> 'Checkbox',
		'contact'		=> 'Contact',
		'email'			=> 'Email',
		'email_subject'		=> "$var1 > Inquiry",
		'email_thanks'		=> 'Thank you, your message has been sent.',
		'field_missing'		=> "Required field, &#8220;<strong>$var1</strong>&#8221;, is missing.",
		'form_expired'		=> 'The form has expired, please try again.',
		'form_used'		=> 'The form was already submitted, please fill out a new form.',
		'general_inquiry'	=> 'General inquiry',
		'invalid_email'		=> "&#8220;<strong>$var1</strong>&#8221; is not a valid email address.",
		'invalid_host'		=> "&#8220;<strong>$var1</strong>&#8221; is not a valid email host.",
		'invalid_utf8'		=> "&#8220;<strong>$var1</strong>&#8221; contains invalid UTF-8 characters.",
		'invalid_value'		=> "Invalid value for &#8220;<strong>$var1</strong>&#8221;, &#8220;<strong>$var2</strong>&#8221; is not one of the available options.",
		'mail_sorry'		=> 'Sorry, unable to send email.',
		'message'		=> 'Message',
		'min_warning'		=> "&#8220;<strong>$var1</strong>&#8221; must contain at least $var2 characters.",
		'max_warning'		=> "&#8220;<strong>$var1</strong>&#8221; must not contain more than $var2 characters.",
		'name'			=> 'Name',
		'option'		=> 'Option',
		'radio'			=> 'Radio',
		'recipient'		=> 'Recipient',
		'refresh'		=> 'Follow this link if the page does not refresh automatically.',
		'secret'		=> 'Secret',
		'send'			=> 'Send',
		'send_article'		=> 'Send article',
		'spam'			=> 'We do not accept spam, thank you!',
		'text'			=> 'Text',
		'to_missing'		=> '&#8220;<strong>To</strong>&#8221; email address is missing.',
		'version'		=> '4.0.3.6'
	);

	return $lang[$what];
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<div style="text-align:center;font-weight:bold;font-size:24px;text-decoration:underline;">Zem Contact Lang</div>

	<p>This is a separate language plug-in for use with Zem Contact Reborn. Both plug-ins need to be installed and activated in order to work properly.</p>

	<p>Separating the language in this way will enable non-english users to update the main plug-in without affecting their &#8220;localisation&#8221;.</p>

<div id="local" style="text-align:center;font-weight:bold;font-size:24px;text-decoration:underline;">Localisation</div>

	<p>Throughout the <code>zem_contact_reborn</code> plug-in, use has been made of a separate <code>gTxt</code> function which you can see in this plug-in&#8217;s code by clicking on the &#8220;Edit&#8221; button.</p>

	<p>If you are using the plug-in for a non-english site you can make use of this to localise text outputs for your preferred language.</p>

	<p>You should only edit text that appears after the <code>=&#62;</code> sign.</p>

	<p>If you have a dual-language site and the languages use separate &#8220;sections&#8221;, you can use the <txp:if_section> tag to enable different translations. An example of this usage is shown in the <strong>&#8220;forum thread&#8221;:http://forum.textpattern.com/viewtopic.php?id=13416</strong>. Our thanks to Els (doggiez) for this example.</p>

 <br />
# --- END PLUGIN HELP ---
-->
<?php
}
?>