<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_query';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.50';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Generic database access via SQL';

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
function smd_query($atts, $thing='') {
	global $pretext, $smd_query_pginfo, $thispage, $thisarticle, $thisimage, $thisfile, $thislink;

	extract(lAtts(array(
		'column' => '',
		'table' => '',
		'where' => '',
		'query' => '',
		'form' => '',
		'pageform' => '',
		'pagevar' => 'pg',
		'pagepos' => 'below',
		'colsform' => '',
		'escape' => '',
		'strictfields' => '0',
		'preparse' => '0',
		'populate' => '', // one of article, image, file, or link
		'urlfilter' => '',
		'urlreplace' => '',
		'defaults' => '',
		'delim' => ',',
		'paramdelim' => ':',
		'silent' => '0',
		'mode' => 'auto', // auto chooses one of input (INSERT/UPDATE) or output (QUERY)
		'count' => 'up',
		'limit' => 0,
		'offset' => 0,
		'hashsize' => '6:5',
		'label' => '',
		'labeltag' => '',
		'wraptag' => '',
		'break' => '',
		'class' => '',
		'breakclass' => '',
		'html_id' => '',
		'debug' => '0',
	),$atts));

	// Grab the form or embedded $thing
	$falsePart = EvalElse($thing, 0);

	$thing = ($form) ? fetch_form($form) . (($falsePart) ? '<txp:else />' . $falsePart : '') : (($thing) ? $thing : '');
	$colsform = (empty($colsform)) ? '' : fetch_form($colsform);
	$pagebit = array();
	if ($pageform) {
		$pagePosAllowed = array("below", "above");
		$paging = 1;
		$pageform = fetch_form($pageform);
		$pagepos = str_replace('smd_', '', $pagepos);
		$pagepos = do_list($pagepos, $delim);
		foreach ($pagepos as $pageitem) {
			$pagebit[] = (in_array($pageitem, $pagePosAllowed)) ? $pageitem : $pagePosAllowed[0];
		}
	}

	// Make a unique hash value for this instance so the queries can be paged independently
	$uniq = '';
	$md5 = md5($column.$table.$where.$query.$defaults);
	list($hashLen, $hashSkip) = explode(':', $hashsize);
	for ($idx = 0, $cnt = 0; $cnt < $hashLen; $cnt++, $idx = (($idx+$hashSkip) % strlen($md5))) {
		$uniq .= $md5[$idx];
	}

	$pagevar = ($pagevar == 'SMD_QUERY_UNIQUE_ID') ? $uniq : $pagevar;
	$urlfilter = (!empty($urlfilter)) ? do_list($urlfilter, $delim) : '';
	$urlreplace = (!empty($urlreplace)) ? do_list($urlreplace, $delim) : '';
	if ($debug > 0) {
		echo "++ URL FILTERS ++";
		dmp($urlfilter);
		dmp($urlreplace);
	}

	// Process any defaults
	$spc = ($strictfields) ? 0 : 1;
	$defaults = do_list($defaults, $delim);
	$dflts = array();
	foreach ($defaults as $item) {
		$item = do_list($item, $paramdelim);
		if ($item[0] == '') continue;
		if (count($item) == 2) {
			$dflts[$item[0]] = smd_query_parse($item[1], array(''), array(''), array(''), $spc);
		}
	}

	if ($debug > 0) {
		echo "++ DEFAULTS ++";
		dmp($dflts);
	}

	// Get a list of fields to escape
	$escapes = do_list($escape, $delim);
	foreach ($escapes as $idx => $val) {
		if ($val == '') {
			unset($escapes[$idx]);
		}
	}

	$rs = array();
	$out = array();
	$colout = $finalout = array();
	$pageout = '';

	// query overrides column/table/where
	if ($query) {
		$query = smd_query_parse($query, $dflts, $urlfilter, $urlreplace, $spc);
		$mode = ($mode == 'auto') ? ((preg_match('/(select|show)/i', $query)) ? 'output' : 'input') : $mode;
		if ($mode == 'input') {
			$rs = ($silent) ? @safe_query($query, $debug) : safe_query($query, $debug);
		} else {
			$rs = ($silent) ? @getRows($query, $debug) : getRows($query, $debug);
		}
	} else {
		if ($column && $table) {
			// TODO: Perhaps doSlash() these?
			$column = smd_query_parse($column, $dflts, $urlfilter, $urlreplace, $spc);
			$table = smd_query_parse($table, $dflts, $urlfilter, $urlreplace, $spc);
			$where = smd_query_parse($where, $dflts, $urlfilter, $urlreplace, $spc);
			$where = ($where) ? $where : "1=1";
			$mode = 'output';
			$rs = ($silent) ? @safe_rows($column, $table, $where, $debug) : safe_rows($column, $table, $where, $debug);
		} else {
			trigger_error("You must specify at least 1 'column' and a 'table'.");
		}
	}

	if ($mode == 'output') {
		$numrows = count($rs);
		$truePart = EvalElse($thing, 1);

		if ($rs) {
			if ($debug > 1) {
				echo "++ QUERY RESULT SET ++";
				dmp($numrows . " ROWS");
				dmp($rs);
			}

			if ($limit > 0) {
				$safepage = $thispage;
				$total = $numrows - $offset;
				$numPages = ceil($total/$limit);
				$pg = (!gps($pagevar)) ? 1 : gps($pagevar);
				$pgoffset = $offset + (($pg - 1) * $limit);
				// send paging info to txp:newer and txp:older
				$pageout['pg'] = $pg;
				$pageout['numPages'] = $numPages;
				$pageout['s'] = $pretext['s'];
				$pageout['c'] = $pretext['c'];
				$pageout['grand_total'] = $numrows;
				$pageout['total'] = $total;
				$thispage = $pageout;
			} else {
				$pgoffset = $offset;
			}

			$rs = array_slice($rs, $pgoffset, (($limit==0) ? 99999 : $limit));
			$pagerows = count($rs);

			$replacements = $repagements = $colreplacements = array();
			$page_rowcnt = ($count=="up") ? 0 : $pagerows-1;
			$qry_rowcnt = ($count=="up") ? $pgoffset-$offset : $numrows-$pgoffset-1;
			$first_row = $qry_rowcnt + 1;

			// Preserve any external context
			switch ($populate) {
				case 'article':
					$safe = ($thisarticle) ? $thisarticle : array();
					break;
				case 'image':
					$safe = ($thisimage) ? $thisimage : array();
					break;
				case 'file':
					$safe = ($thisfile) ? $thisfile : array();
					break;
				case 'link':
					$safe = ($thislink) ? $thislink : array();
					break;
			}

			foreach ($rs as $row) {
				foreach ($row as $colid => $val) {
					if ($page_rowcnt == 0 && $colsform) {
						$colreplacements['{'.$colid.'}'] = $colid;
					}
					// Construct the replacement arrays
					$replacements['{'.$colid.'}'] = (in_array($colid, $escapes) ? htmlspecialchars($val, ENT_QUOTES) : $val);
					if ($page_rowcnt == (($count=="up") ? $pagerows-1 : 0) && $pageform && $limit>0) {
						$prevpg = (($pg-1) > 0) ? $pg-1 : '';
						$nextpg = (($pg+1) <= $numPages) ? $pg+1 : '';
						$repagements['{smd_allrows}'] = $total;
						$repagements['{smd_pages}'] = $numPages;
						$repagements['{smd_prevpage}'] = $prevpg;
						$repagements['{smd_thispage}'] = $pg;
						$repagements['{smd_nextpage}'] = $nextpg;
						$repagements['{smd_row_start}'] = $first_row;
						$repagements['{smd_row_end}'] = $qry_rowcnt + 1;
						$repagements['{smd_rows_prev}'] = (($prevpg) ? $limit : 0);
						$repagements['{smd_rows_next}'] = (($nextpg) ? (($qry_rowcnt+$limit+1) > $total ? $total-$qry_rowcnt-1 : $limit) : 0);
						$repagements['{smd_query_unique_id}'] = $uniq;
						$smd_query_pginfo = $repagements;
					}
				}
				$replacements['{smd_allrows}'] = (($limit>0) ? $total : $numrows-$pgoffset);
				$replacements['{smd_rows}'] = $pagerows;
				$replacements['{smd_pages}'] = (($limit>0) ? $numPages : 1);
				$replacements['{smd_thispage}'] = (($limit>0) ? $pg : 1);
				$replacements['{smd_thisindex}'] = $page_rowcnt;
				$replacements['{smd_thisrow}'] = $page_rowcnt + 1;
				$replacements['{smd_cursorindex}'] = $qry_rowcnt;
				$replacements['{smd_cursor}'] = $qry_rowcnt + 1;
				if ($debug > 0) {
					echo "++ REPLACEMENTS ++";
					dmp($replacements);
				}

				// Attempt to set up contexts to allow TXP tags to be used.
				// This facility relies on the correct columns being pulled out by the query: caveat utilitor
				switch ($populate) {
					case 'article':
						populateArticleData($row);
						$thisarticle['is_first'] = ($page_rowcnt == 1);
						$thisarticle['is_last'] = (($page_rowcnt + 1) == $pagerows);
						break;
					case 'image':
						$thisimage = image_format_info($row);
						break;
					case 'file':
						$thisfile = file_download_format_info($row);
						break;
					case 'link':
						$thislink = array(
							'id'          => $row['id'],
							'linkname'    => $row['linkname'],
							'url'         => $row['url'],
							'description' => $row['description'],
							'date'        => $row['uDate'],
							'category'    => $row['category'],
							'author'      => $row['author'],
						);
						break;
				}

				$out[] = ($preparse) ? strtr(parse($truePart), $replacements) : parse(strtr($truePart, $replacements));
				$qry_rowcnt = ($count=="up") ? $qry_rowcnt+1 : $qry_rowcnt-1;
				$page_rowcnt = ($count=="up") ? $page_rowcnt+1 : $page_rowcnt-1;
			}

			if ($out) {
				if ($colreplacements) {
					$colout[] = ($preparse) ? strtr(parse($colsform), $colreplacements) : parse(strtr($colsform, $colreplacements));
				}
				if ($repagements) {
					$pageout = ($preparse) ? strtr(parse($pageform), $repagements) : parse(strtr($pageform, $repagements));
				}

				// Make up the final output
				if (in_array("above", $pagebit)) {
					$finalout[] = $pageout;
				}
				$finalout[] = doLabel($label, $labeltag).doWrap(array_merge($colout, $out), $wraptag, $break, $class, $breakclass, '', '', $html_id);
				if (in_array("below", $pagebit)) {
					$finalout[] = $pageout;
				}

				// Restore the paging outside the plugin container
				if ($limit > 0) {
					$thispage = $safepage;
				}

				// Restore the other contexts
				if (isset($safe)) {
					switch ($populate) {
						case 'article':
							$thisarticle = $safe;
							break;
						case 'image':
							$thisimage = $safe;
							break;
						case 'file':
							$thisfile = $safe;
							break;
						case 'link':
							$thislink = $safe;
							break;
					}
				}
				return join('', $finalout);
			}
		} else {
			return parse(EvalElse($thing, 0));
		}
	}
	return '';
}

// Returns a string with any ? variables replaced with their globals
// URL Variables are optionally run through preg_replace() to sanitize them.
//  $pat is an array of regex search patterns
//  $rep is an array of regex search repalcements (default = '', i.e. remove whatever matches)
function smd_query_parse($item, $dflts=array(''), $pat=array(''), $rep=array(''), $lax=true) {
	global $pretext, $thisarticle, $thisimage, $thisfile, $thislink, $variable;

	$item = html_entity_decode($item);

	// Sometimes pesky Unicode is not compiled in. Detect if so and fall back to ASCII
	if (!@preg_match('/\pL/u', 'a')) {
		$modRE = ($lax) ? '/(\?)([A-Za-z0-9_\- ]+)/' : '/(\?)([A-Za-z0-9_\-]+)/';
	} else {
		$modRE = ($lax) ? '/(\?)([\p{L}\p{N}\p{Pc}\p{Pd}\p{Zs}]+)/' : '/(\?)([\p{L}\p{N}\p{Pc}\p{Pd}]+)/';
	}

	$numMods = preg_match_all($modRE, $item, $mods);

	for ($modCtr = 0; $modCtr < $numMods; $modCtr++) {
		$modChar = $mods[1][$modCtr];
		$modItem = trim($mods[2][$modCtr]);
		$lowitem = strtolower($modItem);
		$urlvar = $svrvar = '';

		if (gps($lowitem) != '') {
			$urlvar = doSlash(gps($lowitem));
			if ($urlvar && $pat) {
				$urlvar = preg_replace($pat, $rep, $urlvar);
			}
		}
		if (serverSet($modItem) != '') {
			$svrvar = doSlash(serverSet($modItem));
			if ($svrvar && $pat) {
				$svrvar = preg_replace($pat, $rep, $svrvar);
			}
		}

		if (isset($variable[$lowitem]) && $variable[$lowitem] != '') {
			$item = str_replace($modChar.$modItem, $variable[$lowitem], $item);
		} else if ($svrvar != '') {
			$item = str_replace($modChar.$modItem, $svrvar, $item);
		} else if (isset($thisimage[$lowitem]) && !empty($thisimage[$lowitem])) {
			$item = str_replace($modChar.$modItem, $thisimage[$lowitem], $item);
		} else if (isset($thisfile[$lowitem]) && !empty($thisfile[$lowitem])) {
			$item = str_replace($modChar.$modItem, $thisfile[$lowitem], $item);
		} else if (isset($thislink[$lowitem]) && !empty($thislink[$lowitem])) {
			$item = str_replace($modChar.$modItem, $thislink[$lowitem], $item);
		} else if (array_key_exists($lowitem, $pretext) && !empty($pretext[$lowitem])) {
			$item = str_replace($modChar.$modItem, $pretext[$lowitem], $item);
		} else if (isset($thisarticle[$lowitem]) && !empty($thisarticle[$lowitem])) {
			$item = str_replace($modChar.$modItem, $thisarticle[$lowitem], $item);
		} else if ($urlvar != '') {
			$item = str_replace($modChar.$modItem, $urlvar, $item);
		} else if (isset($dflts[$lowitem])) {
			$item = str_replace($modChar.$modItem, $dflts[$lowitem], $item);
		} else {
			$item = str_replace($modChar.$modItem, $modItem, $item);
		}
	}
	return $item;
}
// Convenience functions to check if there's a prev/next page defined. Could also use smd_if
function smd_query_if_prev($atts, $thing) {
	global $smd_query_pginfo;

	$res = $smd_query_pginfo && $smd_query_pginfo['{smd_prevpage}'] != '';
	return parse(EvalElse(strtr($thing, $smd_query_pginfo), $res));
}
function smd_query_if_next($atts, $thing) {
	global $smd_query_pginfo;

	$res = $smd_query_pginfo && $smd_query_pginfo['{smd_nextpage}'] != '';
	return parse(EvalElse(strtr($thing, $smd_query_pginfo), $res));

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
#smd_help table {width:90%; text-align:center; padding-bottom:1em;}
#smd_help td, #smd_help th {border:1px solid #999; padding:.5em 0;}
#smd_help ul { list-style-type:square; }
#smd_help .required {color:red;}
#smd_help li { margin:5px 20px 5px 30px; }
#smd_help .break { margin-top:5px; }
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="smd_help">

	<h1>smd_query</h1>

	<p>The laziest tag ever! Allows you to make ad-hoc queries to the database and process the results, row by row, in a form or container.</p>

	<h2>Features</h2>

	<ul>
		<li>Supports simple queries with a reduced syntax (<span class="caps">SELECT</span> cols <span class="caps">FROM</span> table <span class="caps">WHERE</span> clause) or your own custom queries</li>
		<li>Read information from any part of the current article, image (planned), file, link, <code>&lt;txp:variable /&gt;</code> or <span class="caps">URL</span> line. If any fields are missing you can specify a default value</li>
		<li>Optionally filter the <span class="caps">URL</span> input using regular expressions, for safety</li>
		<li>Each row can be passed to a form (or the plugin can be used as a container tag with or without <code>&lt;txp:else /&gt;</code>) to display the results</li>
		<li>Column headings may be output using a second form</li>
		<li>Result sets can be paginated, with support for a paging form</li>
	</ul>

	<h2>Author</h2>

	<p><a href="http://stefdawson.com/contact">Stef Dawson</a></p>

	<h2>Installation / Uninstallation</h2>

	<p>Download the plugin from either <a href="http://textpattern.org/plugins/976/smd_query">textpattern.org</a>, or the <a href="http://stefdawson.com/sw">software page</a>, paste the code into the <span class="caps">TXP</span> Admin -&gt; Plugins pane, install and enable the plugin. Visit the <a href="http://forum.textpattern.com/viewtopic.php?id=27279">forum thread</a> for more info or to report on the success or otherwise of the plugin.</p>

	<p>Uninstall by simply deleting the plugin from the Admin-&gt;Plugins pane.</p>

	<h2><code>&lt;txp:smd_query /&gt;</code> usage</h2>

	<p>Use this tag in any page, form, article, file, link, etc context to grab stuff from the database. The plugin can operate in one of two modes:</p>

	<ol>
		<li>simple mode just allows <code>SELECT stuff FROM table WHERE clause</code></li>
		<li>advanced mode uses the <code>query</code> attribute so you can design your own query. It can include <span class="caps">COUNT</span> (*), joins, anything; perhaps even <span class="caps">INSERT</span> and <span class="caps">UPDATE</span> (?) although it&#8217;s untested and liable to flooding</li>
	</ol>

	<h3 class="atts " id="attributes">Attributes</h3>

	<h4>Simple queries</h4>

	<ul class="atts">
		<li><span class="atnm">column</span> : comma-separated list of columns to retrieve from the database</li>
		<li><span class="atnm">table</span> : name of the table to retrieve the columns from (yes, non-<span class="caps">TXP</span> tables are also supported if they are in the same database :-)</li>
		<li><span class="atnm">where</span> : any extra clause you wish to specify. Defaults to &#8220;the whole darn table&#8221;</li>
	</ul>

	<h4>Advanced queries</h4>

	<ul>
		<li><span class="atnm">query</span> : any query you like can be used here. Overrides <code>column</code>, <code>table</code> and <code>where</code></li>
		<li><span class="atnm">mode</span> : you should not need to alter this parameter as it is set to automatically detect the query type. If you are using <span class="caps">SELECT</span> or <span class="caps">SHOW</span> statements, the mode is set to <code>output</code>; for any other type of query (e.g. <span class="caps">INSERT</span>/UPDATE) it is set to <code>input</code>. The only difference between the two modes is that if it set to <code>input</code> you can use smd_query as a self-closing tag because it does not use the form/container to parse the result set. Change this parameter only if the plugin detects the mode wrongly or you are doing something unorthodox with your query. Default: <code>auto</code></li>
		<li><span class="atnm">populate</span> : you usually use <code>{replacement}</code> variables in your smd_query container but if you are dealing with the native Textpattern content types (article, image, file, link) you can inform smd_query which of the four you are using via this attribute. You can then use <span class="caps">TXP</span> tags inside your form/container. See <a href="#eg13">example 13</a></li>
	</ul>

	<h4>Forms and paging</h4>

	<ul>
		<li><span class="atnm">form</span> : the form to use to parse each returned row. See <a href="#replacements">replacements</a>. If not specified, the plugin will use anything contained between its opening and closing tag</li>
		<li><span class="atnm">colsform</span> : optional <span class="caps">TXP</span> form to parse any header row containing column names (of limited use)</li>
		<li><span class="atnm">pageform</span> : optional <span class="caps">TXP</span> form used to specify the layout of any paging navigation and statistics such as page number, quantity of records per page, total number of records, etc.</li>
		<li><span class="atnm">pagepos</span> : the position of the paging information. Options are <code>below</code> (the default), <code>above</code>, or both of them separated by <code>delim</code></li>
		<li><span class="atnm">preparse</span> : normally, any container or form will have its replacement variables swapped for content and <em>then</em> it will be parsed to process any <span class="caps">TXP</span> tags. If you wish to reverse this order so that the container is parsed first before the replacements are made, specify <code>preparse=&quot;1&quot;</code>. Very useful when using the <code>&lt;txp:yield /&gt;</code> tag (see <a href="#eg9">example 9</a>). Default: 0</li>
		<li><span class="atnm">limit</span> : show this many results per page. Has no bearing on any <span class="caps">SQL</span> <code>LIMIT</code> you may set. Setting a <code>limit</code> in the smd_query tag switches paging on automatically so you can use the <code>&lt;txp:older /&gt;</code> and <code>&lt;txp:newer /&gt;</code> tags inside your <code>pageform</code> to step through each page of results. You may also construct your own paging (see <a href="#eg11">example 11</a>)</li>
		<li><span class="atnm">offset</span> : skip this many rows before outputting the results</li>
		<li><span class="atnm">pagevar</span> : if you are putting an smd_query on the same page as a standard article list, the built-in newer and older tags will clash with those of smd_query; clicking next/prev will step through both your result set and your article list. Specify a different variable name here so the two lists can be navigated independently, e.g. <code>pagevar=&quot;qpage&quot;</code>. Note that if you change this, you will have to generate your own custom newer/older links (see <a href="#eg11">example 11</a>) and the <a href="#smd_qif">conditional tags</a>. You may also use the special value <code>pagevar=&quot;SMD_QUERY_UNIQUE_ID&quot;</code> which will assign the paging variable to this specific instance of your query. This will allow you to use multiple smd_query tags on a single page and navigate them independently using the same <code>pageform</code> (see <a href="#eg12">example 12</a> for details). Default: <code>pg</code></li>
	</ul>

	<h4>Filters</h4>

	<ul>
		<li><span class="atnm">urlfilter</span> : filter <span class="caps">URL</span> input with this list of regular expressions (each separated by <code>delim</code>)</li>
		<li><span class="atnm">urlreplace</span> : replace each filtered <span class="caps">URL</span> element listed in <code>urlfilter</code> with this list of regular expressions (each separated by <code>delim</code>). If not used, anything matching <code>urlfilter</code> will be removed from any <span class="caps">URL</span> variables. See <a href="#filtering">Filtering and injection</a></li>
		<li><span class="atnm">defaults</span> : comma separated list of values to use in the event some field you specified doesn&#8217;t exist. Each default should be given as <code>name: value</code> (the <code>:</code> is configurable via the <code>paramdelim</code> attribute). For example <code>defaults=&quot;id: 1, my_cat: mammals, user_sec: ?defsec&quot;</code> would mean that if the <code>id</code> field was blank, the number 1 would be used; if the variable <code>my_cat</code> was empty, the word <code>mammals</code> would be used; and if the <code>user_sec</code> variable was empty, use the default value as found in the variable <code>defsec</code> (which could have been set via a <code>&lt;txp:variable /&gt;</code> earlier in the page)</li>
		<li><span class="atnm">escape</span> : list of column names with which to escape <span class="caps">HTML</span> entities. Useful if you have returned body or excerpt blocks that may contain apostrophes that could kill tags inside the smd_query container</li>
	</ul>

	<h4>Tag/class/formatting attributes</h4>

	<ul>
		<li><span class="atnm">label</span> : label to display above the entire output</li>
		<li><span class="atnm">labeltag</span> : the (X)HTML tag to surround the label with. Specify it without angle brackets (e.g. <code>labeltag=&quot;h3&quot;</code>)</li>
		<li><span class="atnm">wraptag</span> : the (X)HTML tag to surround the entire output (e.g. <code>wraptag=&quot;table&quot;</code>)</li>
		<li><span class="atnm">html_id</span> : <span class="caps">HTML</span> ID to apply to the wraptag</li>
		<li><span class="atnm">class</span> : <span class="caps">CSS</span> class name to apply to the wraptag</li>
		<li><span class="atnm">break</span> : each returned row of data will be wrapped with this tag (e.g. <code>break=&quot;tr&quot;</code>)</li>
		<li><span class="atnm">breakclass</span> : <span class="caps">CSS</span> class name to apply to the break tag</li>
	</ul>

	<h4>Plugin customisation</h4>

	<ul>
		<li><span class="atnm">strictfields</span> : when using &#8216;?&#8217; fields, spaces are allowed in field names. Set <code>strictfields=&quot;1&quot;</code> to forbid spaces. Default: 0</li>
		<li><span class="atnm">delim</span> : the delimiter to use between patterns in <code>urlfilter</code> and <code>urlreplace</code>. Default: comma (<code>,</code>)</li>
		<li><span class="atnm">paramdelim</span> : the delimiter to use between name-value pairs in <code>defaults</code>. Default: colon (<code>:</code>)</li>
		<li><span class="atnm">hashsize</span> : (should not be needed) the plugin assigns a 32-character, unique reference to the current smd_query based on your query attributes. <code>hashsize</code> governs the mechanism for making this long reference shorter. It comprises two numbers separated by a colon; the first is the length of the uniqe ID, the second is how many characters to skip past each time a character is chosen. For example, if the unique_reference was <code>0cf285879bf9d6b812539eb748fbc8f6</code> then <code>hashsize=&quot;6:5&quot;</code> would make a 6-character unique ID using every 5th character; in other words <code>05f898</code>. If at any time, you &#8220;fall off&#8221; the end of the long string, the plugin wraps back to the beginning of the string and continues counting. Default: <code>6:5</code></li>
		<li><span class="atnm">silent</span> : if your query contains an error (wrong column name or some malformed input), the plugin will issue a <span class="caps">TXP</span> error message. Using <code>silent=&quot;1&quot;</code> will attempt to hide this error message</li>
		<li><span class="atnm">count</span> : Can be either &#8220;up&#8221; (the default) or &#8220;down&#8221;. See <a href="#replacements">{smd_thisrow}</a></li>
		<li><span class="atnm">debug</span> : set to 1 to show some debug output; use 2 to show a bit more detail</li>
	</ul>

	<p>The attributes <code>query</code>, <code>column</code>, <code>table</code> and <code>where</code> can contain replacements themselves to read values from the current context. Specify the field name with a <code>?</code> in front of it (e.g. <code>query=&quot;SELECT * FROM txp_image WHERE category=&#39;?category1&#39; OR category=&#39;?category2&#39;</code>) would show images that had their category set to one of the article&#8217;s categories.</p>

	<p>The &#8216;?&#8217; fields can be any item from the <span class="caps">TXP</span> universe, including anything set in a <code>&lt;txp:variable /&gt;</code> or some user-input on the <span class="caps">URL</span> address bar. Fields are processed in the following order; as soon as a matching entry is found, the rest are not checked:</p>

	<p><code>&lt;txp:variable /&gt;</code> -&gt; <code>$_SERVER</code> var -&gt; image -&gt; file -&gt; link -&gt; global article -&gt; current article -&gt; <span class="caps">URL</span> var -&gt; default value -&gt; verbatim (without &#8216;?&#8217;)</p>

	<p>This hierarchy allows some degree of safety: since <span class="caps">TXP</span> variables are ultimately set by you, they are checked first, then gradually less specific stuff is checked until <span class="caps">URL</span> variables are considered at the bottom of the food chain.</p>

	<h2 class="atts " id="replacements">Replacement tags</h2>

	<p>In your output form you may specify any column name surrounded with <code>{}</code> characters to display that field. So if your query was <code>SELECT id, name, category FROM txp_image WHERE ext=&quot;.jpg&quot;</code> you would have the following replacements available:</p>

	<ul>
		<li><span class="atnm">{id}</span> : the image ID</li>
		<li><span class="atnm">{name}</span> : the image filename</li>
		<li><span class="atnm">{category}</span> : the image category</li>
	</ul>

	<p>Just put those names into your <code>form</code> among other normal <span class="caps">HTML</span> or <span class="caps">TXP</span> tags, and the relevant value from that row will be displayed. The replacements honour any <code>AS</code> statement you may employ to rename them.</p>

	<p>In addition, the following replacements are added to each row:</p>

	<ul>
		<li><span class="atnm">{smd_allrows}</span> : the total number of rows in the result set<sup id="fnrev18296387764c7d6d832a491" class="footnote"><a href="#fn18296387764c7d6d832a491">1</a></sup></li>
		<li><span class="atnm">{smd_rows}</span> : the total number of rows visible on this page</li>
		<li><span class="atnm">{smd_pages}</span> : the total number of pages in this result set<sup class="footnote"><a href="#fn18296387764c7d6d832a491">1</a></sup></li>
		<li><span class="atnm">{smd_thispage}</span> : the current page number being viewed<sup class="footnote"><a href="#fn18296387764c7d6d832a491">1</a></sup></li>
		<li><span class="atnm">{smd_thisrow}</span> : the current row number on this page</li>
		<li><span class="atnm">{smd_thisindex}</span> : the current row number (zero-based) on this page</li>
		<li><span class="atnm">{smd_cursor}</span> : the current row number from the start of the result set</li>
		<li><span class="atnm">{smd_cursorindex}</span> : the current row number from the start of the result set (zero-based)</li>
	</ul>

	<p id="fn18296387764c7d6d832a491" class="footnote"><sup>1</sup> These three items are also available in your designated <code>pageform</code>. The pageform can also utilise these extra replacements:</p>

	<ul>
		<li><span class="atnm">{smd_prevpage}</span> : the previous page number (empty if on first page)</li>
		<li><span class="atnm">{smd_nextpage}</span> : the next page number (empty if on last page)</li>
		<li><span class="atnm">{smd_row_start}</span> : the first row number being displayed</li>
		<li><span class="atnm">{smd_row_end}</span> : the last row number being displayed</li>
		<li><span class="atnm">{smd_rows_prev}</span> : the number of rows on the previous page. Will either be the value of your <code>limit</code>, or 0 if on the first page</li>
		<li><span class="atnm">{smd_rows_next}</span> : the number of rows on the next page</li>
		<li><span class="atnm">{smd_query_unique_id}</span> : the unique ID assigned to this smd_query tag (see the <code>hashsize</code> attribute and <a href="#eg12">example 12</a> for more)</li>
	</ul>

	<p>These are useful for tables to show row numbers, but can also be used for pagination or can be tested with smd_if to take action from within your form. <code>{smd_thisrow}</code>, <code>{smd_thisindex}</code>, <code>{smd_cursor}</code>, and <code>{smd_cursorindex}</code> count up or down depending on the <code>count</code> attribute (<code>{smd_row_start}</code> and <code>{smd_row_end}</code> also change accordingly).</p>

	<h2 id="smd_qif"><code>&lt;txp:smd_query_if_prev&gt; / &lt;txp:smd_query_if_next&gt;</code></h2>

	<p>Use these container tags to determine if there is a next or previous page and take action if so. Can only be used inside <code>pageform</code>, thus all <a href="#replacements">paging replacement variables</a> are available inside these tags.</p>

<pre class="block"><code class="block">&lt;txp:smd_query_if_prev&gt;Previous page&lt;/txp:smd_query_if_prev&gt;
&lt;txp:smd_query_if_next&gt;Next page&lt;/txp:smd_query_if_next&gt;
</code></pre>

	<p>See <a href="#eg11">example 11</a> for more.</p>

	<h2 id="filtering">Filtering and injection</h2>

	<p>After great deliberation, access to the <span class="caps">URL</span> line has been granted so you may employ user-entered data in your queries, allowing complete flexibility for your user base. However, as Peter Parker&#8217;s conscience might say:</p>

	<blockquote>
		<p>With great power comes great responsibility</p>
	</blockquote>

	<p>Not everybody out there is trustworthy so heed this warning: <strong>Assume <span class="caps">ALL</span> user input is tainted</strong>. Check everything. If you want to know more about what people can do with access to one simple portion of your <span class="caps">SQL</span> query, Google for &#8216;<span class="caps">SQL</span> injection&#8217;.</p>

	<p>For those still reading, the good news is that the plugin does everything it can to pre-filter stuff on the <span class="caps">URL</span> line before it gets to the query. This should make your user input safe enough, but for the paranoid (or sensible) there are two attributes you can use to clamp down allowable user input. If you know anything about <a href="http://www.regular-expressions.info/quickstart.html">regular expressions</a> or are familiar with the <span class="caps">PHP</span> function <a href="http://uk2.php.net/preg_replace">preg_replace()</a> then you&#8217;ll be right at home because, put simply, you can optionally pass every <span class="caps">URL</span> variable through it to remove stuff you don&#8217;t want.</p>

	<h3 id="urlfilter">urlfilter</h3>

	<p>This takes a comma-separated list (at least by default; override the comma with the <code>delim</code> attribute if you need to use commas in your filter strings) of complete regular expression patterns that you wish to search for, in every <span class="caps">URL</span> variable. For example, if you wanted to ensure that your users only entered digits you could specify this:</p>

<pre class="block"><code class="block">urlfilter=&quot;/[^\d]+/&quot;
</code></pre>

	<p>Briefly, the starting and trailing <code>/</code> marks delimit a regular expression &#8212; they must always be present. The square brackets denote a group of characters, the circumflex negates the group, the <code>\d</code> means &#8220;any digit&#8221; and the <code>+</code> specifies that you want it to check for one or more of the preceding things. In other words, look for anything in the input that is <strong>not</strong> one or more digits. That would match any letters, quotes, special characters, anything at all that wasn&#8217;t a zero to nine.</p>

	<p>You can specify more than one filter like this:</p>

<pre class="block"><code class="block">urlfilter=&quot;/\d/, /\s/&quot;
</code></pre>

	<p>That would look for any single digit and any single space character. That&#8217;s a simple example and you could do it all in one regex, but splitting them up can help you filter stuff better (see <a href="#urlreplace">urlreplace</a> for an example).</p>

	<p>By default, if you just specify <code>urlfilter</code> without <code>urlreplace</code>, anything that matches your filter patterns will be removed from the user input.</p>

	<h3 id="urlreplace">urlreplace</h3>

	<p>The other half of the filtering jigsaw allows you to not just remove anything that matches, but actually replace it with something else. Specify a fixed string, a list of fixed strings or more <span class="caps">URL</span> patterns to replace whatever matches your <code>urlfilter</code>. Using the first filter example from above, you could replace anything that is not a digit with a hyphen by specifying:</p>

<pre class="block"><code class="block">urlreplace=&quot;-&quot;
</code></pre>

	<p>So if you allowed a <span class="caps">URL</span> variable called <code>digits</code> and a site visitor entered <code>?digits=Zaphod 4 Trillian</code>, your <span class="caps">URL</span> variable would become: <code>-------4--------</code>. Not much use, but hey, it&#8217;s an example!</p>

	<p>As with <code>urlfilter</code> you can specify more than one replacement and they will pair up with their corresponding filter. In other words, if you take the second filter above (<code>urlfilter=&quot;/\d/, /\s/&quot;</code>) and used this:</p>

<pre class="block"><code class="block">urlreplace=&quot;, -&quot;
</code></pre>

	<p>Then any digit in your user input would be removed (there is nothing before the comma) and any space character would be replaced with a hyphen.</p>

	<p>If at any time a field gives an empty result (i.e. it totally fails any <code>urlfilter</code> tests or simply returns nothing because it has not been set), any <code>defaults</code> assigned to that variable will be used instead. If there are no defaults, the name of the variable itself (minus its <code>?</code>) will be used.</p>

	<p>With these two filters at your disposal and the ability to specify default values for user variables, you can make your queries much safer to the outside world and start using <span class="caps">HTML</span> forms to gather input from users that can then be plugged into queries, fairly safe in the knowledge that your database is not going to implode.</p>

	<p>But please remember:</p>

	<blockquote>
		<p>Assume <strong>all</strong> user input is tainted: check everything.</p>
	</blockquote>

	<h2 id="examples">Examples</h2>

	<h3 id="eg1">Example 1: Simple image select query</h3>

<pre class="block"><code class="block">&lt;txp:smd_query column=&quot;*&quot;
     table=&quot;txp_image&quot;
     where=&quot;category=&#39;mammal&#39; OR category=&#39;bird&#39;&quot;
     form=&quot;dbout&quot; wraptag=&quot;ul&quot; break=&quot;li&quot; /&gt;
</code></pre>

	<p>With form <code>dbout</code> containing:</p>

<pre class="block"><code class="block">&lt;a href=&quot;/images/{id}{ext}&quot; /&gt;&lt;txp:thumbnail name=&quot;{name}&quot; /&gt;&lt;/a&gt;
</code></pre>

	<p>Will render an unordered list of thumbnails with links to the fullsize image if the category is either <code>mammal</code> or <code>bird</code>.</p>

	<h3 id="eg2">Example 2: link category list to parent</h3>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT DISTINCT
     txc.name FROM txp_category AS txc, textpattern AS txp
     WHERE type=&#39;article&#39; AND parent=&#39;animals&#39;
     AND (txc.name = txp.category1 OR txc.name = txp.category2)
     form=&quot;dbout&quot; wraptag=&quot;ul&quot; break=&quot;li&quot; /&gt;
</code></pre>

	<p>With form <code>dbout</code> containing:</p>

<pre class="block"><code class="block">&lt;txp:category name=&quot;{name}&quot; link=&quot;1&quot; title=&quot;1&quot; /&gt;
</code></pre>

	<p>Will render a list of linkable category names that contain articles with categories that have the given parent. If a category is unused it will not be listed.</p>

	<h3 id="eg3">Example 3: child category counts</h3>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT DISTINCT
     txc.name, COUNT(*) AS count FROM txp_category AS txc,
     textpattern AS txp
     WHERE type=&#39;article&#39; AND parent=&#39;?custom3&#39;
     AND (txc.name = txp.category1 OR txc.name = txp.category2)
     GROUP BY txc.name&quot;
     form=&quot;dbout&quot; wraptag=&quot;ul&quot; break=&quot;li&quot; /&gt;
</code></pre>

	<p>With form <code>dbout</code> containing:</p>

<pre class="block"><code class="block">&lt;txp:category name=&quot;{name}&quot; link=&quot;1&quot; title=&quot;1&quot; /&gt; ({count})
</code></pre>

	<p>Will read the parent item from the <code>custom3</code> field and render a similar list to Example 2 but with the article counts added in parentheses afterwards.</p>

	<h3 id="eg4">Example 4: Top 10 downloads</h3>

<pre class="block"><code class="block">&lt;txp:smd_query column=&quot;*&quot; table=&quot;txp_file&quot;
     where=&quot;(category=&#39;?category1&#39; OR category=&#39;?category2&#39;)
     AND status=4 ORDER BY downloads desc LIMIT 10&quot;
     wraptag=&quot;table&quot; break=&quot;tr&quot;
     label=&quot;Most popular downloads&quot; labeltag=&quot;h3&quot;&gt;
  &lt;td&gt;&lt;txp:file_download_link id=&quot;{id}&quot;&gt;{filename}&lt;/txp:file_download_link&gt;&lt;/td&gt;
  &lt;td&gt;{description}&lt;/td&gt;
  &lt;td&gt;downloads: {downloads}&lt;/td&gt;
&lt;txp:else /&gt;
  &lt;p&gt;No recent downloads, sorry&lt;/p&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>This one uses the plugin as a container tag instead of a form and tabulates the top 10 downloads (status=live) that have a category matching either of the current article&#8217;s categories, with most popular listed first. If there are no downloads, the <code>&lt;txp:else /&gt;</code> portion displays a message.</p>

	<h3 id="eg5">Example 5: Article keywords related to link</h3>

	<p>Very interesting use case here. Put this in the plainlinks form:</p>

<pre class="block"><code class="block">&lt;txp:linkdesctitle /&gt;
&lt;txp:smd_query query=&quot;SELECT DISTINCT
     txp.id, txp.title FROM textpattern AS txp
     WHERE (txp.keywords LIKE &#39;%,?category%,&#39;
     OR txp.keywords LIKE &#39;%?category%,&#39;
     OR txp.keywords LIKE &#39;%,?category%&#39;)
     GROUP BY txp.title&quot;
     wraptag=&quot;ul&quot; break=&quot;li&quot;&gt;
  &lt;txp:permlink id=&quot;{id}&quot;&gt;{title}&lt;/txp:permlink&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>When you execute <code>&lt;txp:linklist /&gt;</code> from a page you will get a list of links as usual, but under each one you will see a hyperlinked list of articles that are related (by keyword) to the category of the link.</p>

	<p>The reason it is compared three times is because article keywords are stored like this in the database:</p>

	<p><code>government,conspiracy,id,card,data,biometric,bad,idea</code></p>

	<p>If each category word was compared only once without commas (i.e. <code>txp.keywords LIKE &#39;%?category%&#39;</code>) then a link with category <code>piracy</code> would cause any article containing keyword <code>conspiracy</code> to be included. Essentially, by comparing the category either surrounded by commas, with a comma after it, or with a comma before it, the search is restricted to only match whole words.</p>

	<h3 id="eg6">Example 6: Comparison in queries</h3>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT *
     FROM txp_file WHERE downloads &amp;gt;= 42&quot;&gt;
  &lt;txp:file_download_link id=&quot;{id}&quot;&gt;
     {filename}
  &lt;/txp:file_download_link&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>Shows links to all downloads where the download count is greater than or equal to 42. Note that under <span class="caps">TXP</span> 4.0.6 and below you must use the <span class="caps">HTML</span> entity names for <code>&amp;gt;</code> and <code>&amp;lt;</code> or the parser gets confused.</p>

	<h3 id="eg7">Example 7: unfiltered <span class="caps">URL</span> params (bad)</h3>

	<p>(a bad query)</p>

<pre class="block"><code class="block">&lt;txp:variable name=&quot;cutoff&quot;
     value=&quot;42&quot; /&gt;
&lt;txp:smd_query query=&quot;SELECT Title
     FROM textpattern
     WHERE id &lt; &#39;?usercut&#39;&quot;
     defaults=&quot;usercut: ?cutoff&quot;&gt;
   &lt;txp:permlink&gt;{Title}&lt;/txp:permlink&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>Shows hyperlinks to only those articles with an ID below the number given by the user on the <span class="caps">URL</span> line. If the value is not supplied, the default value from the <span class="caps">TXP</span> variable is used instead (42 in this case).</p>

	<p><strong><span class="caps">NOTE</span></strong>: validation is not performed and you cannot guarantee that the <code>usercut</code> variable is going to be numeric. You should not use this query on a production site unless you add a <code>urlfilter</code> to remove any non-numeric characters (see next example for a better query).</p>

	<h3 id="eg8">Example 8: filtered <span class="caps">URL</span> params (better!)</h3>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT Title
     FROM textpattern
     WHERE status = &#39;?user_status&#39;&quot;
     urlfilter=&quot;/[^1-5]/&quot;
     defaults=&quot;user_status: 4&quot;&gt;
   &lt;txp:permlink&gt;{Title}&lt;/txp:permlink&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>Pulls all articles out of the database that match the given status. This is a more robust query than Example 7 because it checks if the <code>user_status</code> field is 1, 2, 3, 4, or 5 (the regex specifies to remove everything from the user_status variable that is not in the range 1-5). If this condition is not met &#8212; e.g. the user specifies <code>user_status=6</code> or <code>user_status=&quot;abc&quot;</code> &#8212; then user_status will be set to <code>4</code>. Note that using <code>user_status=&quot;Zaphod 4 Trillian&quot;</code> on the <span class="caps">URL</span> address bar will actually pass the test because all characters other than the number &#8216;4&#8217; will be removed.</p>

	<p>You could use a <code>&lt;txp:variable /&gt;</code> if you wish and set all your defaults in a special form, ready to use throughout your page. In that case &#8212; if you had created a variable called <code>dflt_stat</code> &#8212; you might prefer to use <code>defaults=&quot;user_status: ?dflt_stat&quot;</code>.</p>

	<p>Query-tastic :-)</p>

	<h3 id="eg9">Example 9: Using preparse with <code>&lt;txp:yield /&gt;</code></h3>

	<p>Sometimes you may want to re-use a query in a few places throughout your site and show different content. For example, the same query could be used for logged-in and not-logged-in users but you&#8217;d see more detail if you were logged in. Normally you would need to write the query more than once, which is far from ideal. This technique allows you to write the query just once and reuse the form. Put this in a form called <code>user_table</code>:</p>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT * FROM txp_users&quot;
     wraptag=&quot;table&quot; break=&quot;tr&quot; preparse=&quot;1&quot;&gt;
   &lt;txp:yield /&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>Using <code>&lt;txp:output_form&gt;</code> as a container (in <span class="caps">TXP</span> 4.2.0 or higher) you can then call the query like this to show basic info:</p>

<pre class="block"><code class="block">&lt;txp:output_form form=&quot;user_table&quot;&gt;
&lt;td&gt;{name}&lt;/td&gt;
&lt;td&gt;{RealName}&lt;/td&gt;
&lt;/txp:output_form&gt;
</code></pre>

	<p>and like this for more detailed output:</p>

<pre class="block"><code class="block">&lt;txp:output_form form=&quot;user_table&quot;&gt;
&lt;td&gt;{name}&lt;/td&gt;
&lt;td&gt;{RealName}&lt;/td&gt;
&lt;td&gt;{email}&lt;/td&gt;
&lt;td&gt;{last_access}&lt;/td&gt;
&lt;/txp:output_form&gt;
</code></pre>

	<p>Note that when using smd_query in this manner you must remember to use <code>preparse=&quot;1&quot;</code> because you need to fetch the contents of the smd_query container (the <code>&lt;txp:yield /&gt;</code> tag in this case), parse it so it gets the contents of <code>&lt;txp:output_form&gt;</code>&#8217;s container and <em>then</em> applies the replacements. Without the <code>preparse</code>, the plugin tries to apply the replacements directly to the smd_query container, which does not actually contain any <code>{...}</code> tags.</p>

	<h3 id="eg10">Example 10: pagination</h3>

	<p>Iterate over some <span class="caps">TXP</span> user information, 5 people at a time:</p>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT * from txp_users&quot;
     limit=&quot;5&quot; wraptag=&quot;ul&quot; break=&quot;li&quot;
     pageform=&quot;page_info&quot;&gt;
   {RealName} ({privs})
&lt;/txp:smd_query&gt;
</code></pre>

	<p>In <code>page_info</code>:</p>

<pre class="block"><code class="block">Page {smd_thispage} of {smd_pages} |
Showing records {smd_row_start} to {smd_row_end}
of {smd_allrows} |
&lt;txp:older&gt;Next {smd_rows_next}&lt;/txp:older&gt; |
&lt;txp:newer&gt;Previous {smd_rows_prev}&lt;/txp:newer&gt;
</code></pre>

	<p>Underneath your result set you would then see the information regarding which page and rows your visitors were currently viewing. You would also see next/prev links to the rest of the results.</p>

	<h3 id="eg11">Example 11: custom pagination</h3>

	<p>There is a problem with <a href="#eg10">example 10</a> ; if you use txp:older and txp:newer when you are showing a standard article list; the paging tags will step through <em>both</em> your result set and your articles. To break the association between them you need to alter the variable that <span class="caps">TXP</span> uses to control paging. It is called <code>pg</code>and you&#8217;ll notice it in the <span class="caps">URL</span> (<code>?pg=2</code> for example) as you step through article lists.</p>

	<p>Using the <code>pagevar</code> attribute you can tell smd_query to watch for your own variable instead of the default <code>pg</code> and thus build your own next/prev links that only control smd_query.</p>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT * from txp_users&quot;
     limit=&quot;5&quot; wraptag=&quot;ul&quot; break=&quot;li&quot;
     pageform=&quot;page_info&quot; pagevar=&quot;smd_qpg&quot;&gt;
   {RealName} ({privs})
&lt;/txp:smd_query&gt;
</code></pre>

	<p>In <code>page_info</code>:</p>

<pre class="block"><code class="block">Page {smd_thispage} of {smd_pages} |
   Showing records {smd_row_start}
   to {smd_row_end} of {smd_allrows} |
&lt;txp:smd_query_if_prev&gt;
  &lt;a href=&quot;&lt;txp:permlink /&gt;?smd_qpg={smd_prevpage}&quot;&gt;
     Previous {smd_rows_prev}&lt;/a&gt;
&lt;/txp:smd_query_if_prev&gt;
&lt;txp:smd_query_if_next&gt;
  &lt;a href=&quot;&lt;txp:permlink /&gt;?smd_qpg={smd_nextpage}&quot;&gt;
     Next {smd_rows_next}&lt;/a&gt;
&lt;/txp:smd_query_if_next&gt;
</code></pre>

	<h3 id="eg12">Example 12: using <code>SMD_QUERY_UNIQUE_ID</code></h3>

	<p>If you wish to use more than one smd_query on a single page but share a pageform between them you can use <span class="caps">SMD</span>_QUERY_UNIQUE_ID as the paging variable:</p>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT * from txp_users&quot;
     limit=&quot;5&quot; wraptag=&quot;ul&quot; break=&quot;li&quot;
     pageform=&quot;page_info&quot;
     pagevar=&quot;SMD_QUERY_UNIQUE_ID&quot;&gt;
   {RealName} ({privs})
&lt;/txp:smd_query&gt;
</code></pre>

	<p>In <code>page_info</code>:</p>

<pre class="block"><code class="block">Page {smd_thispage} of {smd_pages} |
   Showing records {smd_row_start}
   to {smd_row_end} of {smd_allrows} |
&lt;txp:smd_query_if_prev&gt;
  &lt;a href=&quot;&lt;txp:permlink /&gt;?{smd_query_unique_id}={smd_prevpage}&quot;&gt;
     Previous {smd_rows_prev}&lt;/a&gt;
&lt;/txp:smd_query_if_prev&gt;
&lt;txp:smd_query_if_next&gt;
  &lt;a href=&quot;&lt;txp:permlink /&gt;?{smd_query_unique_id}={smd_nextpage}&quot;&gt;
     Next {smd_rows_next}&lt;/a&gt;
&lt;/txp:smd_query_if_next&gt;
</code></pre>

	<p>Note this is just a simple example: you will have to be more clever than that if you are paging independent sets of rows because you will need to incorporate the paging variable from both smd_query tags in your pageform.</p>

	<h3 id="eg13">Example 13: <span class="caps">TXP</span> tags in container</h3>

<pre class="block"><code class="block">==&lt;txp:smd_query query=&quot;SELECT *,
     unix_timestamp(Posted) as uPosted,
     unix_timestamp(LastMod) as uLastMod,
     unix_timestamp(Expires) as uExpires
     FROM textpattern WHERE Status IN (4,5)&quot;
     wraptag=&quot;ul&quot; break=&quot;li&quot; html_id=&quot;myQuery&quot;
     populate=&quot;article&quot;&gt;
   &lt;txp:title /&gt; [ &lt;txp:posted /&gt; ]
&lt;/txp:smd_query&gt;==
</code></pre>

	<p>Note that the <code>populate</code> attribute relies on you extracting <strong>all</strong> columns to satisfy textpattern&#8217;s internal functions so this feature works correctly. A simple <code>select * from ...</code> will not work. In future versions of <span class="caps">TXP</span> this might change and a simple select * may then be enough.</p>

	<p>For reference, these are the extra columns required in 4.2.0 (and earlier):</p>

	<ul>
		<li>Article: <code>unix_timestamp(Posted) as uPosted, unix_timestamp(LastMod) as uLastMod, unix_timestamp(Expires) as uExpires</code></li>
		<li>Image: none</li>
		<li>File: none</li>
		<li>Link: <code>unix_timestamp(date) as uDate</code></li>
	</ul>

	<h3 id="eg14">Example 14: <code>&lt;txp:else /&gt;</code> with forms</h3>

	<p>If you wish to use txp tags with an &#8216;else&#8217; clause, you usually need to employ a container. As a convenience, smd_query allows you to use the container&#8217;s <code>&lt;txp:else /&gt;</code> clause with a form so you can re-use the query output and display different results in the event the query returns nothing.</p>

<pre class="block"><code class="block">&lt;txp:smd_query query=&quot;SELECT * FROM txp_users&quot;
     form=&quot;show_users&quot;&gt;
&lt;txp:else /&gt;
&lt;p&gt;No user info&lt;/p&gt;
&lt;/txp:smd_query&gt;
</code></pre>

	<p>Your <code>show_users</code> form can contain usual replacement variables and markup to format the results. Perhaps later you may wish to re-use the show_users output in another query:</p>

<pre class="block"><code class="block">==&lt;txp:smd_query query=&quot;SELECT * FROM txp_users WHERE
     RealName like &#39;%?usr%&#39;&quot; form=&quot;show_users&quot;&gt;
&lt;txp:else /&gt;
&lt;p&gt;No matching users found&lt;/p&gt;
&lt;/txp:smd_query&gt;==
</code></pre>

	<p>Note that you can display a different error message but use the same form (we&#8217;re escaping Textile here with <code>==</code> so it doesn&#8217;t interpret the percent signs as <code>&lt;span&gt;</code> elements).</p>

	<p>If you are careful and know you will <em>never</em> use a particular form with an smd_query container you can hard-code your &#8216;else&#8217; clause directly in your form and use smd_query as a self-closing tag. Your form will look a bit odd with a seeming &#8216;dangling&#8217; else, but it will work due to the way the <span class="caps">TXP</span> parser operates. If you do try and use a form with a <code>&lt;txp:else /&gt;</code> in it as well as calling the form using an smd_query with a <code>&lt;txp:else /&gt;</code> in its container, Textpattern will throw an error (usually the, perhaps unexpected, <code>tag does not exist</code> error). Be careful!</p>

	<h2 class="changelog">Changelog</h2>

	<ul>
		<li>22 May 08 | 0.10 | Initial release</li>
		<li>23 May 08 | 0.11 | Allowed maths in queries (use html entities in TXP4.0.6) and fixed <span class="caps">WHERE</span> clause to default to 1=1 if none supplied (both thanks jm) ; added more detailed file and link support</li>
		<li>14 Jul 08 | 0.12 | Added <code>txp:else</code> support in container (thanks jakob) ; added <code>silent</code> and <code>count</code> attributes, and the replacement tags <code>{smd_rows} {smd_thisrow} {smd_thisindex}</code></li>
		<li>23 Nov 08 | 0.20 | Added <code>&lt;txp:variable /&gt;</code> support ; enabled <span class="caps">URL</span> variable support ; added <code>urlfilter</code>, <code>urlreplace</code>, <code>delim</code>, <code>paramdelim</code> and <code>defaults</code> attributes</li>
		<li>17 Mar 09 | 0.21 | Added <code>$_SERVER</code> var support</li>
		<li>16 Oct 09 | 0.22 | Added <code>escape</code> attribute (thanks jakob) ; added <code>preparse</code> attribute</li>
		<li>02 Dec 09 | 0.30 | Added unicode support and <code>strictfields</code> to fix a few bugs (thanks speeke) ; added direct pagination support</li>
		<li>05 Dec 09 | 0.40 | Added <code>pagevar</code>, <code>{smd_prevpage}</code>, <code>{smd_nextpage}</code>, <code>{smd_rows_prev}</code> and <code>{smd_rows_next}</code>, <code>&lt;txp:smd_query_if_prev&gt;</code> and <code>&lt;txp:smd_query_if_next&gt;</code> ; removed <code>pgonly</code> as paging can now <em>only</em> be performed in the <code>pageform</code></li>
		<li>17 Jan 10 | 0.41 | Added <code>hashsize</code>, <code>mode</code> and <code>{smd_query_unique_id}</code></li>
		<li>31 Aug 10 | 0.50 | <code>form</code> overrides container ; container&#8217;s else automatically works in <code>form</code>s ; added <code>populate</code> (thanks atbradley); fixed <span class="caps">PHP</span> 4 compatibility and added <code>html_id</code> and <code>breakclass</code> (thanks makss) ; <span class="caps">SHOW</span> defaults to output mode</li>
	</ul>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>