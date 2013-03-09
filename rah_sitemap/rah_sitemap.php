<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rah_sitemap';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '1.2';
$plugin['author'] = 'Jukka Svahn';
$plugin['author_uri'] = 'http://rahforum.biz';
$plugin['description'] = 'Build a valid advanced sitemap';

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
$plugin['type'] = '1';

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
	##################
	#
	#	rah_sitemap-plugin for Textpattern
	#	version 1.2
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

	if(@txpinterface == 'admin') {
		add_privs('rah_sitemap','1,2');
		register_tab('extensions','rah_sitemap','Sitemap');
		register_callback('rah_sitemap_page','rah_sitemap');
		register_callback('rah_sitemap_head','admin_side','head_end');
	} else 
		register_callback('rah_sitemap','textpattern');

/**
	Checks if the sitemap should be returned
*/

	function rah_sitemap_delivery() {
		global $pretext;
		
		$uri = $pretext['request_uri'];
		$uri = explode('/',$uri);
		$uri = array_reverse($uri);
		
		if(in_array($uri[0],array('sitemap.xml.gz','sitemap.xml')))
			return true;
		
		if(gps('rah_sitemap') == 'sitemap')	
			return true;
		
		return false;
	}

/**
	The sitemap
*/

	function rah_sitemap() {
		if(rah_sitemap_delivery() == false)
			return;
		
		global $s, $thissection, $thiscategory, $c, $pretext, $thispage, $thisarticle;
		
		@$pref = rah_sitemap_prefs();
		
		if(!isset($pref['zlib_output'])) {
			rah_sitemap_install();
			$pref = rah_sitemap_prefs();
		}

		header('Content-type: application/xml');

		if($pref['compress'] == 1 && function_exists('gzencode'))
			header('Content-Encoding: gzip');
		
		if($pref['zlib_output'] == 1)
			ini_set('zlib.output_compression','Off');
	
		$timestampformat = (!empty($pref['timestampformat'])) ? $pref['timestampformat'] : 'c';
		
		$out[] = 
			'<?xml version="1.0" encoding="utf-8"?>'.
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
			'<url><loc>'.hu.'</loc></url>';
		
		if($pref['nosections'] != 1) {
			
			$rs = 
				safe_rows(
					'name',
					'txp_section',
					rah_sitemap_in('name',$pref['sections'],'default') .
					" order by name asc"
				);
			
			$thisarticle['section'] = '';
			
			foreach($rs as $a) {
				
				$s = $thispage['s'] = $thissection['section'] =  $pretext['s'] = $a['name'];
				
				if($pref['permlink_section'])
					$out[] = '<url><loc>'.parse($pref['permlink_section']).'</loc></url>';
				else 
					$out[] = '<url><loc>'.pagelinkurl(array('s' => $a['name'])).'</loc></url>';
			}
			
			$s =  $thissection['section'] = $thispage['s'] = $pretext['s'] = '';
			
		}
		
		$notypes = '';
		
		if($pref['nocategories'] == 1)
			$notypes[] = 'article';
		if($pref['nofile'] == 1)
			$notypes[] = 'file';
		if($pref['noimage'] == 1)
			$notypes[] = 'image';
		if($pref['nolink'] == 1)
			$notypes[] = 'link';
		
		$not = explode(',',$pref['categories']);
		
		$rs = 
			safe_rows(
				'name,type,id',
				'txp_category',
				"name != 'root' " . rah_sitemap_in('and type',$notypes) . " order by name asc"
			);
		
		foreach($rs as $a) {

			if(in_array($a['type'].'_||_'.$a['name'],$not))
				continue;

			$c = $thiscategory['c'] = $pretext['c'] = $thispage['c'] = $a['name'];

			if($pref['permlink_category'])
				$out[] = 
					
					'<url><loc>'.
					
					htmlspecialchars(str_replace(
						array(
							'[type]',
							'[id]'
						),
						array(
							$a['type'],
							$a['id']
						),
						parse($pref['permlink_category'])
					)).
					
					'</loc></url>';
					
			else 
				$out[] = 
					'<url><loc>'.pagelinkurl(array('c' => $a['name'])).'</loc></url>';
		}
		
		$c = $thiscategory['c'] = $pretext['c'] = $thispage['c'] =  '';
		
		if($pref['noarticles'] != 1) {
			
			$sql[] = rah_sitemap_in(' and Category1',$pref['articlecategories']);
			$sql[] = rah_sitemap_in(' and Category2',$pref['articlecategories']);
			$sql[] = rah_sitemap_in(' and Status',$pref['articlestatus'],'1,2,3');
			$sql[] = rah_sitemap_in(' and Section',$pref['articlesections']);
			
			if($pref['articlefuture'])
				$sql[] = ' and Posted <= now()';
			if($pref['articlepast'])
				$sql[] = ' and Posted > now()';
			if($pref['articleexpired'])
				$sql[] = " and (Expires = '0000-00-00 00:00:00' or Expires >= now())";
			
			
			if($pref['permlink_article'])
				$columns = 
				 	'*,  unix_timestamp(Posted) as posted, '.
				 	'unix_timestamp(Expires) as uExpires, '.
				 	'unix_timestamp(LastMod) as uLastMod';
			else
				$columns = 
					'ID as thisid, Section as section, '.
					'Title as title, url_title, unix_timestamp(Posted)'.
					' as posted, unix_timestamp(LastMod) as uLastMod';
			
			$rs = 
				safe_rows(
					$columns,
					'textpattern',
					'1=1' . implode('',$sql). ' order by Posted desc'
				);
			
			foreach($rs as $a) {
				extract($a);
				
				if($pref['permlink_article']) {
					$thisarticle = 
						array(
							'thisid' => $ID,
							'posted' => $posted,
							'modified' => $uLastMod,
							'annotate' => $Annotate,
							'comments_invite' => $AnnotateInvite,
							'authorid' => $AuthorID,
							'title' => $Title,
							'url_title' => $url_title,
							'category1' => $Category1,
							'category2' => $Category2,
							'section' => $Section,
							'keywords' => $Keywords,
							'article_image' => $Image,
							'comments_count' => $comments_count,
							'body' => $Body_html,
							'excerpt' => $Excerpt_html,
							'override_form' => $override_form,
							'status'=> $Status
						)
					;
					
					@$url = htmlspecialchars(parse($pref['permlink_article']));
				}
				
				else {
					@$url = permlinkurl($a);
					
					/*
						Fix for gbp_permanent_links
					*/
					
					if(strpos($url,'/') === 0)
						$url = hu . ltrim($url,'/');
				}
				
				@$out[] = 
					'<url><loc>'.$url.'</loc><lastmod>'.
					($uLastMod < $posted ? 
						date($timestampformat,$posted) : 
						date($timestampformat,$uLastMod)
					).
					'</lastmod></url>'
				;
				
			}
			$thisarticle = '';
		}
		
		$rs = 
			safe_rows(
				'*, unix_timestamp(posted) as uposted',
				'rah_sitemap',
				'1=1 order by posted desc'
			);
		
		foreach($rs as $a) {
			$url = parse($a['url']);
			$out[] = '<url><loc>'.rah_sitemap_uri($url,1).'</loc>';
			if($a['include'] == 1)
				@$out[] = '<lastmod>'.date($timestampformat,$a['uposted']).'</lastmod>';
			$out[] = '</url>';
		}
		
		$out[] = '</urlset>';
		$out = implode('',$out);
		
		echo (@$pref['compress'] == 1 && function_exists('gzencode')) ? gzencode($out,$pref['compression_level']) : $out;
		exit();
	}

/**
	Delivers the panels
*/

	function rah_sitemap_page() {
		require_privs('rah_sitemap');
		global $step;
		if(in_array($step,array(
			'rah_sitemap_save',
			'rah_sitemap_custom_list',
			'rah_sitemap_custom_form',
			'rah_sitemap_custom_save',
			'rah_sitemap_delete'
		))) $step();
		else rah_sitemap_list();
	}

/**
	Preferences panel
*/

	function rah_sitemap_list($message='') {
		@$pref = rah_sitemap_prefs();

		if(!isset($pref['zlib_output'])) {
			rah_sitemap_install();
			$pref = rah_sitemap_prefs();
		}

		rah_sitemap_header(
			
			'	<form method="post" action="index.php">'.n.
			
			'		<p><strong>General preferences</strong></p>'.n.
			
			'		<p title="Click to expand" class="rah_sitemap_heading">'.n.
			'			+ <span>Exclude sections and categories from the sitemap</span>'.n.
			'		</p>'.n.
			
			'		<div class="rah_sitemap_more">'.n.
			
			rah_sitemap_listing('Exclude sections','sections','txp_section',"name != 'default'").
			rah_sitemap_listing('Exclude categories','categories','txp_category',"name != 'root' and title != 'root'").
			
			'			<div class="rah_sitemap_column">'.n.
			
			'				<strong>Advanced settings</strong><br />'.n.
			'				<label><input type="checkbox" name="nosections" value="1"'.(($pref['nosections'] == 1) ? ' checked="checked"' : '').' /> Exclude all section URLs</label><br />'.n.
			'				<label><input type="checkbox" name="nofile" value="1"'.(($pref['nofile'] == 1) ? ' checked="checked"' : '').' /> Exclude all file-type category URLs</label><br />'.n.
			'				<label><input type="checkbox" name="noimage" value="1"'.(($pref['noimage'] == 1) ? ' checked="checked"' : '').' /> Exclude all image-type category URLs</label><br />'.n.
			'				<label><input type="checkbox" name="nolink" value="1"'.(($pref['nolink'] == 1) ? ' checked="checked"' : '').' /> Exclude all link-type category URLs</label><br />'.n.
			'				<label><input type="checkbox" name="nocategories" value="1"'.(($pref['nocategories'] == 1) ? ' checked="checked"' : '').' /> Exclude all article-type category URLs</label><br />'.n.
			
			
			'			</div>'.n.
			
			'		</div>'.n.
			
			'		<p title="Click to expand" class="rah_sitemap_heading">'.n.
			'			+ <span>Filter articles from the sitemap</span>'.n.
			'		</p>'.n.
			
			'		<div class="rah_sitemap_more">'.n.
			
			
			rah_sitemap_listing('Exclude article sections','articlesections','txp_section',"name != 'default'").
			rah_sitemap_listing('Exclude article categories','articlecategories','txp_category',"name != 'root' and title != 'root' and type = 'article'").
			
			'			<div class="rah_sitemap_column">'.n.
			'				<strong>Advanced settings</strong><br />'.n.
			'				<label><input type="checkbox" name="noarticles" value="1"'.(($pref['noarticles'] == 1) ? ' checked="checked"' : '').' /> Don\'t include articles in sitemap</label><br />'.n.
			'				<label><input type="checkbox" name="articlestatus" value="5"'.(($pref['articlestatus'] == 5) ? ' checked="checked"' : '').' /> Exclude sticky articles</label><br />'.n.
			'				<label><input type="checkbox" name="articlefuture" value="1"'.(($pref['articlefuture'] == 1) ? ' checked="checked"' : '').' /> Exclude future articles</label><br />'.n.
			'				<label><input type="checkbox" name="articlepast" value="1"'.(($pref['articlepast'] == 1) ? ' checked="checked"' : '').' /> Exclude past articles</label><br />'.n.
			'				<label><input type="checkbox" name="articleexpired" value="1"'.(($pref['articleexpired'] == 1) ? ' checked="checked"' : '').' /> Exclude expired articles</label>'.n.
			'			</div>'.n.
			
			'		</div>'.n.
			
			'		<p><strong>Advanced settings</strong></p>'.n.
			
			'		<p title="Click to expand" class="rah_sitemap_heading">'.n.
			'			+ <span>Compression methods</span>'.n.
			'		</p>'.n.
			
			'		<div class="rah_sitemap_more">'.n.
			
			'			<p class="rah_sitemap_paragraph"><strong>Compression.</strong> With these settings you can control compression and even turn it off if it causes problems on your server. It is recommeded to leave compression on if possible. Compression level 0 is the mildest and 9 is the maximum compression.</p>'.n.
			'			<p>'.n.
			'				<label for="rah_sitemap_compress">Use Gzip compression:</label><br />'.n.
			
			'				<select name="compress" id="rah_sitemap_compress">'.n.
			'					<option value="1"'.(($pref['compress'] == 1) ? ' selected="selected"' : '').'>Yes</option>'.n.
			'					<option value="0"'.(($pref['compress'] == 0) ? ' selected="selected"' : '').'>No</option>'.n.
			'				</select>'.n.
			
			'			</p>'.n.
			'			<p>'.n.
			'				<label for="rah_sitemap_compression_level">Compression level:</label><br />'.n.
			
			'				<select name="compression_level" id="rah_sitemap_compression_level">'.n.
			'					<option value="0"'.(($pref['compression_level'] == 0) ? ' selected="selected"' : '').'>0</option>'.n.
			'					<option value="1"'.(($pref['compression_level'] == 1) ? ' selected="selected"' : '').'>1</option>'.n.
			'					<option value="2"'.(($pref['compression_level'] == 2) ? ' selected="selected"' : '').'>2</option>'.n.
			'					<option value="3"'.(($pref['compression_level'] == 3) ? ' selected="selected"' : '').'>3</option>'.n.
			'					<option value="4"'.(($pref['compression_level'] == 4) ? ' selected="selected"' : '').'>4</option>'.n.
			'					<option value="5"'.(($pref['compression_level'] == 5) ? ' selected="selected"' : '').'>5</option>'.n.
			'					<option value="6"'.(($pref['compression_level'] == 6) ? ' selected="selected"' : '').'>6</option>'.n.
			'					<option value="7"'.(($pref['compression_level'] == 7) ? ' selected="selected"' : '').'>7</option>'.n.
			'					<option value="8"'.(($pref['compression_level'] == 8) ? ' selected="selected"' : '').'>8</option>'.n.
			'					<option value="9"'.(($pref['compression_level'] == 9) ? ' selected="selected"' : '').'>9</option>'.n.
			'				</select>'.n.
			
			'			</p>'.n.
			
			'			<p>'.n.
			'				<label for="rah_sitemap_zlib_output">Set zlib output compression off. If set to no, configuration is not modified:</label><br />'.n.
			
			'				<select name="zlib_output" id="rah_sitemap_zlib_output">'.n.
			'					<option value="1"'.(($pref['zlib_output'] == 1) ? ' selected="selected"' : '').'>Yes</option>'.n.
			'					<option value="0"'.(($pref['zlib_output'] == 0) ? ' selected="selected"' : '').'>No</option>'.n.
			'				</select>'.n.
			
			'			</p>'.n.
			'		</div>'.n.
			
			
			'		<p title="Click to expand" class="rah_sitemap_heading">'.n.
			'			+ <span>Override timestamp format</span>'.n.
			'		</p>'.n.
			
			'		<div class="rah_sitemap_more">'.n.
			
			'			<p class="rah_sitemap_paragraph"><strong>Timestamps.</strong> Customize the date format used in last modified timestamps. Use <a href="http://php.net/manual/en/function.date.php">date()</a> string values. If unset (left empty) default ISO 8601 date (<code>c</code>) is used. Use this setting if you want to hard-code/override timestamps, timezones or if your server doesn\'t support <code>c</code> format.</p>'.n.
			
			'			<p>'.n.
			'				<label for="rah_sitemap_timestampformat">Format:</label><br />'.n.
			'				<input type="text" class="edit" style="width: 940px;" name="timestampformat" id="rah_sitemap_timestampformat" value="'.htmlspecialchars($pref['timestampformat']).'" />'.n.
			'			</p>'.n.
			
			'		</div>'.n.
			
			'		<p title="Click to expand" class="rah_sitemap_heading">'.n.
			'			+ <span>Override permlink formats</span>'.n.
			'		</p>'.n.
			
			'		<div class="rah_sitemap_more">'.n.
			
			'			<p class="rah_sitemap_paragraph"><strong>Permlinks.</strong> With these settings you can make the Sitemap\'s URLs to match your own URL rewriting rules, or permlinks made by a <em>custom permlink rule</em> plugin. You can leave these fields empty, if using TXP\'s inbuild permlink rules. Note that these setting do not rewrite TXP\'s permlinks for you, use only for matching not rewriting!</p>'.n.
			'			<p>'.n.
			'				<label for="rah_sitemap_permlink_category">Category URLs:</label><br />'.n.
			'				<input type="text" class="edit" name="permlink_category" id="rah_sitemap_permlink_category" value="'.htmlspecialchars($pref['permlink_category']).'" />'.n.
			'			</p>'.n.
			'			<p>'.n.
			'				<label for="rah_sitemap_permlink_section">Section URLs:</label><br />'.n.
			'				<input type="text" class="edit" name="permlink_section" id="rah_sitemap_permlink_section" value="'.htmlspecialchars($pref['permlink_section']).'" />'.n.
			'			</p>'.n.
			'			<p>'.n.
			'				<label for="rah_sitemap_permlink_article">Article URLs:</label><br />'.n.
			'				<input type="text" class="edit" name="permlink_article" id="rah_sitemap_permlink_article" value="'.htmlspecialchars($pref['permlink_article']).'" />'.n.
			'			</p>'.n.
			
			'		</div>'.n.
			
			'		<input type="hidden" name="event" value="rah_sitemap" />'.n.
			'		<input type="hidden" name="step" value="rah_sitemap_save" />'.n.
			
			'		<p><input type="submit" value="'.gTxt('save').'" class="publish" /></p>'.n.
			
			'	</form>',
			
			'rah_sitemap',
			'Manage your sitemap',
			'Sitemap.org Sitemaps',
			$message
			
		);
	}

/**
	Panel, lists custom URLs
*/

	function rah_sitemap_custom_list($message='') {

		$out[] = 
			'	<form method="post" action="index.php">'.n.
			'		<table id="list" class="list rah_sitemap_table"  border="0" cellspacing="0" cellpadding="0">'.n.
			'			<tr>'.n.
			'				<th>URL</th>'.n.
			'				<th>LastMod</th>'.n.
			'				<th>Include LastMod</th>'.n.
			'				<th>View</th>'.n.
			'				<th>&#160;</th>'.n.
			'			</tr>'.n;
		
		$rs =
			safe_rows(
				'url,posted,include',
				'rah_sitemap',
				"1=1 order by posted desc"
			);
		
		if($rs) {
			foreach($rs as $a) {
				$uri = rah_sitemap_uri($a['url'],1);
				$out[] = 
					'			<tr>'.n.
					'				<td><a href="?event=rah_sitemap&amp;step=rah_sitemap_custom_form&amp;edit='.urlencode($a['url']).'">'.$uri.'</a></td>'.n.
					'				<td>'.$a['posted'].'</td>'.n.
					'				<td>'.(($a['include'] == 1) ? 'Yes' : 'No').'</td>'.n.
					'				<td><a target="_blank" href="'.htmlspecialchars($uri).'">'.gTxt('view').'</a></td>'.n.
					'				<td><input type="checkbox" name="selected[]" value="'.htmlspecialchars($a['url']).'" /></td>'.n.
					'			</tr>'.n;
			}
		} else 
			$out[] =  '			<tr><td colspan="5">No custom URLs found.</td></tr>'.n;
		
		$out[] =  
			'		</table>'.n.
			'		<p id="rah_sitemap_step">'.n.
			'			<select name="step">'.n.
			'				<option value="">With selected...</option>'.n.
			'				<option value="rah_sitemap_delete">Delete</option>'.n.
			'			</select>'.n.
			'			<input type="submit" class="smallerbox" value="Go" />'.n.
			'		</p>'.n.
			'		<input type="hidden" name="event" value="rah_sitemap" />'.n.
			'	</form>'.n;
		
		rah_sitemap_header(
			$out,
			'rah_sitemap',
			'List of custom URLs',
			'Sitemap',
			$message
		);
	}

/**
	Panel to add custom URLs
*/

	function rah_sitemap_custom_form($message='') {
		
		$edit = gps('edit');
		
		if($edit) {
			
			$rs = 
				safe_row(
					'*',
					'rah_sitemap',
					"url='".doSlash($edit)."'"
				);
			
			if(!$rs) {
				rah_sitemap_custom_list('Selection not found.');
				return;
			}
			
			extract($rs);
		
		}
		
		if(!isset($rs))
			extract(gpsa(array(
				'url','include','posted'
			)));
		
		rah_sitemap_header(
			'	<form method="post" action="index.php">'.n.
			'		<p>'.n.
			'			<label for="rah_sitemap_url">URL:</label><br />'.n.
			'			<input id="rah_sitemap_url" class="edit" type="text" name="url" value="'.htmlspecialchars($url).'" />'.n.
			'		</p>'.n.
			'		<p>'.n.
			'			<label for="rah_sitemap_posted">LastMod (YYYY-mm-dd HH:MM:SS). Leave empty to use current time:</label><br />'.n.
			'			<input id="rah_sitemap_posted" class="edit" type="text" name="posted" value="'.htmlspecialchars($posted).'" />'.n.
			'		</p>'.n.
			'		<p>'.n.
			'			<label for="rah_sitemap_lastmod">Include LastMod:</label><br />'.n.
			'			<select id="rah_sitemap_lastmod" name="include">'.n.
			'					<option value="0"'.(($include == 0) ? ' selected="selected"' : '').'>'.gTxt('no').' (Recommended)</option>'.n.
			'					<option value="1"'.(($include == 1) ? ' selected="selected"' : '').'>'.gTxt('yes').'</option>'.n.
			'			</select>'.n.
			'		</p>'.n.
			'		<p><input type="submit" value="'.gTxt('save').'" class="publish" /></p>'.n.
			
			(($edit) ? 
				'		<input type="hidden" name="edit" value="'.htmlspecialchars($edit).'" />' : ''
			).
			
			'		<input type="hidden" name="event" value="rah_sitemap" />'.n.
			'		<input type="hidden" name="step" value="rah_sitemap_custom_save" />'.n.
			
			'	</form>'
			
			,'rah_sitemap',
			'Add a new custom URL',
			'Sitemap',
			$message
			
		);
		
	}

/**
	Saves custom URL
*/

	function rah_sitemap_custom_save() {
		extract(doSlash(gpsa(array(
			'url',
			'posted',
			'include',
			'reset',
			'edit'
		))));
		
		if(empty($posted) or $reset == 1)
			$posted = 'posted=now()';
		else 
			$posted = "posted='$posted'";
		
		if(!$edit && safe_count('rah_sitemap',"url='$url'") != 0) {
			rah_sitemap_custom_form('URL already exists.');
			return;
		}
		
		if($edit && safe_count('rah_sitemap',"url='$edit'") == 1) {
			
			if($url != $edit && safe_count('rah_sitemap',"url='$url'") == 1) {
				rah_sitemap_custom_form('New URL already exists.');
				return;
			}
			
			safe_update(
				'rah_sitemap',
				"$posted,
				include='$include',
				url='$url'",
				"url='$edit'"
			);
			
			rah_sitemap_custom_list('URL updated.');
			return;
			
		}
		
		safe_insert(
			'rah_sitemap',
			"url='$url',
			$posted,
			include='$include'"
		);
		
		rah_sitemap_custom_list('URL added.');
		return;
	}

/**
	Outputs the panel's CSS and JavaScript to page's <head> segment
*/

	function rah_sitemap_head() {
		
		global $event;
		
		if($event != 'rah_sitemap')
			return;
		
		echo <<<EOF
			<style type="text/css">
				#rah_sitemap_container {
					width: 950px;
					margin: 0 auto;
				}
				#rah_sitemap_container #rah_sitemap_step {
					text-align: right;
					padding-top: 10px;
				}
				#rah_sitemap_container .rah_sitemap_table {
					width: 100%;
				}
				#rah_sitemap_container .rah_sitemap_column {
					width: 315px;
					float: left;
					display: inline;
					padding: 0 0 10px 0;
				}
				#rah_sitemap_container .rah_sitemap_heading {
					font-weight: 900;
					padding: 5px 0;
					margin: 0 0 10px 0;
					border-top: 1px solid #ccc;
					border-bottom: 1px solid #ccc;
				}
				#rah_sitemap_container .rah_sitemap_heading span {
					cursor: pointer;
					color: #963;
				}
				#rah_sitemap_container .rah_sitemap_heading span:hover {
					text-decoration: underline;
				}
				#rah_sitemap_container .rah_sitemap_more {
					overflow: hidden;
				}
				#rah_sitemap_container input.edit {
					width: 940px;
				}
				#rah_sitemap_zlib_output,
				#rah_sitemap_compression_level,
				#rah_sitemap_compress,
				#rah_sitemap_lastmod {
					width: 450px;
				}
				.rah_sitemap_paragraph {
					margin: 0 0 10px 0;
					padding: 0;
				}
			</style>
			<script type="text/javascript">
				$(document).ready(function(){
					$('.rah_sitemap_more').hide();
					$('.rah_sitemap_heading').click(function(){
						$(this).next('div.rah_sitemap_more').slideToggle();
					});
				});
			</script>
EOF;
	}

/**
	The panel's navigation bar
*/

	function rah_sitemap_header($content='',$title='rah_sitemap',$msg='Manage your sitemap',$pagetop='',$message='') {
		
		pagetop($pagetop,$message);
		
		if(is_array($content))
			$content = implode('',$content);
		
		echo 
			n.
			'	<div id="rah_sitemap_container">'.n.
			'		<h1><strong>'.$title.'</strong> | '.$msg.'</h1>'.n.
			'		<p id="rah_sitemap_nav">'.
					' &#187; <a href="?event=rah_sitemap">Preferences</a>'.
					' &#187; <a href="?event=rah_sitemap&amp;step=rah_sitemap_custom_form">Add custom URL</a>'.
					' &#187; <a href="?event=rah_sitemap&amp;step=rah_sitemap_custom_list">List of custom URLs</a>'.
					' &#187; <a target="_blank" href="'.hu.'?rah_sitemap=sitemap">View the sitemap</a>'.
					'</p>'.n.
			$content.n.	
			'	</div>'.n;
	}

/**
	Builds the required in array for SQL statements
*/

	function rah_sitemap_in($field='',$array='',$default='',$sql=' not in') {
		
		if(empty($array) && empty($default))
			return;
		
		if(!is_array($array))
			$array = explode(',',$array);
		
		if(!empty($default)) {
			$default = explode(',',$default);
			$array = 
				array_merge(
					$array,
					$default
				);
		}
		
		foreach($array as $value) 
			$out[] = "'".doSlash(trim($value))."'";
		
		if(!isset($out))
			return;
	
		return 
			$field . $sql . '(' . implode(',',$out) . ')';
		
	}

/**
	Default settings
*/

	function rah_sitemap_pref_fields() {
		return
			array(
				'noarticles' => '',
				'nosections' => '',
				'nocategories' => '',
				'articlecategories' => '',
				'articlesections' => '',
				'sections' => '',
				'categories' => '',
				'nofile' => 1,
				'noimage' => 1,
				'nolink' => 1,
				'articlestatus' => '',
				'articlefuture' => '',
				'articlepast' => '',
				'articleexpired' => '',
				'permlink_category' => '',
				'permlink_section' => '',
				'permlink_article' => '',
				'timestampformat' => 'c',
				'compress' => 1,
				'compression_level' => 9,
				'zlib_output' => 0
			);
	}

/**
	Returns preferences as an array
*/

	function rah_sitemap_prefs() {
		
		$out = array();
		
		$rs = 
			safe_rows(
				'name,value',
				'rah_sitemap_prefs',
				'1=1'
			);
		
		foreach($rs as $row)
			$out[$row['name']] = $row['value'];
			
		return $out;
		
	}

/**
	Build the custom URLs
*/

	function rah_sitemap_uri($uri='',$escape=0) {
		
		if(
			substr($uri,0,7) != 'http://' && 
			substr($uri,0,8) != 'https://' &&
			substr($uri,0,6) != 'ftp://' &&
			substr($uri,0,7) != 'ftps://' && 
			substr($uri,0,4) != 'www.'
		)
			$uri =  hu . $uri;
		
		else if(substr($uri,0,4) == 'www.')
			$uri =  'http://' . $uri;
		
		if($escape == 1)
			$uri = htmlspecialchars($uri);
		
		return $uri;
	}

/**
	Installer. Creates tables and adds the default rows
*/

	function rah_sitemap_install() {
		safe_query(
			"CREATE TABLE IF NOT EXISTS ".safe_pfx('rah_sitemap')." (
				`url` VARCHAR(255) NOT NULL,
				`posted` DATETIME NOT NULL,
				`include` INT(1) NOT NULL,
				PRIMARY KEY(`url`)
			)"
		);
		safe_query(
			"CREATE TABLE IF NOT EXISTS ".safe_pfx('rah_sitemap_prefs')." (
				`name` VARCHAR(255) NOT NULL,
				`value` LONGTEXT NOT NULL,
				PRIMARY KEY(`name`)
			)"
		);
		
		foreach(rah_sitemap_pref_fields() as $key => $val) {
			if(
				safe_count(
					'rah_sitemap_prefs',
					"name='".doSlash($key)."'"
				) == 0
			) {
				safe_insert(
					'rah_sitemap_prefs',
					"name='".doSlash($key)."', value='".doSlash($val)."'"
				);
			}
		}
	}

/**
	Builds the list of filters
*/

	function rah_sitemap_listing($label='',$field='',$table='',$where='') {
		
		$pref = 
			rah_sitemap_prefs();
		
		$exclude = explode(',',$pref[$field]);
		
		$rs = 
			safe_rows(
				'name,title'.(($table == 'txp_category') ? ',type' : ''),
				$table,
				"$where order by ".(($table == 'txp_category') ? 'type asc, ' : '')." name asc"
			);
		
		$out[] = 
			'					<div class="rah_sitemap_column">'.n.
			'						<strong>'.$label.'</strong><br />'.n;
		
		if($rs){
			foreach($rs as $a) {
				
				$name = $a['name'];
				$title = $a['title'];
				
				if($field == 'categories') {
					$name = $a['type'].'_||_'.$a['name'];
					$title = ucfirst($a['type']).$a['title'];
				}

				$out[] = 
					'						<label><input type="checkbox" name="'.$field.'[]" value="'.$name.'"'.((in_array($name,$exclude)) ? ' checked="checked"' : '').' /> '.$title .'</label><br />'.n;
			}
		} else $out[] = '						Nothing found.'.n;
		$out[] = 
			'					</div>'.n;
		return implode('',$out);
	}

/**
	Saves preferences
*/

	function rah_sitemap_save() {
		foreach(rah_sitemap_pref_fields() as $key => $val) {
			$ps = ps($key);
			
			if(is_array($ps))
				$ps = implode(',',$ps);
			
			safe_update(
				'rah_sitemap_prefs',
				"value='".doSlash(trim($ps))."'",
				"name='".$key."'"
			);
		}
		rah_sitemap_list('Sitemap preferences saved');
	}

/**
	Deletes custom URIs
*/

	function rah_sitemap_delete() {
		
		$selected = ps('selected');
		
		if(!is_array($selected)) {
			rah_sitemap_custom_list('Nothing selected');
			return;
		}
		
		foreach($selected as $name) 
			safe_delete(
				'rah_sitemap',
				"url='".doSlash($name)."'"
			);
		
		rah_sitemap_custom_list('Selection deleted');
	}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>rah_sitemap</h1>

	<p>Rah_sitemap is a sitemap plugin for <a href="http://www.textpattern.com">Textpattern <span class="caps">CMS</span></a>. Easily build valid <a href="http://www.sitemap.org">Sitemap.org</a> <acronym title="eXtensible Markup Language"><span class="caps">XML</span></acronym> sitemaps for search engines, including Google. Supports categories, sections, articles and even custom <span class="caps">URL</span>s. All settings can be managed from clean interface. No dive to code required.</p>

	<ul>
		<li>Version: 1.2</li>
		<li>Updated: 2011/03/09 12:20 AM <span class="caps">UTC</span></li>
	</ul>

	<h3>Table of Contents</h3>

	<ul>
		<li><a href="#list-of-features">List of features</a></li>
		<li><a href="#requirements">Requirements</a></li>
		<li><a href="#installation-and-usage">Installation and usage</a>
	<ul>
		<li><a href="#permlink-rules">Permlink rules</a></li>
	</ul></li>
		<li><a href="#changelog">Changelog</a></li>
	</ul>

	<h3 id="list-of-features">List of features</h3>

	<ul>
		<li>Simple interface under <em>Textpattern &gt; Extensions &gt; Sitemap</em>: build, view, modify and customize sitemap.</li>
		<li>Filter, include and exclude section, category and article <span class="caps">URL</span>s from the sitemap.</li>
		<li>Create and insert custom <span class="caps">URL</span>s to the sitemap with couple simple clicks.</li>
		<li>No need to create files nor set permissions, instead just use the plugin and eventually submit the sitemap <span class="caps">URL</span> to Google (via Webmaster tools) or other searh engine.</li>
		<li>Sitemap is automatically gzipped as much as possible &#8212; at least if server supports gzipping.</li>
	</ul>

	<h3 id="requirements">Requirements</h3>

	<p>Minimum:</p>

	<ul>
		<li>Textpattern 4.0.7+</li>
		<li><span class="caps">PHP</span> 5+ (or 4.3.0 when custom timestamp format is used)</li>
		<li>Optional: <span class="caps">PHP</span> zlib extension</li>
	</ul>

	<p>Recommended:</p>

	<ul>
		<li>Textpattern 4.0.8+</li>
		<li><span class="caps">PHP</span> 5.1.2+</li>
	</ul>

	<h3 id="installation-and-usage">Installation and usage</h3>

	<p>The general behavior stands: paste the plugin code to the plugin installer textarea and run the automatic setup. Then just activate the plugin and you are ready to use the sitemap.</p>

	<p>You can locate rah_sitemap&#8217;s user-interface panel from <a href="?event=rah_sitemap">Textpattern &gt; Extensions &gt; Sitemap.</a> From there you can modify preferences and view the sitemap.</p>

	<h4 id="permlink-rules">Permlink schemes and rules</h4>

	<p>Rah_sitemap version 0.4 included a new feature: permlink settings. These setting, found in the panel, will let you define the permlink form for the <span class="caps">URL</span>s in the Sitemap. This is a ideal tool for matching the <span class="caps">URL</span>s to your own .httaccess rules or a plugin created custom <span class="caps">URL</span> rules. If you want to use in-build urls defined by Textpattern itself, you can leave these setting unset.</p>

	<h5>Article permlinks</h5>

	<p>These <span class="caps">URL</span> settings are used for articles that appear in the sitemap. To form the <span class="caps">URL</span>s you can use any individual article context&#8217;s <code>&lt;txp:/&gt;</code> tag. Big shots like <code>&lt;txp:permlink /&gt;</code>, <code>&lt;txp:posted /&gt;</code>, <code>&lt;txp:title /&gt;</code>, <code>&lt;txp:category1 /&gt;</code>, <code>&lt;txp:category2 /&gt;</code>, <code>&lt;txp:section /&gt;</code>, <code>&lt;txp:if_article_author /&gt;</code>, <code>&lt;txp:if_article_category /&gt;</code>, <code>&lt;txp:if_article_id /&gt;</code> and so on.</p>

	<p>Example Article permlink <span class="caps">URL</span>:</p>

<pre><code>&lt;txp:site_url /&gt;sections/&lt;txp:section/&gt;/articles/&lt;txp:article_id /&gt;/&lt;txp:article_url_title /&gt;
</code></pre>

	<h5>Category permlinks</h5>

	<p>These <span class="caps">URL</span> settings are used for Category links that appear in the sitemap. To form the <span class="caps">URL</span>s you can use plain category context <span class="caps">TXP</span> tags <code>&lt;txp:category /&gt;</code> and <code>&lt;txp:if_category /&gt;</code>. The plugin also provides two extra tagish tags, <code>[type]</code> and <code>[id]</code> which will basically output category&#8217;s type and id.</p>

	<p>Example Category permlink <span class="caps">URL</span>:</p>

<pre><code>&lt;txp:site_url /&gt;view/category/[id]/&lt;txp:category link=&quot;0&quot; title=&quot;0&quot; /&gt;
</code></pre>

	<h5>Section permlinks</h5>

	<p>These <span class="caps">URL</span> settings are used for section links that appear in the sitemap. To form the <span class="caps">URL</span>s you can use the two section tags, <code>&lt;txp:section /&gt;</code> and <code>&lt;txp:if_section /&gt;</code>.</p>

<pre><code>&lt;txp:site_url /&gt;section/&lt;txp:section /&gt;
</code></pre>

	<h3 id="changelog">Changelog</h3>

	<p><strong>Version 1.2</strong></p>

	<ul>
		<li>Added: adds site <span class="caps">URL</span> to relative article permlinks. Basically a fix for gbp_permanent_links.</li>
		<li>Changed: from permlinkurl_id() to permlinkurl(). Greatly reduced the amount of queries generating article permlinks makes.</li>
	</ul>

	<p><strong>Version 1.1</strong></p>

	<ul>
		<li>Fixed issues appearing with the installer when MySQL is in strict mode. <a href="http://forum.textpattern.com/viewtopic.php?pid=236637#p236637">Thank you for reporting, Gallex</a>.</li>
	</ul>

	<p><strong>Version 1.0</strong></p>

	<ul>
		<li>Slightly changed backend&#8217;s installer call; only check for installing if there is no preferences available.</li>
	</ul>

	<p><strong>Version 0.9</strong></p>

	<ul>
		<li>Fixed: now correctly parses category tags in category <span class="caps">URL</span>s. Thank you for <a href="http://forum.textpattern.com/viewtopic.php?pid=233619#p233619">reporting</a>, Andreas.</li>
	</ul>

	<p><strong>Version 0.8</strong></p>

	<ul>
		<li>Now compression level field&#8217;s label now links to the correct field id.</li>
		<li>Now suppresses E_WARNING/E_STRICT notices in live mode caused by Textpattern&#8217;s timezone code when some conditions are met (<span class="caps">TXP</span> 4.2.0, <span class="caps">PHP</span> 5.1.0+, <span class="caps">TXP</span>&#8217;s Auto-<span class="caps">DST</span> feature disabled, <span class="caps">TXP</span> in Live mode). Error suppression will be removed when <span class="caps">TXP</span> version is released with fully working timezone settings.</li>
		<li>Now generates <span class="caps">UNIX</span> timestamps within the <span class="caps">SQL</span> query, not with <span class="caps">PHP</span>.</li>
		<li>Changed sliding panels&#8217; links (<code>a</code> elements) into spans.</li>
	</ul>

	<p><strong>Version 0.7</strong></p>

	<ul>
		<li>Fixed: now deleting custom url leads back to the list view, not to the editing form.</li>
		<li>Removed some leftover inline styles from v0.6.</li>
	</ul>

	<p><strong>Version 0.6</strong></p>

	<ul>
		<li>Rewritten the code that generates the sitemap.</li>
		<li>New admin panel look.</li>
		<li>Now custom permlink modes and custom urls are escaped. Users can input unescaped <span class="caps">URL</span>s/markup from now on.</li>
		<li>Now custom <span class="caps">URL</span> list shows the full formatted <span class="caps">URL</span> after auto-fill instead of the user input.</li>
		<li>Now custom <span class="caps">URL</span>s that start with www. are completed with http:// protocol.</li>
		<li>Now all urls that do not start with either http, https, www, ftp or ftps protocol are auto-completed with the site&#8217;s address.</li>
		<li>Custom url editor got own panel. No longer the form is above the <span class="caps">URL</span> list.</li>
		<li>Added ability to manually turn gzib compression off and change the compression level.</li>
		<li>Added setting to set zlib.output_compression off. <a href="http://forum.textpattern.com/viewtopic.php?pid=224931#p224931">See here</a>, thank you for reporting superfly.</li>
		<li>Preferences are now trimmed during save.</li>
		<li>Merged <code>rah_sitemap_update()</code> with <code>rah_sitemap_save()</code>.</li>
		<li>From now on all new installations have default settings defined that will automatically exclude link, file and image categories from the sitemap. This won&#8217;t effect updaters.</li>
		<li>Changed sitemap&#8217;s callback register from pre <code>pretext</code> to callback after it (callback is now <code>textpattern</code>). Now <code>$pretext</code> is set before the sitemap and thus more plugins might work within permlink settings and custom urls.</li>
		<li>When using <span class="caps">TXP</span>&#8217;s clean <span class="caps">URL</span>s, requesting <code>/sitemap.xml.gz</code> and <code>/sitemap.xml</code> <span class="caps">URL</span>s will return the sitemap, not just the <code>/?rah_sitemap=sitemap</code>. This will of course require existing fully working clean urls.</li>
	</ul>

	<p><strong>Version 0.5</strong></p>

	<ul>
		<li>Added customizable timestamp formats.</li>
		<li>Cleaned backend markup.</li>
		<li>Combined individual preference queries.</li>
	</ul>

	<p><strong>Version 0.4</strong></p>

	<ul>
		<li>Added support for custom permlink rules: Now you can easily set any kind of permlink rules for articles, section and categories.</li>
		<li>Added option to exclude future articles.</li>
		<li>Added option to exclude past articles.</li>
		<li>Added option to exclude expired articles.</li>
		<li>Moved Custom <span class="caps">URL</span> UI to it&#8217;s own page.</li>
		<li>Added multi-delete feature to Custom <span class="caps">URL</span> UI.</li>
		<li>Improved Custom <span class="caps">URL</span> UI.</li>
		<li>Removed default static appending domain from Custom <span class="caps">URL</span> input field.</li>
		<li>Changed <span class="caps">TXP</span> minimum requirement to version 4.0.7 (and above). Note that the plugin still works with older <span class="caps">TXP</span> versions (down to 4.0.5) if the <em>Exclude Expired articles</em> -option is left empty (unset).</li>
	</ul>

	<p><strong>Version 0.3.2</strong></p>

	<ul>
		<li>Fixed view url that still (from version 0.2) included installation address before link.</li>
	</ul>

	<p><strong>Version 0.3</strong></p>

	<ul>
		<li>Added option to insert <span class="caps">URL</span>s that are outside Textpattern install directory.</li>
		<li>Fixed option to exclude categories directly by type: added forgotten link type.</li>
	</ul>

	<p><strong>Version 0.2</strong></p>

	<ul>
		<li>Added option to exclude/include sticky articles.</li>
		<li>Added option to exclude categories directly by type.</li>
		<li>Fixed bug: now shows all categories, and not only article-type, in admin panel.</li>
		<li>Fixed bug: removed double install query (didn&#8217;t do a thing, just checked table status twice).</li>
	</ul>

	<p><strong>Version 0.1.2</strong></p>

	<ul>
		<li>Fixed article listing bug caused by nasty little typo: now only 4 and 5 statuses are listed.</li>
	</ul>

	<p><strong>Version 0.1</strong></p>

	<ul>
		<li>First release.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>