<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'sgb_url_handler';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1.8.4';
$plugin['author'] = 'sgb';
$plugin['author_uri'] = 'http://mighthitgold.net';
$plugin['description'] = 'Adds support for multiple URL schemes.';

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
// Include sgb_error_documents
//include_plugin('sgb_error_documents');

/**
 * This function defines all the aspects of sgb_url_handler. 
 */
function sgb_url_handler_config()
{
	// Defines if error headers should be sent
	$config['send_errors'] = 1; // bool

	// Defines the type of separator used in URLs
	$config['separator'] = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

	// Defines which mode to match
	$config['match'] = 'best';

	// Send 301 redirects?
	$config['send_301'] = 0; // bool

	// Send 404 not founds?
	$config['send_404'] = 0; // bool

	// This section defines available URL schemes, put in the preferred order
	// from first to last. Keywords: %title%, %section%, %category%, %id%, 
	// %year%, %month%, %day%, %string%, %number%, %_author%, %_category%, 
	// %_section%, %_file%. Keywords beginning with _ are locale specific.



	$schemes['_file'] = '/%_file%/%number%';
	$schemes['section_id'] = '/%section%/%id%';
	$schemes['section_category'] = '/%section%/%category%';
//	$schemes['section_category_title'] = '/%section%/%category%/%title%';
//	$schemes['section_date_title'] = '/%section%/%year%/%month%/%day%/%title%';
//	$schemes['section_date'] = '/%section%/%year%/%month%/%day%';
	$schemes['section_title'] = '/%section%/%title%';
	$schemes['section'] = '/%section%';
	$schemes['title_only'] = '/%title%';

	// By default the plugin will use the PERMLINK mode set in the admin, if you 
	// want to use a different modes for a different sections. Use the example 
	// below as a guide -- section needs to match a section in your blog and 
	// scheme_name needs to match a key from the array above.
	// $sections['section'] = 'scheme_name';

	// Specify triggers (or actions) for schemes specified above.
	// Triggers: AUTHOR, FILE, ATOM, RSS, REDIRECT, NOTHING
	$triggers['_author'] = 'AUTHOR';
	$triggers['_author_en'] = 'AUTHOR';
	$triggers['_file'] = 'FILE';
	$triggers['_atom'] = 'ATOM';
	$triggers['_rss'] = 'RSS';

	// Run some clean up stuff -- Don't edit anything below here
	array_walk($schemes, 'sgb_url_handler_prep_schemes');
	$config['match'] = strtolower($config['match']);
	if ($config['send_errors'] == 0 || function_exists('sgb_error_document') == false) {
		$config['send_301'] = 0;
		$config['send_404'] = 0;
	}

	return array('config' => $config, 'schemes' => $schemes, 'sections' => ((!empty($sections)) ? $sections : array()), 'triggers' => ((!empty($triggers)) ? $triggers : array()), 'patterns' => sgb_url_handler_schemes_to_patterns($schemes), 'look_ups' => sgb_url_handler_schemes_to_look_ups($schemes));
}

// Don't edit below this line --

function sgb_url_handler_get_url($u = '') // string
{
	if ($u) {
		$r = trim($u, $sgb_url_handler_cfg['config']['separator']);
	} else if ($_SERVER['PATH_INFO']) {
		$r = trim($_SERVER['PATH_INFO'], $sgb_url_handler_cfg['config']['separator']);
	} else {
		$r = trim(array_shift(explode('?', $_SERVER['REQUEST_URI'])), '/');
		$s = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
		$r = trim(substr($r, strlen($s)), '/');
	}

	return ($r == basename($_SERVER['SCRIPT_NAME'])) ? '' : urldecode($r);
}

function sgb_url_handler_parse_url($u = '') // array
{
	$pt = explode('/', $u);
	if (!empty($pt)) {
		$c = count($pt);
		# Need to come up with a better way to strip page numbers
		$pg  = (is_numeric($pt[$c - 1]) && $c > 2) ? array_pop($pt) : null;
		return array('parts' => $pt, 'count' => $c, 'page' => $pg, 'url' => $u);
	} else {
		return array();
	}
}

function sgb_url_handler_schemes_to_look_ups($a = array()) // array
{
	if (count($a) < 1 || !is_array($a)) {
		return array();
	} else {
		foreach ($a as $k => $v) {
			$a[$k] = array_flip(explode('/', $v));
		}

		return $a;
	}
}

function sgb_url_handler_schemes_to_patterns($a = array()) // array
{
	if (count($a) < 1 || !is_array($a)) {
		return array();
	} else {
		return str_replace(array('%title%', '%section%', '%category%', '%id%', '%year%', '%month%', '%day%', '%string%', '%number%', '%_author%', '%_category%', '%_section%', '%_file%', '/'), array('([A-Za-z0-9-_])*', '([A-Za-z0-9-_])*', '([A-Za-z0-9-_\s])*', '(\d)*', '\d{4}', '\d{2}', '\d{2}', '([A-Za-z0-9-_])*', '(\d)*', strtolower(gTxt('author')), strtolower(gTxt('category')), strtolower(gTxt('section')), strtolower(gTxt('file_download')), '\/'), $a);
	}
}

function sgb_url_handler_prep_schemes(&$v) // array
{
	$v = trim(strtolower($v), '/');
}

function sgb_url_handler_match_url($url, $config) // array
{
	foreach ($config['patterns'] as $scheme => $pattern) {
		if (preg_match('/^'.$pattern.'$/', $url['url']) == 1) {
			/*
			** Are we running a trigger?
			*/
			if (isset($config['triggers'][$scheme])) {
				$return = sgb_url_handler_do_trigger($config['triggers'][$scheme], $scheme, $url);

				if (is_array($return)) {
					return array('scheme'=>$scheme, 'result'=>'trigger', 'article'=>$return);
				}
			} else {
				// Set the section & category
				$s = (isset($config['look_ups'][$scheme]['%section%'])) ? $url['parts'][$config['look_ups'][$scheme]['%section%']] : null;
				$c = (isset($config['look_ups'][$scheme]['%category%'])) ? $url['parts'][$config['look_ups'][$scheme]['%category%']] : null;

				// Set the title
				$t = (isset($config['look_ups'][$scheme]['%title%'])) ? $url['parts'][$config['look_ups'][$scheme]['%title%']] : null;

				// Set the id
				$id = (isset($config['look_ups'][$scheme]['%id%'])) ? $url['parts'][$config['look_ups'][$scheme]['%id%']] : null;

				// Set the year
				$year = (isset($config['look_ups'][$scheme]['%year%'])) ? $url['parts'][$config['look_ups'][$scheme]['%year%']] : null;

				// Set the month
				$month = (isset($config['look_ups'][$scheme]['%month%'])) ? $url['parts'][$config['look_ups'][$scheme]['%month%']] : null;

				// Set the day
				$day = (isset($config['look_ups'][$scheme]['%day%'])) ? $url['parts'][$config['look_ups'][$scheme]['%day%']] : null;

				/*
				** Are we matching an article?
				*/
				if (isset($id) && isset($t) && $config['config']['match'] == 'exact') {
					$return_array = array('s'=>$s, 'id'=>$id, 't'=>$t, 'c'=>$c, 'year'=>$year, 'month'=>$month, 'day'=>$day);
				} else if (isset($id)) {
					$return_array = array('s'=>$s, 'id'=>$id, 'c'=>$c, 'year'=>$year, 'month'=>$month, 'day'=>$day);
				} else if (isset($t)) {
					$return_array = array('s'=>$s, 't'=>$t, 'c'=>$c, 'year'=>$year, 'month'=>$month, 'day'=>$day);
				} else {
					$s = (isset($config['look_ups'][$scheme]['%section%'])) ? sgb_url_handler_ckEx('section', $url['parts'][$config['look_ups'][$scheme]['%section%']]) : null;
					$c = (isset($config['look_ups'][$scheme]['%category%'])) ? sgb_url_handler_ckEx('category', $url['parts'][$config['look_ups'][$scheme]['%category%']]) : null;
					$return_array = null;
				}

				/*
				** Attempt to return something
				*/
				if (!empty($return_array)) {
					$return = sgb_url_handler_lookup($return_array);

					// Number of articles returned
					$count = count($return);

					if ($count > 1) {
						# Place holder for a future "dis-ambiguity" option for multiple matches
						return array('scheme'=>$scheme, 'result'=>'matched', 'article'=>array_change_key_case($return[0], CASE_LOWER));
					} else if ($count > 0) {
						return array('scheme'=>$scheme, 'result'=>'matched', 'article'=>array_change_key_case($return[0], CASE_LOWER));
					} else if ($config['config']['match'] == 'best') {
						// Try matching the ID/Title only in the event we get nothing with the section/category
						$return_array['s'] = null;
						$return_array['c'] = null;
						$return_array['year'] = null;
						$return_array['month'] = null;
						$return_array['day'] = null;

						$return = sgb_url_handler_lookup($return_array);

						if (count($return) > 0) {
							return array('scheme'=>$scheme, 'result'=>'matched', 'article'=>array_change_key_case($return[0], CASE_LOWER));
						}
					}
				} else {
					$s_valid = ((isset($config['look_ups'][$scheme]['%section%']) && isset($s)) || !isset($config['look_ups'][$scheme]['%section%'])) ? 1 : 0;
					$c_valid = ((isset($config['look_ups'][$scheme]['%category%']) && isset($c)) || !isset($config['look_ups'][$scheme]['%category%'])) ? 1 : 0;

					if ($s_valid == 1 && $c_valid == 1) {
						return array('scheme'=>$scheme, 'result'=>'matched', 'article'=>array('section'=>$s, 'category1'=>$c));
					}

					// Record the section & category for a "best" fit
					if ($config['config']['match'] == 'best') {
						if (!empty($s)) { $section = $s; }
						if (!empty($c)) { $category = $c; }
					}
				}
			}
		}
	}

	// Return the "best" section/category if nothing else
	return array('scheme'=>'', 'result'=>'best', 'article'=>array('section'=>$section, 'category1'=>$category));
}

function sgb_url_handler_lookup($a = array()) // array
{
	// Build query
	$id = (!empty($a['id']) && is_numeric($a['id'])) ? "ID = '".doSlash($a['id'])."' and " : null;
	$d = $a['year'].(($a['month']) ? '-'.$a['month'] : '').(($a['day']) ? '-'.$a['day'] : '');
	$d = (!empty($d)) ? "posted like '".doSlash($d)."%' and " : null;
	$s = (!empty($a['s'])) ? "Section like '".doSlash($a['s'])."' and " : null;
	$t = (!empty($a['t'])) ? "url_title like '".doSlash($a['t'])."' and " : null;
	$c = (!empty($a['c'])) ? "Category1 like '".doSlash($a['c'])."' or Category2 like '".doSlash($a['c'])."'" : null;
	$q  = $id.$d.$s.$t.$c;
	if (substr($q, -4, 4) == 'and ') { $q = substr($q, 0, -4); }

	// Run the query
	return safe_rows("ID,Section,Category1,Category2,url_title,unix_timestamp(Posted) as posted", 'textpattern', $q.' order by posted', '');
}

function sgb_url_handler_ckEx($w, $t) // mixed
{
	return (ckEx($w, $t)) ? $t : null;
}

function sgb_url_handler_set($k = null, $v = null) // void
{
	$_GET[$k] = $v;
	$_POST[$k] = $v;
	$_REQUEST[$k] = $v;
}

function sgb_url_handler_permlinkurl($a, $bits = array()) // string
{
	global $sgb_url_handler_cfg, $permlink_mode;

	if (isset($sgb_url_handler_cfg['schemes'][$bits['scheme']])) { 
		$scheme = $bits['scheme'];
	} else if (isset($sgb_url_handler_cfg['sections'][$bits['section']])) {
		$scheme = $sgb_url_handler_cfg['sections'][$bits['section']];
	} else if (isset($sgb_url_handler_cfg['schemes'][$bits['mode']])) {
		$scheme = $bits['mode'];
	} else if (isset($sgb_url_handler_cfg['sections'][$a['section']])) {
		$scheme = $sgb_url_handler_cfg['sections'][$a['section']];
	} else {
		$scheme = $permlink_mode;
	}

	// Check required since thisarticle doesn't set the id (it sets thisid instead)
	$a['id'] = (isset($a['id'])) ? $a['id'] : $a['thisid'];
	
	$v = array('%title%', '%section%', '%category%', '%id%', '%year%', '%month%', '%day%', '//');
	$r = array($a['url_title'], $a['section'], urlencode(((isset($a['category1'])) ? $a['category1'] : $a['category2'])), $a['id'], date('Y', $a['posted']), date('m', $a['posted']), date('d', $a['posted']), '/');

	return hu.str_replace($v, $r, $sgb_url_handler_cfg['schemes'][$scheme]);
}

function sgb_url_handler_permlink($a, $t) // string
{
	global $sgb_url_handler_cfg, $thisarticle, $permlink_mode;
	return tag(parse($t), 'a', ' href="'.sgb_url_handler_permlinkurl($thisarticle, array('mode' => $a['mode'], 'section' => $a['section'], 'scheme' => $a['scheme'])).'" title="'.gTxt('permanent_link').'"');
}

function sgb_url_handler_do_trigger($t, $s = '', $url = array()) // array
{
	global $sgb_url_handler_cfg;

	// Split the parameters off the string
	list($t, $a) = explode('::', $t);

	switch(strtolower($t))
	{
		case 'atom':
			include txpath.'/publish/atom.php';
			exit(atom());
		case 'rss':
			include txpath.'/publish/rss.php';
			exit(rss());
		case 'file':
			$id = $url['parts'][$sgb_url_handler_cfg['look_ups'][$s]['%number%']];
			return (isset($id)) ? array('section'=>'file_download', 'id'=>$id) : array('section'=>null, 'id'=>null);
		case 'redirect':
			if (isset($a)) {
				header('Location: '.$a);
				exit();
			}
		case 'author':
		case 'nothing': return array('ignore'=>true);
	}
	
	return array();
}

function sgb_url_handler()
{
	// Set globals
	global $timeoffset, $permlink_mode, $sgb_url_handler_cfg;

	// Set the request
	$request = sgb_url_handler_parse_url(sgb_url_handler_get_url());

	if (!empty($request['url'])) {
		$result = sgb_url_handler_match_url($request, $sgb_url_handler_cfg);

		// Send a 404 if necessary/possible
		if (empty($result['article']['section']) && empty($result['article']['category1']) && empty($result['article']['category2']) && empty($result['article']['id']) && $sgb_url_handler_cfg['config']['send_404'] == 1) {
			@sgb_error_document('404');
			return;
		}

		// Send a 301 if necessary, only possible for article requests
		if (!empty($result['article']['id']) && $sgb_url_handler_cfg['config']['send_301'] == 1 && $sgb_url_handler_cfg['config']['match'] != 'best') {
			$check_this_permlink_mode = (isset($sgb_url_handler_cfg['sections'][$result['article']['section']])) ? $sgb_url_handler_cfg['sections'][$result['article']['section']] : $permlink_mode;

			// We have to make an exception for articles that may or may not have a section or category.
			$article_has_section = isset($result['article']['section']);
			$scheme_has_section = (strrpos($sgb_url_handler_cfg['schemes'][$check_this_permlink_mode], '%section%') === false) ? false : true;

			$article_has_category = (isset($result['article']['category1']) || isset($result['article']['category2'])) ? true : false;
			$scheme_has_cateogry = (strrpos($sgb_url_handler_cfg['schemes'][$check_this_permlink_mode], '%category%') === false) ? false : true;

			if ($result['scheme'] != $check_this_permlink_mode) {
				// Don't send a 301 if the scheme has a section/category and the article doesn't -- this is ugly
				if ($scheme_has_section == true && $article_has_section == false) {
					# do nothing
				} else if ($scheme_has_category == true && $article_has_category == false) {
					# do nothing
				} else {
					@sgb_error_document('301', sgb_url_handler_permlinkurl($result['article'], array('scheme'=>$check_this_permlink_mode)));
					return;
				}
			}
		}

		// Set up the page
		if (empty($_GET['id']) && empty($_POST['id'])) {
			sgb_url_handler_set('id', $result['article']['id']);
		}

		if (empty($_GET['s']) && empty($_POST['s'])) {
			sgb_url_handler_set('s', $result['article']['section']);
		}

		if (empty($_GET['c']) && empty($_POST['c'])) {
				sgb_url_handler_set('c', (($result['article']['category2']) ? $result['article']['category2'] : $result['article']['category1']));
		}

		if (empty($_GET['p']) && empty($_POST['p'])) {
				sgb_url_handler_set('p', $request['page']);
		}
	}
}


// Set configuration global
global $sgb_url_handler_cfg;
$sgb_url_handler_cfg = sgb_url_handler_config();

sgb_url_handler();

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h2>sgb_url_handler Features</h2>

<p>This plugin adds support for <b>multiple &#38; simultaneous URL schemes</b>, <b>customizable URL schemes</b>, and <b>per-section URL schemes</b>.</p>

<p>Additionally, if sgb_error_documents (version 0.1.2 or higher) is present, this plugin can be send to send 301 and 404 headers when triggered.</p>

<h2>Configuration</h2>

<p>All configuration options are set in the function <code>sgb_url_handler_config()</code>. For the most part all configuration options are set to work out of the box.</p>

<dl>
	<dt><code>$config['separator']</code></dt>
	<dd><p>Defines the separator used by the plugin for URLs. This is an auto-configuring option.</p></dd>

	<dt><code>$config['match']</code></dt>
	<dd><p>Determines how the plugin matches articles. In "exact" mode all elements in a URL (section, date, category, title) must be valid for an article to be returned. In "best" mode an article is returned if a valid ID or title is found. Default is "best".</p></dd>

	<dt><code>$config['send_errors']</code></dt>
	<dd><p>Provided that sgb_error_documents is installed and active this option allows you to enable/disable error generation (404 or 301). Default is true (however it will only work if sgb_error_documents is available).</p></dd>

	<dt><code>$config['send_301']</code></dt>
	<dd><p>Sends 301-redirect if set to true when an article is requested by a URL scheme different from the permlink mode (or from the scheme set in <code>$sections</code>). Default is false.</p></dd>

	<dt><code>$config['send_404']</code></dt>
	<dd><p>Sends 404-not found errors when an article is not found at the specified URL. Default to true.</p></dd>

	<dt><code>$schemes</code></dt>
	<dd><p>Specify all supported URL schemes in this array. Schemes are specified in the following format <code>$schemes['scheme_name'] = "scheme/format";</code>, e.g. to define a scheme for section/title URLs you would enter <code>$schemes['section_title'] = "/%section%/%title%";</code>. It is not necessary to put a leading or trailing slash. Variable words are wrapped in % symbols. You can put literal words in a URL scheme, e.g. "/this-section/%title%" will match articles by their title when the URL starts with "this-section".</p></dd>

	<dt><code>$sections</code></dt>
	<dd><p>The sections array is used to define URL schemes for distinct sections. The schemes specified here override the permlink mode set in the TxP Admin. To utilize the schemes specified here use the <code>&#60;txp: /&#62;</code> tag instead of the usual permlink tag in your forms.</p></dd>

	<dt><code>$triggers</code></dt>
	<dd><p>The Triggers array is used to define "actions" for specific URL schemes. You can also use this array to tell sgb_url_handler to ignore certain URL schemes like RSS, Atom or File Downloads. The format for adding a trigger is <code>$triggers['scheme_name'] = "Trigger Action";</code>.</p></dd>
</dl>

<p>Additional information on this plugin and its usage can be found in the plugin code and at <a href="http://mighthitgold.net/sgb_url_handler">http://mighthitgold.net/sgb_url_handler</a>.</p>

<p>I hope this plugin is useful for you!<br />- sgb</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>