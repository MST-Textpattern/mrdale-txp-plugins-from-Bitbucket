<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_if';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.91';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Generic multiple if condition tests';

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
/**
 * smd_if
 *
 * A Textpattern CMS plugin for evaluating multiple conditional logic statements
 *  -> Test for (in)equality, less/greater than, divisible by, empty, used, defined, begins/ends, contains, in list, type...
 *  -> Supports and/or logic
 *  -> Data filtration options
 *
 * @author Stef Dawson
 * @link   http://stefdawson.com/
 */

function smd_if($atts,$thing) {
	global $thisarticle, $pretext, $thisfile, $thislink, $thisimage, $variable;

	extract(lAtts(array(
		'field'          => '',
		'operator'       => '',
		'value'          => '',
		'logic'          => 'and',
		'case_sensitive' => '0',
		'filter'         => '',
		'replace'        => '',
		'filter_type'    => 'all',
		'filter_on'      => 'field',
		'param_delim'    => ',',
		'mod_delim'      => ':',
		'list_delim'     => '/',
		'var_prefix'     => 'smd_if_',
		'debug'          => '0',
	), $atts));

	// Special field names that refer to $pretext or elsewhere - everything else is assumed to
	// exist in $thisarticle so custom fields can be used
	$allPtxt = array(
		"id"            => '$pretext["id"]',
		"s"             => '$pretext["s"]',
		"c"             => '$pretext["c"]',
		"query"         => '$pretext["q"]',
		"pg"            => '$pretext["pg"]',
		"month"         => '$pretext["month"]',
		"author"        => '$pretext["author"]',
		"status"        => '$pretext["status"]',
		"page"          => '$pretext["page"]',
		"next_id"       => '$pretext["next_id"]',
		"next_title"    => '$pretext["next_title"]',
		"next_utitle"   => '$pretext["next_utitle"]',
		"prev_id"       => '$pretext["prev_id"]',
		"prev_title"    => '$pretext["prev_title"]',
		"prev_utitle"   => '$pretext["prev_utitle"]',
		"permlink_mode" => '$pretext["permlink_mode"]',
	);

	// Each entry has the operation to be eval()d later and a list of disallowed fields
	$allOps = array(
		'eq'        => array('isset(VARNAME) && CAST FIELD === CAST VALUE', ''),
		'not'       => array('isset(VARNAME) && CAST FIELD !== CAST VALUE', ''),
		'gt'        => array('isset(VARNAME) && CAST FIELD > CAST VALUE', ''),
		'ge'        => array('isset(VARNAME) && CAST FIELD >= CAST VALUE', ''),
		'lt'        => array('isset(VARNAME) && CAST FIELD < CAST VALUE', ''),
		'le'        => array('isset(VARNAME) && CAST FIELD <= CAST VALUE', ''),
		'between'   => array('isset(VARNAME) && ($tween=explode("'.$list_delim.'", VALUE)) && CAST FIELD > $tween[0] && CAST FIELD < $tween[1]', ''),
		'range'     => array('isset(VARNAME) && ($rng=explode("'.$list_delim.'", VALUE)) && CAST FIELD >= $rng[0] && CAST FIELD <= $rng[1]', ''),
		'divisible' => array('isset(VARNAME) && CAST FIELD % CAST VALUE === 0', ''),
		'in'        => array('isset(VARNAME) && in_array(FIELD, explode("'.$list_delim.'", VALUE)) !== false', ''),
		'notin'     => array('isset(VARNAME) && in_array(FIELD, explode("'.$list_delim.'", VALUE)) === false', ''),
		'begins'    => array('isset(VARNAME) && strpos(FIELD, VALUE) === 0', ''),
		'contains'  => array('isset(VARNAME) && strpos(FIELD, VALUE) !== false', ''),
		'ends'      => array('isset(VARNAME) && substr(FIELD, strlen(FIELD) - strlen(VALUE)) === VALUE', ''),
		'defined'   => array('isset(VARNAME)', 'parent'),
		'undefined' => array('!isset(VARNAME)', 'parent'),
		'isempty'   => array('isset(VARNAME) && FIELD == ""', ''),
		'isused'    => array('isset(VARNAME) && FIELD != ""', ''),
		'isnum'     => array('isset(VARNAME) && ctype_digit((string)FIELD)', ''),
		'isalpha'   => array('isset(VARNAME) && ctype_alpha((string)FIELD)', ''),
		'isalnum'   => array('isset(VARNAME) && ctype_alnum((string)FIELD)', ''),
		'islower'   => array('isset(VARNAME) && ctype_lower((string)FIELD)', ''),
		'isupper'   => array('isset(VARNAME) && ctype_upper((string)FIELD)', ''),
		'ispunct'   => array('isset(VARNAME) && ctype_punct((string)FIELD)', ''),
		'isspace'   => array('isset(VARNAME) && ctype_space((string)FIELD)', ''),
	);

	$numericOps = "gt, ge, lt, le, eq, not, divisible, range, between";
	$caseOps = "islower, isupper";
	$spaceOps = "isnum, isalpha, isalnum, islower, isupper, ispunct, begins, contains, ends";
	$fields = do_list($field, $param_delim);
	$numFlds = count($fields);
	$ops = do_list($operator, $param_delim);
	$numOps = count($ops);
	$vals = do_list($value, $param_delim);
	$numVals = count($vals);
	$parentCats = ''; // Placeholder for the concatenated list of category leaf nodes
	$replacements = array();
	$type = ($thisfile) ? "file" : (($thislink) ? "link" : (($thisimage) ? "image" : "article"));
	$out = array();
	$iterations = ($numFlds > $numOps) ? $numFlds : $numOps;
	$filter = (!empty($filter)) ? do_list($filter, $param_delim) : array();
	$replace = (!empty($replace)) ? do_list($replace, $param_delim) : array();
	$numFilters = count($filter);
	$numReplace = count($replace);
	$filter_on = (!empty($filter_on)) ? do_list($filter_on, $param_delim) : array();
	$filter_type = (!empty($filter_type)) ? do_list($filter_type, $param_delim) : array();
	if ($debug > 1 && ($filter || $replace)) {
		echo "++ FILTERS / REPLACEMENTS ++";
		dmp($filter, $replace);
	}

	for ($idx = 0; $idx < $iterations; $idx++) {
		$fld = ($idx < $numFlds) ? $fields[$idx] : $fields[0]; // Allow short-circuit
		$fldParts = explode($mod_delim, $fld);
		$val = ($idx < $numVals) ? $vals[$idx] : '';
		$valList = explode($list_delim, $val);
		$valRep = array();
		foreach ($valList as $kdx => $theval) {
			$valRep[$kdx] = explode($mod_delim, $theval);
		}

		$op = ($idx < $numOps && $ops[$idx] != '') ? $ops[$idx] : (($fldParts[0]=="parent") ? "contains" : "eq");
		$opParts = explode($mod_delim, $op);
		$op = (array_key_exists($opParts[0], $allOps)) ? $opParts[0] : "eq";
		$cast = ((count($opParts) == 2) && ($opParts[1] === "NUM") && (in_list($op, $numericOps))) ? '(int)' : '';
		$length = ((count($opParts) == 2) && ($opParts[1] === "LEN") && (in_list($op, $numericOps))) ? 'strlen(FIELD)' : '';
		// The cast to string is necessary to counteract the === in the eq operator.
		// It doesn't impact anything else because string comparisons work fairly intuitively in PHP
		// (e.g. 19 < 2 = false even though in terms of string order they'd go 19, 2, 20, 21,...)
		$count = ((count($opParts) == 2) && ($opParts[1] === "COUNT") && (in_list($op, $numericOps))) ? '(string)count(explode("'.$list_delim.'", FIELD))' : '';
		$killSpaces = ((count($opParts) == 2) && ($opParts[1] === "NOSPACE") && (in_list($op, $spaceOps))) ? true : false;
		$stripFld = ((count($fldParts) > 1) && (in_array("NOTAGS", $fldParts))) ? true : false;
		$trimFld = ((count($fldParts) > 1) && (in_array("TRIM", $fldParts))) ? true : false;
		$escapeFld = ((count($fldParts) > 1) && (in_array("ESC", $fldParts))) ? true : false;
		$escapeAllFld = ((count($fldParts) > 1) && (in_array("ESCALL", $fldParts))) ? true : false;
		$case_sensitive = (in_list($op, $caseOps)) ? 1 : $case_sensitive;
		$pat = ($idx < $numFilters) ? $filter[$idx] : (($filter) ? $filter[0] : '');
		$rep = ($idx < $numReplace) ? $replace[$idx] : (($replace) ? $replace[0] : '');
		if ($debug) {
			echo 'TEST '.($idx+1).n;
			dmp($fldParts, $opParts, $valRep);
		}
		// Get the operator replacement code
		$exclude = do_list($allOps[$op][1]);
		$op = $allOps[$op][0];

		// As long as the current operator allows this field...
		if (!in_array($fldParts[0], $exclude)) {
			// Make up the test field variable
			if ($fldParts[0] == 'file') {
				$rfld = $fldParts[1];
				$fld = '$thisfile["'.$rfld.'"]';
			} else if (isset($thisfile[$fldParts[0]])) {
				$rfld = $fldParts[0];
				$fld = '$thisfile["'.$rfld.'"]';
			} else if ($fldParts[0] == 'link') {
				$rfld = $fldParts[1];
				$fld = '$thislink["'.$rfld.'"]';
			} else if (isset($thislink[$fldParts[0]])) {
				$rfld = $fldParts[0];
				$fld = '$thislink["'.$rfld.'"]';
			} else if ($fldParts[0] == 'image') {
				$rfld = $fldParts[1];
				$fld = '$thisimage["'.$rfld.'"]';
			} else if (isset($thisimage[$fldParts[0]])) {
				$rfld = $fldParts[0];
				$fld = '$thisimage["'.$rfld.'"]';
			} else if ($fldParts[0] == 'pretext') {
				$rfld = $fldParts[1];
				$fld = '$pretext["'.$rfld.'"]';
			} else if (array_key_exists($fldParts[0], $allPtxt)) {
				$rfld = $fldParts[0];
				$fld = $allPtxt[$rfld];
			} else if ($fldParts[0] == "parent") {
				$treeField = 'name';
				$level = '';
				foreach ($fldParts as $part) {
					if ($part == "parent") {
						$theCat = ($thisfile) ? $thisfile['category'] : (($thislink) ? $thislink['category'] : (($thisimage) ? $thisimage['category'] : $pretext['c']));
					} else if (strpos($part, "CAT") === 0) {
						$theCat = $thisarticle["category".substr($part, 3)];
					} else if (strpos($part, "LVL") === 0) {
						$level = substr($part, 3);
					} else if (strpos($part, "TTL") === 0) {
						$treeField = 'title';
					} else if (strpos($part, "KIDS") === 0) {
						$treeField = 'children';
					}
				}

				$tree = getTreePath(doSlash($theCat), $type);
				if ($debug && $tree) {
					echo "CATEGORY TREE:";
					dmp($tree);
				}
				$items = array();
				foreach ($tree as $leaf) {
					if ($leaf['name'] == "root" || $leaf['name'] == $theCat) {
						continue;
					} else if ($level == '' || $level == $leaf['level']) {
						$items[] = $leaf[$treeField];
					}
				}
				$parentCats = implode(" ", $items);
				$rfld = sanitizeForUrl($parentCats);
				if ($debug && $parentCats) {
					echo "++ PARENT INFO ++";
					dmp($parentCats);
				}
				$fld = '$parentCats';
			} else if ($fldParts[0] == "txpvar") {
				if (count($fldParts) > 1) {
					$rfld = $fldParts[1];
					$fld = '$variable["'.$rfld.'"]';
				}
			} else if ($fldParts[0] == "urlvar") {
				if (count($fldParts) > 1) {
					$rfld = $fldParts[1];
					$fld = '$_GET["'.$rfld.'"]';
				}
			} else if ($fldParts[0] == "postvar") {
				if (count($fldParts) > 1) {
					$rfld = $fldParts[1];
					$fld = '$_POST["'.$rfld.'"]';
				}
			} else if ($fldParts[0] == "svrvar") {
				if (count($fldParts) > 1) {
					$rfld = $fldParts[1];
					$fld = '$_SERVER["'.$rfld.'"]';
				}
			} else if ($fldParts[0] == "phpvar") {
				if (count($fldParts) > 1) {
					$rfld = $fldParts[1];
					$fld = '$GLOBALS["'.$rfld.'"]';
				}
			} else if ($fldParts[0] == 'article') {
				$rfld = strtolower($fldParts[1]);
				$fld = '$thisarticle["'.$rfld.'"]';
			} else if (isset($thisarticle[$fldParts[0]])) {
				$rfld = strtolower($fldParts[0]);
				$fld = '$thisarticle["'.$rfld.'"]';
			} else if ($fldParts[0] == "NULL") {
				$smd_if_var = '';
				$fld = '$smd_if_var';
				$rfld = "NULL";
			} else {
				$smd_if_var = $fldParts[0];
				$fld = '$smd_if_var';
				$rfld = "field".($idx*1+1);
			}
			$rlfld = $var_prefix."len_".$rfld;
			$rcfld = $var_prefix."count_".$rfld;
			$rfld = $var_prefix.$rfld;

			// Take a copy of $fld to use in any isset() requests
			$fldClean = $fld;

			// Apply user-defined field filters
			if ($killSpaces) {
				$fld = 'preg_replace("/\s+/","",'.$fld.')';
			}
			if ($stripFld) {
				$fld = 'trim(strip_tags('.$fld.'))';
			}
			if ($trimFld) {
				$fld = 'trim('.$fld.')';
			}
			if ($escapeFld) {
				$fld = 'htmlentities('.$fld.')';
			}
			if ($escapeAllFld) {
				$fld = 'htmlentities('.$fld.', ENT_QUOTES)';
			}
			$do_ffilt = ($pat && in_array('field', $filter_on) && (in_array($fldParts[0], $filter_type) || in_array('all', $filter_type)) ) ? true : false;

			// Find the real value to compare against (may be another field)
			$valcnt = 1;
			$vflds = array();
			$core_vfld = "val".(($idx*1)+1);

			foreach ($valRep as $jdx => $valParts) {
				$stripVal = ((count($valParts) > 1) && (in_array("NOTAGS", $valParts))) ? true : false;
				$trimVal = ((count($valParts) > 1) && (in_array("TRIM", $valParts))) ? true : false;
				$escapeVal = ((count($valParts) > 1) && (in_array("ESC", $valParts))) ? true : false;
				$escapeAllVal = ((count($valParts) > 1) && (in_array("ESCALL", $valParts))) ? true : false;
				$numValParts = count($valParts);
				if ($valParts[0] == "urlvar") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($_GET[$vfld]) && $_GET[$vfld] != "") ? '$_GET["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "postvar") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($_POST[$vfld]) && $_POST[$vfld] != "") ? '$_POST["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "svrvar") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($_SERVER[$vfld]) && $_SERVER[$vfld] != "") ? '$_SERVER["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "txpvar") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($variable[$vfld]) && $variable[$vfld] != "") ? '$variable["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "phpvar") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($GLOBALS[$vfld]) && $GLOBALS[$vfld] != "") ? '$GLOBALS["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "file") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($thisfile[$vfld]) && $thisfile[$vfld] != "") ? '$thisfile["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "link") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($thislink[$vfld]) && $thislink[$vfld] != "") ? '$thislink["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "image") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($thisimage[$vfld]) && $thisimage[$vfld] != "") ? '$thisimage["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "pretext") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($pretext[$vfld]) && $pretext[$vfld] != "") ? '$pretext["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if ($valParts[0] == "article") {
					if ($numValParts > 1) {
						$vfld = $valParts[1];
						$val = (isset($thisarticle[$vfld]) && $thisarticle[$vfld] != "") ? '$thisarticle["'.$vfld.'"]' : doQuote(str_replace('"', '\"', $vfld));
					}
				} else if (strpos($valParts[0], "?") === 0) {
					$valParts[0] = substr(strtolower($valParts[0]), 1);
					$vfld = $valParts[0];
					if (isset($thisfile[$vfld]) && $thisfile[$vfld] != "") {
						$val = '$thisfile["'.$vfld.'"]';
					} else if (isset($thislink[$vfld]) && $thislink[$vfld] != "") {
						$val = '$thislink["'.$vfld.'"]';
					} else if (isset($thisimage[$vfld]) && $thisimage[$vfld] != "") {
						$val = '$thisimage["'.$vfld.'"]';
					} else if (array_key_exists($vfld, $allPtxt) && $allPtxt[$vfld] != "") {
						$val = $allPtxt[$vfld];
					} else if (isset($thisarticle[$vfld]) && $thisarticle[$vfld] != "") {
						$val = '$thisarticle["'.$vfld.'"]';
					} else {
						$val = doQuote(str_replace('"', '\"', $vfld));
					}
				} else {
					$vfld = $core_vfld.'_'.$valcnt;
					$val = doQuote(str_replace('"', '\"', $valParts[0]));
				}

				// Apply user-defined value filters
				if ($stripVal) {
					$val = 'trim(strip_tags('.$val.'))';
				}
				if ($trimVal) {
					$val = 'trim('.$val.')';
				}
				if ($escapeVal) {
					$val = 'htmlentities('.$val.')';
				}
				if ($escapeAllVal) {
					$val = 'htmlentities('.$val.', ENT_QUOTES)';
				}
				$do_vfilt = ($pat && in_array('value', $filter_on) && (in_array($valParts[0], $filter_type) || in_array('all', $filter_type)) ) ? true : false;

				// Replace the string parts by evaluating any variables...
				$filt_fld = ($do_ffilt) ? "preg_replace('$pat', '$rep', $fld)" : $fld;
				$filt_val = ($do_vfilt) ? "preg_replace('$pat', '$rep', $val)" : $val;
				eval ("\$valRep[$jdx] = ".$filt_val.";");

				// Only add sub-values to the replacements array if there's more than one sub-value
				if (count($valRep) > 1) {
					$vflds[$var_prefix.$vfld] = $valRep[$jdx];
					$vflds[$var_prefix."len_".$vfld] = strlen($valRep[$jdx]);
				}

				$valcnt++;
			}

			$joinedVals = join($list_delim, $valRep);
			$smd_prefilter = doQuote($joinedVals);

			// Add the combined operator for backwards compatibility with plugin v0.8x
			$vflds[$var_prefix.$core_vfld] = $joinedVals;
			$vflds[$var_prefix."len_".$core_vfld] = strlen($joinedVals);

			$cmd = str_replace("CAST", $cast, $op);
			$cmd = ($length) ? str_replace("FIELD", $length, $cmd) : $cmd;
			$cmd = ($count) ? str_replace("FIELD", $count, $cmd) : $cmd;
			$cmd = str_replace("FIELD", (($case_sensitive) ? $filt_fld : 'strtolower('.$filt_fld.')'), $cmd);
			$cmd = str_replace("VARNAME", $fldClean, $cmd);
			$cmd = str_replace("VALUE", (($case_sensitive) ? 'VALUE' : 'strtolower(VALUE)'), $cmd);

			// Value replacements have already been run through evil() so they can be assigned directly
			foreach ($vflds as $valit => $valval) {
				$replacements['{'.$valit.'}'] = $valval;
			}

			// Field replacements need some eval() action...
			$cmd = "@\$replacements['{".$rfld."}'] = ".$filt_fld."; \n@\$replacements['{".$rlfld."}'] = strlen(".$filt_fld."); \n@\$replacements['{".$rcfld."}'] = count(explode('".$list_delim."', ".$filt_fld.")); \n\$out[".$idx."] = (".str_replace("VALUE", (($smd_prefilter==="''" && strpos($op, "strpos") !== false) ? "' '" : $smd_prefilter), $cmd).") ? 'true' : 'false';";
			if ($debug) {
				dmp($cmd);
			}
			// ... and evaluate the expression
			eval($cmd);
		}
	}
	if ($debug) {
		echo "RESULT:";
		dmp($out);
		echo "REPLACEMENTS:";
		dmp($replacements);
	}
	if ($debug > 2) {
		echo "PRETEXT:";
		dmp($pretext);
		echo "THIS ARTICLE:";
		dmp($thisarticle);
		echo "THIS FILE:";
		dmp($thisfile);
		echo "THIS LINK:";
		dmp($thislink);
		echo "THIS IMAGE:";
		dmp($thisimage);
	}
	// Check logic
	$result = ($out) ? true : false;
	if (strtolower($logic) == "and" && in_array("false", $out)) {
		$result = false;
	}
	if (strtolower($logic) == "or" && !in_array("true", $out)) {
		$result = false;
	}

	return parse(EvalElse(strtr($thing, $replacements), $result));
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
#smd_help { line-height:1.5 ;}
#smd_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #f0e68c; color:#333; }
#smd_help code.block { font-weight:normal; border:1px dotted #999; display:block; margin:10px 10px 20px; padding:10px; }
#smd_help h1 { font: 22px Georgia, serif; margin: 0; text-align: center; }
#smd_help h2 { border-bottom: 1px solid black; padding:10px 0 0; font: 18px Georgia, serif; }
#smd_help h3 { font: bold 13px Georgia, serif; letter-spacing: 1px; margin: 20px 0 0; text-decoration:underline; }
#smd_help h4 { font: bold 12px Georgia, serif; letter-spacing: 1px; margin: 10px 0 0; text-transform: uppercase; }
#smd_help .atnm { font-weight:bold; }
#smd_help .mand { background:#eee; border:1px dotted #999; }
#smd_help table { width:90%; text-align:center; padding-bottom:1em; border-collapse:collapse; }
#smd_help td, #smd_help th { border:1px solid #999; padding:.5em; }
#smd_help ul { list-style-type:square; }
#smd_help .important { color:red; }
#smd_help li { margin:5px 20px 5px 30px; }
#smd_help .break { margin-top:5px; }
#smd_help dl dd { margin:2px 15px; }
#smd_help dl dd:before { content: "\2022\00a0"; }
#smd_help dl dd dl { padding: 0 15px; }
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="smd_help">

	<h1>smd_if</h1>

	<p>A generic &#8216;if condition&#8217; tester. Can test any field or variable in the current article, file, image, link, <span class="caps">URL</span> var, <code>&lt;txp:variable /&gt;</code> or <span class="caps">PHP</span> context for a variety of attributes and take action if <span class="caps">TRUE</span> or <span class="caps">FALSE</span>.</p>

	<h2>Features</h2>

	<ul>
		<li>Supports most major article, file, image and link variables such as section, category, custom fields, id, query string, author, body, excerpt, yahde yahde, plus url vars, server vars, txp vars, php vars, and sub-category/parent checking</li>
		<li>Tests include equality, inequality, less than, greater than, divisible by, empty, used, defined, begins, ends, contains, is numeric / alpha / alphanumeric / lowercase / uppercase, among others</li>
		<li>Tests for more than one condition at once and applies either <span class="caps">AND</span> logic (all tests must pass) or OR logic (any test must pass)</li>
		<li>All tested fields and values are available to the container so you can display them</li>
		<li>Custom regular-expression filters are available to help weed out bad data</li>
		<li>Ugly and very dirty. Uses <span class="caps">PHP</span>&#8217;s <code>eval()</code> command which most programmers concur should be renamed <code>evil()</code></li>
	</ul>

	<h2>Installation / Uninstallation</h2>

	<p>Download the plugin from either <a href="http://textpattern.org/plugins/930/smd_if">textpattern.org</a>, or the <a href="http://stefdawson.com/sw">software page</a>, paste the code into the Txp <em>Admin-&gt;Plugins</em> pane, install and enable the plugin. Visit the <a href="http://forum.textpattern.com/viewtopic.php?id=25357">forum thread</a> for more info or to report on the success or otherwise of the plugin.</p>

	<p>To uninstall, simply delete from the <em>Admin-&gt;Plugins</em> page.</p>

	<h2>Usage</h2>

	<p>Use the tag in any page, form or article context. Can also be used inside file, image, or link lists to take action depending on attributes of the current item.</p>

	<h3 class="tag">smd_if</h3>

	<p class="tag-summary">At the place you wish to compare a field with another value, put this tag with at least the <code>field</code> attribute. Specify each field you wish to test as a series of comma-separated items &#8212; though the comma is overridable with <code>param_delim</code>. If the result of all the comparison tests is <span class="caps">TRUE</span> the contained block will be executed. If the result is <span class="caps">FALSE</span>, any <code>&lt;txp:else /&gt;</code> will be executed instead. Without any <code>&lt;txp:else /&gt;</code> block, nothing is output if the result is <span class="caps">FALSE</span>.</p>

	<p><strong>Attributes</strong></p>

	<dl>
		<dt><span class="atnm mand">field</span></dt>
		<dd>Comma-separated list of <a href="#field">fields</a> to test.</dd>
		<dt><span class="atnm">operator</span></dt>
		<dd>Comma-separated list of <a href="#operator">operations</a> for comparison with the corresponding field (e.g. <code>eq</code>, <code>not</code>, <code>begins</code>, etc).</dd>
		<dd>Default: <code>eq</code> for most tests, <code>contains</code> for parent tests</dd>
		<dt><span class="atnm">value</span></dt>
		<dd>Comma-separated list of <a href="#value">values</a> with which to compare the corresponding fields.</dd>
		<dt><span class="atnm">logic</span></dt>
		<dd>How multiple tests are joined: Choose from:
	<dl>
		<dd><code>and</code>: all conditions must be met for a <span class="caps">TRUE</span> result.</dd>
		<dd><code>or</code>: any of the conditions that match will give a <span class="caps">TRUE</span> result.</dd>
	</dl></dd>
		<dd>Default: <code>and</code></dd>
		<dt><span class="atnm">case_sensitive</span></dt>
		<dd>Whether to perform case-sensitive comparisons. Note that if using <code>islower</code> or <code>isupper</code> in a comparison, case sensitivity will automatically be switched on while the test is taking place. Values:
	<dl>
		<dd>1 = yes</dd>
		<dd>0 = no</dd>
	</dl></dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">filter</span></dt>
		<dd>List of regular expressions with which to filter one or more fields/values. See <a href="#filtering">filtering</a>.</dd>
		<dt><span class="atnm">replace</span></dt>
		<dd>List of items with which to replace each of the matching filters.</dd>
		<dt><span class="atnm">filter_type</span></dt>
		<dd>Limit the filter to certain types of test. Choose from any of:
	<dl>
		<dd><code>all</code></dd>
		<dd><code>urlvar</code></dd>
		<dd><code>postvar</code></dd>
		<dd><code>svrvar</code></dd>
		<dd><code>txpvar</code></dd>
		<dd><code>phpvar</code></dd>
		<dd>or any field name</dd>
	</dl></dd>
		<dd>Default: <code>all</code></dd>
		<dt><span class="atnm">filter_on</span></dt>
		<dd>Apply the filter to either the <code>field</code>, the <code>value</code> or both. Please see the <a href="#fcaveat">filtering caveat</a> below for important information about this attribute.</dd>
		<dd>Default: <code>field</code></dd>
		<dt><span class="atnm">param_delim</span></dt>
		<dd>Delimiter used between each field, operator or value in a multi-test. You normally only need to change this if you have used that character in the name of a custom field, for example.</dd>
		<dd>Default:  comma (,)</dd>
		<dt><span class="atnm">mod_delim</span></dt>
		<dd>Delimiter used when specifying field or operator modifiers for:
	<dl>
		<dd>urlvar, postvar, svrvar, txpvar</dd>
		<dd>parent from <code>LVL</code> and <code>CAT</code></dd>
		<dd>separating an operator from the <span class="caps">NUM</span> or <span class="caps">NOSPACE</span> modifiers</dd>
	</dl></dd>
		<dd>Default: colon (:)</dd>
		<dt><span class="atnm">list_delim</span></dt>
		<dd>Delimiter used when specifying a list of values to check via the <code>in</code>, <code>notin</code>, <code>between</code> and <code>range</code> operators.</dd>
		<dd>Default: forward-slash (/)</dd>
		<dt><span class="atnm">var_prefix</span></dt>
		<dd>Prefix any replacement variable names with this string. If nesting smd_if tags, you will probably need to specify this on any inner smd_if tags to prevent the {replacement} variables clashing.</dd>
		<dd>Default: <code>smd_if_</code></dd>
	</dl>

	<p>The lists are processed in order, i.e. the 1st field uses the 1st operator in the list and compares it to the 1st value; the 2nd field uses the 2nd operator and compares it to the 2nd value, and so on. Values should usually be specified with placeholders to maintain order: e.g. <code>value=&quot;, 4,, top&quot;</code></p>

	<p>Note that, although the first three attributes are usually mandatory, if you happen to require the default operator for all your tests, you can safely omit <code>operator</code>. Similarly with values: if you are entirely testing the existence or type of variables, you can omit the <code>value</code> parameter if you wish. And if you are testing the same <code>field</code> again and again for differing conditions, you can list it just once as a convenient shortcut.</p>

	<h3 class="atnm " id="field">field</h3>

	<p>List of field locations to look at. A non-exhaustive list of some useful values are:</p>

	<ul>
		<li><code>s</code> (global section) or <code>section</code> (article section)</li>
		<li><code>c</code> (global category) or <code>category1</code>, or <code>category2</code> if on an article page</li>
		<li><code>authorid</code> or <code>author</code></li>
		<li><code>id</code> (file/link/image/article ID). In an individual article context, use <code>thisid</code> instead</li>
		<li><code>query</code> (the query string from the search form)</li>
		<li><code>pg</code> (the current page number in lists)</li>
		<li><code>month</code> (current month field from the address bar)</li>
		<li><code>status</code> (document status: 200, 404, 403, etc)</li>
		<li><code>page</code> (the Txp Page template used by this section)</li>
		<li><code>next_id</code> / <code>prev_id</code> (ID of next/prev document if on article page)</li>
		<li><code>next_title</code> / <code>prev_title</code>(Title of next/prev document if on article page)</li>
		<li><code>next_utitle</code> / <code>prev_utitle</code> (url-title of next/prev document if on article page)</li>
		<li><code>permlink_mode</code> (take action based on one of the messy/clean <span class="caps">URL</span> schemes)</li>
		<li><code>other article/file/link field</code> (e.g. body, excerpt, article_image, keywords, linkanme, filename, downloads, &#8230;)</li>
		<li><code>my_custom_field_name</code> (any custom fields you have defined)</li>
		<li><code>urlvar:var_name</code> (any variable in the address bar after the <code>?</code>)</li>
		<li><code>postvar:var_name</code> (any variable posted from an <span class="caps">HTML</span> form)</li>
		<li><code>svrvar:var_name</code> (any standard server variable, e.g. <span class="caps">HTTP</span>_USER_AGENT)</li>
		<li><code>txpvar:my_var</code> (any Textpattern variable set with <code>&lt;txp:variable name=&quot;my_var&quot; /&gt;</code>)</li>
		<li><code>phpvar:my_var</code> (any <span class="caps">PHP</span> variable <em>in the global scope</em>)</li>
		<li><code>parent:optional_modifiers</code> (whether the given category is a descendent of another category)</li>
		<li><code>NULL</code> (useful when comparing arbitrary values for emptiness)</li>
	</ul>

	<p>If you specify a field name that does not exist, the text you use will be taken verbatim in most cases.</p>

	<p>To avoid ambiguity you can prefix the field name with one of <code>pretext</code>, <code>article</code>, <code>image</code>, <code>link</code> or <code>file</code>, separating it from the field by <code>param_delim</code>.</p>

	<p>If you wish to compare a field that might contain <span class="caps">HTML</span> (e.g <code>body</code>), add the modifier <code>:NOTAGS</code> to the end of the field. It will have its <span class="caps">HTML</span> and <span class="caps">PHP</span> tags stripped from it and will also be trimmed to remove leading and trailing spaces. You may choose to solely remove spaces from both the start and end of any field by adding <code>:TRIM</code> to the end of the field.</p>

	<p>If you suspect a field might contain <span class="caps">HTML</span>ish input like <code>&lt;</code>, <code>&gt;</code>, <code>&amp;</code> or quotes/apostrophes you can elect to convert them to entities like <code>&amp;lt;</code>, <code>&amp;quot;</code>, etc. Just specify the <code>:ESC</code> modifier to replace everything except apostrophes with its entity equivalent, or use <code>:ESCALL</code> to include apostrophes.</p>

	<p>The special field <code>parent</code> checks the parent category for a match. Unlike the other field types, the default <a href="#operator">operator</a> for parent is &#8216;contains&#8217;. This is because the entire tree is checked for a match, starting from the top of the tree down to the current category. Internally, the plugin makes up a &#8220;breadcrumb trail&#8221; of categories in the current branch, each separated by a space, so testing for equality would require putting them all in the <a href="#value">value</a> parameter.</p>

	<p>You are of course free to choose an alternative operator; <code>begins</code> is very useful for testing if the top level category matches the one given in the <code>value</code> field.</p>

	<p>If you use <code>parent:LVLn</code>, the comparison will be restricted to that &#8220;level&#8221; of sub-category; LVL1 is the &#8220;top&#8221; level, LVL2 is the next sub-category level, and so on. When using these modifiers, the &#8216;eq&#8217; operator becomes more useful because you are comparing a single parent category.</p>

	<p>If you wish to compare against the category&#8217;s title instead of its name, add the <code>:TTL</code> modifier. To test the number of children the given category has, specify <code>:KIDS</code>. Note that <span class="caps">TTL</span> and <span class="caps">KIDS</span> are mutually exclusive and if they are both employed, the last one used takes priority.</p>

	<p>When using articles, you can further modify the behaviour of the parent using the <code>CATn</code> syntax (where &#8216;n&#8217; is 1 or 2). Specifying &#8220;parent&#8221; without <code>CATn</code> will use the global category (<code>?c=</code>). If you add <code>:CATn</code> it will instead compare the article&#8217;s category1 or category2 respectively.</p>

	<p>You can use <span class="caps">CAT</span>, <span class="caps">LVL</span> and <span class="caps">TTL</span>/KIDS in combination, independently or not at all. This allows comparisons such as &#8220;if the 2nd sub-category of category1 equals blahblah&#8221; or &#8220;if category2 is a child of blahblah&#8221;. See <a href="#eg4">Example 4</a>.</p>

	<p>One other special field is <code>NULL</code>. This is exactly what it says it is: empty. The reason for its inclusion is that sometimes you wish to test something that isn&#8217;t a true variable &#8212; e.g. a replacement variable from smd_vars or smd_each &#8212; to see if it&#8217;s empty or not.</p>

	<p>If you were to put this:</p>

	<p><code>&lt;txp:smd_if field=&quot;{result}&quot; operator=&quot;isempty&quot;&gt;</code></p>

	<p>you would not get the result you expect (it&#8217;s pretty esoteric but it revolves around the fact that <code>&quot;&quot;</code> (as a variable name) is not empty, it&#8217;s invalid). To get round this you may use <span class="caps">NULL</span> as a placeholder and move the thing you want to check into the <code>value</code> instead, e.g:</p>

	<p><code>&lt;txp:smd_if field=&quot;NULL&quot; operator=&quot;eq&quot; value=&quot;{result}&quot;&gt;</code></p>

	<p>will test the <span class="caps">NULL</span> object (i.e. &#8216;emptiness&#8217;) to see if it&#8217;s equal to the <code>{result}</code> replacement variable. You can use similar logic to test for optional variables by swapping the field and value, like this:</p>

	<p><code>&lt;txp:smd_if field=&quot;7&quot; operator=&quot;gt&quot; value=&quot;{result}&quot;&gt;</code></p>

	<p>That would see if the replacement variable <code>{result}</code> was less than or equal to 7 (that&#8217;s not a typo, the logic is reversed in this case: it is interpreted as: &#8220;is 7 greater than {result}&#8221;, which is the same as &#8220;is {result} less than or equal to 7&#8221;!)</p>

	<h3 class="atnm " id="operator">operator</h3>

	<p>List of operators to apply, in order, to each field. Choose from:</p>

	<ul>
		<li><code>eq</code> Equal (the default for all except &#8216;parent&#8217;)</li>
		<li><code>not</code> Not equal</li>
		<li><code>lt</code> Less than</li>
		<li><code>gt</code> Greater than</li>
		<li><code>le</code> Less than or equal to</li>
		<li><code>ge</code> Greater than or equal to</li>
		<li><code>in</code> Field is one of a list of values (use <code>list_delim</code> to separate values)</li>
		<li><code>notin</code> Field is not one of the given list of values (use <code>list_delim</code> to separate values)</li>
		<li><code>divisible</code> Field is exactly divisible by the value</li>
		<li><code>between</code> Field lies between the given values, exclusive (use <code>list_delim</code> to separate values)</li>
		<li><code>range</code> Field is within the range of given values, inclusive (use <code>list_delim</code> to separate values)</li>
		<li><code>begins</code> Field begins with a sequence of characters</li>
		<li><code>contains</code> Field contains a sequence of characters (default for &#8216;parent&#8217;)</li>
		<li><code>ends</code> Field ends with a sequence of characters</li>
		<li><code>isempty</code> Field is empty (contains nothing)</li>
		<li><code>isused</code> Field has some value</li>
		<li><code>defined</code> Field is set (useful with urlvar variables)</li>
		<li><code>undefined</code> Field is not set, or missing from the <span class="caps">URL</span> line</li>
		<li><code>isnum</code> Field is a number</li>
		<li><code>isalpha</code> Field contains characters only</li>
		<li><code>isalnum</code> Field contains alphanumeric characters only</li>
		<li><code>islower</code> Every character in the field is lower case</li>
		<li><code>isupper</code> Every character in the field is upper case</li>
		<li><code>ispunct</code> Every character in the field is some punctuation mark</li>
		<li><code>isspace</code> Every character in the field is a whitespace character (or tab, newline, etc)</li>
	</ul>

	<p>With the comparison operators (primarily gt, lt, ge, le) you may find odd behaviour when comparing numbers. For example, <code>urlvar:pic gt 6</code> will return <span class="caps">TRUE</span> if <code>pic</code> is set to &#8220;fred&#8221;. This is because the word &#8220;fred&#8221; (or at least the &#8220;character f&#8221;) is greater in the <span class="caps">ASCII</span> table than the &#8220;character 6&#8221;.</p>

	<p>To circumvent this problem, you may append <code>:NUM</code> to the end of any of these operators to force the plugin to check that the values are integers.</p>

	<p>If you wish to compare the length of a field, append <code>:LEN</code> to any of the numerical comparison operators. It doesn&#8217;t make sense to use both <code>:NUM</code> and <code>:LEN</code> together, so they are mutually exclusive.</p>

	<p>If you wish to compare the quantity (count) of a list of items in a field, append <code>:COUNT</code> to any of the numerical comparison operators. Again, this option is mutually exclusive with all other modifiers.</p>

	<p>There is a subtle difference between the operators <code>between</code> and <code>range</code>; the former does not include the endpoint values you specify whereas the latter does. For example, the value <code>10</code> is in the range <code>10/20</code> but it is <em>not</em> between <code>10/20</code>. The value <code>11</code>, however, satisfies both. You can use the <code>:NUM</code> modifier for these two operators, though most tests will work fine without it, and you may check if list <code>:COUNT</code> values lie within two endpoints. You can also compare non-integer values with these operators.</p>

	<p>The <code>begins</code>, <code>ends</code> and <code>contains</code> operators, along with any of the <code>is</code> operators (except <code>isspace</code>), can take an extra parameter as well. Since they compare every character against the given behaviour, space characters can mess things up a bit. For example <code>field=&quot;custom1&quot; operator=&quot;islower&quot;</code> will fail if custom1 contains &#8220;this is a test&#8221;. Or comparing something to <code>body</code> can fail because the body often starts with a number of space characters. To circumvent this, add <code>:NOSPACE</code> to the operator which will remove all spaces from the string before testing it.</p>

	<p>Note also that while <code>defined</code> and <code>undefined</code> differ semantically from <code>isused</code> and <code>isempty</code> (respectively), the way Txp assigns variables means that, for the most part, the terms are interchangeable. When dealing with urlvars, postvars and svrvars, the two sets of operators behave independently, as you would expect. See <a href="#eg5">Example 5</a> for more. Neither <code>defined</code> nor <code>undefined</code> make sense with <code>parent</code>, so they are forbidden.</p>

	<h3 class="atnm " id="value">value</h3>

	<p>List of values to compare each field in turn to. Can be static values/text or the name of any Txp field, like those given in <a href="#field">field</a> (except &#8220;parent&#8221;).</p>

	<p>To distinguish a Txp field from static text, prefix the field name with <code>?</code>. For example: <code>value=&quot;title&quot;</code> will compare your chosen field against the word &#8220;title&#8221;, whereas <code>value=&quot;?title&quot;</code> will compare your field against the current article&#8217;s title.</p>

	<p>If you wish to compare a value that might contain <span class="caps">HTML</span> (e.g <code>?body</code>), add the modifier <code>:NOTAGS</code> to the end of the value. It will have any <span class="caps">HTML</span> and <span class="caps">PHP</span> tags stripped from it and will also be trimmed to remove leading and trailing spaces. You may choose to solely remove spaces from both the start and end of any value by adding <code>:TRIM</code> to the end of the value.</p>

	<p>If you suspect a value might contain <span class="caps">HTML</span>ish input like <code>&lt;</code>, <code>&gt;</code>, <code>&amp;</code> or quotes/apostrophes you can elect to convert them to entities like <code>&amp;gt;</code>, <code>&amp;quot;</code>, etc. Just specify the <code>:ESC</code> modifier to replace everything except apostrophes with its entity equivalent, or use <code>:ESCALL</code> to include apostrophes.</p>

	<p>Note that you may find using double-quotes in fields gives unexpected results. They are best avoided, or worked around by using <code>contains</code> instead of <code>eq</code>.</p>

	<h3>Replacement tags</h3>

	<p>Every field or value that you refer to in your smd_if tag becomes available within the containing block so you can display its contents if you wish. Most of the time this is not much use but it can be very useful with the <code>in</code> operator or the <code>:LEN</code> modifier. For instance, if you have asked smd_if to test the <span class="caps">URL</span> variable named &#8216;level&#8217; and told it to compare it to the custom field labelled &#8216;allowable_levels&#8217;, two tags become available which you can use within the containing block:</p>

	<ul>
		<li><code>{smd_if_level}</code> would display the value of the &#8216;level&#8217; <span class="caps">URL</span> variable.</li>
		<li><code>{smd_if_allowable_levels}</code> would display the contents of the current article&#8217;s custom field.</li>
	</ul>

	<p>By default the replacement tags are prefixed with <code>smd_if_</code> so they don&#8217;t clash with the ones in smd_gallery (for example, when using smd_if inside an smd_gallery tag). You can change this prefix with the <code>var_prefix</code> attribute.</p>

	<p>If you are comparing a fixed-value field (such as <code>field=&quot;NULL&quot;</code> or <code>value=&quot;12&quot;</code> or smd_gallery&#8217;s <code>value=&quot;{category}&quot;</code>) the name of the replacement tags are <code>{smd_if_fieldN}</code> for fields and <code>{smd_if_valN}</code> for values, where N is the test number starting from 1.</p>

	<p>If you use the multiple value options such as <code>in</code>, <code>between</code>, <code>range</code>, etc you will also see replacement tags of the following format: <code>{smd_if_valN_X}</code> where N is the value counter (as above) and X is an incrementing number; with one for each value in your list. For example, <code>value=&quot;10/20/30&quot;</code> sets:</p>

	<ul>
		<li><code>{ smd_if_val1_1 }</code> = 10</li>
		<li><code>{ smd_if_val1_2 }</code> = 20</li>
		<li><code>{ smd_if_val1_3 }</code> = 30</li>
	</ul>

	<p>There are also &#8216;length&#8217; replacement tags. Following a similar convention to above, these are prefixed with <code>smd_if_len_</code>. If you get stuck, temporarily switch <code>debug=&quot;1&quot;</code> on to see the replacements available and their associated names/values.</p>

	<p>See <a href="#eg8">Example 8</a> and <a href="#eg9">9</a> for more.</p>

	<h2 id="filtering">Filtering</h2>

	<p>All user input is tainted by default.</p>

	<p>Any time you rely on someone to enter something, at least one person will invariably catch you out; either accidentally or maliciously. For this reason, smd_if supports powerful filtering rules so you can trap and remove suspect input. It already does some of this for you with the <span class="caps">NOTAGS</span> modifier, but filtering gives another level of control above that.</p>

	<p>Let&#8217;s say you are asking the user to enter a value and you need to compare it to a range. What if, instead of a number, they entered <code>fr3d</code> or <code>;rm -rf 1*;</code>. The plugin might just fall over (probably with an error, which is not very pleasant for users), or it might cause harm to the filesystem if a person trying to hack your site was skilled enough (it&#8217;s unlikely, but possible).</p>

	<p>In both cases, filtering comes to the rescue. You can specify that you <em>expect</em> only digits from 0 to 9 in your fields and can tell the plugin to chop out anything that is not numeric. So in the first case above, all you would see if the user entered &#8216;fr3d&#8217; would be &#8216;3&#8217;.</p>

	<p>Fortunately &#8212; and unfortunately &#8212; it&#8217;s built around <a href="http://www.regular-expressions.info/">regular expressions</a> which are fiendishly powerful but can also be fiendishly tricky to learn if you are not familiar. They are worth learning.</p>

	<p>Let&#8217;s dive in at the deep end and tell the plugin that, under no circumstances, must we allow any input that is non-numeric:</p>

<pre class="block"><code class="block">&lt;txp:variable name=&quot;highest&quot; value=&quot;100&quot; /&gt;
&lt;txp:smd_if field=&quot;urlvar:low&quot; operator=&quot;ge, le&quot;
     value=&quot;1, txpvar:highest&quot; filter=&quot;/[^0-9]+/&quot;&gt;
   &lt;p&gt;{smd_if_low} is valid and is between
   {smd_if_val1} and {smd_if_highest}.&lt;/p&gt;
&lt;txp:else /&gt;
   &lt;p&gt;Sorry, the value {smd_if_low} is not within the
     range {smd_if_val1}-{smd_if_highest}.&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>[ Eagle-eyed people may notice that something similar can be achieved with the <code>:NUM</code> modifier. The difference here is that the replacement variables are also filtered, whereas with <code>:NUM</code> they contain the original (possibly invalid) input ]</p>

	<p>Although out of the scope of this documentation, it&#8217;s worth just taking a moment to see what the filter is doing:</p>

<pre><code>/[^0-9]+/
</code></pre>

	<ul>
		<li>The forward slashes are start and end delimiters and should always be present (unless you know what you&#8217;re doing!)</li>
		<li>The square brackets <code>[]</code> denote a character class, or group of characters. In this case they contain the range of digits 0 to 9</li>
		<li>The circumflex <code>^</code> negates the class (i.e. non-digits)</li>
		<li>The plus <code>+</code> means &#8216;one or more of the things I&#8217;ve just seen&#8217;</li>
	</ul>

	<p>So putting it all together, it reads &#8220;Find every occurrence of one or more non-digits&#8221;. Thus it looks at every field and finds anything non-numeric. Then it replaces whatever it finds with <code>&#39;&#39;</code>, i.e. nothing, nada, zip. Effectively, it deletes whatever non-digits it finds and leaves the good stuff (the numbers) behind.</p>

	<h3 id="freplacing">Replacing bad data</h3>

	<p>For each matching filter there&#8217;s an equivalent <code>replace</code> string. By default this is set to <code>replace=&quot;&quot;</code> which means &#8220;replace whatever you find with nothing&#8221;; or in other words &#8220;delete everything that matches the filter&#8221;. You may elect to replace your filtered data with something else, say, <code>replace=&quot;txp&quot;</code>. So if someone entered &#8220;fr3d&#8221; you would see the replacement variable has a value &#8220;txp3txp&#8221; (the reason you only get one &#8216;txp&#8217; before the number is due to the expression being <em>greedy</em> and gobbling up as many characters as it can in a group before replacing them. See any regex tutorial for more on this topic).</p>

	<p>Under normal circumstances you won&#8217;t want to mess with <code>replace</code> as it&#8217;ll do what you want with the default &#8216;delete&#8217; operation.</p>

	<h3 id="foptions">Filtering options</h3>

	<p>By default, the plugin only looks at <code>field</code> data. If you wish to change that, use the <code>filter_in</code> attribute.</p>

	<p>The plugin also applies the filter to all fields (<code>filter_type=&quot;all&quot;</code>). You may wish to only target a filter at url and server vars, in which case you would specify <code>filter_type=&quot;urlvar, svrvar&quot;</code>. Or maybe you wish to validate the article image field in case someone entered some rogue data there: <code>filter_type=&quot;article_image&quot;</code>.</p>

	<h3 id="fcaveat">Filtering caveat</h3>

	<p>If you specify <code>filter_on=&quot;field, value&quot;</code> it is important to note that the <em>same filter</em> will be applied to each corresponding field and value. If your filter is too strict there&#8217;s a chance it may filter every character out of both <code>field</code> and <code>value</code>, thus if your test was for equality the test would return &#8216;true&#8217;. Here&#8217;s an example:</p>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:comp1&quot; operator=&quot;eq&quot;
     value=&quot;urlvar:comp2&quot; filter=&quot;/[^a-zA-Z]+/&quot;
     filter_on=&quot;field, value&quot;&gt;
   // I&#39;m NOT necessarily valid
&lt;/txp:smd_if&gt;
</code></pre>

	<p>If your user typed in the <span class="caps">URL</span> <code>site.com/article?comp1=12345&amp;comp2=67890</code> the plugin would do the following:</p>

	<ol>
		<li>Filter comp1 and remove all non-letter characters</li>
		<li>Filter comp2 and remove all non-letter characters</li>
		<li>Look at comp1 (which is now empty) and comp2 (which is also empty), then compare them</li>
	</ol>

	<p>You can see what&#8217;s going to happen: the test result is going to be &#8216;true&#8217; because &#8220;nothing&#8221; does indeed equal &#8220;nothing&#8221;. So the act of the user entering two nonsensical, completely numeric strings of data has broken your logic.</p>

	<p>For this reason, if you are filtering on both field and value, you should perform <em>additional</em> tests to see if either field / value is set at all. This is better:</p>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:comp1, urlvar:comp1, urlvar:comp2&quot;
     operator=&quot;eq, isused, isused&quot; value=&quot;urlvar:comp2&quot;
     filter=&quot;/[^a-zA-Z]+/&quot;
     filter_on=&quot;field, value&quot;&gt;
   // I&#39;m now actually valid
&lt;/txp:smd_if&gt;
</code></pre>

	<p>So now, if your filter removes everything from both <span class="caps">URL</span> vars, it still fails the &#8216;has the user entered anything at all&#8217; tests because as far as the plugin is concerned, the visitor has submitted rubbish.</p>

	<h3 id="ffurther">Going further</h3>

	<p>The above examples all use a single filter. You can specify more than one filter and replacement if you wish, just comma-delimit them (unless you&#8217;ve overriden the <code>param_delim</code> of course).</p>

	<p>When you specify more than one filter, they ignore the <code>filter_type</code> attribute because the filters are applied in order; one per test. If you wish to skip a particular field and not apply a filter, simply leave an empty comma as a placeholder, e.g. <code>filter=&quot;, /[^a-zA-Z0-9\-]+/, , /[^0-9]+/&quot;</code> would apply the respective filters to the 2nd and 4th tests only.</p>

	<h2 id="eg1">Example 1: standard comparison</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;section, id&quot;
     operator=&quot;begins, gt&quot;
     value=&quot;lion, 12&quot;&gt;
 &lt;p&gt;The lion sleeps tonight&lt;/p&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Roooooarrrr! *CHOMP*&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>Checks if the current section name begins with the word &#8220;lion&#8221; and the article ID is greater than 12. Displays &#8220;The lion sleeps tonight&#8221; if both conditions are met or displays the text &#8220;Roooooarrrr! <strong><span class="caps">CHOMP</span></strong>&#8221; if not.</p>

	<h2 id="eg2">Example 2: other types of field</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;summary, category1, urlvar:pic&quot;
     operator=&quot;isused, eq, isnum&quot;
     value=&quot;, animal ,&quot; &gt;
  &lt;p&gt;All matched&lt;/p&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Match failed&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>Checks if the custom field labelled &#8220;summary&#8221; has some data in it, checks if category1 equals &#8220;animal&#8221; and tests if the urlvar <code>pic</code> is numeric (e.g. <code>?pic=5</code>).</p>

	<p>If all these conditions are met the &#8220;All matched&#8221; message is displayed, else the &#8220;Match failed&#8221; message is shown. Note that <code>isused</code> and <code>isnum</code> don&#8217;t take arguments for <code>value</code> and their positions are held by empty commas (technically the last comma isn&#8217;t needed but it helps keep everything neat if you add further tests later on).</p>

	<h2 id="eg3">Example 3: using &#8216;or&#8217; logic</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;article_image, svrvar:HTTP_USER_AGENT&quot;
     operator=&quot;eq, contains&quot;
     value=&quot;urlvar:pic, Safari&quot;
     logic=&quot;or&quot;&gt;
 &lt;p&gt;Come into my parlour&lt;/p&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Not today, thanks&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>Compares (for equality) the current article image id with the value of the url variable <code>pic</code> and checks if the value of the <span class="caps">HTTP</span>_USER_AGENT string contains &#8220;Safari&#8221;. This example uses the &#8216;or&#8217; logic, hence if <em>either</em> condition is met the &#8216;come into my parlour&#8217; message is shown, otherwise the &#8216;not today&#8217; message is displayed.</p>

	<h2 id="eg4">Example 4: sub-category testing</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;parent:LVL2&quot;
     operator=&quot;eq&quot;
     value=&quot;mammal&quot;&gt;
 &lt;txp:article /&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Not today, thanks&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>On a category list page, this checks the 2nd sub-category of the tree to see if it equals &#8220;mammal&#8221;. If it does, the article is displayed; if not, the message is shown instead. Removing the <code>:LVL2</code> &#8212; which means you can also remove the operator parameter to force the comparison to be the default &#8220;contains&#8221; &#8212; checks if the current (global) category is a child of &#8216;mammal&#8217; at any nesting level.</p>

	<p>Move the example into an article or article Form and change the field to <code>parent:CAT1</code> to see if the article&#8217;s category1 matches &#8216;mammal&#8217; at any level, or use <code>field=&quot;parent:CAT1:LVL2&quot;</code> to combine the checks.</p>

	<h2 id="eg5">Example 5: defined/undefined/isused/isempty</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:pic, urlvar:page&quot;
     operator=&quot;gt:NUM, undefined&quot;
     value=&quot;?article_image,&quot;&gt;
 &lt;p&gt;Yes please&lt;/p&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Not today, thanks&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>Tests if the url variable <code>pic</code> is strictly numerically greater than the value in the current article&#8217;s <code>article_image</code> field and that the url variable <code>page</code> is missing from the <span class="caps">URL</span> address. Compare the outcome of this test with the other operators using the following table when testing the <code>page</code> urlvar:</p>

	<table>
		<tr>
			<th><span class="caps">URL</span> </th>
			<th>defined </th>
			<th>undefined </th>
			<th>isused </th>
			<th>isempty </th>
		</tr>
		<tr>
			<td> index.php?pag </td>
			<td> <span class="caps">FALSE</span> </td>
			<td> <span class="caps">TRUE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
		</tr>
		<tr>
			<td> index.php?page= </td>
			<td> <span class="caps">TRUE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
			<td> <span class="caps">TRUE</span> </td>
		</tr>
		<tr>
			<td> index.php?page=4 </td>
			<td> <span class="caps">TRUE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
			<td> <span class="caps">TRUE</span> </td>
			<td> <span class="caps">FALSE</span> </td>
		</tr>
	</table>

	<h2 id="eg6">Example 6: short circuiting the <code>field</code></h2>

	<p>Put this inside your <code>plainlinks</code> form and execute a <code>&lt;txp:linklist /&gt;</code> from an article page/form:</p>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;id&quot;
     operator=&quot;ge:NUM, le:NUM&quot;
     value=&quot;urlvar:min, urlvar:max&quot;&gt;
  &lt;txp:linkdesctitle /&gt;&lt;br /&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>That will list only the links that have IDs between the <code>min</code> and <code>max</code> variables specified on the address bar. Notice that the id field is only listed once and each operator is applied to it in turn.</p>

	<h2 id="eg7">Example 7: alphanumeric testing</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:product_code&quot;
     operator=&quot;isalnum&quot;&gt;
  &lt;txp:output_form form=&quot;show_product&quot; /&gt;
&lt;txp:else /&gt;
 &lt;p&gt;Invalid product code&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>Tests to see if the product_code <span class="caps">URL</span> variable is alphanumeric and displays a form if so.</p>

	<h2 id="eg8">Example 8: displaying used values</h2>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:sort_order&quot;
     operator=&quot;in&quot;
     value=&quot;id/price/size/colour&quot;&gt;
  &lt;p&gt;Sorting values by {smd_if_sort_order}&lt;/p&gt;
  // Do some stuff
&lt;txp:else /&gt;
  // Use a default sort, or show an error here
&lt;/txp:smd_if&gt;
</code></pre>

	<p>By using the replacement tag {smd_if_sort_order} you have plucked the value from the <span class="caps">URL</span> bar and inserted it into the article. Useful when using the <code>in</code> or <code>notin</code> operators because, although you know that the field matched one of the values in your list, you would otherwise not know which one has been given on the address bar. If you specify the <code>debug</code> attribute in tags like these you can more easily see what replacements are available.</p>

	<h2 id="eg9">Example 9: using phpvar</h2>

<pre class="block"><code class="block">&lt;txp:php&gt;
global $bodyex;
$bodyex = excerpt(array()).body(array());
&lt;/txp:php&gt;
&lt;txp:smd_if field=&quot;phpvar:bodyex&quot;
     operator=&quot;gt:LEN&quot;
     value=&quot;300&quot;&gt;
  &lt;p&gt;You are a big boy at {smd_if_len_bodyex}
     characters long!&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>If put in an article Form (<span class="caps">NOT</span> directly in an article or you&#8217;ll get an out of memory error!), this checks the excerpt and body and shows the message if the combined total length is more than 300 characters.</p>

	<h2 id="eg10">Example 10: <code>between</code> and <code>range</code></h2>

	<p>Check that the given <span class="caps">URL</span> params <code>day</code> and <code>month</code> are within reasonable tolerance. Note that it&#8217;s still possible to specify the 31st February so you may require extra checks in the container.</p>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:day, urlvar:month&quot;
     operator=&quot;between, between&quot; value=&quot;0/32, 0/13&quot;&gt;
   // Likely a valid date supplied
&lt;/txp:smd_if&gt;
</code></pre>

	<p>This is functionally equivalent, and probably more obvious to anyone else reading the code:</p>

<pre class="block"><code class="block">&lt;txp:smd_if field=&quot;urlvar:day, urlvar:month&quot;
     operator=&quot;range, range&quot; value=&quot;1/31, 1/12&quot;&gt;
   // Likely a valid date supplied
&lt;/txp:smd_if&gt;
</code></pre>

	<p>If you wanted to factor the year in and make sure that nobody used a year less than 1900 or greater than the current year, try this:</p>

<pre class="block"><code class="block">&lt;txp:php&gt;
global $thisyear;
$thisyear = date(&quot;Y&quot;);
&lt;/txp:php&gt;
&lt;txp:smd_if field=&quot;urlvar:d, urlvar:m, urlvar:y&quot;
     operator=&quot;range, range, range&quot;
     value=&quot;1/31, 1/12, 1900/phpvar:thisyear&quot;&gt;
   // Likely a valid date supplied
&lt;/txp:smd_if&gt;
</code></pre>

	<h2 id="eg11">Example 11: reading multiple values from different places</h2>

<pre class="block"><code class="block">&lt;txp:variable name=&quot;ltuae&quot;&gt;42&lt;/txp:variable&gt;
&lt;txp:smd_if field=&quot;urlvar:trigger&quot;
     operator=&quot;in&quot; value=&quot;3/15/36/txpvar:ltuae/180/?secret&quot;&gt;
   &lt;p&gt;You found one of the magic numbers&lt;/p&gt;
&lt;/txp:smd_if&gt;
</code></pre>

	<p>First of all we set up the Txp variable, then test the <span class="caps">URL</span> variable <code>trigger</code> and see if it is one of the numbers listed in the <code>value</code> attribute. Note that we have specified the Txp variable as one of the numbers, and also the contents of the custom field called <code>secret</code>. Essentially, this builds up the value attribute from all the sources and tests the final result. So if secret held the number 94, this smd_if tag checks if trigger is one of 3, 15, 36, 42, 180 or 94 and displays the message if so. If <code>secret</code> instead contained <code>94/101/248</code> these three values would also be tested as part of the <code>in</code> operator.</p>

	<h2 id="eg12">Example 12: item counts</h2>

	<p>This counts the number of items in <code>my_list</code> and tests them against the <code>value</code>. Note that <code>list_delim</code> is used between list items.</p>

<pre><code>&lt;txp:variable name=&quot;my_list&quot;&gt;8 / 42 / 11 / 75 / 14&lt;/txp:variable&gt;
&lt;txp:smd_if field=&quot;txpvar:my_list&quot; operator=&quot;gt:COUNT&quot; value=&quot;3&quot;&gt;
   Yes, there are {smd_if_val1} or more values
   (actually: {smd_if_count_my_list})
&lt;txp:else /&gt;
  There are fewer than {smd_if_val1} values in the list.
&lt;/txp:smd_if&gt;
</code></pre>

	<h2>Author</h2>

	<p><a href="http://stefdawson.com/contact">Stef Dawson</a>. Based on an idea brewing in the back of my mind while hacking chs_if_urlvar.</p>

	<h2 class="changelog">Changelog</h2>

	<ul>
		<li>30 Dec 2007 | 0.10 | Initial release</li>
		<li>30 Dec 2007 | 0.20 | Added parent category checking (thanks the_ghost)</li>
		<li>02 Jan 2008 | 0.30 | Added defined/undefined and strict numeric comparisons</li>
		<li>06 Jan 2008 | 0.40 | Added <code>?</code> notation to allow the value to read Txp fields; better quote support (both thanks NeilA)</li>
		<li>06 Jan 2008 | 0.41 | Fixed lower case field names and undefined index error (thanks peterj)</li>
		<li>14 Jan 2008 | 0.50 | Added case_sensitive option; made &#8216;contains&#8217; the default for &#8216;parent&#8217; tests; improved help (all thanks the_ghost); added delim options</li>
		<li>15 Jan 2008 | 0.51 | Fixed defined/undefined syntax error; tightened isused/isempty to distinguish them from defined/undefined</li>
		<li>25 May 2008 | 0.60 | Fixed &#8216;undefined index&#8217; errors (thanks redbot and the_ghost) ; added more pretext variables ; added more <code>is</code> checks (and the <span class="caps">NOSPACE</span> modifier) ; allowed file and link tests (including parent categories)</li>
		<li>26 May 2008 | 0.61 | Fixed stupid oversight in field name generation to allow arbitrary names instead of forcing $thisarticle (thanks to Joana Carvalho for leading me to this)</li>
		<li>11 Jun 2008 | 0.62 | Fixed incorrect result if eval() is empty ; added <span class="caps">NULL</span> field object</li>
		<li>10 Sep 2008 | 0.70 | Fixed warning if empty custom field in value (thanks visualpeople) ; added txpvar support (thanks the_ghost) ; added thisimage support (for the future) ; added operators <code>in</code>, <code>notin</code> and the <code>list_delim</code> attribute; enabled replacement tags for matched variables</li>
		<li>01 Oct 2008 | 0.71 | Fixed the fix for empty custom fields implemented in 0.7 (thanks mapu/visualpeople)</li>
		<li>01 Oct 2008 | 0.72 | Added <code>:NOTAGS</code> (thanks mapu)</li>
		<li>13 Oct 2008 | 0.73 | Added <code>:NOSPACE</code> to <code>begins</code>, <code>ends</code> and <code>contains</code> (thanks mapu), added phpvar support, <code>:LEN</code> modifier and length replacement tags (all thanks the_ghost)</li>
		<li>13 Oct 2008 | 0.74 | Bug fix the smd_if_ names of vals and fields to avoid clashes. Now numerically indexed</li>
		<li>02 Dec 2008 | 0.75 | Added <code>divisible</code> operator (thanks gomedia) ; allow short-circuit of fields (thanks redbot)</li>
		<li>20 Mar 2009 | 0.76 | Added <code>postvar</code> field type (thanks kostas45)</li>
		<li>22 Mar 2009 | 0.77 | Added <code>:TRIM</code> modifier (thanks gomedia)</li>
		<li>05 Apr 2009 | 0.80 | Added filtering capability</li>
		<li>26 Sep 2009 | 0.81 | Added parent <code>TTL</code> and <code>KIDS</code> modifiers (thanks photonomad) ; improved parent debug output</li>
		<li>02 Mar 2010 | 0.82 | Added <code>between</code> and <code>range</code> (thanks speeke)</li>
		<li>02 Mar 2010 | 0.90 | Internal code refactorisation ; allowed multiple values to be read from multiple sources (thanks speeke) ; enhanced replacement tags</li>
		<li>28 Jan 2012 | 0.91 | Fixed pretext checks for section/category (thanks saccade) ; enabled explicit checks for pretext, file, link, image, and article ; added <code>var_prefix</code> to allow nesting of smd_if tags; added <code>:COUNT</code> modifier (thanks the_ghost) ; added <code>:ESC</code> and <code>:ESCALL</code> modifiers ; fixed checks for defined / undefined</li>
	</ul>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>