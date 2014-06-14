<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'aks_header';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.3.6';
$plugin['author'] = 'makss';
$plugin['author_uri'] = 'http://textpattern.org.ua/';
$plugin['description'] = 'Strip white spaces and GZIP compress pages on the fly. Set any page headers. Simple 301 redirect with conditions.';

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
if(@txpinterface == 'public'){ if(!in_array('aks_header_callback', ob_list_handlers() )){ ob_start('aks_header_callback'); }  }

function aks_header($atts) {
	$ext=array(			// extension => header
		'txt' => 'text/plain',
		'css' => 'text/css',
		'js' => 'application/x-javascript',
		'xml' => 'application/rss+xml'
	);
	global $aks_headers,$aks_strip_it,$aks_no_strip,$aks_gzip_it, $thisarticle;
	extract(lAtts(array(
		'file'  => '0',
		'nodebug'  => '0',
		'nocache'  => '0',
		'name'  => 'Content-Type',
		'value'  => '',
		'strip'  => '0',
		'gzip'  => '0',
		'nostrip' => 'pre,code,textarea,script,style'
	),$atts));
//	$aks_no_strip = preg_replace('/,/','|',$nostrip);
	$aks_no_strip = $nostrip;

	if($strip){ $aks_strip_it = 1; }
	if($gzip && !in_array('zlib output compression', ob_list_handlers()) ){ $aks_gzip_it=1;	}

	if($value){ $aks_headers[$name] = $value; }
	if($nodebug || $file){ $GLOBALS[production_status] = 'live'; }

	if($file && preg_match('/^.*\.(.*)$/',$thisarticle['url_title'],$mm) ){
		if($ext[$mm[1]]){ $aks_headers['Content-Type'] = $ext[$mm[1]];}
		if($mm[1] !='xml'){$aks_headers['Last-Modified'] =safe_strftime('rfc822',$thisarticle['modified']);}
	}
	if($nocache){
		$aks_headers["Cache-Control"] = "no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0";
		$aks_headers["Expires"] = "Sat, 9 Jun 1990 07:00:00 GMT";
		$aks_headers['Last-Modified'] =safe_strftime('rfc822');
		$aks_headers["Pragma"] = "no-cache";
	}

}

function aks_header_callback_preg($mm) {
	static $c=0; $c+=1;
	global $aks_header_block;

	$key="@@DONTOUCHMETAG$c@@";
	$aks_header_block[$key]=$mm[0];
	return $key;
}

function aks_header_callback($html) {
	global $aks_headers,$aks_strip_it,$aks_no_strip,$aks_gzip_it, $aks_header_block;
	$aks_header_block=array();
	if(isset($aks_headers) && is_array($aks_headers)) { foreach ($aks_headers as $name => $value){	header("$name: $value");} }

	if($aks_strip_it){
		$html=preg_replace ("/\015\012|\015|\012/", PHP_EOL, $html);
//		$html=preg_replace_callback("!<(".$aks_no_strip.")[^>]*>.*?</(".$aks_no_strip.")>!isx", 'aks_header_callback_preg', $html);

		$aa=preg_split('/[, ]+/', $aks_no_strip);
		foreach($aa as $a){
			$html=preg_replace_callback("!<(".$a.")[^>]*>.*?</(".$a.")>!isx", 'aks_header_callback_preg', $html);
		}
//		$html=preg_replace('/<!--([^\[]).*?-->/s', '', $html);

		$html=preg_replace('/((?<!\?>)'.PHP_EOL.')[\s]+/m', '\1', $html);
		$html=str_replace(">".PHP_EOL."<", '><', $html);
		$html=str_replace(PHP_EOL, ' ', $html);
		$html=preg_replace('/[ \t]+/',' ', $html);
		foreach( array_reverse($aks_header_block,true) as $key=>$block){ $html = str_replace($key,  $block, $html); }
	}

	// Check for buggy versions of Internet Explorer
	if ($aks_gzip_it && !strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
		preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {	$version = floatval($matches[1]);
		if ($version < 6 || ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'SV1')) ){ $aks_gzip_it=0; }
	}

	if($aks_gzip_it && isset ($_SERVER['HTTP_ACCEPT_ENCODING'])){
		$encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		if(function_exists('gzencode')&& preg_match('/gzip/i',$encoding)){
			header ('Content-Encoding: gzip');
			$html = gzencode($html);
		}elseif(function_exists('gzdeflate')&& preg_match('/deflate/i',$encoding)){
			header ('Content-Encoding: deflate');
			$html = gzdeflate($html);
		}
	}
//	$size=strlen($html);	if($size){ header ('Content-Length: ' . $size); }
	return $html;
}



function aks_301($atts) {
	extract(lAtts(array(
		'start'=>'',
		'url'=>'http://'.$_SERVER["SERVER_NAME"].'/',
		'ignore'=>'',
		'black'=>'cltreq\.asp|owssvr\.dll'
	),$atts));

	if($ignore){ $ignor=explode('|',$ignore);
		for ($i = 0; $i < count($ignor); $i++) {
			if(preg_match("|".$ignor[$i]."|i", $_SERVER["REQUEST_URI"]) ){	return; }
		}
	}

	if($black){ $bl=explode('|',$black);
		for ($i = 0; $i < count($bl); $i++) {
			if(preg_match("|".$bl[$i]."|i", $_SERVER["REQUEST_URI"]) ){ header("HTTP/1.0 404 Not Found"); exit; }
		}
	}

	if($start){  $st=explode('|',$start); $ur=explode('|',$url);
		for ($i = 0; $i < count($st); $i++) {
			if(preg_match("|^".$st[$i]."|i", $_SERVER["REQUEST_URI"]) ){	if(!$ur[$i]){ $ur[$i]=$ur[0]; }
				if(preg_match('/\(/',$st[$i])){         // check if regexp
					$ur[$i]=preg_replace("|^".$st[$i]."|i",$ur[$i],$_SERVER["REQUEST_URI"]);
				}
				header ('HTTP/1.1 301 Moved Permanently'); header ("Location: ".$ur[$i]); exit; 
			}
		}
	}
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
#aks h1 { color: #000000; font: 20px sans-serif;}
#aks h2 { border-bottom: 1px solid black; padding:10px 0 0; color: #000000; font: 15px sans-serif; }
#aks table {width: 100%; border:1px solid; border-color:#ddd #000 #000 #ddd;}
#aks th {
	background-color: #E3E3DB;
	border:1px solid;
	border-color:#ddd #999 #888 #ddd;
	padding: 10px 1px 10px 1px;
}
#aks td { background-color: #F2F2ED; padding: 5px 1px 5px 1px;}

#aks pre { padding: 5px;
line-height: 1.6em;
font-family: Verdana, Arial;
font-size: 100%;
border: 1px dashed #CCCCCC;
margin: 1.5em 0;
}

</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="aks">
<h1>aks_header</h1>

<p><a href="http://textpattern.org.ua/plugins/aks_header">aks_header homepage</a></p>
<p><i>This plugin based on <a href="http://textpattern.org/plugins/856/mg_setheader">mg_setheader</a>  by Mike Gravel.</i></p>

<h2>Features:</h2><ul><li>Set any page headers. It’s useful for create your custom feeds.</li><li>Strip white spaces on the fly.</li><li><span class="caps">GZIP</span> compress on the fly.</li><li>301 redirect non exist pages or directories to other place.</li></ul><p>.</p><p><code>&lt;txp:aks_header /&gt;</code></p><table><tbody><tr><th>attributes</th><th>default</th><th>sample</th><th>description</th></tr><tr><td>name</td><td><code>'Content-Type'</code></td><td><code>'Content-Type'</code></td><td>header</td></tr><tr><td>value</td><td><code>''</code></td><td><code>'application/rss+xml'</code></td><td>header</td></tr><tr><td>gzip</td><td><code>'0'</code></td><td><code>'1'</code></td><td><span class="caps"><span class="caps">GZIP</span></span> compressions</td></tr><tr><td>strip</td><td><code>'0'</code></td><td><code>'1'</code></td><td>Strip white spaces</td></tr><tr><td>nostrip</td><td><code>'pre,code,textarea,script,style'</code></td><td><code>'pre'</code></td><td>Not Strip tags</td></tr><tr><td>file</td><td><code>'0'</code></td><td><code>'1'</code></td><td>Auto set header per file extension</td></tr><tr><td>nodebug</td><td><code>'0'</code></td><td><code>'1'</code></td><td>Set production_status = ‘live’</td></tr><tr><td>nocache</td><td><code>'0'</code></td><td><code>'1'</code></td><td>Set <strong>no-cache</strong> header</td></tr></tbody></table><h2>Examples:</h2><p>Place on top your page template</p> <pre>&lt;txp:aks_header strip="1" gzip="1" /&gt;&lt;!DOCTYPE html  . . .

</pre> <p>or place on top your xml page template</p> <pre>&lt;txp:aks_header name="Content-Type" value="application/rss+xml" strip="1" gzip="1" /&gt;&lt;?xml version="1.0" . . .
</pre> <h2>Tests</h2><table><tbody><tr><th>Page</th><th>Original size</th><th>Strip white spaces</th><th><span class="caps"><span class="caps">GZIP</span></span></th><th>Strip+GZIP</th></tr><tr><td>http://textpattern.org.ua/plugins/aks_rss</td><td>16726 bytes</td><td>15385 bytes</td><td>4234 bytes</td><td>4039 bytes</td></tr><tr><td>Save space from original size</td><td>0 %</td><td>8 %</td><td>75 %</td><td>76 %</td></tr></tbody></table><h2>Set header per file extension. “File” attribute</h2><p><strong>Support file extensions:</strong></p> <pre>	'txt' =&gt; 'text/plain',
	'css' =&gt; 'text/css',
	'js' =&gt; 'application/x-javascript',
	'xml' =&gt; 'application/rss+xml'

</pre> <p>.</p><h1>aks_301 – Simple 301 redirect with conditions</h1><h2>Summary</h2><p>This tag useful for redirect non exist pages or directories to other place.<br> Default: to domain root.</p><h2>Tags</h2><p><strong style="text-align: left;">txp:aks_301 /&gt;</strong> — place this tag <strong>!!!ONLY!!!</strong> to start your page.</p><p>Sample my <strong>error_default</strong> page:</p> <pre>&lt;txp:aks_301 start="/categories/|/article/" /&gt;&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;

&lt;html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"&gt;
&lt;head&gt;
[skip...]
</pre> <h2>Attributes</h2><table><tbody><tr><th>Attribute</th><th>Default</th><th>Description</th></tr><tr><td><strong>start</strong></td><td><code>''</code></td><td>one or more conditions. Delimiter: <code>|</code></td></tr><tr><td><strong>url</strong></td><td><code>'http://yourdomain.com/'</code></td><td>one or more urls. Delimiter: <code>|</code> Default: your domail root</td></tr>
<tr>
	<td><strong>ignore</strong></td>
	<td><code>&#39;&#39;</code></td>
	<td>one or more conditions. Delimiter: <code>|</code>. Does nothing.</td>
</tr>
<tr>
	<td><strong>black</strong></td>
	<td> <code>&#39;cltreq.asp|owssvr.dll&#39;</code> </td>
	<td>one or more conditions. Delimiter: <code>|</code>. Return 404 header and exit.</td>
</tr>
</tbody></table><p>.</p><h2>Examples</h2><p><strong>error_default</strong> page:</p> <pre>&lt;txp:aks_301 start="/categories/" url="http://mydomain.com/somepage.html" /&gt;

all non exist pages in /categories/* - 301 redirect to http://mydomain.com/somepage.html
</pre> <pre>&lt;txp:aks_301 start="/categories/|/section1/" /&gt;
all non exist pages in /categories/* and /section1/* - 301 redirect to domain root
</pre> <pre>&lt;txp:aks_301 start="/" /&gt;
ALL non exist pages  - 301 redirect to domain root
</pre> <p><strong>default</strong> page:</p> <pre>&lt;txp:aks_301 start="/categories/" url="http://mydomain.com/somepage.html" /&gt;
all EXIST pages in /categories/* - 301 redirect to http://mydomain.com/somepage.html

</pre> <pre>&lt;txp:aks_301 start="/" url="http://otherdomain.com/" /&gt;
ALL EXIST pages  - 301 redirect to http://otherdomain.com/
</pre> <p>.</p><h2>HowTo use aks_header:</h2><ul><li><a href="http://textpattern.org.ua/howto/store-external-text-based-files-as-txp-articles">Store external text-based files as TxP articles</a></li><li><a href="http://textpattern.org.ua/howto/create-and-customize-rss-feeds">Create and customize your own <span class="caps">RSS</span> feeds</a></li></ul><p>.</p>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>