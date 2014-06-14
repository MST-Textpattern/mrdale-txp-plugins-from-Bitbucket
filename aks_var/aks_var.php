<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'aks_var';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2.2b';
$plugin['author'] = 'makss';
$plugin['author_uri'] = 'http://textpattern.org.ua/plugins/aks_var';
$plugin['description'] = 'Easy variables and calculations TxP';

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
function aks_var($atts, $thing='') { aks_evar($atts,$thing); return ""; }

function aks_evar($atts, $thing='') {
	extract(lAtts(array(
		'name'			=> '',
		'customfield'		=> '',
		'value'			=> '',

		'var'			=> '',		// any variables
		'avar'			=> '',		// apache variables  $_SERVER[$avar]
		'tvar'			=> '',		// thisarticle variables   $GLOBALS['thisarticle'][$tvar]

		'substr'		=> '',

		'regexp'		=> '',
		'to'		=> '',

		'calc'			=> '',
		'round'			=> '',

		'setname'     => ''
	), $atts));
//--------------------------- get $value
  if (!$value){
     if($name){$value = $GLOBALS['variable'][$name];}
     if($customfield){$customfield=strtolower($customfield); $value = $GLOBALS['thisarticle'][$customfield];}
  }else{
     $value=parse($value);
  }
  if ($var){ eval('$value='.$var.';'); }
  if ($avar){ eval('$value=$_SERVER["'.$avar.'"];'); }
  if ($tvar){ eval('$value=$GLOBALS["thisarticle"]["'.$tvar.'"];'); }

  if ($thing){ $value=parse($thing); }

//---------------------------- some calc with $value
  if ($substr){ list($s1,$s2)=split(':', $substr,2); if($s2){$s1.=",$s2";} eval("\$value=substr('$value',$s1);"); }   // warning, no UTF-8 support!
  if ($regexp){ $value=preg_replace($regexp, $to, $value); }
  if ($calc){ eval('$value='.$value.parse($calc).";"); }
  if ($round || $round =="0"){ $value=round($value,$round); }

//---------------------------- out $value
  if($setname){
     $GLOBALS['variable'][$setname] = $value;
  }else{
     if($name){$GLOBALS['variable'][$name] = $value;}
  }

  return $value;
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>aks_var – Easy variables and calculations TxP</h1>

	<p>Version 0.2 <a href="http://textpattern.org.ua/files/aks_var.txt">download link</a> / <a href="http://textpattern.org.ua/plugins/aks_var">homepage</a></p>

	<h2>Summary</h2>

	<p>aks_var operate with Txp variables and Custom Fields.</p>

	<h2>Tags</h2>

	<p><strong>&#60;txp:aks_var /&#62;</strong>	or <strong>&#60;txp:aks_var&#62;some&#8230;&#60;/txp:aks_var&#62;</strong><br />
<strong>&#60;txp:aks_evar /&#62;</strong>	same as &#60;txp:aks_var /&#62;, but it display result.  (evar &#8211; <strong>E</strong> cho <strong><span class="caps">VAR</span></strong>)</p>

	<h2>Attributes</h2>

	<table>
		<tr>
			<th>Attribute</th>
			<th>Sample</th>
			<th>Description</th>

		</tr>
		<tr>
			<td>// &#8212;&#8212; input $value</td>
		</tr>
		<tr>
			<td><strong>name</strong></td>
			<td><code>'my_var'</code></td>

			<td><span class="caps"><span class="caps">TXP</span></span> variable</td>
		</tr>
		<tr>
			<td><strong>customfield</strong></td>
			<td><code>'my_cust_field'</code></td>
			<td>Custom field</td>

		</tr>
		<tr>
			<td><strong>value</strong></td>
			<td><code>'12345 qwerty'</code></td>
			<td>Init value, text or digits</td>
		</tr>
		<tr>

			<td><strong>avar</strong></td>
			<td><code>'SERVER_NAME'</code></td>
			<td>apache variables, same as $_SERVER[$avar]</td>
		</tr>
		<tr>
			<td><strong>tvar</strong></td>
			<td><code>'url_title'</code></td>

			<td>thisarticle variables, same as $<span class="caps"><span class="caps">GLOBALS</span></span>[&#8216;thisarticle&#8217;][$tvar]</td>
		</tr>
		<tr>
			<td><strong>var</strong></td>
			<td><code>'$GLOBALS["prefs"]["spam_blacklists"]'</code></td>

			<td><span class="caps"><span class="caps">ANY</span></span> variables</td>
		</tr>
		<tr>
			<td>// &#8212;&#8212; some calc</td>
		</tr>
		<tr>

			<td><strong>calc</strong></td>
			<td><code>'*5-(32-2)/4'</code></td>
			<td>Any arithmetic operations   + &#8211; * /  &#8230;</td>
		</tr>
		<tr>
			<td><strong>substr</strong></td>

			<td><code>'2:7'</code></td>
			<td>Same as <span class="caps"><span class="caps">PHP</span></span> substr.  <strong><span class="caps"><span class="caps">WARNING</span></span></strong> &#8211; NO <span class="caps"><span class="caps">UTF</span></span>-8 support</td>
		</tr>

		<tr>
			<td><strong>regexp</strong></td>
			<td><code>'/^(some text)/i'</code></td>
			<td>preg_replace($regexp, $to, <em>$value</em>)</td>
		</tr>
		<tr>

			<td><strong>to</strong></td>
			<td><code>"It's $1"</code></td>
		</tr>
		<tr>
			<td>// &#8212;&#8212; output $value</td>
		</tr>

		<tr>
			<td><strong>setname</strong></td>
			<td><code>'my_var2'</code></td>
			<td>result to this <span class="caps"><span class="caps">TXP</span></span> variable</td>
		</tr>
	</table>

	<p>.</p>

	<h2>Examples dump:  :)</h2>

	<p>.<br />
<strong>Variables</strong></p>

	<table>

		<tr>
			<th>Example</th>
			<th>Result</th>
			<th>Comments</th>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_var name="my_var" value="123" /&#62;</code></td>

			<td>display none</td>
			<td>set <span class="caps"><span class="caps">TXP</span></span> variable  <strong>my_var</strong> =123. It&#8217;s equal <strong>&#60;txp:variable name=&#8220;my_var&#8221; value=&#8220;123&#8221; /&#62;</strong> tag.</td>

		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" /&#62;</code></td>
			<td>123</td>
			<td>only display <strong>my_var</strong>. It&#8217;s equal as <strong>&#60;txp:variable name=&#8220;my_var&#8221; /&#62;</strong> tag</td>

		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" setname="other_var" /&#62;</code></td>
			<td>123</td>
			<td><strong>other_var</strong> =my_var</td>
		</tr>

	</table>

	<p>.<br />
<strong>Custom Fields</strong></p>

	<table>
		<tr>
			<th>Example</th>
			<th>Comments</th>

		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar customfield="my_cust_field" /&#62;</code></td>
			<td>It&#8217;s equal as <strong>&#60;txp:custom_field name=&#8220;my_cust_field&#8221; /&#62;</strong></td>
		</tr>

		<tr>
			<td><code>&#60;txp:aks_evar customfield="my_cust_field" setname="my_var" /&#62;</code></td>
			<td><strong>my_var</strong> = my_cust_field    //get custom field to TxP variable</td>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar customfield="my_cust_field" substr="0:5" setname="my_var" /&#62;</code></td>

			<td><strong>my_var</strong> = substr(my_cust_field,0,5)</td>
		</tr>
	</table>

	<p>.<br />
<strong><span class="caps">CALC</span></strong></p>

	<table>

		<tr>
			<th>Example</th>
			<th>Result</th>
			<th>Comments</th>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" value="14" calc="/2" /&#62;</code></td>

			<td>7</td>
			<td><strong>my_var</strong> =14/2</td>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" calc="+1" /&#62;</code></td>
			<td>8</td>

			<td><strong>my_var</strong> =my_var+1</td>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" calc="*2+(24-4)/2" setname="other_var" /&#62;</code></td>
			<td>26</td>
			<td><strong>other_var</strong> =my_var*2+(24-4)/2   // my_var &#8211; no change</td>

		</tr>
	</table>

	<p>.<br />
<strong><span class="caps">SUBSTR</span></strong></p>

	<table>
		<tr>
			<th>Example</th>

			<th>Result</th>
			<th>Comments</th>
		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" value="123456789" substr="3" /&#62;</code></td>
			<td>456789</td>
			<td><strong>my_var</strong> =substr(&#8216;123456789&#8217;,3)     // <strong><span class="caps"><span class="caps">WARNING</span></span></strong> &#8211; NO <span class="caps"><span class="caps">UTF</span></span>-8 support</td>

		</tr>
		<tr>
			<td><code>&#60;txp:aks_evar name="my_var" substr="2:3" setname="other_var" /&#62;</code></td>
			<td>678</td>
			<td><strong>other_var</strong> =substr(my_var,2,3)</td>
		</tr>

	</table>

	<p>.</p>

	<h2><span class="caps">REGEXP</span></h2>

	<p>Change \r\n to &#60;br /&#62;</p>

<pre>&#60;txp:aks_evar regexp="/[\r\n]+/s" to="&#60;br /&#62;"&#62;

Some text
next line
next
etc
&#60;/txp:aks_evar&#62;
</pre>

	<p>Get subdomain name. Sample:   abc.domain.com =&#62; abc   and www.abc.domain.com =&#62; abc</p>

<pre>&#60;txp:aks_evar avar='SERVER_NAME' regexp="/^(www\.)?(.*?)\..*$/i" to="$2" /&#62;
</pre>

	<h2>2DO</h2>

	<ul>
		<li><del>preg_replace</del>  <strong>done</strong></li>
		<li><del>apache variables</del>  <strong>done</strong></li>
		<li><del>some other TxP variables</del>  <strong>done</strong></li>

		<li>split</li>
	</ul>

	<h2>Changelog</h2>

	<p><strong>0.2</strong></p>

	<ul>
		<li>add preg_replace</li>

		<li>add read apache, <span class="caps"><span class="caps">PHP</span></span>, TxP variables</li>
		<li>allow include txp tags:  &#60;txp:aks_var value=&#8221;&#60;txp:title /&#62;&#8221; /&#62; or &#60;txp:aks_var&#62;&#60;txp:title /&#62;&#60;/txp:aks_var&#62;</li>

	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>