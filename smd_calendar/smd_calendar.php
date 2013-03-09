<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_calendar';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.52';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Calendar / event / schedule system with events as TXP articles';

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
// Based on mdp_calendar - thanks Marshall!
// TODO: * allow table header to be removed / restyled completely (the month/week dropdown & nav icons)
//         -- a form (navform?) for the header row with access to all vars such as which month is being displayed?
//         -- tools to allow the header to be generated from components and laid out in any manner?
//       * allow URL vars to be passed as POST (to bypass gbp_permlinks)
//       * add custom rows to the table (header, footer) -- header could be used to replace the current nav/dropdowns
//       * div-based calendar layout?
// TODO: Fix expiry dates on extra+ allspanned dates in smd_article_event (and calendar?). They currently 'creep' a day for every day of a spanned event

if( $date = gps('date') ) {
	$_GET['month'] = $date;
}
function smd_calendar($atts, $thing='') {
	global $pretext, $thisarticle, $variable, $prefs, $smd_cal_flag, $smd_date, $smd_calinfo, $smd_cal_ucls;

	extract(lAtts(array(
		'time'          => 'any',
		'size'          => 'large',
		'expired'       => '',
		'category'      => '',
		'subcats'       => '',
		'section'       => '',
		'author'        => '',
		'realname'      => '',
		'status'        => 'live',
		'showall'       => '0',
		'static'        => '',
		'form'          => '',
		'spanform'      => 'SMD_SAME',
		'recurform'     => 'SMD_SAME',
		'cellform'      => '',
		'headerform'    => '',
		'stepfield'     => '',
		'skipfield'     => '',
		'omitfield'     => '',
		'extrafield'    => '',
		'extrastrict'   => '0',
		'datefields'    => '',
		'showskipped'   => '0',
		'showspanned'   => '1',
		'holidays'      => '',
		'holidayflags'  => 'standard',
		'classlevels'   => 'cell, event',
		'linkposted'    => 'recur, multi, multiprev, multilast',
		'classprefixes' => 'smd_cal_, smd_cal_ev_',
		'class'         => '',
		'rowclass'      => '',
		'cellclass'     => '',
		'emptyclass'    => 'empty',
		'isoweekclass'  => 'week',
		'navclass'      => 'navprev, navnext',
		'navarrow'      => '&#60;, &#62;',
		'navid'         => '',
		'eventclasses'  => 'category',
		'eventwraptag'  => 'span',
		'select'        => '',
		'selectbtn'     => '',
		'myclass'       => '',
		'mywraptag'     => '',
		'caption'       => '',
		'summary'       => '',
		'id'            => '',
		'week'          => '',
		'month'         => '',
		'year'          => '',
		'remap'         => '',
		'yearwidth'     => '0',
		'isoweeks'      => '',
		'dayformat'     => 'ABBR',
		'monthformat'   => 'FULL',
		'firstday'      => 0,
		'maintain'      => 'calid',
		'nameval'       => '',
		'gmt'           => 0,
		'lang'          => '',
		'debug'         => 0,
	), $atts));

	$size = (in_array($size, array('small', 'large'))) ? $size : 'large';
	$status = ($status) ? $status : 'live'; // in case status is empty
	$firstday = ($isoweeks == '') ? $firstday : 1;
	$spanform = ($spanform == 'SMD_SAME') ? $form : $spanform;
	$recurform = ($recurform == 'SMD_SAME') ? $form : $recurform;
	$cellform = (empty($cellform)) ? '' : fetch_form($cellform);
	$headerform = (empty($headerform)) ? '' : fetch_form($headerform);
	$frontpage = ($section=='' && $pretext['s']=='default') ? true : false;

	// Set up the class prefixes
	$clevs = do_list($classlevels);
	$cls = do_list($classprefixes);
	$cls_pfx = $evc_pfx = $cls[0];
	if (count($cls) > 1){
		$evc_pfx = $cls[1];
	}

	// Set up the nav class(es)
	$maintain = do_list($maintain);
	$navarrow = do_list($navarrow);
	$navparr = $navarrow[0];
	$navnarr = (count($navarrow) > 1) ? $navarrow[1] : $navarrow[0];
	$navclass = do_list($navclass);
	$navpclass = $navclass[0];
	$navnclass = (count($navclass) > 1) ? $navclass[1] : $navclass[0];

	// Filters
	$fopts = array();
	$catSQL = $secSQL = $authSQL = $fpSQL = '';
	if($category) {
		$fopts['c'] = $category; // TODO: Can fopts take a list? Should it include subcats?
		$allcats = do_list($category);
		$subcats = (empty($subcats)) ? 0 : ((strtolower($subcats)=="all") ? 99999 : intval($subcats));
		if ($subcats) {
			$outcats = array();
			foreach ($allcats as $cat) {
				$cats = getTree(doslash($cat), 'article');
				foreach ($cats as $jdx => $val) {
					if ($cats[$jdx]['level'] <= $subcats) {
						$outcats[] = $cats[$jdx]['name'];
					}
				}
			}
			$allcats = $outcats;
		}
		$catSQL = doQuote(join("','", doSlash($allcats)));
		$catSQL = " AND ( Category1 IN (".$catSQL.") OR Category2 IN (".$catSQL.") ) ";
	}
	if($section) {
		$secs = do_list($section);
		$smd_calinfo['s'] = $secs[0];
		$secSQL = doQuote(join("','", doSlash($secs)));
		$secSQL = " AND Section IN (".$secSQL.") ";
	}
	if($realname) {
		$authors = safe_column('name', 'txp_users', 'RealName IN ('. doQuote(join("','", doArray(do_list($realname), 'urldecode'))) .')' );
		$author = join(',', $authors);
	}
	if($author) {
		$fopts['author'] = htmlentities(gps('author'));
		$authSQL = doQuote(join("','", doSlash(do_list($author))));
		$authSQL = " AND AuthorID IN (".$authSQL.") ";
	}
	if ($frontpage && !$showall) {
		$fpSQL = filterFrontPage();
	}
	$smd_calinfo['evid'] = 0;
	$smd_calinfo['artid'] = $thisarticle['thisid'];
	$smd_calinfo['artitle'] = $thisarticle['url_title'];
	$nameval = do_list($nameval);
	foreach ($nameval as $nv) {
		$nv = explode("=", $nv);
		if ($nv[0]) {
			$fopts[$nv[0]] = ((isset($nv[1])) ? $nv[1] : '');
		}
	}
	$status = do_list($status);
	$stati = array();
	foreach ($status as $stat) {
		if (empty($stat)) {
			continue;
		} else if (is_numeric($stat)) {
			$stati[] = $stat;
		} else {
			$stati[] = getStatusNum($stat);
		}
	}
	$stati = " Status IN (".join(',', $stati).")";

	$expired = ($expired) ? $expired : $prefs['publish_expired_articles'];
	$expired = (($expired) ? '' : ' AND (now() <= Expires OR Expires = '.NULLDATETIME.')');
	$eventclasses = do_list($eventclasses);
	$holidayflags = do_list($holidayflags);
	$linkposted = do_list($linkposted);
	$datefields = do_list($datefields);

	// Work out the first and last posts to determine the year range - probably a better way of doing this than 3 queries
	$filt = $stati . (($category) ? $catSQL : '') . (($section) ? $secSQL : '') . (($author) ? $authSQL : '') . $fpSQL;
	$earliest = safe_field('unix_timestamp(Posted) AS uPosted', 'textpattern', $filt .' ORDER BY Posted ASC LIMIT 0, 1', $debug);
	$lp = safe_field('unix_timestamp(Posted) AS uPosted', 'textpattern', $filt .' ORDER BY Posted DESC LIMIT 0, 1', $debug);
	$lm = safe_field('unix_timestamp(LastMod) AS uLastMod', 'textpattern', $filt .' ORDER BY LastMod DESC LIMIT 0, 1', $debug);
	$latest = ($time=="past") ? time() : (($lp > $lm) ? $lp : $lm);

	$yearwidth = do_list($yearwidth);
	$yearwidth[0] = (empty($yearwidth[0])) ? 0 : $yearwidth[0];
	if (count($yearwidth) == 1) {
		$yearwidth[1] = $yearwidth[0];
	}
	$usenow = array(false,false);
	foreach ($yearwidth as $yridx => $yritem) {
		if (strpos($yritem,"c") !== false) {
			$yearwidth[$yridx] = intval($yritem);
			$usenow[$yridx] = true;
		}
	}

	// Remap w/m/y to other vars if required
	$remap = do_list($remap);
	$dmap = array("y" => "y", "m" => "m", "w" => "w");
	foreach ($remap as $dpair) {
		$dpair = do_list($dpair, ':');
		$dmap[$dpair[0]] = (isset($dpair[1])) ? $dpair[1] : $dpair[0];
	}
	$earliest = date("Y", strtotime("-".$yearwidth[0]." year", ( (empty($earliest) || $usenow[0]==true) ? time() : $earliest) ) );
	$latest = date("Y", strtotime("+".$yearwidth[1]." year", ( (empty($latest) || $usenow[1]==true) ? time() : $latest) ) );

	// Check the URL for current date and calendar target info
	$in_calid = gps('calid');
	$in_year = (gps($dmap["y"]) and is_numeric(gps($dmap["y"]))) ? (int)gps($dmap["y"]) : '';
	$in_month = (gps($dmap["m"]) and is_numeric(gps($dmap["m"]))) ? (int)gps($dmap["m"]) : '';
	$in_week = (gps($dmap["w"]) and is_numeric(gps($dmap["w"]))) ? (int)gps($dmap["w"]) : '';

	if($static) { // if we're static w/o any supplied vars, use the current date
		if(!$year) { $year = safe_strftime('%Y'); }
		if(!$month) { $month = safe_strftime('%m'); }
	} else { // otherwise use current date only if there's nothing else
		if( $id == $in_calid ) { // use incoming
			$year = ($in_year) ? $in_year : (($year) ? $year : safe_strftime('%Y'));
			$month = ($in_month) ? $in_month : (($month) ? $month : safe_strftime('%m'));
			// If week is used, adjust month so it encompasses the given week
			$week = $in_week;
			if ($week) {
				$month = safe_strftime("%m", strtotime($year."W".str_pad($week, 2, '0', STR_PAD_LEFT))); // Get the month from the week
			}
		} else { // use current
			if(!$year) { $year = safe_strftime('%Y'); }
			if(!$month) { $month = safe_strftime('%m'); }
			if($week) { $month = safe_strftime("%m", strtotime($year."W".str_pad($week, 2, '0', STR_PAD_LEFT))); }
		}
	}
	$smd_calinfo['id'] = ($in_calid) ? $in_calid : $id;
	$smd_date['y'] = $year; $smd_date['m'] = $month; // $week/day/isoyear are set per event later

	$ts_first = mktime(0, 0, 0, $month, 1, $year);
	$ts_last = mktime(23, 59, 59, $month, date('t',$ts_first), $year);
	$ts_lastoff = $ts_last - tz_offset($ts_last);
	if ($debug) {
		echo "++ THIS MONTH'S CALENDAR [ start stamp // end date // end stamp // end date // tz offset (end) ] ++";
		dmp($ts_first, date('Y-m-d H:i:s', $ts_first), $ts_last, date('Y-m-d H:i:s', $ts_last), $ts_lastoff);
	}
	$extrasql = $catSQL . $secSQL . $authSQL . $fpSQL;

	switch($time) {
		case "any" : break;
		case "future" : $extrasql .= " AND Posted > now()"; break;
		default : $extrasql .= " AND Posted < now()"; break; // The past
	}

	// Holidays are global 'exclusions', either defined directly or in a txp:variable
	$holidays = do_list($holidays);
	$txphols = do_list($holidays[0], ":");
	if ($txphols[0] == "txpvar") {
		$holidays = do_list($variable[$txphols[1]]);
	}
	// Force each holiday to a known format. Holidays without years use current year
	foreach ($holidays as $key => $val) {
		if (empty($val)) continue;
		$numparts = preg_match('/^([\d\w]+).?([\d\w]+).?([\d\w]+)?$/', $val, $parts);

		if ($numparts) {
			if (count($parts) == 3) {
				$parts[3] = $year;
         }
			$val = str_pad($parts[1], 2, '0', STR_PAD_LEFT).'-'.str_pad($parts[2], 2, '0', STR_PAD_LEFT).'-'.$parts[3];
		}
		$holidays[$key] = date("d-M-Y", safe_strtotime($val));
	}

	if ($debug > 0 && !empty($holidays) && $holidays[0] != '') {
		echo "++ HOLIDAYS ++ ";
		dmp($holidays);
	}

	// Get all matching articles in (and before) this month
	$events = array();
	$uposted_field = (empty($datefields[0])) ? 'uPosted' : "UNIX_TIMESTAMP($datefields[0])";
	$sql2 = $stati . " HAVING $uposted_field <= ".$ts_lastoff. $expired . $extrasql ." ORDER BY Posted ASC";
	$grabCols = '*, unix_timestamp(Posted) as uPosted, unix_timestamp(LastMod) as uLastMod, unix_timestamp(Expires) as uExpires';
	$evlist = safe_rows($grabCols, 'textpattern', $sql2, $debug);
	article_push();

	// If any events recur and fall within the current month, add those as well
	// If any dates are to be excluded, the entry is skipped UNLESS showskipped indicates otherwise
	foreach ($evlist as $row) {
		$idx = 0; // In case the 1st day of the month is a continuation of an event from the end of the previous month
		$start = (!empty($datefields[0]) && !empty($row[$datefields[0]]) && ($stdt = strtotime($row[$datefields[0]])) !== false) ? $stdt : $row['uPosted'] + tz_offset($row['uPosted']);
		$start_date = date("Y-m-d", $start); // For recurring/spanned events on a minical, this is the event the cell links to
		$real_end = (isset($datefields[1]) && !empty($row[$datefields[1]]) && ($endt = strtotime($row[$datefields[1]])) !== false) ? $endt : (($row['uExpires']==0) ? 0 : $row['uExpires'] + tz_offset($row['uExpires']));

		// If end < start the user-specified dates cannot be trusted
		if ($real_end != 0 && $real_end <= $start) {
			$start = $row['uPosted'] + tz_offset($row['uPosted']);
			$real_end = $row['uExpires'] + tz_offset($row['uExpires']);
			trigger_error('Expiry cannot be before start date in "'.$row['Title'].'": ignored', E_USER_WARNING);
		}

		$end = ($real_end != 0 && $real_end < $ts_last) ? $real_end : $ts_last;
		$real_diff = ($real_end==0) ? 0 : $real_end - $start;
		$fake_diff = strtotime(date("Y-M-d", $real_end) . " 23:59:59");
		$diff = ($real_end==0) ? 0 : $fake_diff - $start;
		$smd_cal_flag = $smd_cal_ucls = array();

		$ev_month = date('m', $start);
		$ev_year = date('Y', $start);
		$ev_hr = date('H', $start);
		$ev_mn = date('i', $start);
		$ev_sc = date('s', $start);

		if ($debug > 1) {
			echo '++ EVENT START // END // (if non-zero) REAL END ++';
			dmp(date('d-M-Y H:i:s', $start) .' // '. date('d-M-Y H:i:s', $end) .' // '. ( ($real_end == 0) ? '' : date('d-M-Y H:i:s', $real_end) ));
			dmp($row['Title']);
			if ($debug > 2) {
				dmp($row);
			}
		}

		$multi = (($end > $start) && ($real_end > $start) && ($real_end > $ts_first) && (date("d-m-Y", $real_end) != date("d-m-Y", $start))) ? true : false;
		$recur = (empty($row[$stepfield])) ? false : true;
		$hol_hit = in_array(date("d-M-Y", $start), $holidays);
		$evclasses = array();
		foreach ($eventclasses as $evcls) {
			switch ($evcls) {
				case "":
					break;
				case "gcat":
					if (isset($pretext['c']) && !empty($pretext['c'])) {
						$evclasses[] = $evc_pfx.$pretext['c'];
					}
					break;
				case "category":
					if (isset($row['Category1']) && !empty($row['Category1'])) {
						$evclasses[] = $evc_pfx.$row['Category1'];
					}
					if (isset($row['Category2']) && !empty($row['Category2'])) {
						$evclasses[] = $evc_pfx.$row['Category2'];
					}
					break;
				case "section":
					if (isset($pretext['s']) && !empty($pretext['s'])) {
						$evclasses[] = $evc_pfx.$pretext['s'];
					}
					break;
				case "author":
					if (isset($pretext['author']) && !empty($pretext['author'])) {
						$evclasses[] = $evc_pfx.$pretext['author'];
					}
					break;
				default:
					if (isset($row[$evcls]) && !empty($row[$evcls])) {
						$evclasses[] = $evc_pfx.$row[$evcls];
					}
					break;
			}
		}
		$ignore = $omit = $cflag = array();

		if ($debug > 1 && $evclasses) {
			echo '++ EVENT CLASSES ++';
			dmp($evclasses);
		}

		// Events that start or are added this month
		if (($start < $end) && ($start > $ts_first)) {
			populateArticleData($row);
			$smd_calinfo['evid'] = $row['ID'];
			// a standard event or start of a multi
			if ($showspanned && $multi && !$recur) {
				$smd_cal_flag[] = 'multifirst';
			}
			if ($recur) {
				$smd_cal_flag[] = 'recurfirst';
			}
			if (!$smd_cal_flag) {
				$smd_cal_flag[] = 'standard';
			}
			if ( ( $hol_hit && !in_array('multi',$holidayflags) && in_array('multifirst',$smd_cal_flag) ) || ( $hol_hit && !in_array('standard',$holidayflags) && in_array('standard',$smd_cal_flag) ) ) {
				$smd_cal_flag[] = 'cancel';
			}
			foreach ($smd_cal_flag as $item) {
				$cflag[] = $cls_pfx.$item;
			}

			$idx = $smd_date['d'] = (int)strftime('%d', $start);
			$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $start), $start);
			$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $start), $start);
			$use_posted = in_array('standard', $linkposted);

			$op = ($thing) ? parse($thing) : (($form) ? parse_form($form) : (($size=="small") ? smd_cal_minilink($row, $idx, $month, $year, $use_posted) : href($row['Title'], permlinkurl($row), ' title="'.$row['Title'].'"')) );
			$events[$idx][] = array('ev' => $op, 'flag' => $smd_cal_flag, 'classes' => array_merge($cflag, $smd_cal_ucls, $evclasses), 'posted' => $start_date);
			$smd_cal_flag = $cflag = $smd_cal_ucls = array();
			$use_posted = '';
		}

		// Generate a skip array for this event
		if ($skipfield && $row[$skipfield] != '') {
			$ignores = do_list($row[$skipfield]);
			foreach ($ignores as $val) {
				$igrng = smd_expand_daterange($val, $start, $end);
				foreach ($igrng as $theval) {
					$ignore[] = date("d-M-Y", $theval); // Force each date to a known format
				}
			}
		}
		// Generate an omit array for this event
		if ($omitfield && $row[$omitfield] != '') {
			$omits = do_list($row[$omitfield]);
			foreach ($omits as $val) {
				$omrng = smd_expand_daterange($val, $start, $end);
				foreach ($omrng as $theval) {
					$omit[] = date("d-M-Y", $theval);
				}
			}
		}
		if ($debug > 1 && ($ignore || $omit)) {
			echo '++ OMITTED DATES ++';
			dmp($omit);
			echo '++ CANCELLED DATES ++';
			dmp($ignore);
		}
		// Calculate the date offsets and check recurring events that fall within the month of interest
		if ($stepfield && $row[$stepfield] != '') {
			$freq = do_list($row[$stepfield]);
			$stampoff = (int)(3600*$ev_hr) + (int)(60*$ev_mn) + (int)$ev_sc;
			foreach ($freq as $interval) {
				$max_loop = 99999; // Yuk, but practically limitless
				$origerval = $interval;
				$interval = str_replace("?month", date('F', mktime(0,0,0,$month,1)), $interval);
				$interval = str_replace("?year", $year, $interval);
				if (strpos($interval, "last") === 0) {
					$interval = date("l, F jS Y", strtotime( $interval, mktime(12, 0, 0, date("n", mktime(0,0,0,$month,1,$year))+1, 1, $year) ));
					$max_loop = 1;
				} else if (strpos($interval, "first") === 0) {
					$interval = date("l, F jS Y", strtotime( $interval, mktime(12, 0, 0, (($month>1) ? $month-1 : 12), date("t", mktime(0,0,0,$month-1,1,(($month==1) ? $year-1: $year))), (($month==1) ? $year-1: $year)) ));
					$max_loop = 1;
				} else if (strpos($interval, "this") === 0) {
					$max_loop = 1;
				}
				$ts_loop = 0;
				$ts_curr = $start;
				if (strpos($origerval, "?month") || strpos($origerval, "?year")) {
					$max_loop = 1;
            }

				while ($ts_curr < $end && $ts_loop < $max_loop) {
					if ($max_loop == 1) {
						$ts_curr = strtotime($interval);
						$ts_curr = ($ts_curr < $start || $ts_curr > $end) ? $start : $ts_curr;
					} else {
						$ts_curr = strtotime($interval, $ts_curr);
					}
					if ($ts_curr === false) {
						$ts_loop++;
						break;
					} else {
						if ($debug > 1) {
							dmp("INTERVAL: ". date('d-M-Y H:i:s', $ts_curr+$stampoff));
						}
						if ($ts_curr < $end && $ts_curr >= $ts_first && $ts_curr != $start) {
							// A recurring event. Check it isn't a holiday or to be ignored
							populateArticleData($row);
							$op = '';
							$idx = (int)strftime('%d', $ts_curr);
							$smd_cal_flag[] = 'recur';
							$thisdate = date("d-M-Y", $ts_curr);
							$omit_me = in_array($thisdate, $omit);
							$show_me = !in_array($thisdate, $ignore);
							$hol_hit = in_array($thisdate, $holidays);
							$show_hol = ($hol_hit && !in_array('recur',$holidayflags) ) ? false : true;
							$use_posted = smd_cal_in_array(array('recur', 'recurfirst'), $linkposted);

							if ( $omit_me ) {
								$smd_cal_flag[] = 'omit';
							}
							if ( (!$show_me || !$show_hol) && !$omit_me ) {
								$smd_cal_flag[] = 'cancel';
							}
							foreach ($smd_cal_flag as $item) {
								$cflag[] = $cls_pfx.$item;
							}

							// Create the events that appear in the cell but only if they've not appeared before, or are to be ignored/omitted
							if (!$omit_me) {
								if (($show_me && $show_hol) || $showskipped) {
									$smd_date['d'] = $idx;
									$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $ts_curr), $ts_curr);
									$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $ts_curr), $ts_curr);
									$op = ($recurform) ? parse_form($recurform) : (($thing) ? parse($thing) : (($size=="small") ? smd_cal_minilink($row, $idx, $month, $year, $use_posted) : href($row['Title'], permlinkurl($row), ' title="'.$row['Title'].'"')) );
								}
							}
							$used = array();
							if (isset($events[$idx]) && $events[$idx] != NULL) {
								foreach ($events[$idx] as $ev) {
									$used[] = $ev['ev'];
								}
							}
							if (isset($events[$idx]) && $events[$idx] == NULL || !in_array($op, $used)) {
								$events[$idx][] = array('ev' => $op, 'flag' => $smd_cal_flag, 'classes' => array_merge($cflag, $smd_cal_ucls, $evclasses), 'posted' => $start_date);
							}
							$smd_cal_flag = $cflag = $smd_cal_ucls = array();
							$use_posted = '';
						}
						$ts_loop++;
					}
				}
			}
		} else if ($showspanned && $multi) {
			// Non-recurring events may span more than one date but they must still respect ignored dates and holidays
			populateArticleData($row);
			$lastday = (int)strftime('%d', $end);
			$real_lastday = (int)strftime('%d', $real_end);
			while (++$idx <= $lastday) {
				$op = '';
				$multiflag = ($idx==$real_lastday) ? 'multilast' : (($idx==1) ? 'multiprev' : 'multi');
				$smd_cal_flag[] = $multiflag;
				$thistime = mktime(0, 0, 0, $month, $idx, $year);
				$thisdate = date("d-M-Y", $thistime);
				$omit_me = in_array($thisdate, $omit);
				$show_me = !in_array($thisdate, $ignore);
				$hol_hit = in_array($thisdate, $holidays);
				$show_hol = ($hol_hit && !in_array('multi',$holidayflags) ) ? false : true;
				$use_posted = smd_cal_in_array(array('multi', 'multifirst', 'multilast', 'multiprev'), $linkposted);
				if ( $omit_me ) {
					$smd_cal_flag[] = 'omit';
				}
				if ( (!$show_me || !$show_hol) && !$omit_me ) {
					$smd_cal_flag[] = 'cancel';
				}
				foreach ($smd_cal_flag as $item) {
					$cflag[] = $cls_pfx.$item;
				}
				// Create the spanned event that appears in the cell
				if (!$omit_me) {
					if ( ($show_me && $show_hol) || $showskipped) {
						$smd_date['d'] = $idx;
						$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $thistime), $thistime);
						$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $thistime), $thistime);
						$op = ($spanform) ? parse_form($spanform) : (($thing) ? parse($thing) : (($size=="small") ? smd_cal_minilink($row, $idx, $month, $year, $use_posted) : href('&rarr;', permlinkurl($row), ' title="'.$row['Title'].'"')) );
					}
				}
				$events[$idx][] = array('ev' => $op, 'flag' => $smd_cal_flag, 'classes' => array_merge($cflag, $smd_cal_ucls, $evclasses), 'posted' => $start_date);
				$smd_cal_flag = $cflag = $smd_cal_ucls = array();
				$use_posted = '';
			}
		}
		// Add any extra dates for this event that are within the current month
		if ($extrafield && $row[$extrafield] != '') {
			$xtra = do_list($row[$extrafield]);
			$ev_hr = date('H', $start);
			$ev_mn = date('i', $start);
			$ev_sc = date('s', $start);
			$stampoff = (int)(3600*$ev_hr) + (int)(60*$ev_mn) + (int)$ev_sc;
			foreach ($xtra as $val) {
				if (strpos($val, "+") === false) {
					$exrng = smd_expand_daterange($val);
					$val = date("Y-m-d", $exrng[0]);
					$spidth = count($exrng);
					$spex = 0;
				} else {
					$chk = $showspanned && !$recur;
					$spidth = $chk ? ceil($diff / (60*60*24)) : 1; // days between dates
					$val = rtrim($val, '+');
					$spex = $chk ? 1 : 0;
				}

				for ($jdx = 1; $jdx <= $spidth; $jdx++) {
					$tm = safe_strtotime($val . (($jdx==1) ? '' : '+'.($jdx-1).' days'));
					if ($diff > 0 && $jdx == 1) {
						$expstamp = $tm+$stampoff+$real_diff;
					}
					$idx = $smd_date['d'] = (int)strftime('%d', $tm);
					$dt = date("Y-m-d", $tm);
					$lst = ($extrastrict) ? $end : $ts_last;
					if ($tm < $lst && $tm >= $ts_first) {
						$fakerow = $row;
						$fakerow['Posted'] = date("Y-m-d H:i:s", $tm+$stampoff);
						$fakerow['uPosted'] = $tm+$stampoff;
						if ($diff>0) {
							$fakerow['Expires'] = date("Y-m-d H:i:s", $expstamp);
							$fakerow['uExpires'] = $expstamp;
						}

						populateArticleData($fakerow);
						$smd_cal_flag[] = 'extra';
						$cflag[] = $cls_pfx.'extra';
						$omit_me = false;
						$show_me = $show_hol = true;
						if ($spex) {
							$multiflag = ($jdx==1) ? 'multifirst' : (($jdx==$spidth) ? 'multilast' : (($idx==1) ? 'multiprev' : 'multi'));
							$thisdate = date("d-M-Y", $tm);
							$omit_me = in_array($thisdate, $omit);
							$show_me = !in_array($thisdate, $ignore);
							$hol_hit = in_array($thisdate, $holidays);
							$show_hol = ($hol_hit && !in_array('multi',$holidayflags) ) ? false : true;
							$use_posted = in_array('extra', $linkposted);
							if ($omit_me) {
								$smd_cal_flag[] = 'omit';
							}
							if ( (!$show_me || !$show_hol) && !$omit_me ) {
								$smd_cal_flag[] = 'cancel';
							}
							$smd_cal_flag[] = $multiflag;
							$cflag[] = $cls_pfx.$multiflag;
						}
						if (!$omit_me) {
							if ( ($show_me && $show_hol) || $showskipped) {
								$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $tm), $tm);
								$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $tm), $tm);
								$op = ($spex && $spanform) ? parse_form($spanform) : (($thing) ? parse($thing) : (($form) ? parse_form($form) : (($size=="small") ? smd_cal_minilink($row, $idx, $month, $year, $use_posted) : href((($spex && $jdx>1) ? '&rarr;' : $row['Title']), permlinkurl($row), ' title="'.$row['Title'].'"')) ));
								$events[$idx][] = array('ev' => $op, 'flag' => $smd_cal_flag, 'classes' => array_merge($cflag, $smd_cal_ucls, $evclasses), 'posted' => $dt);
								$smd_cal_flag = $cflag = $smd_cal_ucls = array();
								$use_posted = '';
							}
						}
					}
				}
			}
		}
	}
	article_pop();

	if ($debug > 1 && $events) {
		echo '++ ALL EVENTS ++';
		dmp($events);
	}

	// Generate the calendar
	$calendar = new SMD_Calendar($size, $year, $month, $events, $section, $category, $debug);
	$calendar->setWeek($week);
	$calendar->setGMT($gmt);
	$calendar->setLang($lang);
	$calendar->setClassLevels($clevs);
	$calendar->setClassPrefix($cls_pfx);
	$calendar->setEventWraptag($eventwraptag);
	$calendar->setCellForm($cellform);
	$calendar->setHdrForm($headerform);
	$calendar->setMYWraptag($mywraptag);
	$calendar->setSummary($summary);
	$calendar->setCaption($caption);
	$calendar->setTableID($id);
	$calendar->setTableClass($class);
	$calendar->setRowClass($rowclass);
	$calendar->setCellClass($cellclass);
	$calendar->setEmptyClass($emptyclass);
	$calendar->setISOWeekClass($isoweekclass);
	$calendar->setNavInfo($navpclass,$navnclass,$navparr,$navnarr,$navid);
	$calendar->setNavKeep($maintain);
	$calendar->setMYClass($myclass);
	$calendar->setNameFormat($dayformat, "d");
	$calendar->setNameFormat($monthformat, "m");
	$calendar->setRemap($dmap);
	$calendar->setShowISOWeek($isoweeks);
	$calendar->setEYear($earliest);
	$calendar->setLYear($latest);
	$calendar->setFilterOpts($fopts);
	$calendar->setHolidays($holidays);
	$calendar->setSelectors(do_list($select), $selectbtn);
	$calendar->setFirstDayOfWeek($firstday);

	return $calendar->display($static);
}

class SMD_Calendar extends SMD_Raw_Calendar {
	// Override Constructor
	// Permits multiple events to show per day
	var $section = '';
	var $category = '';
	var $size = '';
	var $debug = 0;
	var $events = array();
	function SMD_Calendar($size,$year,$month,$events,$section,$category, $debug=0) {
		$this->debug = $debug;
		$this->section = $section;
		$this->category = $category;
		$this->events = $events;
		$this->size = $size;
		$this->smd_Raw_Calendar($year,$month,$debug);
	}

	// Override dspDayCell to display stuff right
	function dspDayCell($theday) {
		global $smd_cal_flag, $smd_calinfo, $smd_cal_ucls, $smd_date, $permlink_mode;

		$smd_cal_flag = $smd_cal_ucls = $tdclass = array();
		$hasarticle = isset($this->events[$theday]);
		$now = time() + tz_offset();

		$thedate = mktime(0, 0, 0, $this->month, $theday, $this->year);
		$hol_hit = in_array(date("d-M-Y", $thedate), $this->holidays);
		if ($hasarticle) {
			$smd_cal_flag[] = 'event';
		}
		if ($hol_hit) {
			$smd_cal_flag[] = 'hols';
		}
		$cflag = array();
		foreach ($smd_cal_flag as $item) {
			$cflag[] = $this->cls_pfx.$item;
		}

		if ($this->cellclass) {
			$tdclass[] = $this->cellclass;
		}
		$tdclass = array_merge($tdclass, $cflag);
		$runningclass = (in_array("cell", $this->cls_lev) || in_array("cellplus", $this->cls_lev)) ? $tdclass : array();

		if($this->year == date('Y',$now) and $this->month == date('n',$now) and $theday == date('j',$now) ) {
			$smd_cal_flag[] = 'today';
			$runningclass[] = $this->cls_pfx.'today';
		}

		$out = $flags = array();
		$fout = array('standard'=>array(),'recur'=>array(),'recurfirst'=>array(),'multifirst'=>array(),'multi'=>array(),'multiprev'=>array(),'multilast'=>array(),'cancel'=>array(),'extra'=>array());
		if (empty($this->cellform) && $this->size == 'large') {
			$out[] = hed($theday,4);
		}
		if( isset($this->events[$theday]) ) {
			$days_events = $this->events[$theday];
			$evcnt = 0;
			foreach($days_events as $ev) {
				$evclass = $ev['classes'];
				$flags = array_merge($flags, $ev['flag']);
				if (in_array("cellplus", $this->cls_lev)) {
					$runningclass = array_merge($runningclass, $evclass);
				}
				$cls = ($evclass && in_array("event", $this->cls_lev)) ? ' class="'.join(' ', $evclass).'"' : '';
				$op = ($this->evwraptag) ? tag($ev['ev'], $this->evwraptag, $cls) : $ev['ev'];
				foreach ($ev['flag'] as $flev) {
					$fout[$flev][] = $op;
				}
				$out[] = $op;
				$evcnt++;
				if ($this->size == 'small' && $evcnt == 1) {
					break;
				}
			}
		} elseif ($this->size == 'small') {
			$out[] = hed($theday,4);
		}

		// Amalgamate the event-level classes and cell-level classes if required
		$runningclass = array_unique($runningclass);
		if (in_array("cellplus", $this->cls_lev)) {
			$smd_cal_flag = array_merge($smd_cal_flag, $flags);
		}

		if ($this->cellform) {
			$thistime = mktime(0, 0, 0, $this->month, $theday, $this->year);
			$smd_calinfo['id'] = $this->tableID;
			$smd_date['y'] = $this->year;
			$smd_date['m'] = $this->month;
			$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $thistime), $thistime);
			$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $thistime), $thistime);
			$smd_date['d'] = $theday;
			$reps = array(
				'{evid}' => $smd_calinfo['evid'],
				'{standard}' => join('',$fout['standard']),
				'{recur}' => join('',$fout['recur']),
				'{recurfirst}' => join('',$fout['recurfirst']),
				'{allrecur}' => join('',array_merge($fout['recur'], $fout['recurfirst'])),
				'{multifirst}' => join('',$fout['multifirst']),
				'{multiprev}' => join('',$fout['multiprev']),
				'{multi}' => join('',$fout['multilast']),
				'{multilast}' => join('',$fout['multilast']),
				'{allmulti}' => join('',array_merge($fout['multifirst'],$fout['multi'],$fout['multiprev'],$fout['multilast'])),
				'{cancel}' => join('',$fout['cancel']),
				'{extra}' => join('',$fout['extra']),
				'{events}' => join('',$out),
				'{day}' => $theday,
				'{dayzeros}' => str_pad($theday, 2, '0', STR_PAD_LEFT),
				'{weekday}' => ((is_array($this->dayNameFmt)) ? $this->dayNames[date('w',$thistime)] : strftime($this->dayNameFmt, $thistime)),
				'{weekdayabbr}' => strftime('%a', $thistime),
				'{weekdayfull}' => strftime('%A', $thistime),
				'{week}' => $smd_date['w'],
				'{month}' => $this->month,
				'{monthzeros}' => str_pad($this->month, 2, '0', STR_PAD_LEFT),
				'{monthname}' => ((is_array($this->mthNameFmt)) ? $this->mthNames[date('n',$thistime)] : strftime($this->mthNameFmt, $thistime)),
				'{monthnameabbr}' => strftime('%b', $thistime),
				'{monthnamefull}' => strftime('%B', $thistime),
				'{year}' => $this->year,
				'{shortyear}' => strftime('%y', $thistime),
				'{isoyear}' => $smd_date['iy'],
				'{shortisoyear}' => strftime(smd_cal_reformat_win('%g', $thistime), $thistime),
			);
			$cellout = parse(strtr($this->cellform, $reps));
			$carray = array_merge($runningclass, $smd_cal_ucls);
			$smd_cal_ucls = array();

			return doTag($cellout,'td',join(' ',$carray));
		} else {
			return doTag(join('',$out),'td',join(' ',$runningclass));
		}
	}

	function display($static=false) {
		$sum = ($this->tblSummary) ? ' summary="'.$this->tblSummary.'"' : '';
		$id = ($this->tableID) ? ' id="'.$this->tableID.'"' : '';
		$c[] = ($this->tblCaption) ? '<caption>'.$this->tblCaption.'</caption>' : '';
		$c[] = '<thead>';
		$c[] = $this->dspHeader($static);
		$c[] = $this->dspDayNames();
		$c[] = '</thead>';
		$c[] = $this->dspDayCells();

		return doTag(join('',$c),'table',$this->tableclass,$sum.$id);
	}

	function dspHeader($static) {
		global $pretext, $smd_calinfo, $permlink_mode;

		$currmo = $this->month;
		$curryr = $this->year;
		$navpclass = $this->getNavInfo("pc");
		$navnclass = $this->getNavInfo("nc");
		$navparrow = $this->getNavInfo("pa");
		$navnarrow = $this->getNavInfo("na");
		$navid = $this->getNavInfo("id");
		$navpclass = ($navpclass) ? ' class="'.$navpclass.'"' : '';
		$navnclass = ($navnclass) ? ' class="'.$navnclass.'"' : '';
		$fopts = $this->fopts;

		$sec = (isset($smd_calinfo['s']) && !empty($smd_calinfo['s'])) ? $smd_calinfo['s'] : '';
		foreach ($this->maintain as $col) {
			switch ($col) {
				case "section":
					if ($pretext['s'] && $permlink_mode != 'year_month_day_title') {
						$fopts = array('s' => $pretext['s']) + $fopts;
					}
					break;
				case "article":
					if ($pretext['id']) {
						$fopts = array('id' => $pretext['id']) + $fopts;
					}
					break;
				case "category":
					if ($pretext['c']) {
						$fopts = array('c' => $pretext['c']) + $fopts;
					}
					break;
				case "author":
					if (gps('author')) {
						$fopts = array('author' => gps('author')) + $fopts;
					}
					break;
				case "date":
					if (gps('date')) {
						$fopts = array('date' => gps('date')) + $fopts;
					}
					break;
				case "pg":
					if ($pretext['pg']) {
						$fopts = array('pg' => $pretext['pg']) + $fopts;
					}
					break;
				case "calid":
					if ($this->tableID) {
						$fopts = array('calid' => $this->tableID) + $fopts;
					}
					break;
				default:
					if (gps($col)) {
						$fopts = array($col => gps($col)) + $fopts;
					}
					break;
			}
		}

		$fopts = array_unique($fopts);
		$filters = array();
		$filterHid = array();
		if (!$static) {
			foreach($fopts as $key => $val) {
				$filters[] = $key.'='.$val;
				$filterHid[] = hInput($key, $val);
			}
		}

		// Week select list
		if ($this->useSelector('week') && !$static) {
			$currwk = ($this->week) ? $this->week : date('W', safe_strtotime($curryr."-".$currmo."-1 12:00"));
			for ( $idx = 1; $idx <= 53; $idx++ ) {
				$tagatts = ' value="'.$idx.'"';
				if ( $idx == $currwk ) $tagatts .= ' selected="selected"';
				$optiontags[] = doTag($this->selpfx['week'].str_pad($idx, 2, '0', STR_PAD_LEFT).$this->selsfx['week'], 'option', '', $tagatts);
			}
			$selector[] = doTag(join(n, $optiontags), 'select', (($this->mywraptag) ? '' : $this->myclass), ' name="'.$this->remap['w'].'"'.(($this->selbtn) ? '' : ' onchange="submit()"'), '')
   				. (($this->useSelector('year')) ? '' : hInput($this->remap['y'], $curryr));
			$optiontags = array(); // Blank out
		}

		// Month select list - note mktime has the day forced to 1. If not you get
		// bizarre repeated month names on the 31st of some months :-\
		if (!$this->useSelector('week')) {
			if ($this->useSelector('month') && !$static) {
				for ( $idx = 1; $idx <= 12; $idx++ ) {
					$tagatts = ' value="'.$idx.'"';
					if ( $idx == $currmo ) $tagatts .= ' selected="selected"';
					$optiontags[] = doTag($this->selpfx['month'].((is_array($this->mthNameFmt)) ? $this->mthNames[date('n',mktime(12,0,0,$idx,1))] : safe_strftime($this->mthNameFmt, mktime(12,0,0,$idx,1) )).$this->selsfx['month'], 'option', '', $tagatts);
				}
				$selector[] = doTag(join(n, $optiontags), 'select', (($this->mywraptag) ? '' : $this->myclass), ' name="'.$this->remap['m'].'"'.(($this->selbtn) ? '' : ' onchange="submit()"'), '')
	   				. (($this->useSelector('year')) ? '' : hInput($this->remap['y'], $curryr));
				$optiontags = array(); // Blank out
			} else {
				$selector[] = doTag($this->getMonthName(), 'span', (($this->mywraptag) ? '' : $this->myclass));
			}
		}

		// Year select list
		$y0 = $this->eyr;
		$y1 = $this->lyr;
		if ($this->useSelector('year') && ($y0 != $y1) && !$static) {
			for ( $idx = $y0; $idx <= $y1; $idx++ ) {
				$tagatts = ' value="'.$idx.'"';
				if ( $idx == $curryr ) $tagatts .= ' selected="selected"';
				$optiontags[] = doTag($this->selpfx['year'].$idx.$this->selsfx['year'], 'option', '', $tagatts);
			}
			$selector[] = doTag(join(n, $optiontags), 'select', (($this->mywraptag) ? '' : $this->myclass), ' name="'.$this->remap['y'].'"'.(($this->selbtn) ? '' : ' onchange="submit()"'), '')
					. (($this->useSelector('month') || $this->useSelector('week')) ? '' : hInput($this->remap['m'], $currmo));
		} else {
			$selector[] = doTag($curryr, 'span', (($this->mywraptag) ? '' : $this->myclass));
		}

		$request = serverSet('REQUEST_URI');
		$redirect = serverSet('REDIRECT_URL');
		if (!empty($redirect) && ($request != $redirect) && is_callable('_l10n_set_browse_language')) {
			// MLP in da house: use the redirect URL instead
			$request = $redirect;
		}
		$urlp = parse_url($request);
		$action = $urlp['path'];

		if ($permlink_mode == 'messy') {
			$out = makeOut('id','s','c','q','pg','p','month');
			foreach($out as $key => $val) {
				if ($val) {
					$filters[] = $key.'='.$val;
					$filterHid[] = hInput($key, $val);
				}
			}
		}
		$filterHid = array_unique($filterHid);
		$filters = array_unique($filters);

		$extras = '';
		if (!$static && ( $this->useSelector('month') || $this->useSelector('year') )) {
			if ($this->selbtn) {
				$extras .= doTag('', 'input', 'smd_cal_input', ' type="submit" value="'.$this->selbtn.'"');
			}
			$extras .= join(n, $filterHid);
		}

		$selector = '<form action="'.$action.'" method="get"'.(($navid) ? ' id="'.$navid.'"' : '').'>'.doTag(join(sp, $selector).$extras, $this->mywraptag, $this->myclass).'</form>';

		$nav_back_link = $this->navigation($curryr, $currmo, '-', $filters, $urlp['path']);
		$nav_fwd_link  = $this->navigation($curryr, $currmo, '+', $filters, $urlp['path']);

		$nav_back = (!$static && $nav_back_link) ? '<a href="'.$nav_back_link.'"'.$navpclass.'>'.$navparrow.'</a>' : '&nbsp;';
		$nav_fwd  = (!$static && $nav_fwd_link) ? '<a href="'.$nav_fwd_link.'"'.$navnclass.'>'.$navnarrow.'</a>' : '&nbsp;';

		$c[] = doTag($nav_back,'th');
		$c[] = '<th colspan="'.(($this->showISOWeek) ? 6 : 5).'">'.$selector.'</th>';
		$c[] = doTag($nav_fwd,'th');

		return doTag(join('',$c),'tr', 'smd_cal_navrow');
	}

	function navigation($year,$month,$direction,$flt,$url='') {
		global $permlink_mode;

		if($direction == '-') {
			if($month - 1 < 1) {
				$month = 12;
				$year -= 1;
			} else {
				$month -= 1;
			}
		} else {
			if($month + 1 > 12) {
				$month = 1;
				$year += 1;
			} else {
				$month += 1;
			}
		}

		// Abort if we're about to go out of range
		if ($year < $this->eyr || $year > $this->lyr) {
			return '';
		}

		$flt[] = $this->remap['m']."=$month";
		$flt[] = $this->remap['y']."=$year";

		return $url . "?" . join(a, $flt);
	}
}

/**
* Basic Calendar data and display
* http://www.oscarm.org/static/pg/calendarClass/
* @author Oscar Merida
* @created Jan 18 2004
*/
class SMD_Raw_Calendar {
var $gmt = 1, $lang, $debug = 0;
var $year, $eyr, $lyr, $month, $week;
var $dayNameFmt, $mthNameFmt, $dayNames, $mthNames, $startDay, $endDay, $firstDayOfWeek = 0, $startOffset = 0;
var $selectors, $selbtn, $selpfx, $selsfx;
var $showISOWeek, $ISOWeekHead, $ISOWeekCell;
var $cls_lev, $cls_pfx, $fopts;
var $evwraptag, $mywraptag;
var $rowclass, $cellclass, $emptyclass, $isoclass, $myclass, $tableID, $tblSummary, $tblCaption;
var $navpclass, $navnclass, $navparrow, $navnarrow, $navid;
var $holidays, $cellform, $hdrform, $maintain, $remap;
/**
* Constructor
*
* @param integer, year
* @param integer, month
* @return object
* @public
*/
function SMD_Raw_Calendar ($yr, $mo, $debug=0) {
	$this->setDebug($debug);
	$this->setYear($yr);
	$this->setMonth($mo);
	$this->setClassPrefix('smd_cal_');

	$this->startTime = strtotime( "$yr-$mo-01 00:00" );
	$this->startDay = date( 'D', $this->startTime );
	$this->endDay = date( 't', $this->startTime );
	$this->endTime = strtotime( "$yr-$mo-".$this->endDay." 23:59:59" );
	if ($this->debug) {
		echo "++ THIS MONTH'S RENDERED CALENDAR [ start stamp // end date // start day // end stamp // end date // end day number ] ++";
		dmp($this->startTime, date('Y-m-d H:i:s', $this->startTime), $this->startDay, $this->endTime, date('Y-m-d H:i:s', $this->endTime), $this->endDay);
	}
	$this->setNameFormat('%a', 'd');
	$this->setNameFormat('%B', 'm');
	$this->setFirstDayOfWeek(0);
	$this->setShowISOWeek('');
	$this->setTableID('');
	$this->setTableClass('');
}
// === end Calendar ===
// Getters
function useSelector($val) { return in_array($val, $this->selectors); }
function getDayName($day) { return ($this->dayNames[$day%7]); }
function getMonthName() {
	if (is_array($this->mthNameFmt)) {
		return $this->mthNames[date('n',$this->startTime)];
	} else {
		return strftime($this->mthNameFmt, $this->startTime);
	}
}
function getNavInfo($type) {
	$r = '';
	switch ($type) {
		case "id": $r = $this->navid; break;
		case "pc": $r = $this->navpclass; break;
		case "nc": $r = $this->navnclass; break;
		case "pa": $r = $this->navparrow; break;
		case "na": $r = $this->navnarrow; break;
	}
	return $r;
}
// Setters
function setDebug($d){ $this->debug = $d; }
function setGMT($b){ $this->gmt = $b; }
function setLang($code){ $this->lang = $code; }
function setSummary($txt){ $this->tblSummary = $txt; }
function setCaption($txt){ $this->tblCaption = $txt; }
function setCellForm($frm){ $this->cellform = $frm; }
function setHdrForm($frm){ $this->hdrform = $frm; }
function setTableID($id){ $this->tableID = $id; }
function setYear($yr){ $this->year = $yr; }
function setEYear($yr){ $this->eyr = $yr; }
function setLYear($yr){ $this->lyr = $yr; }
function setMonth($mth){ $this->month = (int)$mth; }
function setWeek($wk){
	if ($wk) {
		$wk = str_pad($wk, 2, '0', STR_PAD_LEFT);
		$this->week = $wk;
		$this->month = safe_strftime("%m", strtotime($this->year."W".$wk));
	}
}
function setNavKeep($ar){ $this->maintain = $ar; }
function setShowISOWeek($val) {
	$this->showISOWeek = ($val) ? true : false;
	if ($val) {
		$val = do_list($val);
		$this->ISOWeekHead = $val[0];
		$this->ISOWeekCell = (isset($val[1])) ? $val[1] : '{week}';
	}
}
function setRemap($map){ $this->remap = $map; }
function setClassLevels($cls){ $this->cls_lev = $cls; }
function setClassPrefix($cls){ $this->cls_pfx = $cls; }
function setEventWraptag($wrap){ $this->evwraptag = $wrap; }
function setMYWraptag($wrap){ $this->mywraptag = $wrap; }
function setTableClass($cls) { $this->tableclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setRowClass($cls){ $this->rowclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setCellClass($cls){ $this->cellclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setEmptyClass($cls){ $this->emptyclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setISOWeekClass($cls){ $this->isoclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setNavInfo($clsp, $clsn, $arrp, $arrn, $nid){
	$this->navpclass = ($clsp) ? $this->cls_pfx.$clsp : '';
	$this->navnclass = ($clsn) ? $this->cls_pfx.$clsn : '';
	$this->navparrow = ($arrp) ? $arrp : '';
	$this->navnarrow = ($arrn) ? $arrn : '';
	$this->navid = ($nid) ? $this->cls_pfx.$nid : '';
}
function setMYClass($cls){ $this->myclass = ($cls) ? $this->cls_pfx.$cls : ''; }
function setFilterOpts($f) { $this->fopts = $f; }
function setHolidays($hols) { $this->holidays = $hols; }
function setSelectors($sel, $btn) {
	foreach ($sel as $idx => $item) {
		$selparts = explode(":", $item);
		$sel[$idx] = $selparts[0];
		$this->selpfx[$selparts[0]] = (isset($selparts[1])) ? $selparts[1] : '';
		$this->selsfx[$selparts[0]] = (isset($selparts[2])) ? $selparts[2] : '';
	}
	$this->selectors = $sel;
	$this->selbtn = $btn;
}
function setFirstDayOfWeek($d) {
	$this->firstDayOfWeek = ((int)$d <= 6 and (int)$d >= 0) ? (int)$d : 0;
	$this->startOffset = date('w', $this->startTime) - $this->firstDayOfWeek;
	if ( $this->startOffset < 0 ) {
		$this->startOffset = 7 - abs($this->startOffset);
	}
}
/**
* frm: any valid PHP strftime() string or ABBR/FULL
* typ: d to set day, m to set month format
*/
function setNameFormat($frm, $typ="d") {
	switch ($frm) {
		case "full":
		case "FULL":
			$fmt = ($typ == 'd') ? "%A" : "%B";
			break;
		case "abbr":
		case "ABBR":
			$fmt = ($typ == 'd') ? "%a" : "%b";
			break;
		default:
			if (strpos($frm, '%') === 0) {
				$fmt = $frm;
			} else {
				$frm = trim($frm, '{}');
				$frm = do_list($frm);
				$fmt = $frm;
			}
			break;
	}

	if ($typ == "d") {
		$this->dayNameFmt = $fmt;
		$this->dayNames = array();

		// This is done to make sure Sunday is always the first day of our array
		// Unix time gets a little funky at the beginning depending upon the timezone.
		$serveroffset = gmmktime(0,0,0) - mktime(0,0,0);
		$start = ($serveroffset < 0) ? 4 : 3;
		$end = $start + 7;
		for($i=$start; $i<$end; $i++) {
			if (is_array($fmt)) {
				$this->dayNames[] = $fmt[$i-$start];
			} else {
				$this->dayNames[] = ucfirst(strftime($fmt, 86400*$i));
			}
		}
	} else {
		$this->mthNameFmt = $fmt;
		$this->mthNames = array();
		for ($i=0; $i<12; $i++) {
			if (is_array($fmt)) {
				$this->mthNames[$i+1] = $fmt[$i];
			} else {
				$this->mthNames[$i+1] = ucfirst(strftime($fmt, 86400*$i));
			}
		}
	}
}
/**
* Return markup for displaying the calendar.
* @return
* @public
*/
function display ( ) {
	$id = ($this->tableID) ? ' id="'.$this->tableID.'"' : '';
	$c[] = '<table'.$id.'>';
	$c[] = '<thead>' . $this->dspDayNames() . '</thead>';
	$c[] = $this->dspDayCells();
	$c[] = '</table>';

	return join('',$c);
}
// === end display ===
/**
* Displays the row of day names.
* @return string
* @private
*/
function dspDayNames ( ) {
	if ($this->hdrform) {
		$reps = array(
			'{firstday}' => $this->firstDayOfWeek,
			'{daynames}' => join(',', $this->dayNames),
			'{isoweekhead}' => $this->ISOWeekHead,
			'{week}' => date('W', $this->startTime),
			'{month}' => date('n', $this->startTime),
			'{year}' => date('Y', $this->startTime),
			'{isoyear}' => date('o', $this->startTime),
		);

		return parse(strtr($this->hdrform, $reps));
	} else {
		$c[] = '<tr class="smd_cal_daynames">';

		$i = $this->firstDayOfWeek;
		$j = 0; // count number of days displayed
		$end = false;

		if ($this->showISOWeek) {
			$c[] = "<th>".$this->ISOWeekHead."</th>";
		}
		for($j = 0; $j<=6; $j++, $i++) {
			if($i == 7) { $i = 0; }
			$c[] = '<th>'.$this->getDayName($i)."</th>";
		}

		$c[] = '</tr>';
		return join('',$c);
	}
}
// === end dspDayNames ===
/**
* Displays all day cells for the month
*
* @return string
* @private
*/
function dspDayCells ( ) {
	$i = 0; // cell counter
	$emptyClass = $this->emptyclass;
	$isoClass = $this->isoclass;
	$rowClass = $this->rowclass;
	$rowClass = ($rowClass) ? ' class="'.$rowClass.'"' : '';

	$c[] = '<tr'.$rowClass.'>';

	if ($this->showISOWeek) {
		$reps = array(
			'{week}' => date('W', $this->startTime),
			'{month}' => date('n', $this->startTime),
			'{year}' => date('Y', $this->startTime),
			'{isoyear}' => date('o', $this->startTime),
		);
		$wkcell = strtr($this->ISOWeekCell, $reps);
		$c[] = '<td class="'.$isoClass.'">'.$wkcell.'</td>';
	}
	// first display empty cells based on what weekday the month starts in
	for( $j=0; $j<$this->startOffset; $j++ )	{
		$i++;
		$c[] = '<td class="'.$emptyClass.'">&nbsp;</td>';
	} // end offset cells

	// write out the rest of the days, at each sunday, start a new row.
	for( $d=1; $d<=$this->endDay; $d++ ) {
		$i++;
		$c[] = $this->dspDayCell( $d );
		if ( $i%7 == 0 ) { $c[] = '</tr>'; }
		if ( $d<$this->endDay && $i%7 == 0 ) {
			$c[] = '<tr'.$rowClass.'>';
			if ($this->showISOWeek) {
				$theTime = safe_strtotime($this->year."-".$this->month."-".(int)($d + 1) ." 00:00");
				$reps = array(
					'{week}' => date('W', $theTime),
					'{month}' => date('n', $theTime),
					'{year}' => date('Y', $theTime),
					'{isoyear}' => date('o', $theTime),
				);
				$wkcell = strtr($this->ISOWeekCell, $reps);
				$c[] = '<td class="'.$isoClass.'">'.$wkcell.'</td>';
			}
		}
	}
	// fill in the final row
	$left = 7 - ( $i%7 );
	if ( $left < 7)	{
		for ( $j=0; $j<$left; $j++ )	{
		  $c[] = '<td class="'.$emptyClass.'">&nbsp;</td>';
		}
		$c[] = "\n\t</tr>";
	}
	return '<tbody>' . join('',$c) . '</tbody>';
}
// === end dspDayCells ===
/**
* outputs the contents for a given day
*
* @param integer, day
* @abstract
*/
function dspDayCell ( $day ) {
	return '<td>'.$day.'</td>';
}
// === end dayCell ===
} // end class

function smd_cal_minilink($row, $day, $month, $year, $use_posted=false) {
	global $permlink_mode;

	$lang = '';
	$request = serverSet('REQUEST_URI');
	$redirect = serverSet('REDIRECT_URL');
	if (!empty($redirect) && ($request != $redirect) && is_callable('_l10n_set_browse_language')) {
		// MLP in da house so extract the language currently in use -- is there an MLP-native method for this?
		$reqparts = explode('/', $request);
		$redparts = explode('/', $redirect);
		$lang = join('', array_diff($redparts, $reqparts)) . '/';
	}

	if( $permlink_mode == 'year_month_day_title' ) {
		$linkdate = ($use_posted) ? date('Y/m/d', $row['uPosted']) : $year.'/'.str_pad($month,2,"0",STR_PAD_LEFT).'/'.str_pad($day,2,"0",STR_PAD_LEFT);
		$href = ' href="'.hu.$lang.$linkdate.'"';
	} else {
		$linkdate = ($use_posted) ? date('Y-m-d', $row['uPosted']) : $year.'-'.str_pad($month,2,"0",STR_PAD_LEFT).'-'.str_pad($day,2,"0",STR_PAD_LEFT);
		$href = ' href="'.hu.$lang.'?date='.$linkdate;
		if($row['Section']) { $href = $href.a.'s='.$row['Section']; }
//		if($category) { $href = $href.a.'c='.$category; }
		$href .= '"';
	}

	return tag($day, 'a', $href);
}

// Perform one of two types of test: a flag-based test, or an info-based test
function smd_if_cal($atts, $thing) {
	global $smd_cal_flag, $smd_calinfo, $smd_date;

	extract(lAtts(array(
		'flag'    => '',
		'calid'   => '',
		'isoyear' => '',
		'year'    => '',
		'month'   => '',
		'week'    => '',
		'day'     => '',
		'logic'   => 'or',
		'debug'   => '0',
	), $atts));

	$flag = do_list($flag);
	$ctr = $num = 0;

	if ($debug) {
		dmp($atts);
	}

	if ($flag && $flag[0] != '') {
		$num += count($flag);
		foreach ($flag as $whatnot) {
			if (empty($whatnot)) continue;
			$ctr += (in_array($whatnot, $smd_cal_flag) || ($whatnot == 'SMD_ANY' && !empty($smd_cal_flag))) ? 1 : 0;
		}
	}
	if ($calid) {
		$num++;
		$ctr += ($smd_calinfo['id'] === $calid) ? 1 : 0;
	}
	foreach (array("iy" => "isoyear", "y" => "year", "m" => "month", "w" => "week", "d" => "day") as $idx => $test) {
		$tester = $$test;
		$compare = $smd_date[$idx];

		if ($tester) {
			$num++;
			preg_match('/([!=<>]+)?([\d]+)/', $tester, $matches);
			if ($debug) {
				dmp("TEST IF: ". $compare. (($matches[1]) ? $matches[1] : '=') . $matches[2] );
			}
			switch ($matches[1]) {
				case "!":
					$ctr += ($compare!=$matches[2]) ? 1 : 0;
					break;
				case ">":
					$ctr += ($compare>$matches[2]) ? 1 : 0;
					break;
				case ">=":
					$ctr += ($compare>=$matches[2]) ? 1 : 0;
					break;
				case "<":
					$ctr += ($compare<$matches[2]) ? 1 : 0;
					break;
				case "<=":
					$ctr += ($compare<=$matches[2]) ? 1 : 0;
					break;
				default:
					$ctr += ($compare==$matches[2]) ? 1 : 0;
					break;
			}
		}
	}
	$result = (($ctr === $num && $logic == "and") || $ctr > 0 && $logic == "or") ? true : false;
	return parse(EvalElse($thing, $result));
}

// Convenient wrapper for smd_cal_info use="event"
function smd_event_info($atts) {
	$atts['use'] = 'event';
	return smd_cal_info($atts);
}

// Grab additional information about the current event
function smd_cal_info($atts) {
	global $pretext, $thisarticle, $smd_cal_flag, $smd_calinfo, $smd_date, $smd_eventinfo;

	extract(lAtts(array(
		'type'        => 'flag',
		'join'        => ' ',
		'join_prefix' => 'SMD_AUTO',
		'html'        => 0,
		'escape'      => 'html',
		'use'         => 'cal', // 'cal' for calendar (uses $smd_calinfo) or 'event' for event lists (uses $smd_eventinfo). Not publically alterable
		'debug'       => 0,
	), $atts));

	// Validate $use attribute
	$use = (in_array($use, array('cal', 'event'))) ? $use : 'cal';
	$cal_global = ${'smd_'.$use.'info'};

	if ($debug && $thisarticle) {
		echo '++ Event name ++';
		dmp($thisarticle['title']);
	}
	if ($debug && $cal_global) {
		echo '++ Available '.$use.' info ++';
		dmp($cal_global);
	}

	if ($debug && $smd_date) {
		echo '++ Available date info ++';
		dmp($smd_date);
	}

	if ($debug && $smd_cal_flag) {
		echo '++ Available flag info ++';
		dmp($smd_cal_flag);
	}

	// Type: 0=date, 1=smd_cal/eventinfo, 2=pretext, 3=thisarticle, 4(or other)=user value
	$map = array(
		'year' => array(0, 'y'),
		'isoyear' => array(0, 'iy', 'y'),
		'month' => array(0, 'm'),
		'week' => array(0, 'w'),
		'day' => array(0, 'd'),
		'section' => array(3, '', 's'),
		'category1' => array(3, '', 'c'),
		'category2' => array(3, '', 'c'),
		'thisid' => array(3, 'thisid', 'id'),
		'article' => array(1, 'artid', 'id'),
		'calid' => array(1, 'id', 'calid'),
		'category' => array(2, 'c'),
		'realname' => array(2, 'author'),
	);
	$join = ($html) ? a : $join; // html mode forces ampersand join
	$type = do_list($type);
	$ret = array();
	foreach ($type as $item) {
		$pts = do_list($item, ':');
		$item = $pts[0];

		if (empty($item)) continue;
		// Default html id
		$hid = (isset($map[$item])) ? ((isset($map[$item][2])) ? $map[$item][2] : $map[$item][1]) : $item;
		// User-specified htmlid overrides it
		$hid = (count($pts) > 1 && !empty($pts[1])) ? $pts[1] : $hid;
		if ($item == "flag") {
			$ret[] = (($join_prefix=="SMD_AUTO") ? $join : '').join($join, $smd_cal_flag);
		} else if ($item == "author" || $item == "realname") {
			$currauthor = ($thisarticle == NULL) ? '' : author(array());
			if ($currauthor) {
				$ret[] = (($html) ? $hid.'=' : '') . $currauthor;
			}
		} else if ($item == "s") {
			$sec = (!empty($pretext['s'])) ? $pretext['s'] : ((isset($cal_global['s']) && !empty($cal_global['s'])) ? $cal_global['s'] : '');
			if ($sec) {
				$ret[] = (($html) ? $hid.'=' : '') . $sec;
			}
		} else if (isset($map[$item])) {
			$typ = $map[$item][0];
			$idx = empty($map[$item][1]) ? $item : $map[$item][1];
			switch ($typ) {
				case 0:
					if ($smd_date[$idx]) {
						$ret[] = (($html) ? $hid.'=' : '') . $smd_date[$idx];
					}
					break;
				case 1:
					if (!empty($cal_global[$idx])) {
						$ret[] = (($html) ? $hid.'=' : '') . $cal_global[$idx];
					}
					break;
				case 2:
					if (!empty($pretext[$idx])) {
						$ret[] = (($html) ? $hid.'=' : '') . $pretext[$idx];
					}
					break;
				case 3:
					if ($thisarticle != NULL && isset($thisarticle[$idx]) && !empty($thisarticle[$idx])) {
						$ret[] = (($html) ? $hid.'=' : '') . $thisarticle[$idx];
					}
					break;
			}
		} else if (array_key_exists($item, $pretext)) {
			if ($pretext[$item]) {
				$ret[] = (($html) ? $hid.'=' : '') . $pretext[$item];
			}
		} else if (isset($cal_global[$item])) {
			if (!empty($cal_global[$item])) {
				$ret[] = (($html) ? $hid.'=' : '') . $cal_global[$item];
			}
		} else {
			if ($thisarticle != NULL && isset($thisarticle[$item]) && !empty($thisarticle[$item])) {
				$ret[] = (($html) ? $hid.'=' : '') . $thisarticle[$item];
			}
		}
	}
	$ret = array_unique($ret);
	$out = (($join_prefix=="SMD_AUTO") ? (($html) ? '?' : '') : $join_prefix).join($join, $ret);
	return ($escape=='html') ? htmlspecialchars($out) : $out;
}

// Return a formatted timestamp, with optional 'time now' override
function smd_cal_now($atts) {
	global $dateformat;

	extract(lAtts(array(
		'format' => $dateformat,
		'now'    => '',
		'offset' => '',
		'gmt'    => '',
		'lang'   => '',
	), $atts));

	$theDay = (gps('d') && is_numeric(gps('d'))) ? (int)gps('d') : safe_strftime('%d');
	$theMonth = (gps('m') && is_numeric(gps('m'))) ? (int)gps('m') : safe_strftime('%m');
	$theYear = (gps('y') && is_numeric(gps('y'))) ? (int)gps('y') : safe_strftime('%Y');
	if ($now) {
		$now = str_replace("?month", date('F', mktime(12,0,0,$theMonth,$theDay,$theYear)), $now);
		$now = str_replace("?year", $theYear, $now);
		$now = str_replace("?day", $theDay, $now);
		$now = is_numeric($now) ? $now : strtotime($now);
	} else {
		$now = time();
	}

	if ($offset) {
		$now = strtotime($offset, $now);
	}

	$format = smd_cal_reformat_win($format, $now);
	return safe_strftime($format, $now, $gmt, $lang);
}

// Set user-defined classes for a cell
function smd_cal_class($atts) {
	global $smd_cal_ucls;

	extract(lAtts(array(
		'name' => '',
	), $atts));

	$name = do_list($name);
	$smd_cal_ucls = array_merge($smd_cal_ucls, $name);
}

// <txp:article_custom /> replacement(ish) tag that understands how to handle recurring events
function smd_article_event($atts, $thing=NULL) {
	global $prefs, $pretext, $thispage, $thisarticle, $smd_eventinfo, $smd_cal_flag, $smd_date;

	extract(lAtts(array(
		'time'        => 'any',
		'type'        => 'standard,recur,multi',
		'expired'     => '',
		'id'          => '',
		'category'    => '',
		'section'     => '',
		'author'      => '',
		'realname'    => '',
		'custom'      => '',
		'status'      => 'live',
		'param_delim' => ':',
		'sort'        => '',
		'form'        => '',
		'stepfield'   => '',
		'skipfield'   => '',
		'omitfield'   => '',
		'extrafield'  => '',
		'allspanned'  => '0',
		'datefields'  => '',
		'month'       => '',
		'from'        => '',
		'to'          => '',
		'offset'      => 0,
		'limit'       => '10',
		'eventlimit'  => '10',
		'paging'      => '1',
		'pageby'      => '',
		'pgonly'      => '',
		'wraptag'     => '',
		'break'       => '',
		'class'       => '',
		'debug'       => 0,
	), $atts));

	// Phase 1 filters
	$filtSQL = $subSQL = array();
	if($category) {
		$tmp = doQuote(join("','", doSlash(do_list($category))));
		$filtSQL[] = '( Category1 IN ('.$tmp.') OR Category2 IN ('.$tmp.') )';
	}
	if($section) {
		$filtSQL[] = 'Section IN ('.doQuote(join("','", doSlash(do_list($section)))).')';
	}
	if($realname) {
		$authors = safe_column('name', 'txp_users', 'RealName IN ('. doQuote(join("','", doArray(do_list($realname), 'urldecode'))) .')' );
		$author = join(',', $authors);
	}
	if($author) {
		$filtSQL[] = 'AuthorID IN ('.doQuote(join("','", doSlash(do_list($author)))).')';
	}
	if($id) {
		$filtSQL[] = 'ID IN ('.join(',', array_map('intval', do_list($id))).')';
	}
	if($custom) {
		$custs = do_list($custom);
		$validOps = array('=', '!=', '>', '>=', '<', '<=', 'like', 'not', 'not like');
		foreach ($custs as $set) {
			if (strpos($set, $param_delim) !== false) {
				$clauseOpts = do_list($set, $param_delim);
				$fld = $clauseOpts[0];
				$oper = ((count($clauseOpts) == 3) && (in_array(strtolower($clauseOpts[1]), $validOps))) ? $clauseOpts[1] : '=';
				$clause = (count($clauseOpts) == 3) ? $clauseOpts[2] : ((count($clauseOpts) == 2) ? $clauseOpts[1] : '');
				$filtSQL[] = $fld . " $oper " . doQuote(doSlash($clause));
			}
		}
	}

	$type = do_list($type);
	$pageby = (empty($pageby) ? $limit : $pageby);

	foreach ($type as $evtyp) {
		switch($evtyp) {
			case 'standard':
				if ($stepfield) {
					$subSQL[] = "(".$stepfield." = '' AND Expires = ".NULLDATETIME.")";
				}
				break;
			case 'recur':
				if ($stepfield) {
					$subSQL[] = "(".$stepfield." != '')";
				}
				break;
			case 'multi':
				if ($stepfield) {
					$subSQL[] = "(".$stepfield." = '' AND Expires != ".NULLDATETIME.")";
				}
				break;
		}
	}
	if ($subSQL) {
		$filtSQL[] = '('.join(' OR ', $subSQL).')';
	}

	$status = ($status) ? $status : 'live'; // in case status has been emptied
	$status = do_list($status);
	$stati = array();
	foreach ($status as $stat) {
		if (empty($stat)) {
			continue;
		} else if (is_numeric($stat)) {
			$stati[] = $stat;
		} else {
			$stati[] = getStatusNum($stat);
		}
	}
	$filtSQL[] = 'Status IN ('.doQuote(join("','", $stati)).')';

	$expired = ($expired) ? $expired : $prefs['publish_expired_articles'];
	if (!$expired) {
		$filtSQL[] = '(now() <= Expires OR Expires = '.NULLDATETIME.')';
	}

	// Sorting rules: data is sorted once as it is extracted via SQL and then again after the fake dates have been inserted
	$sort = (empty($sort)) ? 'Posted asc' : $sort;
	$sort = do_list($sort);
	$sortPrefix = "SORT_";
	$sortOrder = array();
	for ($idx = 0; $idx < count($sort); $idx++) {
		$sorties = explode(' ', $sort[$idx]);
		if (count($sorties) <= 1) {
			$sorties[1] = "asc";
		}
		$sorties[1] = $sortPrefix.(($sorties[1] == "desc") ? 'DESC' : 'ASC');
		$sortOrder[] = array("by" => $sorties[0], "dir" => $sorties[1]);
	}
	$filtSQL = join(' AND ', $filtSQL);
	$filtSQL .= ' ORDER BY '.join(',',doSlash($sort));

	$grabCols = '*, unix_timestamp(Posted) as uPosted, unix_timestamp(LastMod) as uLastMod, unix_timestamp(Expires) as uExpires';
	$evlist = safe_rows($grabCols, 'textpattern', $filtSQL, $debug);

	if ($debug>2) {
		echo "++ RECORD SET ++";
		dmp($evlist);
	}
	$all_evs = $ev_tally = array();
	$now = time() + tz_offset();

	$eventlimit = do_list($eventlimit);
	if (count($eventlimit) == 1) {
		$eventlimit[1] = $eventlimit[0];
	}

	$datefields = do_list($datefields);

	// Phase 2: expand any recurring dates and collate all events that fall within the alloted ranges
	foreach ($evlist as $row) {
		$ev_posted = (!empty($datefields[0]) && !empty($row[$datefields[0]]) && ($stdt = strtotime($row[$datefields[0]])) !== false) ? $stdt : $row['uPosted']+tz_offset($row['uPosted']);
		$ev_expires = (isset($datefields[1]) && !empty($row[$datefields[1]]) && ($endt = strtotime($row[$datefields[1]])) !== false) ? $endt : (($row['uExpires']==0) ? 0 : $row['uExpires']+tz_offset($row['uExpires']));

		$skip = ($skipfield && $row[$skipfield] != '');
		$omit = ($omitfield && $row[$omitfield] != '');
		$recur = ($stepfield && $row[$stepfield] != '');
		$extra = ($extrafield && $row[$extrafield] != '');
		$multi = ($ev_expires > $ev_posted && (date("d-m-Y", $ev_expires) != date("d-m-Y", $ev_posted))) ? true : false;

		// If end < start the user-specified dates cannot be trusted
		if ($ev_expires != 0 && $ev_expires <= $ev_posted) {
			$ev_posted = $row['uPosted']+tz_offset($row['uPosted']);
			$ev_expires = (($row['uExpires']==0) ? 0 : $row['uExpires']+tz_offset($row['uExpires']));
			trigger_error('Expiry cannot be before start date in "'.$row['Title'].'": ignored', E_USER_WARNING);
		}
		if ($debug > 1) {
			echo '++ EVENT START // END ++';
			dmp($row['Title']);
			dmp($ev_posted, date('Y-m-d H:i:s', $ev_posted), $ev_expires, date('Y-m-d H:i:s', $ev_expires));
		}

		// Rewrite the start/end dates in case they are user-defined
		$row['uPosted'] = $ev_posted;
		$row['Posted'] = date("Y-m-d H:i:s", $ev_posted);
		$row['uExpires'] = $ev_expires;
		$row['Expires'] = ($ev_expires==0) ? '0000-00-00 00:00:00' : date("Y-m-d H:i:s", $ev_expires);

		$diff = ($ev_expires == 0) ? 0 : $ev_expires - $ev_posted;
		$ev_month = date('m', $ev_posted);
		$ev_year = date('Y', $ev_posted);
		$ev_hr = date('H', $ev_posted);
		$ev_mn = date('i', $ev_posted);
		$ev_sc = date('s', $ev_posted);
		$ignore = array();

		// Generate a skip array for this event
		if ($skip) {
			$ignores = do_list($row[$skipfield]);
			foreach ($ignores as $val) {
				$igrng = smd_expand_daterange($val, $ev_posted, $ev_expires);
				foreach ($igrng as $theval) {
					$ignore[] = date("d-M-Y", $theval); // Force each date to a known format
				}
			}
		}
		// Append any omitted events
		if ($omit) {
			$omits = do_list($row[$omitfield]);
			foreach ($omits as $val) {
				$omrng = smd_expand_daterange($val, $ev_posted, $ev_expires);
				foreach ($omrng as $theval) {
					$ignore[] = date("d-M-Y", $theval);
				}
			}
		}
		if ($debug > 1 && $ignore) {
			echo '++ IGNORED DATES ++';
			dmp($ignore);
		}

		// Does the base event deserve to be in the results?
		if (smd_include_event($ev_posted, $now, $ignore, $time, $from, $to, $month)) {
			$all_evs[] = array('ev' => $row, 'flags' => ($multi ? array('multifirst') : array('standard')) );
			$ev_tally[$row['uPosted']] = (isset($ev_tally[$row['uPosted']])) ? $ev_tally[$row['uPosted']]+1 : 1;
		}

		// Add any extra dates for this event
		if ($extra) {
			$xtra = do_list($row[$extrafield]);
			$xtras = array();

			// Make up an array of all extra dates
			foreach ($xtra as $val) {
				if (strpos($val, "+") === false) {
					$exrng = smd_expand_daterange($val);
					$xtras[] = date("Y-m-d", $exrng[0]);
					$spex = 0;
				} else {
					$fake_diff = safe_strtotime(date("Y-M-d", $ev_expires) . " 23:59:59");
					$fdiff = ($ev_expires==0) ? 0 : $fake_diff - $ev_posted;
					$chk = $allspanned && $multi && !$recur;
					$spidth = $chk ? ceil($fdiff / (60*60*24)) : 1; // days between dates
					$val = rtrim($val, '+');
					for ($jdx = 1; $jdx <= $spidth; $jdx++) {
						$xtras[] = date("Y-m-d", safe_strtotime($val . (($jdx==1) ? '' : '+'.($jdx-1).' days')));
					}
					$spex = $chk ? 1 : 0;
				}
			}

			$xtras = array_unique($xtras);

			$stampoff = (int)(3600*$ev_hr) + (int)(60*$ev_mn) + (int)$ev_sc;
			foreach ($xtras as $jdx => $val) {
				$tm = strtotime($val);
				$flags = array('extra');

				// No $ignore for additional events, as they always show up
				if (smd_include_event($tm+$stampoff, $now, array(), $time, $from, $to, $month)) {
					$fakerow = $row;
					$fakerow['Posted'] = date("Y-m-d H:i:s", $tm+$stampoff);
					$fakerow['uPosted'] = $tm+$stampoff;
					if ($diff > 0) {
						$fakerow['Expires'] = date("Y-m-d H:i:s", $tm+$stampoff+$diff);
						$fakerow['uExpires'] = $tm+$stampoff+$diff;
					}
					if ($spex) {
						$flags[] = ($jdx==0) ? 'multifirst' : (($jdx==$spidth-1) ? 'multilast' : 'multi');
					}
					$all_evs[] = array('ev' => $fakerow, 'flags' => $flags);
					$ev_tally[$fakerow['uPosted']] = (isset($ev_tally[$fakerow['uPosted']])) ? $ev_tally[$fakerow['uPosted']]+1 : 1;
				}
			}
		}

		if ($recur) {
			$flags = array('recurfirst');
			$freq = do_list($row[$stepfield]);
			$monthly = false;
			$currmonth = $ev_month;
			$curryear = $ev_year;
			foreach ($freq as $interval) {
				$fakerow = $row;
				$cstamp = $ev_posted;
				for($idx = 0; $idx < 99999; $idx++) {
					$lstamp = $cstamp;
					if ((isset($ev_tally[$row['uPosted']]) && ($ev_tally[$row['uPosted']] >= $eventlimit[0])) || ($to && $cstamp > safe_strtotime($to))) {
						break;
					}
					$ival = str_replace("?month", date('F', mktime(0,0,0,$currmonth,1)), $interval);
					$ival = str_replace("?year", $curryear, $ival);

					if (strpos($ival, "last") === 0) {
						$ival = date("l, F jS Y", strtotime( $ival, mktime(12, 0, 0, date("n", mktime(0,0,0,$currmonth,1,$curryear))+1, 1, $curryear) ));
						$monthly = true;
					} else if (strpos($ival, "first") === 0) {
						$ival = date("l, F jS Y", strtotime( $ival, mktime(12, 0, 0, (($currmonth>1) ? $currmonth-1 : 12), date("t", mktime(0,0,0,$currmonth-1,1,(($currmonth==1) ? $curryear-1: $curryear)) ), (($currmonth==1) ? $curryear-1: $curryear)) ));
						$monthly = true;
					} else if (strpos($ival, "this") === 0) {
						$monthly = true;
					}
					if (strpos($interval, "?month") || strpos($interval, "?year")) {
						$monthly = true;
	            }

					if ($monthly) {
						$cstamp = strtotime($ival);
					} else {
						$cstamp = strtotime($ival, $cstamp);
					}

					// This kludge takes account of timestamps like "last Thursday" (of the month). The last 'whatever day' of
					// a month can only be a maximum of 31 days before the last timestamp we saw, so check for that (+/- 10 mins)
					$diffstamp = $cstamp - $lstamp;
					if ($diffstamp < 0) {
						if ($diffstamp > -(60*60*24*31)+600) {
							$cstamp = false; // Some 'last weekday' of the previous month
						} else {
							break; // PHP_INT_MAX exceeded
						}
					}
					if ($cstamp !== false) {
						if ($debug > 1) {
							dmp("INTERVAL: ". $cstamp . ' // ' .date('d-M-Y H:i:s', $cstamp));
						}

						if (($cstamp < $ev_expires || $ev_expires == '0') && ($cstamp != $ev_posted)) {
							$show_me = smd_include_event($cstamp, $now, $ignore, $time, $from, $to, $month);
							if ($show_me) {
								$flags[] = 'recur';
								$fakerow['Posted'] = date("Y-m-d H:i:s", $cstamp);
								$fakerow['uPosted'] = $cstamp;
								$all_evs[] = array('ev' => $fakerow, 'flags' => $flags);
								$ev_tally[$row['uPosted']] = (isset($ev_tally[$row['uPosted']])) ? $ev_tally[$row['uPosted']]+1 : 1;
								$flags = array(); // reset so recurfirst is removed
							}
						} else {
							break;
						}
					}
					// Increment the month/year ready for the next interval
					if ($monthly) {
						$curryear = ($currmonth==12) ? $curryear+1 : $curryear;
						$currmonth = ($currmonth==12) ? 1 : $currmonth+1;
					}
				}
				if ($debug>1) {
					if (isset($ev_tally[$row['uPosted']])) {
						dmp("TALLY: ". $ev_tally[$row['uPosted']]);
					}
				}
			}
		} else if ($allspanned && date("Y-M-d", $ev_expires) != date("Y-M-d", $ev_posted)) {
			$postdate = date("Y-M-d H:i:s", $ev_posted);
			$fake_diff = safe_strtotime(date("Y-M-d", $ev_expires) . " 23:59:59");
			$diff = ($ev_expires==0) ? 0 : $fake_diff - $ev_posted;
			$spidth = ceil($diff / (60*60*24)); // days between dates
			for ($jdx = 1; $jdx < $spidth; $jdx++) {
				$flags = array();
				$tm = safe_strtotime($postdate.'+'.$jdx.' days');
				$show_me = smd_include_event($tm, $now, $ignore, $time, $from, $to, $month);
				if ($show_me) {
					$flags[] = ($jdx==$spidth-1) ? 'multilast' : 'multi';
					$fakerow = $row;
					$fakerow['Posted'] = date("Y-m-d H:i:s", $tm);
					$fakerow['uPosted'] = $tm;
					$all_evs[] = array('ev' => $fakerow, 'flags' => $flags);
				}
			}
		}
	}

	if ($debug>2) {
		echo "++ PRE-SORTED ++";
		dmp($all_evs);
	}

	// Make up an array_multisort arg list and execute it
	foreach($all_evs as $key => $entry) {
		$row = $entry['ev'];
		foreach ($row as $identifier => $item) {
			$varname = "col_".$identifier;
			${$varname}[$key] = $item;
		}
	}
	if(count($all_evs) > 0) {
		for ($idx = 0; $idx < count($sortOrder); $idx++) {
			$sortargs[] = '$col_'.$sortOrder[$idx]['by'];
			$sortargs[] = $sortOrder[$idx]['dir'];
		}
		$sortit = 'array_multisort('.implode(", ",$sortargs).', $all_evs);';
		eval($sortit);
	}

	if ($debug>2) {
		echo "++ POST-SORTED ++";
		dmp($all_evs);
	}

	// Handle paging
	if ($paging) {
		$grand_total = count($all_evs);
		$total = $grand_total - $offset;
		$numPages = ceil($total/$pageby);
		$pg = (!$pretext['pg']) ? 1 : $pretext['pg'];
		$pgoffset = $offset + (($pg - 1) * $pageby);
		// send paging info to txp:newer and txp:older
		$pageout['pg'] = $pg;
		$pageout['numPages'] = $numPages;
		$pageout['s'] = $pretext['s'];
		$pageout['c'] = $pretext['c'];
		$pageout['grand_total'] = $grand_total;
		$pageout['total'] = $total;

		if (empty($thispage))
			$thispage = $pageout;
		if ($pgonly)
			return;
	} else {
		$pgoffset = $offset;
	}

	// Phase 3: iterate over the new array obeying any offset/limit. Anything in the range gets populated and parsed
	$out = array();
	$ctr = 0;
	article_push();
	$lastposted = 0;
	foreach ($all_evs as $idx => $entry) {
		$smd_cal_flag = $smd_date = $smd_eventinfo = array();

		if ($idx >= $pgoffset && $ctr < $limit) {
			$row = $entry['ev'];

			$smd_cal_flag = $entry['flags'];

			$thisposted = date('Y-m-d', $row['uPosted']);
			$nextposted = isset($all_evs['ev'][$idx+1]) ? date('Y-m-d', $all_evs['ev'][$idx+1]['uPosted']) : 0;

			// Adjust times so txp:posted/expires return correct stamps
			$row['Posted'] = $row['Posted']-tz_offset(strtotime($row['Posted']));
			$row['uPosted'] = $row['uPosted']-tz_offset($row['uPosted']);
			$row['Expires'] = ($row['uExpires'] == 0) ? '0000-00-00 00:00:00' : $row['Expires']-tz_offset(strtotime($row['Expires']));
			$row['uExpires'] = ($row['uExpires'] == 0) ? 0 : $row['uExpires']-tz_offset($row['uExpires']);

			// Populate additional event information
			$fakestamp = ($row['uExpires'] == 0) ? 0 : strtotime(date("Y-M-d", $row['uExpires']) . " 23:59:59");
			$smd_eventinfo['duration'] = ($row['uExpires'] == 0) ? 0 : $row['uExpires'] - $row['uPosted'];
			$smd_eventinfo['durationdays'] = ($fakestamp) ? ceil(($fakestamp - $row['uPosted']) / (60*60*24)) : 0;
			$smd_date['y'] = strftime('%Y', $row['uPosted']);
			$smd_date['m'] = strftime('%m', $row['uPosted']);
			$smd_date['d'] = (int)strftime('%d', $row['uPosted']);
			$smd_date['w'] = strftime(smd_cal_reformat_win('%V', $row['uPosted']), $row['uPosted']);
			$smd_date['iy'] = strftime(smd_cal_reformat_win('%G', $row['uPosted']), $row['uPosted']);
			if ($row['uExpires'] == 0) {
				$smd_date['expy'] = $smd_date['expm'] = $smd_date['expd'] = $smd_date['expw'] = $smd_date['expiy'] = '';
			} else {
				$smd_date['expy'] = strftime('%Y', $row['uExpires']);
				$smd_date['expm'] = strftime('%m', $row['uExpires']);
				$smd_date['expd'] = (int)strftime('%d', $row['uExpires']);
				$smd_date['expw'] = strftime(smd_cal_reformat_win('%V', $row['uExpires']), $row['uExpires']);
				$smd_date['expiy'] = strftime(smd_cal_reformat_win('%G', $row['uExpires']), $row['uExpires']);
			}
			populateArticleData($row);
			$thisarticle['is_first'] = ($thisposted != $lastposted);
			$thisarticle['is_last'] = ($thisposted != $nextposted);
			$lastposted = $thisposted;
			$out[] = ($thing) ? parse($thing) : (($form) ? parse_form($form) : href($row['Posted'], permlinkurl($row), ' title="'.$row['Title'].'"') );
			$ctr++;
		}
	}
	article_pop();
	return doWrap($out, $wraptag, $break, $class);
}

// Try and output "nice" dates that read well across month/year boundaries.
// For example: 28 May - 05 Jun 2011 or May 11-16 2011, depending on format
function smd_event_duration($atts) {
	extract(lAtts(array(
		'start'     => posted(array('format' => '%s')),
		'end'       => expires(array('format' => '%s')),
		'format'    => '%d %b %Y',
		'separator' => ' &ndash; ',
		'debug'     => 0,
	), $atts));

	// Extract the relevant portions of the format so we can muck about with them
	preg_match_all('/\%([dejbBmhgGyY])/', $format, $matches);

	$indexes = array('day' => '', 'month' => '', 'year' => '');
	foreach($matches[1] as $idx => $token) {
		switch ($token) {
			case 'd':
			case 'e':
			case 'j':
				$indexes['day'] = $idx;
				break;
			case 'b':
			case 'B':
			case 'm':
			case 'h':
				$indexes['month'] = $idx;
				break;
			case 'g':
			case 'G':
			case 'y':
			case 'Y':
				$indexes['year'] = $idx;
				break;
		}
	}

	$day_first = ($indexes['day'] < $indexes['month']);
	$year_first = ($indexes['year'] == 0);
	$has_year = false;

	if ($end) {
		if (strftime('%Y %m %d', $start) == strftime('%Y %m %d',$end)) {
			 // begin and end on same day
			 $s_format = '';
			 $e_format = $format;
			 $has_year = true;
		 } else {
			if (strftime('%Y', $start) == strftime('%Y', $end)) {
				// same year
				if (strftime('%m', $start) == strftime('%m', $end)) {
					// and same month
					$re1 = ($day_first) ? '/\%[bBmhgGyY]/' : '/\%[gGyY]/';
					$re2 = ($day_first) ? '/\%[gGyY]/' : '/\%[bBmhgGyY]/';
	
					$s_format = trim(preg_replace($re1, '', $format));
					$e_format = ($re2) ? trim(preg_replace($re2, '', $format)) : $format;
				} else {
					// not same month
					$s_format = $e_format = trim(preg_replace('/\%[gGyY]/', '', $format));
				}
			} else {
				// different year
				$s_format = $e_format = $format;
				$has_year = true;
			}
			$s_format .= $separator;
		}
	
		// Add the year back in the correct position
		$s_format = ($has_year) ? $s_format : (($year_first) ? $matches[0][$indexes['year']] . ' ' . $s_format : $s_format);
		$e_format = ($has_year) ? $e_format : (($year_first) ? $e_format : $e_format . ' ' . $matches[0][$indexes['year']]);
	} else {
		$s_format = $format;
		$e_format ='';
	}

	return (($s_format) ? strftime($s_format, $start) : '') . (($e_format) ? strftime($e_format, $end) : '');
}

// An unoptimized workaround when "%V" and "%G" fails (usually on Windows)
// Algorithm adapted from http://www.personal.ecu.edu/mccartyr/ISOwdALG.txt with thanks.
// All other shortcut algorithms failed edge cases
function smd_cal_iso_week($format='%V', $time = null) {
	if (!$time) $time = time();
	
	$yr = strftime("%Y", $time);
	$leap = ( ( ($yr % 4 == 0) && ($yr % 100 != 0) ) || $yr % 400 == 0 );
	$leap_prev = ( ( (($yr-1) % 4 == 0) && (($yr-1) % 100 != 0) ) || ($yr-1) % 400 == 0 );
	$day_of_year = strftime('%j', $time);

	// Find the weekday of Jan 1st in the given year
	$yy = ($yr - 1) % 100;
	$c = ($yr - 1) - $yy;
	$g = $yy + ($yy / 4);
	$jan1weekday = 1 + ((((($c / 100) % 4) * 5) + $g) % 7);

	// Find weekday ( could use: $weekday = strftime('%u', $time); )
	$h = $day_of_year + ($jan1weekday - 1);
	$weekday = 1 + ( ($h - 1) % 7);

	// Find if $time falls in iso_year Y-1, iso_week 52 or 53
	if (($day_of_year <= (8 - $jan1weekday)) && $jan1weekday > 4) {
		$iso_year = $yr - 1;
		if ($jan1weekday == 5 || ($jan1weekday == 6 && $leap_prev)) {
			$iso_week = 53;
		} else {
			$iso_week = 52;
		}
   } else {
		$iso_year = $yr;
	}

	// Find if $time falls in iso_year Y+1, iso_week 1
	if ($iso_year == $yr) {
		$idx = ($leap) ? 366 : 365;
		if ( ($idx - $day_of_year) < (4 - $weekday) ) {
			$iso_year = $yr + 1;
			$iso_week = 1;
		}
	}

	// Find if $time falls in iso_year Y, iso_week 1 thru 53
	if ($iso_year == $yr) {
		$jdx = $day_of_year + (7 - $weekday) + ($jan1weekday - 1);
		$iso_week = $jdx / 7;
		if ($jan1weekday > 4) {
			$iso_week--;
		}
	}

	// Replacement array
	$reps = array(
		'%V' => str_pad($iso_week, 2, '0', STR_PAD_LEFT),
		'%G' => $iso_year,
		'%g' => substr($iso_year, 2),
	);
	return strtr($format, $reps);
}

// Adapted from: http://php.net/manual/en/function.strftime.php
function smd_cal_reformat_win($format, $ts = null) {
	// Only Win platforms need apply
	if (!is_windows()) return $format;
	if (!$ts) $ts = time();

	$mapping = array(
		'%C' => sprintf("%02d", date("Y", $ts) / 100),
		'%D' => '%m/%d/%y',
		'%e' => sprintf("%' 2d", date("j", $ts)),
		'%F' => '%Y-%m-%d',
		'%g' => smd_cal_iso_week('%g', $ts),
		'%G' => smd_cal_iso_week('%G', $ts),
		'%h' => '%b',
		'%l' => sprintf("%' 2d", date("g", $ts)),
		'%n' => "\n",
		'%P' => date('a', $ts),
		'%r' => date("h:i:s", $ts) . " %p",
		'%R' => date("H:i", $ts),
		'%s' => date('U', $ts),
		'%t' => "\t",
		'%T' => '%H:%M:%S',
		'%u' => ($w = date("w", $ts)) ? $w : 7,
		'%V' => smd_cal_iso_week('%V', $ts),
	);
	$format = str_replace(
		array_keys($mapping),
		array_values($mapping),
		$format
	);

	return $format;
}

// Find if the haystack contains one of the values in needle. Is there a cleverer way to do this?
function smd_cal_in_array($needle, $haystack) {
	foreach ($haystack as $val) {
		if (in_array($val, $needle)) {
			return true;
		}
	}
	return false;
}

// Check the passed timestamp against every time restriction and return true if it passes them all
function smd_include_event($ts, $now, $ign, $time, $from, $to, $month) {
	$show = array();
	$show[] = !in_array(date("d-M-Y", $ts), $ign);
	$time = do_list($time);
	$showor = false;
	foreach($time as $tm) {
		switch($tm) {
			case "any":
				$showor = true;
				break;
			case "future":
				$showor = $showor || (($ts > $now) ? true : false);
				break;
			case "today":
				$showor = $showor || (($ts >= strtotime(date('Y-m-d 00:00:00', $now)) && $ts <= strtotime(date('Y-m-d 23:59:59', $now))) ? true : false);
				break;
			default :
				$showor = $showor || (($ts < $now) ? true : false);
				break;
		}
	}
	$show[] = $showor;
	if ($from) { $show[] = ($ts >= safe_strtotime($from)) ? true : false; }
	if ($to) { $show[] = ($ts <= safe_strtotime($to)) ? true : false; }
	if ($month) { $show[] = (date("Y-m", $ts) == $month) ? true : false; }

	return (!in_array(0, $show)) ? true : false;
}

// Convert date ranges like 24-Oct-08 => 5-Nov-08 to an array of discrete date entities
// Also, weekday vals such as {Sun:Mon:Wed} would return those days between $start and $end
function smd_expand_daterange($range, $start='', $end='', $fmt='%s') {
	$out = array();
	$rng = do_list($range, "=>");

	if (count($rng) > 1) {
		// Range expansion
		$diff = safe_strtotime($rng[1]) - safe_strtotime($rng[0]);
		$diffdays = ceil($diff / (60*60*24)); // days between dates
		for ($jdx = 0; $jdx <= $diffdays; $jdx++) {
			$out[] = safe_strftime($fmt, safe_strtotime($rng[0] . (($jdx==0) ? '' : '+'.$jdx.' days')));
		}
	} else if ($start && $end && strpos($range, '{') === 0 && strpos($range, '}') === strlen($range)-1) {
		// Day of week expansion
		$days = do_list(trim($range,'{}'), ':');
		$diffdays = ceil(($end-$start) / (60*60*24));
		for ($jdx = 0; $jdx <= $diffdays; $jdx++) {
			$tm = $start + ($jdx*60*60*24);
			if (in_array(date('D', $tm), $days)) {
				$out[] = safe_strftime($fmt, $tm);
			}
		}
	} else {
		// Single date
		$out[] = safe_strftime($fmt, safe_strtotime($rng[0]));
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
#smd_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #f0e68c; color:#333; }
#smd_help code.block { font-weight:normal; border:1px dotted #999; display:block; margin:10px 10px 20px; padding:10px; }
#smd_help h1 { font: 20px Georgia, sans-serif; margin: 0; text-align: center; }
#smd_help h2 { border-bottom: 1px solid black; padding:10px 0 0; font: 17px Georgia, sans-serif; }
#smd_help h3 { font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0; text-decoration:underline; }
#smd_help h4 { font: bold 11px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0; text-transform: uppercase; }
#smd_help .atnm { font-weight:bold; }
#smd_help .mand { background:#eee; border:1px dotted #999; }
#smd_help table { width:90%; text-align:center; padding-bottom:1em; border-collapse:collapse; }
#smd_help td, #smd_help th { border:1px solid #999; padding:.5em; }
#smd_help ul { list-style-type:square; }
#smd_help .important { color:red; }
#smd_help li { margin:5px 20px 5px 30px; }
#smd_help .break { margin-top:5px; }
#smd_help dl dd { margin:2px 15px; }
#smd_help dl dd:before { content: "\21d2\00a0"; }
#smd_help dl dd dl { padding: 0 15px; }
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
<div id="smd_help">

	<h1>smd_calendar</h1>

	<p>Render a calendar with one or more articles as events on each day. Useful for gig guides, &#8220;what&#8217;s on&#8221; or scheduling apps.</p>

	<h2>Features</h2>

	<ul>
		<li>Full-size / mini <a href="#smdcal">calendar</a> by month, with <span class="caps">ISO</span> week support</li>
		<li>Nav: next/prev month or month/year dropdown. Year range can be from first/last article or +/- N years</li>
		<li>One article = one event, native to <span class="caps">TXP</span>: posted date = the date it appears in the calendar (overrideable)</li>
		<li>Filter events by cat / section / author / status / time / expiry</li>
		<li>Specify event frequency in custom field (1 week / 10 days / 3 months / etc)</li>
		<li>Custom fields for in/exclusions (dates on which an event is (re)scheduled/cancelled/omitted)</li>
		<li>Multi-day spanned events based on article expiry</li>
		<li>Display future, expired or sticky events</li>
		<li>Holidays per-calendar</li>
		<li>Pass each event to a form/container. Spanned and/or recurring events can be sent to a separate form. Cell format also customisable</li>
		<li><a href="#ifcal">Conditional</a> tests for flags and dates so you can build your own logic</li>
		<li>Table-, row-, cell-, and event-level classes for indicating different scenarios</li>
		<li>Tags to <a href="#artev">display recurring events</a> ; event/calendar <a href="#calinfo">characteristics</a> ; or the <a href="#calnow">current date/time</a></li>
	</ul>

	<h2>Author / credits</h2>

	<p><a href="http://stefdawson.com/contact">Stef Dawson</a>. Jointly funded by generous donators: mrdale, woof, jakob, renobird and joebaich. Originally based on mdp_calendar. All props to the original author, and of course the class upon which the calendar is based.</p>

	<h2>Installation / Uninstallation</h2>

	<p class="important">Requires Textpattern 4.4.1+</p>

	<p>Download the plugin from either <a href="http://textpattern.org/plugins/1052/smd_calendar">textpattern.org</a>, or the <a href="http://stefdawson.com/sw">software page</a>, paste the code into the <span class="caps">TXP</span> Admin-&gt;Plugins pane, install and enable the plugin. Create any needed <a href="#fldatts">custom fields</a>. Visit the <a href="http://forum.textpattern.com/viewtopic.php?id=29375">forum thread</a> for more info or to report on the success or otherwise of the plugin.</p>

	<p>To uninstall, delete from the Admin-&gt;Plugins page.</p>

	<h2>Naming convention</h2>

	<p>The core of the plugin is the smd_calendar tag, which renders a standard calendar. Each cell contains one or more events that occur on that day. Before diving into the tags here are the basics:</p>

	<ul>
		<li><span class="atnm">calid</span> : you may put more than one calendar on a page: each can be uniquely referenced with a calendar ID so they may be controlled independently</li>
		<li><span class="atnm">event</span> : an article. Any article written in the given section(s) will appear on the calendar as long as its expiry hasn&#8217;t been met. More than one event may appear in each cell</li>
		<li><span class="atnm">event flag</span> : events can either be <a href="#events">standard</a>, they can <a href="#recur">recur</a>, they can span <a href="#multi">multiple</a> days, dates can be <a href="#cancel">unscheduled or omitted</a> from the calendar, or <a href="#extra">extra</a> dates can be added</li>
		<li><span class="atnm">cell flag</span> : a series of flags are used to label each cell according to what it contains. Cells can either be empty (i.e. no date: the filler cells at the start/end of the month), they can be a regular day (no flag), they can contain an event, or may be a <a href="#holidays">holiday</a></li>
	</ul>

	<p>Flags provide information <em>about</em> the cell / event  &#8212; and can be tested with the conditional tag &#8212; while the corresponding class names are just there for your <span class="caps">CSS</span> use.</p>

	<p>When assigned as classes, flags <strong>always</strong> take the &#8216;class&#8217; prefix (i.e. the 1st item given in smd_calendar&#8217;s <code>classprefixes</code> attribute) &#8212; whether they appear at the <em>event</em> or <em>cell</em> levels. The only classes that take the optional 2nd &#8216;event&#8217; prefix are the fields you specify in the <code>eventclasses</code> attribute.</p>

	<h2 id="events">Standard cells and events</h2>

	<p>The following cumulative naming rules apply:</p>

	<ul>
		<li>Normal (day) cells don&#8217;t have a class assigned to them unless you specify one with <code>cellclass</code></li>
		<li>Any cell that contains an event (of any type) is flagged as an <code>event</code></li>
		<li>Any cell that falls on a holiday is given the flag <code>hols</code></li>
		<li>The current day is given the flag <code>today</code></li>
	</ul>

	<p>A single event (aka article) with a Posted date will be flagged <code>standard</code>, unless it recurs or spans more than one day.</p>

	<h2 id="recur">Recurring events</h2>

	<p>If you have nominated a field as your <code>stepfield</code> you may enter a comma-separated list of frequencies at which the event is to repeat. The format of the repetition is <strong>number interval</strong>, e.g. <code>4 weeks</code> or <code>10 days</code> or <code>6 months</code>. The plugin will do its best to figure out what you mean. See <a href="http://www.gnu.org/software/tar/manual/html_node/Date-input-formats.html">date input formats</a> for more.</p>

	<p>A few points:</p>

	<ul>
		<li><code>second tuesday</code> will show an event <em>every</em> 2nd Tuesday from the start date (i.e. fortnightly)</li>
		<li><code>second tuesday ?month ?year</code> will substitute the current month and year in first, and then calculate the date, resulting in the event only recurring on the 2nd Tuesday of each month. You <strong>must</strong> use both <code>?month</code> and <code>?year</code> if you choose this type of date or your event won&#8217;t appear</li>
		<li><code>20 ?month</code> will show the event on the 20th of every month</li>
		<li>things like <code>first thursday</code> or <code>last friday</code> or <code>this tuesday</code> don&#8217;t need the <code>?month</code> or <code>?year</code> because they can <em>only</em> occur monthly, and will only show the event on the given day of the month</li>
	</ul>

	<p>If you can find a use for it, you may specify multiple frequencies, for example <code>3 days, 1 week</code>. That means you would see the event on the 1st day, then the 3rd, 6th, 7th, 9th, 12th, 14th, 15th, 18th, 21st, 24th&#8230; days thereafter. Note that the event only occurs once on the 21st day even though the two frequencies clash.</p>

	<p>Recur rules:</p>

	<ul>
		<li>The repetition will continue forever<sup id="fnrev18490876304dffc033b9295" class="footnote"><a href="#fn18490876304dffc033b9295">1</a></sup> unless you specify an expiry time</li>
		<li>The very first event of a recurring set will be flagged with <code>recurfirst</code></li>
		<li>Each following cell that contains a recurring event will be flagged with <code>recur</code></li>
		<li>Both flags are applied to the event as classes using the class prefix</li>
		<li>Repeated events may be <a href="#cancel">cancelled or omitted</a> on a per-date basis</li>
	</ul>

	<p class="small" id="fn18490876304dffc033b9295"><sup>1</sup> &#8216;forever&#8217; is limited to 99999 recurrences per event, or 274 years&#8217; worth of daily events. Either <span class="caps">UNIX</span> will run out of dates or <span class="caps">PHP</span> will run out of memory long before you reach this limit :-)</p>

	<h2 id="multi">Multi-day (spanned) events</h2>

	<p>Any event that has:</p>

	<ul>
		<li>a start date</li>
		<li>an expiry date that is a day or more later than the start date, and</li>
		<li><em>does not</em> have any repetition in its <code>stepfield</code></li>
	</ul>

	<p>is flagged as a <code>multi</code>. By default the first event is displayed in full and each &#8216;continuation&#8217; event is shown only as a right arrow in subsequent cells. Events may span months or years and the plugin figures everything out using flag rules:</p>

	<ul>
		<li>The first cell of a spanned set is <code>multifirst</code></li>
		<li>The last cell is <code>multilast</code></li>
		<li>Every other cell is a <code>multi</code> <em>except</em> if the event rolls over into the next month. In that case, the entry on the 1st of the month is a <code>multiprev</code> to indicate it belongs to a previous event</li>
		<li>If the 1st day of the month is the last day of the event, the <code>multiprev</code> is dropped in favour of <code>multilast</code></li>
	</ul>

	<p>Switch off spanning completely with <code>showspanned=&quot;0&quot;</code>. Events that have an expiry then become standard events.</p>

	<p>&#8216;Continuation&#8217; cells may be processed with a separate form (<code>spanform</code>). If you choose not to use a <code>spanform</code>, the standard <code>form</code> or container will be used and you will have to distinguish between the different multi flags yourself using the <a href="#ifcal">conditional tag</a>.</p>

	<p>You can also <a href="#cancel">cancel or omit</a> days of a spanned event in the same manner as you do with <a href="#recur">recurring events</a>, except you cannot cancel the first day; you should move the event start date and apologise!</p>

	<h2 id="cancel">Cancelling and omitting events</h2>

	<p>Plans change and you may find that a gig has to be cancelled. Perhaps you advertise a weekly boot fair but the field is waterlogged one week. No problem: nominate a <code>skipfield</code> in your <code>smd_calendar</code> tag and enter the date of the cancelled event.</p>

	<p>Or you might run a theatre web site that has a three-week production performance on weeknights only. Instead of setting up three separate events &#8212; one for each week &#8212; nominate an <code>omitfield</code> and list the dates on which the performance does not air. Omitted dates will not, under any circumstances, appear on the calendar and they override cancelled dates/holidays. If <code>cellplus</code> mode is used, you will however see a cell flag labelled <code>omit</code> (plus class prefix) in case you do wish to style such cells.</p>

	<p>To specify the dates in either field, use any acceptable date format, but derivatives of dd-monthname-yyyy or monthname-dd-yyyy are the most unambiguous to avoid problems (e.g. is <code>1/5</code> Jan 5th or May 1st). You can specify as many cancellations or omissions as you like; comma-separate each one. You may also specify ranges of dates to omit/cancel by using the notation <code>start date =&gt; end date</code>.</p>

	<p>By default, cancelled events will not appear on the calendar. If you use <code>showskipped=&quot;1&quot;</code> the event will appear in the cell as normal and the <code>cancel</code> flag will apply to the event (and cell if you choose) so you may detect/style it.</p>

	<h2 id="extra">Extra dates</h2>

	<p>When events are <a href="#cancel">cancelled</a> you may elect to reschedule the event on a different date instead of having to create a new event with identical details. Use the <code>extrafield</code> to simply add it to the calendar on its new day. You might also use this feature if you have a <a href="#recur">recurring</a> event on the 1st of every month but for some reason you have to move one of the events a day or two. Simply cancel the offending date and add the new date using <code>extrafield</code>.</p>

	<p>The list of dates can be in any standard date format (including date ranges using <code>start =&gt; end</code>). They will be added to the calendar and flagged as <code>extra</code>. Also see the <code>extrastrict</code> attribute.</p>

	<p>If your original event spans more than one day, you may elect to schedule the entire block again. Add a &#8216;+&#8217; after any date you wish to repeat in its entirety, e.g. <code>2009-Mar-12+, 2009-Jun-18+, 2009-Feb-19</code> would copy the entire event block to March and June, but only the 1st date to Feb 19th. Events/cells are flagged as both <code>extra</code> and <code>multi</code> where applicable.</p>

	<p>Notes:</p>

	<ul>
		<li>if you schedule an extra event on a day that already contains the same event (perhaps on a spanned or recurring date) you may see two identical events on the same day. Try and avoid this, unless it&#8217;s your intended behaviour</li>
		<li>date ranges and the &#8216;+&#8217; syntax are mutually exlcusive on any date (e.g. <code>2009-Mar-10+ =&gt; 2009-Mar-15</code> is illegal)</li>
	</ul>

	<h2 id="holidays">Holidays</h2>

	<p>Public holidays need not be a nuisance to your events. Give the plugin a holiday list and it&#8217;ll make sure any recurring or multi events are not scheduled on those days. <a href="#events">Standard</a> one-off events are permitted by default because you might want to organise a special event on that day, though you can forbid those too if you wish.</p>

	<p>The list of dates you specify can be entered directly in a string in the <code>holidays</code> attribute or in a <code>&lt;txp:variable /&gt;</code>:</p>

<pre class="block"><code class="block">&lt;txp:variable name=&quot;nat_hols&quot;
     value=&quot;Dec 25, 26 Dec, 31/Dec, Jan-1,
        May 4 2009, 2009-08-31&quot; /&gt;
&lt;txp:smd_calendar holidays=&quot;txpvar:nat_hols&quot; /&gt;
</code></pre>

	<p>That list shows some of the variety and breadth of formats the plugin allows. There are more. Note that the events without a year occur on the same date every year, whereas the ones with a year will only occur on that specific date.</p>

	<p>Once your dates are defined you can control which events are allowed to fall on those dates via <code>holidayflags</code>. Combining that attribute, <code>showspanned</code>, <code>showskipped</code> and the forms &#8212; along with the conditional tag &#8212; can give a wide variety of ways to display events and cells.</p>

	<p>Holiday cells are given the flag <code>hols</code>, and any event that is not specifically permitted by the <code>holidayflags</code> is automatically assigned a <code>cancel</code> flag if it falls on one of the days. Omitted dates will, however, cause the <code>cancel</code> flag to be removed.</p>

	<h2 id="smdcal">Tag: <code>&lt;txp:smd_calendar /&gt;</code></h2>

	<p>Put this tag wherever you want your calendar to appear. Use the following attributes to control its output. The default value is unset unless stated otherwise.</p>

	<h3 class="atts">Display attributes</h3>

	<dl>
		<dt><span class="atnm">size</span></dt>
		<dd>Calendar size. Options:
	<dl>
		<dd>large</dd>
		<dd>small</dd>
	</dl></dd>
		<dd>The small is more geared towards a minical, although functionally there <a href="#sizediff">isn&#8217;t much between them</a></dd>
		<dd>Default: large</dd>
		<dt><span class="atnm">firstday</span></dt>
		<dd>First day of the week to show in the calendar. 0=Sunday, 1=Monday &#8230; 6=Saturday</dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">dayformat</span></dt>
		<dd>Way in which day names are rendered. Options:
	<dl>
		<dd><span class="caps">ABBR</span> shows abbreviated day names; Mon, Tue, Wed, etc</dd>
		<dd><span class="caps">FULL</span> uses full names</dd>
		<dd>Any valid <a href="http://uk2.php.net/strftime">strftime()</a> codes. Locale-specific names are returned</dd>
		<dd>A comma-separated list of custom day names surrounded by <code>{}</code> brackets</dd>
	</dl></dd>
		<dd>Example, for two-letter German weekdays: <code>dayformat=&quot;{So,Mo,Di,Mi,Do,Fr,Sa}&quot;</code>. The first day in the list <strong>must</strong> represent Sunday or things will break</dd>
		<dd>Default: <span class="caps">ABBR</span></dd>
		<dt><span class="atnm">monthformat</span></dt>
		<dd>Way in which the month names are rendered. Options:
	<dl>
		<dd><span class="caps">FULL</span> shows full month names</dd>
		<dd><span class="caps">ABBR</span> uses abbreviated names</dd>
		<dd>Any valid strftime() codes. Locale-specific names are returned</dd>
		<dd>A comma-separated list of custom month names surrounded by <code>{}</code> brackets</dd>
	</dl></dd>
		<dd>Example, for single-letter month names: <code>monthformat=&quot;{J,F,M,A,M,J,J,A,S,O,N,D}&quot;</code>. The first month in the list <strong>must</strong> represent January or things will break</dd>
		<dd>Default: <span class="caps">FULL</span></dd>
		<dt><span class="atnm">select</span></dt>
		<dd>Use a select dropdown for rapid access to weeks, months or years instead of fixed names. Choose one or more of:
	<dl>
		<dd>week</dd>
		<dd>month</dd>
		<dd>year</dd>
	</dl></dd>
		<dd>Note: week and month are mutually exclusive</dd>
		<dd>You may also specify up to two extra arguments, separated by <code>:</code> chars. These add text in front of and behind the week/month/year, respectively</dd>
		<dd>Example: <code>select=&quot;week:WEEK#, year:&lt;:&gt;&quot;</code> displays a select list with entries like this: <code>WEEK#15 &lt;2009&gt;</code></dd>
		<dt><span class="atnm">selectbtn</span></dt>
		<dd>Add a dedicated submit button to week/month/year select lists. Specify the text you wish to appear on the button. It will have the <span class="caps">CSS</span> class name <code>smd_cal_input</code></dd>
		<dd>Default: unset (i.e. auto-submit on select list change)</dd>
		<dt><span class="atnm">isoweeks</span></dt>
		<dd>Show <span class="caps">ISO</span> week numbers as a column at the start of the calendar. Any text in this attribute enables the feature, and becomes the heading of the <span class="caps">ISO</span> week column</dd>
		<dd>You may change the default week number in each cell by adding a comma and some text; whatever you enter will be put in each <span class="caps">ISO</span> week cell. Use the following replacement codes in your markup to insert the relevant info:
	<dl>
		<dd>{week}</dd>
		<dd>{month}</dd>
		<dd>{year}</dd>
		<dd>{isoyear}</dd>
	</dl></dd>
		<dd>Example: <code>isoweeks=&quot;wk, #{week}&quot;</code> will put &#8216;wk&#8217; at the top of the column and something like <code>#24</code>, <code>#25</code>, <code>#26</code>&#8230; beneath it. If the first item is omitted, there will be no column heading</dd>
		<dd>Note that if this feature is enabled, <code>firstday</code> will be forced to start on a Monday as governed by the <span class="caps">ISO</span> specification</dd>
		<dt><span class="atnm">navarrow</span></dt>
		<dd>Comma-separated pair of items you want to appear as prev/next arrows in the calendar</dd>
		<dd>Default: <code>&amp;#60;, &amp;#62;</code></dd>
		<dt><span class="atnm">caption</span></dt>
		<dd>Add a caption to the calendar</dd>
		<dt><span class="atnm">summary</span></dt>
		<dd>Add a summary to the calendar</dd>
	</dl>

	<h3 class="atts">Filter attributes</h3>

	<dl>
		<dt><span class="atnm">time</span></dt>
		<dd>Which events to display. Options:
	<dl>
		<dd>any</dd>
		<dd>past</dd>
		<dd>future</dd>
		<dd>today</dd>
	</dl></dd>
		<dd>Default: any</dd>
		<dt><span class="atnm">expired</span></dt>
		<dd>Hide or show expired events. Options:
	<dl>
		<dd>unset (use the <span class="caps">TXP</span> Preference &#8216;Publish expired articles&#8217;)</dd>
		<dd>0 (hide)</dd>
		<dd>1 (show)</dd>
	</dl></dd>
		<dt><span class="atnm">status</span></dt>
		<dd>Events in this status list are published on the calendar. Options:
	<dl>
		<dd>live</dd>
		<dd>sticky</dd>
	</dl></dd>
		<dd>Default: live</dd>
		<dt><span class="atnm">category</span></dt>
		<dd>Filter events by this list of categories</dd>
		<dt><span class="atnm">subcats</span></dt>
		<dd>Consider sub-categories. Choose a numeric nesting level to consider, or the word <code>all</code></dd>
		<dt><span class="atnm">section</span></dt>
		<dd>Filter events by this list of sections</dd>
		<dt><span class="atnm">author</span></dt>
		<dd>Filter events by this list of author login names</dd>
		<dt><span class="atnm">realname</span></dt>
		<dd>Filter events by this list of author Real Names (note: may add one extra query)</dd>
		<dt><span class="atnm">showall</span></dt>
		<dd>If your calendar appears on the front page of your site and you have <em>not</em> used the <code>section</code> attribute, then:
	<dl>
		<dd>0: only shows events from sections marked with <code>On front page</code></dd>
		<dd>1: shows all events from all sections</dd>
	</dl></dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">month</span></dt>
		<dd>Start the calendar on this month from 1 (Jan) to 12 (Dec). Normal calendar navigation overrides this value</dd>
		<dd>If <code>static</code> is used, this month becomes the only one you may view</dd>
		<dd>If unset, and no <code>m=</code> value appears on the <span class="caps">URL</span> line, the current month is used</dd>
		<dd><span class="atnm">week</span></dd>
		<dd>Start the calendar during the month containing this <span class="caps">ISO</span> week (from 1 to 53)</dd>
		<dd>Ignored if <code>static</code> or one of the calendar navigation controls is used</dd>
		<dd>If a <code>w=</code> value appears on the <span class="caps">URL</span> line, the given week overrides any month value</dd>
		<dt><span class="atnm">year</span></dt>
		<dd>Start the calendar at this 4-digit year. Normal calendar navigation overrides this value</dd>
		<dd>If <code>static</code> is used, this year becomes the only one you may view</dd>
		<dd>If unset, and no <code>y=</code> value appears on the <span class="caps">URL</span> line, the current year is used</dd>
		<dt><span class="atnm">static</span></dt>
		<dd>Force the calendar to be fixed to one month/year (i.e. no navigation). Month and year decided by attributes <code>month</code> and <code>year</code> or, if omitted, the current date will be used</dd>
	</dl>

	<h3 class="atts">Form attributes</h3>

	<dl>
		<dt><span class="atnm">form</span></dt>
		<dd>Use the given <span class="caps">TXP</span> form to process each event</dd>
		<dd>If the smd_calendar tag is used as a container it will be used in preference to the form</dd>
		<dd>If neither are used, a default is used (Large: hyperlinked article title, Small: empty event)</dd>
		<dt><span class="atnm">spanform</span></dt>
		<dd>Display spanned events differently to standard events; they usually use the same <code>form</code>/container</dd>
		<dd>If neither are specified, a right-arrow will be used to indicate continuation of the previous day&#8217;s event</dd>
		<dd>Note: the first day of a spanned set is <em>not</em> passed to the <code>spanform</code>; only continuation cells are passed to it</dd>
		<dt><span class="atnm">recurform</span></dt>
		<dd>Display recurring events differently to standard events</dd>
		<dd>Note: the first event of a recurring set is <em>not</em> passed to <code>recurform</code></dd>
		<dt><span class="atnm">cellform</span> (<span class="important">only on large calendars</span>)</dt>
		<dd>Use if you wish to build each cell entirely from scratch</dd>
		<dd>There are some <a href="#cellform">replacement variables</a> you can use to insert dynamic pieces of information in your cells</dd>
		<dd>Note: you cannot use <span class="caps">TXP</span> article or plugin tags in the Form</dd>
		<dt><span class="atnm">headerform</span></dt>
		<dd>Use if you wish to build the header yourself</dd>
		<dd>There are some <a href="#hdrform">replacement variables</a> you can use to insert dynamic pieces of information in your header</dd>
	</dl>

	<h3 class="atts" id="fldatts">Field-based attributes (custom fields or other article fields)</h3>

	<dl>
		<dt><span class="atnm">stepfield</span></dt>
		<dd>ID of a field within which an event may be told to repeat</dd>
		<dd>Note: it is the field&#8217;s ID <em>not its name</em>, so for custom fields you must use <code>custom_1</code> or <code>custom_2</code> etc</dd>
		<dd>Without this attribute, no <a href="#recur">recurring events</a> may be defined</dd>
		<dt><span class="atnm">omitfield</span></dt>
		<dd>ID (not name) of a field that contains a list of dates on which this event is to be <a href="#cancel">omitted</a></dd>
		<dt><span class="atnm">skipfield</span></dt>
		<dd>ID (not name) of a field that contains a list of dates on which this event is <a href="#cancel">cancelled</a></dd>
		<dt><span class="atnm">extrafield</span>:</dt>
		<dd>ID (not name) of a field from which a list of <a href="#extra">additional event dates</a> may be given; the same event details will be copied to the new day(s)</dd>
		<dt><span class="atnm">extrastrict</span></dt>
		<dd>Control visibility of extra dates on the calendar. Options:
	<dl>
		<dd>0: <code>extrafield</code> dates automatically appear on the calendar</dd>
		<dd>1: restrict new dates from appearing after the event&#8217;s expiry date</dd>
	</dl></dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">showskipped</span></dt>
		<dd>Control visibility of cancelled events on the calendar. Options:
	<dl>
		<dd>0 (hide)</dd>
		<dd>1 (show)</dd>
	</dl></dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">showspanned</span></dt>
		<dd>Control visibility of spanned events on the calendar. Options:
	<dl>
		<dd>0 (hide)</dd>
		<dd>1 (show)</dd>
	</dl></dd>
		<dd>Default: 1</dd>
		<dt><span class="atnm">datefields</span></dt>
		<dd>IDs (not names) of up to two fields from which posted and expiry date stamps may be read. Comma-separate the field names; the first will be used as the Posted date and the second as the Expiry</dd>
		<dd>If either is omitted or mangled, the article&#8217;s &#8220;real&#8221; posted/expiry date will be used instead</dd>
		<dd>If the expiry date occurs before the start date, your datefields will be ignored and a warning will be issued</dd>
	</dl>

	<h3 class="atts">Config attributes</h3>

	<dl>
		<dt><span class="atnm">holidays</span></dt>
		<dd>List of dates that are decreed as <a href="#holidays">holidays</a></dd>
		<dd>May be deferred to a <code>&lt;txp:variable /&gt;</code>, in which case define your list in a named variable and use <code>holidays=&quot;txpvar:my_var_name&quot;</code> to read them into the plugin</dd>
		<dt><span class="atnm">holidayflags</span></dt>
		<dd>Permit certain event flags to be scheduled on holidays. List one or more of:
	<dl>
		<dd>standard</dd>
		<dd>recur</dd>
		<dd>multi</dd>
	</dl></dd>
		<dd>Default: <code>standard</code></dd>
		<dt><span class="atnm">id</span></dt>
		<dd><span class="caps">HTML</span> ID to apply to the table that holds the calendar. This becomes the value of the <span class="caps">URL</span> variable <code>calid</code></dd>
		<dd>Use this if you have more than one calendar on a page and wish to control them separately via the <span class="caps">URL</span> vars</dd>
		<dt><span class="atnm">navid</span></dt>
		<dd><span class="caps">HTML</span> ID to apply to the prev/next/month/year navigation form</dd>
		<dt><span class="atnm">yearwidth</span></dt>
		<dd>A comma-separated list that specifies how many years your calendar spans. Visitors will not be permitted to navigate (next/prev) the calendar outside this range. Options:
	<dl>
		<dd>0: use the earliest (posted) event as the earliest year, and the latest (modified or posted, whichever is greater) event as the latest year</dd>
		<dd>any other single number expands the range equally in past &amp; future directions</dd>
		<dd>any pair of numbers subtracts the first from the earliest event and adds the second number of whole years to the latest event</dd>
		<dd>adding <code>c</code> to either value causes the current year to be used instead of the earliest or latest event</dd>
	</dl></dd>
		<dd>Example: <code>yearwidth=&quot;2,4c&quot;</code> subtracts 2 years from the earliest event and adds 4 whole years to today&#8217;s date</dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">remap</span></dt>
		<dd>When dealing with multiple calendars on a page it is often beneficial to use different names for <code>w=</code>, <code>m=</code> or <code>y=</code> in the <span class="caps">URL</span> so you can navigate calendars individually. This attribute enables you to rename the w, m, &amp; y variable to any name specified after a colon (:)</dd>
		<dd>Example: <code>remap=&quot;w:wk, y:yr&quot;</code></dd>
		<dt><span class="atnm">linkposted</span> (<span class="important">only on small calendars</span>)</dt>
		<dd>Each cell that contains an event flag of the given type(s) will have its day number linked to the event&#8217;s true start date instead of the cell&#8217;s date. This allows you to always link to a valid article/event</dd>
		<dd>Note that if more than one event occurs in the cell, the link will only be to the first event the plugin finds</dd>
		<dd>Example: if you have a weekly event that starts on the 20th December 2008, setting <code>linkposted=&quot;recur&quot;</code> will cause the link to be <code>date=2008-12-20</code> every week. Without linkposted, the dates would be 2008-12-20, 2008-12-27, 2009-01-03, and so on</dd>
		<dd>Default: <code>recur, multi, multiprev, multilast</code> (i.e. any recurring or spanned event)</dd>
		<dt><span class="atnm">maintain</span></dt>
		<dd>Keep track of this comma-separated list of variables in the <span class="caps">URL</span> when navigating the calendar using the next/prev or month/year select lists. If you wish to maintain state yourself or do something exotic, empty this attribute first to avoid weirdness. Options:
	<dl>
		<dd>calid</dd>
		<dd>section</dd>
		<dd>category</dd>
		<dd>author</dd>
		<dd>article</dd>
		<dd>date</dd>
		<dd>pg</dd>
		<dd>any other <span class="caps">URL</span> variable of your choosing</dd>
	</dl></dd>
		<dd>Example: use <code>maintain=&quot;section, article, calid&quot;</code> if you have an individual article page with a calendar in a sidebar, so the currently viewed article will remain in view when changing date</dd>
		<dd>Default: calid</dd>
		<dt><span class="atnm">nameval</span></dt>
		<dd>Add your own name/value pairs to the calendar&#8217;s <span class="caps">URL</span>.</dd>
		<dd>Example: <code>nameval=&quot;tracker=mycal, keep=1&quot;</code> would add <code>?tracker=mycal&amp;keep=1</code> to the <span class="caps">URL</span>. Useful if you want to <code>maintain</code> some values which you can&#8217;t add to the <span class="caps">URL</span> on page load</dd>
	</dl>

	<h3 class="atts">Style attributes</h3>

	<dl>
		<dt><span class="atnm">classlevels</span></dt>
		<dd>Each flagged event can be given a <span class="caps">CSS</span> class based on its flag name(s). Classes can be applied to events, cells, or both. You may also promote (i.e. copy) all event classes that occur in a day to the cell itself so you can style the cell based on the events it contains. Options:
	<dl>
		<dd>event</dd>
		<dd>cell</dd>
		<dd>cellplus (for copying unique event classes to the containing cell)</dd>
	</dl></dd>
		<dd>Note you should not use <code>cell</code> and <code>cellplus</code> together because the latter overrides the former</dd>
		<dd>Default: cell, event</dd>
		<dt><span class="atnm">classprefixes</span></dt>
		<dd>Comma-separated list of up to two prefixes to apply to your class names. The first prefix is applied to cell-level classes (and flags) and the second prefix is applied to event classes (see <code>eventclasses</code>)</dd>
		<dd>If you only specify one prefix, it will be used for both. If you use <code>classprefixes=&quot;&quot;</code> then no prefixes will be used at all</dd>
		<dd>Default: smd_cal_, smd_cal_ev_</dd>
		<dt><span class="atnm">class</span></dt>
		<dd>Class name of the calendar table itself</dd>
		<dd>Default: unset</dd>
		<dt><span class="atnm">rowclass</span></dt>
		<dd>Class name of each table row</dd>
		<dd>Default: unset</dd>
		<dt><span class="atnm">cellclass</span></dt>
		<dd>Class name of each table cell</dd>
		<dd>Default: unset</dd>
		<dt><span class="atnm">emptyclass</span></dt>
		<dd>Class name of any cells that don&#8217;t contain a day number (i.e. the blank cells at the start &amp; end of a month)</dd>
		<dd>Default: empty</dd>
		<dt><span class="atnm">isoweekclass</span></dt>
		<dd>Class name of each cell containing an <span class="caps">ISO</span> week</dd>
		<dd>Default: week</dd>
		<dt><span class="atnm">navclass</span></dt>
		<dd>Class name of the prev/next month nav arrows. If a comma-separated list is used, the first item will be the name of the class of the previous month, the 2nd item of the next month. If a single value is used, both class names will be the same</dd>
		<dd>Default: navprev, navnext</dd>
		<dt><span class="atnm">myclass</span></dt>
		<dd>Class name of both the month and year in the calendar header (either the <code>&lt;span&gt;</code> or <code>&lt;select&gt;</code> tags). If <code>mywraptag</code> is used, the class is applied to the wraptag instead</dd>
		<dd>Default: unset</dd>
		<dt><span class="atnm">eventclasses</span></dt>
		<dd>Comma-separated list of items to add as classes to each event. Each are prefixed with the event prefix</dd>
		<dd>Example: <code>eventclasses=&quot;ID, AuthorID, custom_5&quot;</code> would add three classes to each event corresponding to the event&#8217;s ID, its author (login) name and the contents of custom_5</dd>
		<dd>If you use <code>cellplus</code>, these classes will be copied to the cell level. Some special names exist: <code>category</code> adds both Category1 and Category2 (if set); <code>gcat</code> will add the current &#8216;global&#8217; category (if filtering by category); <code>author</code> adds the author ID (if filtering by author); <code>section</code> adds the current section</dd>
		<dd>Default: category</dd>
		<dt><span class="atnm">eventwraptag</span></dt>
		<dd>(X)HTML tag, without brackets, to wrap each event with</dd>
		<dd>Default: span</dd>
		<dt><span class="atnm">mywraptag</span></dt>
		<dd>(X)HTML tag, without brackets, to wrap around <em>both</em> month + year dropdown select lists and submit button</dd>
		<dd>Default: unset</dd>
	</dl>

	<h3 id="cellform">Using a <code>cellform</code> with replacement variables</h3>

	<p>If you don&#8217;t like the layout of the default cell, you can do it yourself with the <code>cellform</code> attribute. The cells are generated <em>last</em>, so by the time the plugin reaches this attribute, all events have already been processed by any of your forms/containers. Thus you can&#8217;t use <span class="caps">TXP</span> article or plugin tags.</p>

	<p>To build your own cells you often need information such as the events that fall on a particular day; or the week, month or day numbers, etc. So you may also insert any of the following replacements to have the relevant value inserted among your markup:</p>

	<ul>
		<li>{day} / {dayzeros} : day of the week (1-31 / 01-31)</li>
		<li>{weekday} : weekday in the local language, or from your <code>dayformat</code> list</li>
		<li>{weekdayfull} or {weekdayabbr} : weekday in the local language</li>
		<li>{week} : <span class="caps">ISO</span> week number (01-53)</li>
		<li>{month} / {monthzeros} : month number (1-12 / 01-12)</li>
		<li>{monthname} : month name in the local language, or from your <code>monthformat</code> list</li>
		<li>{monthnamefull} or {monthnameabbr} : month name in the local language</li>
		<li>{year} : 4-digit year</li>
		<li>{shortyear} : 2-digit year</li>
		<li>{isoyear} : 4 digit <span class="caps">ISO</span> year</li>
		<li>{shortisoyear} : 2 digit <span class="caps">ISO</span> year</li>
		<li>{evid} : event (article) ID</li>
		<li>{events} : all events for the day</li>
		<li>{standard} : only standard events</li>
		<li>{recurfirst} / {recur} : various recurring events</li>
		<li>{allrecur} : all recurring events for the day</li>
		<li>{multifirst} / {multi} / {multiprev} / {multilast} : various multi events</li>
		<li>{allmulti} : all multi events for the day</li>
		<li>{cancel} : only cancelled events</li>
		<li>{extra} : only extra events</li>
	</ul>

	<h3 id="hdrform">Using a <code>headerform</code></h3>

	<p>You can create your own header if you wish and employ any of the following replacements in the markup:</p>

	<ul>
		<li>{firstday} : current weekday (as a number from 0 to 6)</li>
		<li>{daynames} : comma-separated day names</li>
		<li>{isoweekhead} : <span class="caps">ISO</span> week heading</li>
		<li>{week} : <span class="caps">ISO</span> week</li>
		<li>{month} : month</li>
		<li>{year} : year</li>
		<li>{isoyear} : <span class="caps">ISO</span> year</li>
	</ul>

	<h3 id="sizediff">Differences between large and small calendars</h3>

	<ul>
		<li><code>cellform</code> cannot be used on a small calendar</li>
		<li>By default, no event descriptions are placed in the small calendar. You can add them yourself if you wish using a form/container</li>
		<li>The only thing rendered in a small calendar cell is the hyperlinked date and any flags so you can style the boxes</li>
		<li><code>classlevels</code> are ignored: everything is automatically assigned at the cell level (i.e. <code>cellplus</code> is set)</li>
		<li><code>eventclasses</code> are still honoured if you wish to use a form to process them yourself</li>
		<li>The small calendar outputs year-month-day-title or messy permlinks only</li>
	</ul>

	<h2 id="ifcal">Tag: <code>&lt;txp:smd_if_cal&gt;</code></h2>

	<p>This conditional tag allows you &#8212; inside your container/forms  &#8212; to test certain conditions of the current event/cell. For enhanced conditional checking (perhaps in conjunction with <a href="#calnow">smd_cal_now</a>), consider the smd_if plugin. The default value is unset unless stated otherwise.</p>

	<h3 class="atts" id="attsifcal">Attributes</h3>

	<dl>
		<dt><span class="atnm">flag</span></dt>
		<dd>The cell or event flag(s) you want to test, each separated by a comma. List one or more of:
	<dl>
		<dd>event</dd>
		<dd>standard</dd>
		<dd>recurfirst</dd>
		<dd>recur</dd>
		<dd>multifirst</dd>
		<dd>multi</dd>
		<dd>multilast</dd>
		<dd>multiprev</dd>
		<dd>cancel</dd>
		<dd>omit</dd>
		<dd>extra</dd>
		<dd>hols</dd>
		<dd>today</dd>
		<dd><span class="caps">SMD</span>_ANY (will trigger if the cell or event contains any of the above)</dd>
	</dl></dd>
		<dt><span class="atnm">calid</span></dt>
		<dd>The calendar ID you wish to check for a match</dd>
		<dt><span class="atnm">year</span></dt>
		<dd>The year the current cell falls in</dd>
		<dt><span class="atnm">isoyear</span></dt>
		<dd>The <span class="caps">ISO</span> year the current cell falls in</dd>
		<dt><span class="atnm">month</span></dt>
		<dd>The month number (1-12) that the current cell falls in</dd>
		<dt><span class="atnm">week</span></dt>
		<dd>The <span class="caps">ISO</span> week number that the current cell falls in</dd>
		<dt><span class="atnm">day</span></dt>
		<dd>The day number the current cell falls in</dd>
		<dt><span class="atnm">logic</span></dt>
		<dd>Method of combining the nominated tests. Options:
	<dl>
		<dd>or: tag will trigger if at least one of the tests is true</dd>
		<dd>and: tag will only trigger if all the tests are true</dd>
	</dl></dd>
		<dd>Default: or</dd>
	</dl>

	<p>&#8216;And&#8217; logic is useful for checking if the cell is of a certain type <span class="caps">AND</span> is later than the 15th of the month, for example.</p>

	<p>Rudimentary comparators can be applied to the <code>(iso)year</code>, <code>month</code>, <code>week</code> and <code>day</code> attributes. Normally the value you supply will be tested for an exact match but if you prefix it with one of the following character sequences then the behaviour changes:</p>

	<ul>
		<li><code>&gt;</code> tests if attribute is greater than the given value (e.g. <code>year=&quot;&gt;2008&quot;</code>)</li>
		<li><code>&gt;=</code> tests if attribute is greater than or equal to the given value (e.g. <code>month=&quot;&gt;=7&quot;</code>)</li>
		<li><code>&lt;</code> tests if attribute is less than the given value</li>
		<li><code>&lt;=</code> tests if attribute is less than or equal to the given value</li>
		<li><code>!</code> tests if attribute is <em>not</em> the given value (e.g. <code>day=&quot;!15&quot;</code>)</li>
	</ul>

	<h2 id="calinfo">Tag: <code>&lt;txp:smd_cal_info /&gt;</code></h2>

	<p>Inside your smd_calendar container/forms, use this tag to output certain information about the current event.</p>

	<h3 class="atts " id="attscalinfo">Attributes</h3>

	<dl>
		<dt><span class="atnm">type</span></dt>
		<dd>Comma-separated list of types of information you want to display. Options:
	<dl>
		<dd>flag</dd>
		<dd>calid</dd>
		<dd>(iso)year</dd>
		<dd>month</dd>
		<dd>week</dd>
		<dd>day</dd>
		<dd>s (current section)</dd>
		<dd>category</dd>
		<dd>author</dd>
		<dd>realname</dd>
		<dd>article (id of the currently viewed article)</dd>
		<dd>any other article variable such as <code>section</code> (the current article&#8217;s section), <code>authorid</code>, <code>article_image</code>, etc</dd>
	</dl></dd>
		<dd>If using the <code>html</code> attribute, you may optionally specify the name you want the variable to appear as in the <span class="caps">URL</span> string. The variables all take on sensible defaults (e.g. &#8216;section&#8217; becomes <code>?s=&lt;section name&gt;</code>, &#8216;category1&#8217; becomes <code>?c=&lt;category1 name&gt;</code>, etc).</dd>
		<dd>Example: <code>&lt;txp:smd_cal_info type=&quot;catgeory:the_cat&quot; html=&quot;1&quot; /&gt;</code> means you would see <code>?the_cat=&lt;category1 name&gt;</code> in the <span class="caps">URL</span></dd>
		<dd>Default: flag</dd>
		<dt><span class="atnm">join</span></dt>
		<dd>The characters you want to use to separate each item you asked for. Note it is the characters <em>between</em> each item so the very first entry will <strong>not</strong> have the <code>join</code> in front of it (see <code>join_prefix</code>)</dd>
		<dd>Default: a space</dd>
		<dt><span class="atnm">join_prefix</span></dt>
		<dd>The string you want to put in front of the first item in the returned list. If you do not specify this attribute it tries to be clever:
	<dl>
		<dd>If using <code>type=&quot;flag&quot;</code> the join_prefix is set to the same as <code>join</code>. Thus with <code>join=&quot; cal_&quot;</code> you might get <code> cal_multi cal_today cal_hols</code></dd>
		<dd>If using <code>html=&quot;1&quot;</code> the join_prefix is set to a question mark, thus: <code>type=&quot;month,year,category&quot; html=&quot;1&quot;</code> might render <code>?m=12&amp;y=2008&amp;c=gigs</code>, which can be put straight on the end of an anchor</dd>
	</dl></dd>
		<dd>Default: <code>SMD_AUTO</code></dd>
		<dt><span class="atnm">html</span></dt>
		<dd>Control in which format the information is returned. Options:
	<dl>
		<dd>0: return items verbatim</dd>
		<dd>1: return items as a <span class="caps">URL</span> parameter string. This is useful if you are building your own content inside each cell via a <code>form</code> and wish to maintain the current search environment. If you allow people to filter events by category or author you can use this to return the &#8216;current&#8217; state of certain variables so you can pass them to the next page and maintain state</dd>
	</dl></dd>
		<dd>Note: Setting this attribute to 1 overrides the <code>join</code> attribute and sets it to an ampersand</dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">escape</span></dt>
		<dd>Escape <span class="caps">HTML</span> entities such as <code>&lt;</code>, <code>&gt;</code> and <code>&amp;</code> for page validation purposes. Use <code>escape=&quot;&quot;</code> to turn this off</dd>
		<dd>Default: <code>html</code></dd>
	</dl>

	<h2 id="calclass">Tag: <code>&lt;txp:smd_cal_class /&gt;</code></h2>

	<p>Inside your smd_calendat container/forms, use this tag to add a list of classes to the current cell/event. Very useful if building cells yourself because inside a <a href="#ifcal">conditional</a> tag you could add particular class names based on some value in a cell.</p>

	<h3 class="atts " id="attscalclass">Attributes</h3>

	<dl>
		<dt><span class="atnm">name</span></dt>
		<dd>Comma-separated list of classnames to add to the current cell/event. These are <strong>not</strong> subject to any <code>classprefixes</code> so will always appear exactly as you write them</dd>
		<dd>Default: unset</dd>
	</dl>

	<h2 id="calnow">Tag: <code>&lt;txp:smd_cal_now /&gt;</code></h2>

	<p>Return the current date/time, formatted however you please. Useful for extracting parts of the current system timestamp to compare things via other conditional plugins or the <code>&lt;txp:smd_if_cal&gt;</code> tag.</p>

	<h3 class="atts " id="attscalnow">Attributes</h3>

	<dl>
		<dt><span class="atnm">format</span></dt>
		<dd>The way you want the date/time represented. Use any valid <a href="http://uk2.php.net/strftime">strftime()</a> string. Has full Windows support, even for those values where strftime() indicates otherwise</dd>
		<dd>Default: the date format set in Basic Preferences.</dd>
		<dt><span class="atnm">now</span></dt>
		<dd>If you don&#8217;t want the time to be &#8216;now&#8217; you can state what time &#8216;now&#8217; is! Use any standard date/time format</dd>
		<dd>You may also use the codes <code>?day</code>, <code>?month</code> or <code>?year</code> in your time string which will do one of two things:
	<dl>
		<dd>replace the codes with the <span class="caps">URL</span> parameters <code>d=</code>, <code>m=</code> or <code>y=</code>, if they are being used</dd>
		<dd>use the current day, month or year (i.e. the parts of today&#8217;s date)</dd>
	</dl></dd>
		<dd>Default: now! (the time at which you call the tag)</dd>
		<dt><span class="atnm">offset</span></dt>
		<dd>An offset into the future that you wish to apply to <code>now</code></dd>
		<dd>Example: <code>2 months</code>. See <a href="#eg6">Example 6</a> for a practical application of this attribute</dd>
		<dt><span class="atnm">gmt</span></dt>
		<dd>Return either:
	<dl>
		<dd>0: local time according to the time zone set in Basic Prefs</dd>
		<dd>1: <span class="caps">GMT</span> time</dd>
	</dl></dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">lang</span></dt>
		<dd>An <span class="caps">ISO</span> language code that formats time strings suitable for the specified language (or locale) as defined by <a href="http://en.wikipedia.org/wiki/ISO_639"><span class="caps">ISO</span> 639</a></dd>
		<dd>Default: unset (i.e. use the value as stated in <span class="caps">TXP</span> prefs)</dd>
	</dl>

	<h2 id="artev">Tag: <code>&lt;txp:smd_article_event /&gt;</code></h2>

	<p>When you create recurring events, they really only exist once as a single article; the repetition is a trick. Thus the built-in article tags only show the single, real articles.</p>

	<p>This tag &#8212; similar in function to <code>&lt;txp:article_custom /&gt;</code> &#8212; allows you to list recurring articles as if they were &#8216;real&#8217; articles in the database. <span class="important">They don&#8217;t become real articles, they are just listed as such</span>.</p>

	<p>Inside the tag&#8217;s <code>form</code> or container you can use all existing article tags to display any information you like about each &#8216;virtual&#8217; article. The default value is unset unless stated otherwise.</p>

	<h3>Identical attributes to <a href="#smdcal">smd_calendar</a>:</h3>

	<dl>
		<dt><span class="atnm">stepfield</span></dt>
		<dt><span class="atnm">skipfield</span></dt>
		<dt><span class="atnm">omitfield</span></dt>
		<dt><span class="atnm">extrafield</span></dt>
		<dt><span class="atnm">datefields</span></dt>
		<dt><span class="atnm">section</span></dt>
		<dt><span class="atnm">category</span></dt>
		<dt><span class="atnm">author</span></dt>
		<dt><span class="atnm">realname</span></dt>
		<dt><span class="atnm">status</span></dt>
		<dt><span class="atnm">time</span></dt>
		<dt><span class="atnm">expired</span></dt>
	</dl>

	<h3>Other attributes</h3>

	<dl>
		<dt><span class="atnm">id</span></dt>
		<dd>Restrict events to this list of article IDs</dd>
		<dt><span class="atnm">custom</span></dt>
		<dd>Specify your own comma-separated list of custom field clauses to the query. Separate each field from its (optional) operator and clause with a colon (alterable via <code>param_delim</code>).</dd>
		<dd>Example: <code>custom=&quot;custom_3:like:Lion%, custom_6:Chessington&quot;</code> would add <code>AND custom_3 like &#39;Lion%&#39; AND custom_6=&#39;Chessington&#39;</code> to the database query.</dd>
		<dt><span class="atnm">param_delim</span></dt>
		<dd>Alter the separator character(s) between field-clause items in the <code>custom</code> attribute.</dd>
		<dd>Default: colon (:)</dd>
		<dt><span class="atnm">type</span></dt>
		<dd>Comma-separated list of event types to display. Options:
	<dl>
		<dd>standard</dd>
		<dd>recur</dd>
		<dd>multi</dd>
	</dl></dd>
		<dd>Default: standard, recur, multi</dd>
		<dt><span class="atnm">allspanned</span></dt>
		<dd>Control display of spanned events:</dd>
		<dd>0: any event that has a start date in the past will be omitted from the list</dd>
		<dd>1: display remaining days from spanned events that began in the past</dd>
		<dd>Example: use <code>allspanned=&quot;1&quot;</code> if you are listing remaining performance dates from a Broadway show&#8217;s schedule that started some months ago.</dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">month</span></dt>
		<dd>Only show events that occur in the given <span class="caps">YYYY</span>-mm</dd>
		<dt><span class="atnm">from</span></dt>
		<dd>Only show events with Posted dated beginning after this start date. Can be any valid date format</dd>
		<dt><span class="atnm">to</span></dt>
		<dd>Only show events with Posted dates up to this end date. Can be any valid date format</dd>
		<dt><span class="atnm">sort</span></dt>
		<dd>Order the events by this column (case-sensitive) and sort direction (asc or desc)</dd>
		<dd>Default: Posted asc</dd>
		<dt><span class="atnm">form</span></dt>
		<dd>Pass each matching event to the given <span class="caps">TXP</span> form. Note that using a container overrides this attribute, and if you specify neither a form nor container, you will see a list of article Posted dates</dd>
		<dt><span class="atnm">paging</span></dt>
		<dd>Unlike article_custom, events from the smd_article_event tag may be paged using <code>&lt;txp:older /&gt;</code> and <code>&lt;txp:newer /&gt;</code>. But if you wish to show an event list on the same page as a standard article list, the older/newer tags will navigate both lists simultaneously. Under this circumstance you may need to turn paging off (0). Options:
	<dl>
		<dd>0 (off)</dd>
		<dd>1 (on)</dd>
	</dl></dd>
		<dd>Default: 1</dd>
		<dt><span class="atnm">offset</span></dt>
		<dd>Begin displaying events from this numeric position, instead of from the start of the list of events</dd>
		<dd>Default: 0</dd>
		<dt><span class="atnm">limit</span></dt>
		<dd>Inly show this many events maximum <strong>per page</strong>, i.e. the number of events to display, whether they come from one &#8216;real&#8217; article or many</dd>
		<dd>Default: 10</dd>
		<dt><span class="atnm">eventlimit</span></dt>
		<dd>Only show this many events maximum <strong>per event</strong></dd>
		<dd>Example: if you have a weekly repeated event that lasts for four months and you set <code>eventlimit=&quot;6&quot;</code> you will only see a maximum of 6 events from every article containing repetition. The range (start and end date) is determined by other plugin attributes</dd>
		<dd>Default: 10</dd>
		<dt><span class="atnm">pageby</span></dt>
		<dd>Esoteric paging feature, identical to <code>&lt;txp:article /&gt;</code></dd>
		<dd>Default: same as <code>limit</code></dd>
		<dt><span class="atnm">pgonly</span></dt>
		<dd>Set to 1 to perform the paging action without displaying anything. Probably useless</dd>
		<dt><span class="atnm">wraptag</span></dt>
		<dd>The (X)HTML tag, without brackets, to wrap the list in</dd>
		<dt><span class="atnm">break</span></dt>
		<dd>The (X)HTML tag to separate each item with</dd>
		<dt><span class="atnm">class</span></dt>
		<dd>The <span class="caps">CSS</span> class to apply to the <code>wraptag</code></dd>
	</dl>

	<h3>The smd_article_event tag process</h3>

	<p>It is worth noting that this tag executes in 3-phases:</p>

	<ol>
		<li>Pre-filter: all events that match <code>type</code>, <code>category</code>, <code>section</code>, <code>author</code>, <code>status</code>, <code>id</code>, and <code>expired</code> are extracted</li>
		<li>Time-filter: any &#8220;time-based&#8221; attributes are then applied to the above list. At this point, any <code>extrafield</code>, <code>stepfield</code>, <code>omitfield</code>, or <code>skipfield</code> are calculated to find repeated dates (up to as many as <code>eventlimit</code> allows or the calculation exceeds the event&#8217;s expiry time). The attributes <code>time</code>, <code>month</code>, <code>from</code>, and <code>to</code> are used to refine the filtration here</li>
		<li>Output: whatever the previous phases have left behind is subject to any <code>paging</code>, <code>offset</code> and <code>limit</code> you may have specified, then wrapped and displayed</li>
	</ol>

	<h2 id="eventinfo">Tag: <code>&lt;txp:smd_event_info /&gt;</code></h2>

	<p>Identical tag to <a href="#calinfo" style="text-align:left;">txp:smd_calinfo /&gt;</a> but for use inside <code>&lt;txp:smd_article_event /&gt;</code>.</p>

	<h2 class="examples">Examples</h2>

	<h3 id="eg1">Example 1: basic calendar</h3>

	<p>You have the entire arsenal of <span class="caps">TXP</span> tags available to you in a calendar. Thus you can set the article&#8217;s start and end dates to the same day and set the start and end times to indicate the start and end of the event. You can then use standard <code>&lt;txp:posted /&gt;</code> or <code>&lt;txp:expires /&gt;</code> tags with various <code>format</code> strings to render the event&#8217;s criteria.</p>

	<p>Similarly you can use any other <span class="caps">TXP</span> tags to show as much or as little detail as you like in the calendar cell.</p>

<pre class="block"><code class="block">&lt;txp:smd_calendar section=&quot;events&quot;&gt;
&lt;div&gt;
   Event: &lt;txp:permlink&gt;&lt;txp:title /&gt;&lt;/txp:permlink&gt;
   &lt;br /&gt;&lt;txp:excerpt /&gt;
&lt;/div&gt;
&lt;div class=&quot;evtime&quot;&gt;
   Start: &lt;txp:posted format=&quot;%H:%M&quot; /&gt;
&lt;/div&gt;
&lt;div class=&quot;evtime&quot;&gt;
   End: &lt;txp:expires format=&quot;%H:%M&quot; /&gt;
&lt;/div&gt;
&lt;/txp:smd_calendar&gt;
</code></pre>

	<h3 id="eg2">Example 2: conditional calendar</h3>

	<p>Using the conditional tag you can take action if certain events contain particular flags. This example also shows a completely useless manner of employing <code>&lt;txp:smd_cal_now /&gt;</code>.</p>

<pre class="block"><code class="block">Time is: &lt;txp:smd_cal_now format=&quot;%T&quot; /&gt;
&lt;txp:smd_calendar form=&quot;evform&quot;
     stepfield=&quot;custom_3&quot; skipfield=&quot;custom_6&quot;
     spanform=&quot;multis&quot; /&gt;
</code></pre>

	<p>In form <code>evform</code>:</p>

<pre class="block"><code class="block">&lt;txp:smd_if_cal flag=&quot;recur&quot;&gt;
  &lt;txp:permlink&gt;(RECUR)&lt;/txp:permlink&gt;
&lt;txp:else /&gt;
   &lt;txp:permlink&gt;&lt;txp:title /&gt;&lt;/txp:permlink&gt;
   &lt;txp:smd_if_cal flag=&quot;multifirst&quot;&gt;
      &lt;span class=&quot;right&quot;&gt;&amp;laquo;--&lt;/span&gt;
   &lt;/txp:smd_if_cal&gt;
   &lt;txp:smd_if_cal flag=&quot;recurfirst&quot;&gt;
      &lt;span&gt;One of many...&lt;/span&gt;
   &lt;/txp:smd_if_cal&gt;
&lt;/txp:smd_if_cal&gt;
</code></pre>

	<p>And in form <code>multis</code>:</p>

<pre class="block"><code class="block">&lt;txp:smd_if_cal flag=&quot;multi, multiprev&quot;&gt;
   &lt;txp:permlink&gt;--&amp;raquo;--&lt;/txp:permlink&gt;
&lt;/txp:smd_if_cal&gt;
&lt;txp:smd_if_cal flag=&quot;multilast&quot;&gt;
   &lt;txp:permlink&gt;
      &lt;span class=&quot;left&quot;&gt;--&amp;raquo;&lt;/span&gt; END &lt;txp:title /&gt;
   &lt;/txp:permlink&gt;
&lt;/txp:smd_if_cal&gt;
</code></pre>

	<p>Notice that <code>multifirst</code> is tested inside the same form as standard events. This is because only <em>continuation spanned cells</em> are passed to the <code>spanform</code>; the first event of a spanned group is just like any standard event. Similarly, if you had been using <code>recurform</code> the first event of the recurring set would be processed in the usual form/container and every subsequent event would be passed to the dedicated form.</p>

	<h3 id="eg3">Example 3: classes</h3>

	<p>You could use the calendar tags to output various pieces of flag information to build your own class names. This example also demonstrates the <code>html</code> attribute of <code>&lt;txp:smd_cal_info /&gt;</code> to build up a query string that is passed along with an event&#8217;s <code>category1</code> when a visitor clicks the anchor.</p>

	<p>This allows your site visitors to filter events by category while retaining the ability to show the calendar for the current month/year and section they are viewing instead of dropping back to the current month/year like other calendar systems often do.</p>

<pre class="block"><code class="block">&lt;txp:smd_calendar isoweeks=&quot;WEEK#&quot;
     yearwidth=&quot;0,2&quot; select=&quot;year, month&quot;
     stepfield=&quot;custom_1&quot; skipfield=&quot;custom_2&quot;
     showskipped=&quot;1&quot; expired=&quot;1&quot;&gt;
   &lt;span class=&quot;&lt;txp:smd_cal_info join=&quot; cal_&quot; /&gt;&quot;&gt;
      &lt;txp:permlink&gt;&lt;txp:title /&gt;&lt;/txp:permlink&gt;
      &lt;a href=&quot;?c=&lt;txp:category1
         /&gt;&amp;&lt;txp:smd_cal_info type=&quot;s, year,
         month, calid&quot; html=&quot;1&quot; join_prefix=&quot;&quot;
         /&gt;&quot;&gt;&lt;txp:category1 title=&quot;1&quot; /&gt;&lt;/a&gt;
   &lt;/span&gt;
&lt;/txp:smd_calendar&gt;
</code></pre>

	<p>What is also useful about the <code>&lt;txp:smd_cal_info /&gt;</code> tag is that if a particular value is not set it will <em>not</em> be included in the output.</p>

	<h3 id="eg4">Example 4: upcoming events</h3>

	<p>List the next 5 upcoming &#8212; recurring &#8212; events, plus any standard and spanned events, formatting them as a definition list.</p>

<pre class="block"><code class="block">&lt;h2&gt;Upcoming Events&lt;/h2&gt;
&lt;txp:smd_article_event stepfield=&quot;custom_1&quot;
     wraptag=&quot;dl&quot; time=&quot;future&quot; eventlimit=&quot;5&quot;&gt;
   &lt;txp:if_different&gt;
     &lt;dt&gt;&lt;txp:posted format=&quot;%B %Y&quot; /&gt;&lt;/dt&gt;
   &lt;/txp:if_different&gt;
   &lt;dd&gt;
      &lt;txp:permlink&gt;&lt;txp:title/&gt;&lt;/txp:permlink&gt;
      &lt;txp:posted /&gt;
   &lt;/dd&gt;
&lt;/txp:smd_article_event&gt;
</code></pre>

	<p>If you add pagination tags you can flip through all the events; they will be displayed 10 at a time on each page (if you want to keep track of which page you are on as you flip through a calendar that is in a sidebar, add <code>pg</code> to the <code>maintain</code> attribute in your calendar).</p>

	<p>Note the hyperlinked title shown here will jump to the &#8216;real&#8217; article (the first date in the recurring set) when clicked, not to an article with a date matching the recurrence.</p>

	<p>If you wanted to allow people to book an event, try this in your hyperlinked individual article:</p>

<pre class="block"><code class="block">&lt;txp:if_individual_article&gt;
 &lt;txp:article limit=&quot;1&quot;&gt;
  &lt;h3&gt;&lt;txp:title /&gt;&lt;/h3&gt;
  &lt;txp:body /&gt;
  &lt;p&gt;Please choose a date to book:&lt;/p&gt;
  &lt;select&gt;
  &lt;txp:smd_article_event stepfield=&quot;custom_1&quot;
    type=&quot;recur&quot; id=&#39;&lt;txp:article_id /&gt;&#39;&gt;
    &lt;option
     value=&#39;&lt;txp:posted format=&quot;%G-%m-%d&quot;/&gt;&#39;&gt;
      &lt;txp:posted format=&quot;%m %d, $G&quot; /&gt;
    &lt;/option&gt;
  &lt;/txp:smd_article_event&gt;
  &lt;/select&gt;
 &lt;/txp:article&gt;
&lt;/txp:if_individual_article&gt;
</code></pre>

	<p>With some cunning you could even add the &#8216;virtual&#8217; date that they chose in your original smd_article_event list and pass it as a <span class="caps">URL</span> variable to your individual article where you could read the value and pre-select the date in the select list for the vistor.</p>

	<h3 id="eg5">Example 5: iCal synchronisation</h3>

	<p>How about being able to output your events in iCal format so other people can sync their calendars to yours? Put this in a new Page template in its own Section:</p>

<pre class="block"><code class="block">BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALNAME:Gigs Calendar
PRODID:-//Apple Computer, Inc//iCal 1.5//EN
X-WR-TIMEZONE:Europe/London
&lt;txp:smd_article_event form=&quot;icsitem&quot; time=&quot;any&quot;
section=&quot;gigs&quot; limit=&quot;1000&quot;&gt;
 BEGIN:VEVENT
 DTSTART:&lt;txp:posted format=&quot;%Y%m%dT%H%i%s&quot; /&gt;
 DTEND:&lt;txp:expires format=&quot;%Y%m%dT%H%i%s&quot; /&gt;
 SUMMARY:&lt;txp:title /&gt;
 END:VEVENT
&lt;/txp:smd_article_event&gt;
END:VCALENDAR
</code></pre>

	<p>That will output an iCal-formatted gig list (repeated or otherwise). If you got freaky with it and added some conditional logic inside the template you could even read in <span class="caps">URL</span> variables and plug them in. Thus you could link to it directly off the calendar itself, pass in the section, category or event info and have a customised iCal stream pumped out of Textpattern.</p>

	<p>Thanks to woof for bringing the original <a href="http://de-online.co.uk/2006/05/05/textpattern-and-ical">David Emery</a> article to my attention.</p>

	<h3 id="eg6">Example 6: redefining now</h3>

	<p>Using the <code>now</code> and <code>offset</code> attributes of <code>&lt;txp:smd_cal_now /&gt;</code> you can effectively set &#8216;now&#8217; to be any time you like and make calculations based on a particular date.</p>

	<p>Plugging the <code>?month</code> and <code>?year</code> codes in allows you to make <code>&lt;txp:smd_article_event /&gt;</code> track the calendar. So you can automatically show only the events that occur in the month the visitor is browsing via the calendar:</p>

<pre class="block"><code class="block">&lt;txp:smd_calendar stepfield=&quot;custom_1&quot; /&gt;
&lt;h2&gt;Events this month&lt;/h2&gt;
&lt;txp:smd_article_event stepfield=&quot;custom_1&quot;
     from=&#39;&lt;txp:smd_cal_now now=&quot;01-?month-?year&quot; /&gt;&#39;
     to=&#39;&lt;txp:smd_cal_now now=&quot;?month-?year&quot;
        offset=&quot;1 month&quot; /&gt;&#39; time=&quot;any&quot; wraptag=&quot;ul&quot;&gt;
&lt;li&gt;&lt;txp:permlink&gt;&lt;b&gt;&lt;txp:title/&gt;&lt;/b&gt;&lt;/txp:permlink&gt;&lt;/li&gt;
&lt;/txp:smd_article_event&gt;
</code></pre>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>