<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_each';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Iterate over TXP, URL or smd_vars variables';

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
// -------------------------------------------------------------
// Iterate over arbitrary matching variables, either from TXP fields or on the URL line
// by Stef Dawson
function smd_each($atts, $thing='') {
	global $pretext, $thisarticle, $thisfile, $thislink, $variable;

	extract(lAtts(array(
		'type' => 'field',
		'include' => '',
		'exclude' => '',
		'match' => '',
		'matchwith' => 'name',
		'subset' => '0',
		'collate' => '',
		'form' => '',
		'delim' => ',',
		'paramdelim' => ':',
		'outdelim' => ',',
		'wraptag' => '',
		'break' => '',
		'class' => '',
		'var_prefix' => 'smd_',
		'debug' => '0',
	), $atts));

	// Lookups for allowable options
	$matchOpts = array('name', 'value');

	// Validate the options
	$thing = (empty($form)) ? $thing : fetch_form($form);
	$thing = (empty($thing)) ? '{'.$var_prefix.'var_value}' : $thing;

	$include = do_list($include, $delim);
	$include = ($include[0] == "") ? array() : $include;
	$exclude = do_list($exclude, $delim);
	$exclude = ($exclude[0] == "") ? array() : $exclude;
	$match = do_list($match, $delim);
	$match = ($match[0] == "") ? array() : $match;
	$matchwith = do_list($matchwith, $delim);
	foreach ($matchwith as $thismatch) {
		$matches[] = (in_array($thismatch,$matchOpts)) ? $thismatch : $matchOpts[0];
	}
	$matches = array_unique($matches);

	// Set up collated quoting if necessary
	if ($collate) {
		$quotes = array('SMDNONE');
		$collateOpts = do_list($collate, $delim);
		foreach ($collateOpts as $option) {
			$indexes = do_list($option, $paramdelim);
			$colType = array_shift($indexes);
			// Supplying 'quote' on its own will empty the relevant array, implying "ALL" fields are to be quoted
			switch ($colType) {
				case "quote":
					$quotes = $indexes;
					break;
			}
		}
	}

	// Make up an array of name => value pairs populated from the arrays of the chosen $types
	$types = array();
	$vars = array();
	$type = do_list($type, $delim);
	foreach ($type as $thistype) {
		switch ($thistype) {
			case "urlvar":
				if ($_POST) $vars = smd_each_grab($_POST, $vars, 'doSpecial, doStripTags', $delim, $subset);
				if ($_GET) $vars = smd_each_grab($_GET, $vars, 'doSpecial, doStripTags', $delim, $subset);
				break;
			case "svrvar":
				if ($_SERVER) $vars = smd_each_grab($_SERVER, $vars, 'doSpecial, doStripTags', $delim, $subset);
				break;
			case "cookie":
				if ($_COOKIE) $vars = smd_each_grab($_COOKIE, $vars, 'doSpecial, doStripTags', $delim, $subset);
				break;
			case "txpvar":
				if ($variable) $vars = smd_each_grab($variable, $vars, '', $delim, $subset);
				break;
			case "fixed":
				$newvars = array();
				foreach ($include as $key => $value) {
					$subs = do_list($value, $paramdelim);
					if (count($subs) > 1) {
						$newvar = array_shift($subs);
						$extravars[$newvar] = join($delim, $subs);
						// The new variable's values have been extracted and stored, so overwrite the array entry with just the variable name
						$include[$key] = $newvar;
					}
				}
				if ($extravars) $vars = smd_each_grab($extravars, $vars, 'doStripTags', $delim, $subset);
				break;
			case "field":
			case "default":
				if ($thisfile) $vars = smd_each_grab($thisfile, $vars, '', $delim, $subset);
				if ($thislink) $vars = smd_each_grab($thislink, $vars, '', $delim, $subset);
				if ($pretext) $vars = smd_each_grab($pretext, $vars, '', $delim, $subset);
				if ($thisarticle) $vars = smd_each_grab($thisarticle, $vars, 'doStripTags', $delim, $subset);
				break;
		}
	}

	if ($debug) {
		echo "++ VARIABLE POOL ++";
		dmp($vars);
	}

	// Filter the array if necessary into a smaller array of desired elements
	$filtervars = array();

	// Named variables are always copied across
	if ($include) {
		foreach ($include as $thisname) {
			if ($thisname != "") {
				$re = '/('.$thisname.'(_[0-9]*)?)/';
				$num = preg_match_all($re, join($delim,array_keys($vars)), $keynames);
				for ($ctr = 0; $ctr < $num; $ctr++) {
					$filtervars[$keynames[0][$ctr]] = $vars[$keynames[0][$ctr]];
				}
			}
		}
	}

	// Any names/values matching $match are also added
	if ($match) {
		foreach ($vars as $key => $val) {
			if (in_array("name", $matches) && (!array_key_exists($key, $filtervars))) {
				foreach ($match as $thismatch) {
					if ($thismatch && strpos($key, $thismatch) !== false) {
						if ($debug) {
							dmp("ADDING ".$key." (from name) ");
						}
						$filtervars[$key] = $val;
						break;
					}
				}
			}
			// No point checking the values if the variable has already been added to the filtered list
			if (in_array("value", $matches) && (!array_key_exists($key, $filtervars))) {
				foreach ($match as $thismatch) {
					if ($thismatch && strpos($val, $thismatch) !== false) {
						if ($debug) {
							dmp ("ADDING ".$key." (from value) ");
						}
						$filtervars[$key] = $val;
						break;
					}
				}
			}
		}
	}

	// Remove any excluded variables
	if ($exclude) {
		foreach ($exclude as $thisname) {
			if ($thisname != "") {
				$re = '/('.$thisname.'(_[0-9]*)?)/';
				$num = preg_match_all($re, join($delim,array_keys($filtervars)), $keynames);
				for ($ctr = 0; $ctr < $num; $ctr++) {
					if ($debug) {
						dmp("EXCLUDING ".$keynames[0][$ctr]);
					}
					unset ($filtervars[$keynames[0][$ctr]]);
				}
			}
		}
	}

	// If no filters are specified
	if (!$include && !$exclude && !$match) {
		$filtervars = $vars;
	}

	if ($debug && ($filtervars != $vars)) {
		echo "++ FILTERED VARS ++";
		dmp($filtervars);
	}

	// Throw the name => value pairs at the form
	$out = array();
	$collations = array();
	$ctr = 1;
	$totalvars = count($filtervars);

	foreach ($filtervars as $key => $val) {
		$replacements = array(
			'{'.$var_prefix.'var_name}' => $key,
			'{'.$var_prefix.'var_value}' => $val,
			'{'.$var_prefix.'var_counter}' => $ctr,
			'{'.$var_prefix.'var_total}' => $totalvars,
		);

		// Solos are items in the output form that require details from a specific row. Useful only in collation
		// mode, they are added to the replacements array on an as-needed basis to save space/time
		$soloRE = '/\{'.$var_prefix.'([a-z0-9_]+)#'.$ctr.'\}/';
		$numSolos = preg_match_all($soloRE, $thing, $solos);
		for ($soloCtr = 0; $soloCtr < $numSolos; $soloCtr++) {
			$fieldname = '{'.$var_prefix.$solos[1][$soloCtr].'#'.$ctr.'}';
			$grabfield = '{'.$var_prefix.$solos[1][$soloCtr].'}';
			$replacements[$fieldname] = $replacements[$grabfield];
		}

		if ($debug) {
			echo "++ REPLACEMENT #$ctr ++";
			dmp($replacements);
		}
		// In collate mode the form is only parsed at the end: build a collosal multi-dimension array of all items here
		if ($collate) {
			foreach ($replacements as $defName => $defVal) {
				$collations[$defName][] = $defVal;
			}
		} else {
			$out[] = parse(strtr($thing, $replacements));
		}

		$ctr++;
	}

	// Handle quoting of collations
	if ($collate) {
		foreach ($collations as $item => $list) {
			// Quote the lists if required
			$list = (empty($quotes) || in_array($item, $quotes)) ? doArray($list, 'doQuote') : $list;
			$collations[$item] = implode($outdelim, $list);
		}
		if ($debug) {
			echo "++ COLLATIONS ++";
			dmp($collations);
		}
		$out[] = parse(strtr($thing, $collations));
	}

	return doWrap($out, $wraptag, $break, $class);
}

// -------------------------------------------------------------
// Take an array of name-value pairs, process and combine them with what is already in $out.
// Handle sub-lists as well
function smd_each_grab($item, $out='', $proc='', $dlm=',', $sub=0) {
	$out = ($out) ? $out : array();
	$proc = do_list($proc, $dlm);
	$proc = ($proc[0] == "") ? array() : $proc;

	while (list($key, $val) = each($item)) {
		$val = (is_array($val)) ? join($dlm, $val) : $val;
		if ($sub) {
			if ($sub == 1) {
				// Add the complete element before attempting to split it
				foreach($proc as $op) {
					$val = $op($val);
				}
				$out[$key] = $val;
			}
			$vals = do_list($val, $dlm);
			$ctr = 1;
			foreach ($vals as $subval) {
				foreach($proc as $op) {
					$subval = $op($subval);
				}
				$out[$key.'_'.$ctr++] = $subval;
			}
		} else {
			foreach($proc as $op) {
				$val = $op($val);
			}
			$out[$key] = $val;
		}
	}
	return $out;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
#smd_help { line-height:1.5 ;}
#smd_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #FFFFCC;}
#smd_help code.block { font-weight:normal; border:1px dotted #999; background-color: #f0e68c; display:block; margin:10px 10px 20px; padding:10px; }
#smd_help a:link, #smd_help a:visited { color: #00c; text-decoration: none; border-bottom: 1px solid blue; padding-bottom:1px;}
#smd_help a:hover, #smd_help a:active { color: blue; text-decoration: none; border-bottom: 2px solid blue; padding-bottom:1px;}
#smd_help h1 { color: #369; font: 20px Georgia, sans-serif; margin: 0; text-align: center; }
#smd_help h2 { border-bottom: 1px solid black; padding:10px 0 0; color: #369; font: 17px Georgia, sans-serif; }
#smd_help h3 { color: #079; font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0;text-transform: uppercase; text-decoration:underline;}
#smd_help h4 { font: bold 11px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0 ;text-transform: uppercase; }
#smd_help .atnm { font-weight:bold; color:#33d; }
#smd_help .mand { background:#eee; border:1px dotted #999; }
#smd_help table {width:90%; text-align:center; padding-bottom:1em; border:1px solid #666;}
#smd_help td, #smd_help th {border:1px solid #999; padding:.5em 0;}
#smd_help ul { list-style-type:square; }
#smd_help li { margin:5px 20px 5px 30px; }
#smd_help .break { margin-top:5px; }
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="smd_help">

	<h1 id="top">smd_each</h1>

	<h2 id="features">Features</h2>

	<ul>
		<li>Iterate over any variables you can get your hands on. These can be anything from the current article (custom field, article_image, etc), any <span class="caps">URL</span>/SERVER variable or cookie (subject to normal escaping rules) or any <code>&lt;txp:variable /&gt;</code></li>
		<li>Include or exclude particular variables, or only choose variables that match particular text</li>
		<li>Iterate over subsets of data contained in any field</li>
		<li>Choose to process each variable one by one with a form, or collect them together into a delimited list to be processed only once by the form</li>
		<li>Provides free headaches if you don&#8217;t keep your wits about you</li>
	</ul>

	<h2 id="author">Author</h2>

	<p><a href="http://stefdawson.com/commentForm">Stef Dawson</a>. For other software by me, or to make a donation, see the <a href="http://stefdawson.com/sw">software page</a>.</p>

	<h2 id="install">Installation / Uninstallation</h2>

	<p>Download the plugin from either <a href="http://textpattern.org/plugins/986/smd_each">textpattern.org</a>, or the software page above, paste the code into the <span class="caps">TXP</span> Admin -&gt; Plugins pane, install and enable the plugin. Visit the <a href="http://forum.textpattern.com/viewtopic.php?id=27464">forum thread</a> for more info or to report a bug/feature request.</p>

	<p>To remove the plugin, simply delete it from the Admin-&gt;Plugins tab.</p>

	<h2 id="usage">Usage</h2>

	<p>For some ideas on usage scenarios, see the <a href="#examples">examples</a>.</p>

	<h3 class="tag " id="smd_each">smd_each</h3>

	<p class="tag-summary">Place one or more smd_each tags in any article, page or form, supply any of the following options to configure it and use a form (or container) to define what to do with each matching variable.</p>

	<p>In a nutshell, it grabs every array from the places you specify, allows you to filter the array with matches or specific items, then assigns each successive name/value pair to two replacement tags that you can use in your form to do stuff. You could simply display the values or populate new tables with them, plug values into queries, create smd_vars, test the values with smd_if, you name it.</p>

	<h4 id="attributes">Attributes</h4>

	<ul>
		<li><span class="atnm">type</span> : where to look for variables. They can be combined into a comma-separated list if you wish to search more than one place at once. Options are:
	<ul>
		<li><code>field</code> : a <span class="caps">TXP</span> article field (the default)</li>
		<li><code>urlvar</code> : the <span class="caps">URL</span>, e.g. name1=val&amp;name2=val&amp;&#8230;</li>
		<li><code>svrvar</code> : the server environment variables</li>
		<li><code>cookie</code> : any user cookies</li>
		<li><code>txpvar</code> : a value from any <code>&lt;txp:variable /&gt;</code> tag</li>
		<li><code>fixed</code> : specify your own list of variables to inject into the mix (via <code>include</code>)</li>
	</ul></li>
		<li><span class="atnm">include</span> : base list of variable names you definitely want to be returned in the result. If you are using <code>fixed</code> as one of the <code>type</code>s you can add your own variables here, delimited by <code>paramdelim</code>. For example <code>include=&quot;keywords, my_var:north:south:east:west&quot;</code> would include the <code>keywords</code> article field and create <code>my_var</code>, giving it a value of &#8220;north, south, east, west&#8221;. Note that if you are using a custom field, it is likely to have been converted to all lower case by Textpattern, so you should use an all lower case name here</li>
		<li><span class="atnm">exclude</span> : base list of variable names you definitely do not want to be returned in the result</li>
		<li><span class="atnm">match</span> : list of text strings to search for to refine the returned variables. By default this will match against <em>every</em> variable in every location you specified in <code>type</code>. It is automatically wild so will match portions of a variable</li>
		<li><span class="atnm">matchwith</span> : defaults to <code>name</code> which looks only at the variable&#8217;s name for a match. Can be set to <code>value</code> to look at its value or <code>name, value</code> to look in both for matches</li>
		<li><span class="atnm">subset</span> : if you think your variables are going to contain lists themselves, specify <code>subset=&quot;1&quot;</code> to have the plugin add each pseudo-variable to the array. For example, if you article_image field contained <code>14, 6, 17, 3, 9</code> you would get five more variables called <code>article_image_1</code> (value:14), <code>article_image_2</code> (value: 6), and so on.<div class="break">Note that you will also get the full article_image (and all other matching non-subset vars) included. If you wish to <em>only</em> see variables that contain sublists of data, use <code>subset=&quot;2&quot;</code></div></li>
		<li><span class="atnm">var_prefix</span> : if you are nesting smd_each tags you&#8217;ll find that any inner <code>{smd_var_value}</code> replacements will take on the values of the outer tag. For this reason you can specify a prefix that will be used in all replacements. Thus if you set <code>var_prefix=&quot;opt_&quot;</code> on your inner smd_each tag, you would use <code>{opt_var_value}</code> and {opt_var_name} in the inner container/form. Default: <code>smd_</code></li>
		<li><span class="atnm">form</span> : the <span class="caps">TXP</span> form to execute for every matching variable. If not specified, the container will be used. If there&#8217;s no container, a default form with just the {smd_var_value} is used</li>
		<li><span class="atnm">collate</span> : prevent the <code>form</code> being executed for every variable. Instead, collect the variable names internally and then process the entire list by the form once only, after all variables have been read. See <a href="#collate">collate mode</a></li>
		<li><span class="atnm">delim</span> : the delimiter to use for specifying plugin options. Defaults to comma (,)</li>
		<li><span class="atnm">paramdelim</span> : the delimiter to use for specifying inter-value plugin options (for example in collate mode). Defaults to colon (:)</li>
		<li><span class="atnm">outdelim</span> : the delimiter to use to separate each variable displayed in collate mode</li>
		<li><span class="atnm">wraptag</span> : the (X)HTML tag to wrap the form in, e.g. <code>wraptag=&quot;ul&quot;</code></li>
		<li><span class="atnm">break</span> : the (X)HTML tag to wrap each call to the form in, e.g. <code>break=&quot;li&quot;</code></li>
		<li><span class="atnm">class</span> : the <span class="caps">CSS</span> class name to give to the wraptag</li>
	</ul>

	<h4 class="atts " id="reps">Replacement tags</h4>

	<p>For every matching variable, you can use the following replacement tags in your form:</p>

	<ul>
		<li><span class="atnm">{smd_var_name}</span> : the variable name</li>
		<li><span class="atnm">{smd_var_value}</span> : the variable&#8217;s value</li>
		<li><span class="atnm">{smd_var_counter}</span> : the variable&#8217;s position in the list (1, 2, 3&#8230;)</li>
		<li><span class="atnm">{smd_var_total}</span> : the total number of matching variables being iterated</li>
	</ul>

	<p>(Note that each replacement tag will have whatever <code>var_prefix</code> you have designated: the default is <code>smd_</code>).</p>

	<p>These can be used for whatever devious means you see fit. e.g.</p>

	<ul>
		<li><code>&lt;txp:article_custom id=&quot;{smd_var_value}&quot; /&gt;</code> will display the given article for each matching ID.</li>
		<li><code>&lt;txp:article keywords=&quot;{smd_var_value}&quot; /&gt;</code> in collate mode, might display the given articles that have keywords matching every user-submitted search term</li>
		<li><code>&lt;txp:smd_if field=&quot;{smd_var_counter}&quot; operator=&quot;eq&quot; value=&quot;{smd_var_total}&quot;&gt;This is the last item&lt;/txp:smd_if&gt;</code></li>
	</ul>

	<h3 id="collate">Collate mode</h3>

	<p>Instead of parsing each variable with the form/container, you may elect to internally &#8216;collect&#8217; all matching variables and output them in one big list via the form. Thus, the form is only called once at the end. It may seem useless but it can be of value for creating lists of things from each matching variable.</p>

	<p>Starting simply, <code>collate=&quot;1&quot;</code> switches collation mode on. If the plugin matched 3 variables from your article and you were to use the replacement tags in your form like this:</p>

<pre class="block"><code class="block">The matching vars: {smd_var_name} = {smd_var_value}
</code></pre>

	<p>you might get this:</p>

<pre class="block"><code class="block">The matching vars: section,category1,category2 = article,news,politics
</code></pre>

	<p>Compare that with the regular mode, which outputs:</p>

<pre class="block"><code class="block">The matching vars: section = article
The matching vars: category1 = news
The matching vars: category2 = politics
</code></pre>

	<p>Sometimes it&#8217;s useful to be able to put quotes around each item; you can tell collate mode to do that:</p>

	<p><code>collate=&quot;quote:{smd_var_value}&quot;</code></p>

	<p>You would then get:</p>

<pre class="block"><code class="block">The matching vars: section,category1,category2 = &#39;article&#39;,&#39;news&#39;,&#39;politics&#39;
</code></pre>

	<p>The delimiter (a comma in this case) can be overridden with the <code>outdelim</code> attribute. You can quote more than one thing at once by specifying the items as a delimited list:</p>

	<p><code>collate=&quot;quote:{smd_var_name}:{smd_var_value}&quot;</code></p>

	<p>but there&#8217;s not much point because there are only two replacement tags and you can thus use the shortcut <code>collate=&quot;quote&quot;</code> to quote them all. The delimiter used between items (the colon) can be overriden with the <code>paramdelim</code> attribute.</p>

	<p>The second special feature of collation mode is that you do not have to always output the entire list. You can grab individual entities from within the internal array by using the &#8216;#&#8217; notation in your form:</p>

<pre class="block"><code class="block">{smd_var_value} might output &#39;article&#39;,&#39;news&#39;,&#39;politics&#39; (as before)
{smd_var_value#1} would only output article
{smd_var_name#3} would only output category2
</code></pre>

	<p>Note that when pulling out individual entries they <strong>do not</strong> get quotes added to them, regardless of whether you used <code>quote</code> or not. This is because it is a single item so you can easily put the quotes in the form itself (viz: <code>&quot;{smd_var_name#2}&quot;</code>)</p>

	<h2 id="examples">Examples</h2>

	<h3 id="eg1">Example 1</h3>

	<p>Display each article image from a comma-separated list of IDs in the Article Image field.</p>

<pre class="block"><code class="block">&lt;txp:smd_each include=&quot;article_image&quot; subset=&quot;2&quot;&gt;
  &lt;txp:image id=&quot;{smd_var_value}&quot; /&gt;
&lt;/txp:smd_each&gt;
</code></pre>

	<h3 id="eg2">Example 2</h3>

	<p>Display each article image from a comma-separated list of IDs in the Article Image field, and also allow IDs to be given in the <span class="caps">URL</span> line via the variable name <code>my_image_list</code> :</p>

<pre class="block"><code class="block">&lt;txp:smd_each type=&quot;field, urlvar&quot;
     include=&quot;article_image, my_image_list&quot;
     subset=&quot;2&quot; form=&quot;varout&quot; var_prefix=&quot;me_&quot; /&gt;
</code></pre>

	<p>And in form <code>varout</code>:</p>

<pre class="block"><code class="block">&lt;txp:image id=&quot;{me_var_value}&quot; /&gt;
</code></pre>

	<h3 id="eg3">Example 3</h3>

	<p>This is simply to highlight the differences between the various matching modes. Let&#8217;s take a simple article:</p>

	<ul>
		<li>section: animals</li>
		<li>title (url_title) : The lion (the-lion)</li>
		<li>category1: mammal</li>
		<li>category2: dangerous</li>
		<li>article_image: 25, 28, 12</li>
		<li>keywords: big, cat, mane, fur, teeth, roar, chomp, ouch</li>
		<li>custom1 (&#8220;origin&#8221;): africa</li>
	</ul>

	<p>There are a whole host of other variables (switch debug=&#8220;1&#8221; on to see them all for your chosen <code>type</code>s) but we&#8217;ll concentrate on these for now to keep things simple.</p>

	<table>
		<tr>
			<th>tag options </th>
			<th>vars returned </th>
			<th>remarks </th>
		</tr>
		<tr>
			<td> match=&#8220;cat&#8221; </td>
			<td> category1, category2 </td>
			<td> default is to match name only </td>
		</tr>
		<tr>
			<td> match=&#8220;cat&#8221; matchwith=&#8220;name, value&#8221; </td>
			<td> category1, category2, keywords, id_keywords </td>
			<td> now checks value as well </td>
		</tr>
		<tr>
			<td> match=&#8220;cat&#8221; include=&#8220;article_image&#8221; </td>
			<td> category1, category2, article_image </td>
			<td> includes are always selected </td>
		</tr>
		<tr>
			<td> match=&#8220;cat&#8221; include=&#8220;article_image, origin&#8221; subset=&#8220;1&#8221; </td>
			<td> category1, category2, origin, article_image, article_image_1, article_image_2, article_image_3 </td>
			<td> looked &#8220;inside&#8221; each var to find any lists </td>
		</tr>
		<tr>
			<td> match=&#8220;cat&#8221; include=&#8220;article_image, origin&#8221; subset=&#8220;2&#8221; </td>
			<td> article_image_1, article_image_2, article_image_3 </td>
			<td> return <em>only</em> data sets that contain lists and removes the aggregate (base) entry </td>
		</tr>
		<tr>
			<td> match=&#8220;cat, article_image&#8221; matchwith=&#8220;name,value&#8221; subset=&#8220;2&#8221; </td>
			<td> article_image_1, article_image_2, article_image_3, keywords, id_keywords </td>
			<td> checks both name and value for each match term, and only shows items that have lists in them (if <code>keywords</code> held just one item &#8220;cat&#8221; it would <em>not</em> be displayed) </td>
		</tr>
	</table>

	<p>Note that if you are using this tag in an article, the &#8216;body&#8217; may match because it contains the smd_each tag which contains the very information you are matching! In this case, adding <code>exclude=&quot;body&quot;</code> will remove it from the results.</p>

	<h3 id="eg4">Example 4</h3>

	<p>From a page template, allow visitors to display any number of articles.</p>

<pre class="block"><code class="block">&lt;form name=&quot;show_arts&quot; action=&quot;/my/results/page&quot;&gt;
  &lt;txp:article_custom form=&quot;list_checks&quot;
       category=&quot;animals&quot; limit=&quot;999&quot; /&gt;
  &lt;input type=&quot;submit&quot; /&gt;
&lt;/form&gt;
</code></pre>

	<p>Your form <code>list_checks</code> simply displays the article title and a checkbox:</p>

<pre class="block"><code class="block">&lt;li&gt;
&lt;txp:title /&gt;&lt;input type=&quot;checkbox&quot;
     name=&quot;article_lists[]&quot;
     value=&quot;&lt;txp:article_id /&gt;&quot; /&gt;
&lt;/li&gt;
</code></pre>

	<p>When the user submits the form we can use smd_each to find which checkboxes have been clicked and show the relevant articles:</p>

<pre class="block"><code class="block">&lt;txp:smd_each type=&quot;urlvar&quot;
     match=&quot;article_lists&quot; subset=&quot;2&quot;&gt;
  &lt;txp:article_custom id=&quot;{smd_var_value}&quot; /&gt;
&lt;/txp:smd_each&gt;
</code></pre>

	<p>Alternatively, if you are using a copy of <span class="caps">TXP</span> that supports lists in article_custom&#8217;s <code>id</code> attribute you can drop the <code>subset</code>.</p>

	<h2 id="changelog">Changelog</h2>

	<ul>
		<li>12 Jun 08 | 0.1 | Initial release</li>
		<li>22 Jun 08 | 0.11 | Added type=fixed (thanks mrdale)</li>
		<li>04 Apr 09 | 0.12 | Fixed <code>subset=&quot;2&quot;</code> bug with single entries (thanks jeremywood) ; added <code>{var_counter}</code> and <code>{var_total}</code> (thanks mrdale)</li>
		<li>30 Aug 09 | 0.2 | Added <code>var_prefix</code> attribute and set it to <code>smd_</code> by default</li>
	</ul>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>