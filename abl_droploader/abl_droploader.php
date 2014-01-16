<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'abl_droploader';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.19';
$plugin['author'] = 'Andreas Blaser';
$plugin['author_uri'] = 'http://www.blaser-it.ch';
$plugin['description'] = 'Drag & drop file upload utility';

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
$plugin['type'] = '3';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '3';



if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/**
* Plugin:      abl_droploader
* Version:     0.19
* URL:         http://www.blaser-it.ch
* Description: Drag & drop file upload utility
* Year:        2013
* MD5:         0a8796eb68cc36c67bcd7a30731ef6f1
*
* Author:      Andreas Blaser
*
* License:     GPL v2
*
*/

if (@txpinterface == 'admin') {

	// privs
    add_privs('abl_droploader', '1,2,3,4,6');
	add_privs('plugin_lifecycle.abl_droploader', '1,2');
	add_privs('plugin_prefs.abl_droploader', '1,2');

	// inject js & css in html-header
	register_callback('abl_droploader_head_end', 'admin_side', 'head_end');
	// trigger (button/link) elements
	register_callback('abl_droploader_article_ui', 'article_ui', 'extend_col_1');
	// following callback has been replaced by javascript!
	//register_callback('abl_droploader_image_ui', 'image_ui', 'extend_controls');
	// ajax interface
    register_callback('abl_droploader_ajax', 'abl_droploader');
	// intercept image_uploaded-event from txp_image
    register_callback('abl_droploader_image_uploaded', 'image_uploaded', 'image');
	// lifecycle
	register_callback('abl_droploader_lifecycle', 'plugin_lifecycle.abl_droploader');
	// prefs
	$abl_droploader_prefs = abl_droploader_load_prefs(); // load hard-coded defaults
	@require_plugin('soo_plugin_pref');
	register_callback('abl_droploader_prefs', 'plugin_prefs.abl_droploader');

}


	/**
	 * handle plugin lifecycle events
	 *
	 * @param string $event (plugin_lifecycle.abl_droploader)
	 * @param string $step (enabled, disabled, installed, deleted)
	 */
	function abl_droploader_lifecycle($event, $step) {
		global $prefs;

		$msg = '';
		switch ($step) {
			case 'installed':
				if (function_exists('soo_plugin_pref')) {
					abl_droploader_prefs($event, $step);
				} else {
					$msg = 'Please install <em>soo_plugin_pref</em> to edit preferences (Default preferences apply).';
				}
				abl_droploader_install();

				// set txp's thumb prefs if they are not set (fresh install)
				if (!isset($prefs['thumb_w'])) set_pref('thumb_w', '100', 'image', PREF_HIDDEN);
				if (!isset($prefs['thumb_h'])) set_pref('thumb_h', '100', 'image', PREF_HIDDEN);
				if (!isset($prefs['thumb_crop'])) set_pref('thumb_crop', '1', 'image', PREF_HIDDEN);

				if ($msg == '') $msg = '<em>abl_droploader</em> successfully installed.';
				break;
			//case 'deleted':
			//	break;
		}
		return $msg;

	}

	/**
	 * add plugin prefs to database
	 *
	 * @param string $event (plugin_prefs.abl_droploader)
	 * @param string $step (-)
	 */
    function abl_droploader_prefs($event, $step) {
		if (function_exists('soo_plugin_pref')) {
			soo_plugin_pref($event, $step, abl_droploader_defaults());
			return true;
		} else {
			$msg = 'Please install <em>soo_plugin_pref</em> to edit preferences (Default preferences apply).';
			pagetop(gTxt('edit_preferences') . " &#8250; abl_droploader", $msg);
			$default_prefs = abl_droploader_defaults();
			$html = '<table id="list" class="txp-list" align="center" border="0" cellpadding="3" cellspacing="0">
<thead>
<tr>
<th colspan="2"><h1>DropLoader default preferences</h1></th>
</tr>
<tr>
<th>Option</th>
<th>Value</th>
</tr>
</thead>
<tbody>
';
			foreach ($default_prefs as $key => $pref) {
				$html .= '<tr>
<td>' . htmlspecialchars($pref['text']) . '</td>
<td>' . htmlspecialchars($pref['val']) . '</td>
</tr>
';
			}
			$html .= '</tbody>
</table>
';
			echo $html;
			return false;
		}
	}

	/**
	 * get plugin prefs default values
	 *
	 * @param string $values_only
	 */
	function abl_droploader_defaults($values_only = false) {

		$defaults = array(
			'imageMaxUploadCount' => array(
				'val'	=> '10',
				'html'	=> 'text_input',
				'text'	=> gTxt('abl_droploader_prefs_image_max_upload_count'),
			),
			'reloadImagesTab' => array(
				'val'	=> '0',
				'html'	=> 'yesnoradio',
				'text'	=> gTxt('abl_droploader_prefs_reload_image_tab'),
			),
			'useDefaultStylesheet' => array(
				'val'	=> '1',
				'html'	=> 'yesnoradio',
				'text'	=> gTxt('abl_droploader_prefs_use_default_stylesheet'),
			),
			'customStylesheet' => array(
				'val'	=> '',
				'html'	=> 'text_input',
				'text'	=> gTxt('abl_droploader_prefs_custom_stylesheet'),
			),
			'articleImageFields' => array(
				'val'	=> '#article-image',
				'html'	=> 'text_input',
				'text'	=> gTxt('abl_droploader_prefs_article_image_fields'),
			),
			//'fileMaxUploadCount' => array(
			//	'val'	=> '10',
			//	'html'	=> 'text_input',
			//	'text'	=> gTxt('abl_droploader_prefs_file_max_upload_count'),
			//),
		);
		if ($values_only)
			foreach ($defaults as $name => $arr)
				$defaults[$name] = $arr['val'];
		return $defaults;
	}


	/**
	 * Load plugin prefs
	 *
	 * @param string $values_only
	 */
	function abl_droploader_load_prefs() {
		return function_exists('soo_plugin_pref_vals')
			? array_merge(abl_droploader_defaults(true), soo_plugin_pref_vals('abl_droploader'))
			: abl_droploader_defaults(true);
	}

	/**
	 * install plugin resources (css, js)
	 *
	 * @param mixed $resources
	 * @param string $path
	 */
	function abl_droploader_install($resources=null, $path='') {
		global $prefs;

		if ($resources === null) {
			$path = $prefs['path_to_site'];
			$resources = array();
			$resources['/res/css/abl.droploader-app.css'] =  'H4sIAAAAAAAAA71X207jMBB9pl9hqVppF+EQUtKtwtP+xL6unNhJLRw7st0LIP59x3aSpm3a
wAotoCq+zZw5Z3occkVf0NvsRm2ZLoXa4X2G1pxSJp8Gky8ZMoVWQjzN3mf3t4jXpGJIcGPR
7f1s7od/CiUt7EERyQWmWjVCEco0Vg2TLgVEIjZDmldrC8FroisusR9m6IHVMFdCCP+MfjNN
iSR36JfmRNwhQ6TBhmlettvwjoWTuRLUwZrNT/IWQhnmElNuGkGghlyo4hnON8pwy5XMEMmN
EhvLYNKqJkOJh9FiilI/agilXFYZWjR79BDDx7J9gMVcacgFmGEKQnGK5kVRuHBsbzFlhdIk
pJJKujRAqeUFEZgIXkkH31pVdwfayYJJy3RPSPIPhNwUSigANk+SxOEkxXOl1UZSmCpLd+gV
c0kZ6L2AH3dgo4070Sgesr+PU5qtXV84YrsUpUtxroDbcpnqGB4EK2142nFq10BjHH+D0bot
JQwhMvTcafBS6Rpx2WwsevNdOLZO+faoA4IIZ4WhaOOfgJFSfRXsy4piw18ZbGsb7Eg9CbCJ
uKqfrnLyPUnTO3T4iKNV+uNc1JFKDROssLjkgpkvK7UDu1wuR8E+xoCz/4ijnx8E68Yl3zOK
AeYxWj89XWHzSQdIfT2Xyg6mBac2VvnlKY0XVxQ+tYIafFeMtSf2BosLAlGZ+GRBq6GlBZs9
6IM74YJp9Vbn903hCDw7OK2Xh64JXT2iC68r3Gi25Wx3LKVmAkxy+6nGg0byBtwpsnDG7GQZ
3FuDq8zdVdjYF8GwfWlYb8d9xZfEHK3DsZCrvaui/yKslh7PJVEBgWT4sNujvxoc6Arc7nFL
waHm/Wmk/iJaHC6iYPO4Vq8uHjZrQh0nMfwmsGsex65o6Mv8mdtrWy4vTagcTcndFpbGoYhe
3Hh5JO7DQNwPi3QOI6Ig+vBtZ9AgXWrfBxou1xZsmPeWZTo2r623bF7boq6s+terBgoIr1iN
VpVmxoxdcG2r9Fsucdx3SuI57XvpmOL0wPCHfekMQjQOZmBJh6/1yQ3SworH7SlJV0sad68C
Tj4ixHV2vP1Ps+MNZ5Ge9N/jkKquO0e6cYSSk7z/iRKmtdII/gpVN4K5JKiGrK6Jxthp1zoX
m6ZmmovJu9DAtef/g/gL2bND4HUMAAA=';
			$resources['/res/js/abl.droploader-app.js'] =  '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
3Bom0ZoMTl8uYSq8ZObjfwFwbFnExBUAAA==';
			$resources['/res/js/jquery.droploader.js'] =  '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';
			$resources['/res/js/jquery.filedrop.js'] =  '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';
		}

		foreach($resources as $file => $resource) {
			$newfile = $path . $file;
			if (is_array($resource)) {
				abl_droploader_install($resource, $newfile . '/');
			} else {
				if (!is_dir(dirname($newfile))) {
					mkdir(dirname($newfile), 0777, true);
				}
				$temp = base64_decode($resource);
				if (strncmp($temp, "\x1F\x8B", 2) === 0) {
					$temp = gzinflate(substr($temp, 10));
				}
				if (file_exists($newfile)) unlink($newfile);
				$handle = fopen($newfile, 'w');
				fwrite($handle, $temp);
				chmod($newfile, 0644);
				fclose($handle);
			}
		}

		// install new prefs (not present in previous plugin versions)
		$pi_pref_defaults = abl_droploader_defaults();
		$max_pos = safe_field('position', 'txp_prefs', "name like 'abl_droploader.%' ORDER BY position DESC");
		if ($max_pos === false) {
			$max_pos = 0;
		} else {
			$max_pos++;
		}
		foreach ($pi_pref_defaults as $name => $pref) {
			if (get_pref('abl_droploader.' . $name, 'nope', 1) == 'nope') {
				set_pref(
					'abl_droploader.' . $name,
					$pref['val'],
					'plugin_prefs',
					2,
					$pref['html'],
					$max_pos
				);
				$max_pos++;
			}
		}

	}

	/**
	 * inject css & js in html-header
	 *
	 * @param string $evt (admin_side)
	 * @param string $stp (head_end)
	 */
	function abl_droploader_head_end($evt, $stp) {
		global $event, $step, $prefs, $abl_droploader_prefs;

		if (($event == 'image'
			&& (in_array($step, array('list', 'image_list', 'image_multi_edit', 'image_change_pageby', 'image_save', ''))))
		|| ($event == 'article'
			&& (in_array($step, array('create', 'edit'))))) {
			$abl_droploader_prefs = abl_droploader_load_prefs();
			$css = '';
			if (intval($abl_droploader_prefs['useDefaultStylesheet']) != 0) {
				$css .= '<link rel="stylesheet" href="../res/css/abl.droploader-app.css" type="text/css" media="screen,projection" />' . n;
			}
			if ($abl_droploader_prefs['customStylesheet'] != '') {
				$css .= '<link rel="stylesheet" href="' . $abl_droploader_prefs['customStylesheet'] . '" type="text/css" media="screen,projection" />' . n;
			}
			if ($css == '') {
				$css = '<link rel="stylesheet" href="../res/css/abl.droploader-app.css" type="text/css" media="screen,projection" />' . n;
			}
			$article_image_field_ids = '"' . implode('", "', explode(',', $abl_droploader_prefs['articleImageFields'])) . '"';
			$script = '<script type="text/javascript">
	var file_max_upload_size = ' . sprintf('%F', (intval($prefs['file_max_upload_size']) / 1048576)) . ';
	var file_max_files = 5;
	var image_max_upload_size = 1;
	var image_max_files = ' . intval($abl_droploader_prefs['imageMaxUploadCount']) . ';
	var reload_image_tab = ' . intval($abl_droploader_prefs['reloadImagesTab']) . ';
	var article_image_field_ids = new Array(' . $article_image_field_ids . ');
	var article_image_field = null;
</script>
<script src="../res/js/jquery.filedrop.js" type="text/javascript"></script>
<script src="../res/js/jquery.droploader.js" type="text/javascript"></script>
<script src="../res/js/abl.droploader-app.js" type="text/javascript"></script>' . n;
			echo $css . $script;
		}

	}


	/**
	 * Insert DropLoader link in column 1 on the write-tab
	 *
	 * @param string $event (article_ui)
	 * @param string $step (extend_col_1)
	 */
	function abl_droploader_article_ui($event, $step) {
		$content = '
<ul class="abl_droploader plain-list">
<li><a id="abl-droploader-open" class="abl-droploader-open" href="#" title="' . gTxt('abl_droploader_open_title') . '">' . gTxt('abl_droploader_open') . '</a></li>
</ul>';
		if (is_callable('wrapRegion')) { // new in txp 4.6
			return wrapRegion('abl_droploader_group', $content, 'abl_droploader_link', 'upload', 'article_abl_droploader');
		} else {
			return '
<div id="abl_droploader_group"><h3 class="plain lever"><a href="#abl_droploader-link">' . gTxt('upload') . '</a></h3>
<div id="abl_droploader-link" class="toggle" style="display:none">' .
$content . '
</div>
</div>
';
		}
	}

	/**
	 * Insert DropLoader link above the image-list on the image-tab
	 *
	 * @param string $event (image_ui)
	 * @param string $step (extend_controls)
	 */
	function abl_droploader_image_ui($event, $step) {
		return '<p class="plain"><a id="abl-droploader-open" class="abl-droploader-open" href="#" title="' . gTxt('abl_droploader_open_title') . '">' . gTxt('abl_droploader_open') . '</a></p>' . n;
	}

	/**
	 * handle AJAX requests
	 * return JSON and exit
	 *
	 * @param string $event (abl_droploader)
	 * @param string $step (get_form_data, get_image_data)
	 */
	function abl_droploader_ajax($event, $step) {

		if ($event == 'abl_droploader') {
			switch ($step) {
				case 'get_form_data':
					$response = array(
						'status' => 0,
						'image_upload_link' => '',
						'image_upload_form' => '',
						'image_cat_select' => '',
						'l10n' => ''
					);
					$items = explode(',', gps('items'));
					foreach ($items as $item) {
						switch ($item) {
							case 'image_upload_link':
								$response['image_upload_link'] = abl_droploader_image_ui('', '');
								break;
							case 'image_upload_form':
								$response['image_upload_form'] = abl_droploader_get_image_upload_form();
								break;
							case 'image_cat_select':
								$response['image_cat_select'] = abl_droploader_get_image_cat_select();
								break;
							case 'l10n':
								$response['l10n'] = abl_droploader_get_localisation();
								break;
							case 'all':
								$response['image_upload_link'] = abl_droploader_image_ui('', '');
								$response['image_upload_form'] = abl_droploader_get_image_upload_form();
								$response['image_cat_select'] = abl_droploader_get_image_cat_select();
								$response['l10n'] = abl_droploader_get_localisation();
								break;
						}
					}
					$response['status'] = 1;
					break;
			}
			echo json_encode($response);
			exit;
		}

	}

	/**
	 * get image category-selector form element
	 */
	function abl_droploader_get_image_cat_select() {
		$image_categories = getTree('root', 'image');
		//$image_categories_select = str_ireplace("\n", '', tag('<label for="image-category">' . gTxt('image_category') . '</label>' . br .
		//	treeSelectInput('category', $image_categories, '', 'image-category'), 'div', ' id="abl-droploader-image-cat-sel" class="category"'));
		//$alt_caption =	tag('<label for="alt-text">'.gTxt('alt_text').'</label>'.br.
		//		fInput('text', 'alt', '', 'edit', '', '', 50, '', 'alt-text'), 'div', ' class="alt text"').
		//	tag('<label for="caption">'.gTxt('caption').'</label>'.br.
		//		'<textarea id="caption" name="caption"></textarea>'
		//		, 'div', ' class="caption description text"');
		$image_categories_select = str_ireplace("\n", '', tag(tag('<label for="image-category">'.gTxt('image_category').'</label>'.br.
				treeSelectInput('category', $image_categories, '', 'image-category'), 'div', ' class="category"'), 'div', ' id="abl-droploader-image-cat-sel"'));
		return $image_categories_select;
	}

	/**
	 * get image upload-form
	 */
	function abl_droploader_get_image_upload_form() {
		$upload_form = upload_form(gTxt('upload_image'), 'upload_image', 'image_insert', 'image', '');
		//return '<div id="droploader">' . n . $upload_form . n . '</div>' . n;
		return $upload_form;
	}

	/**
	 * get localised items from textpack
	 */
	function abl_droploader_get_localisation() {
		$l10n = array(
			'open' => '',
			'open_title' => '',
			'close' => '',
			'close_title' => '',
			'error_method' => '',
			'info_text' => '',
			'err_invalid_filetype' => '',
			'err_browser_not_supported' => '',
			'err_too_many_files' => '',
			'err_file_too_large' => '',
			'all_files_uploaded' => '',
			'no_files_uploaded' => '',
			'some_files_uploaded' => '',
		);
		foreach ($l10n as $k => $v) {
			$l10n[$k] = gTxt('abl_droploader_' . $k);
		}
		return $l10n;
	}

	/**
	 * image_uploaded callback (txp_image.php)
	 * return JSON (image-id) and exit
	 *
	 * @param string $event (image_uploaded)
	 * @param string $step (image)
	 * @param string $id (image_id)
	 */
	function abl_droploader_image_uploaded($event, $step, $id) {
		if (ps('abl_droploader') == '') return;
		$response = array(
			'status' => 1,
			'image_id' => $id,
		);
		echo json_encode($response);
		exit;
	}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>abl_droploader</h1>

<p>DropLoader for <a href="http://textpattern.com/" rel=" rel="1"">Textpattern <span class="caps">CMS</span></a> allows you to upload multiple images at once, simply by dragging files from the desktop onto the browser window. The user interface of DropLoader is a transparent area, shown above Textpatterns own UI.</p>

<p>A special feature of DropLoder is the fact, that it hides the standard upload-form, but uses it in the background for sending the data to server. The form-data will therefore be processed by Textpatterns regular image upload-script, txp_image.php.</p>

<p>DropLoader is enabled in two places. On the image-tab (Menu: Content &gt; Images), where it replaces the standard upload-form, but also on the write-tab (Content &gt; Write). This makes it possible to upload images right from there, without a need to switch between the write- and image-tabs. Uploaded images will be automatically assigned to the current article (field article image), if DropLoader is opened directly from the write-tab.</p>

<p>It is also possible to automatically assign a category to the uploaded images. Simply select a category, <em>before</em> files are dropped or selected.</p>

<p>Both, &#8220;Drag &amp; Drop&#8221; and the upload functionality is based on the jQuery plugin <a href="https://github.com/weixiyen/jquery-filedrop" rel=" rel="1"">jquery.filedrop.js</a> by Weixi Yen. Thanks!</p>

<h2>Browser Support</h2>

<p>The jQuery-plugin filedrop requires the &#8220;File Reader <span class="caps">API</span>&#8221; and the &#8220;Drag &amp; Drop <span class="caps">API</span>&#8221;. <strong>DropLoader disables itself, if these <span class="caps">API</span>&#8217;s are not supported.</strong></p>

<p>Recent versions of <strong>Firefox</strong> and <strong>Google Chrome</strong> supports all features and work perfectly. However, Internet Explorer (incl. IE9), Opera and Safari currently do not support all of the required <span class="caps">API</span>&#8217;s, so DropLoader will not be available with these browsers.</p>

<h2>Features</h2>

	<ul>
		<li>Enables uploading of multiple images at once</li>
		<li>File selection using drap &amp; drop or the file open-dialog</li>
		<li>Hides the standard upload-form in the images-tab</li>
		<li>Automatically assign image-category to each uploaded image (optional)</li>
		<li>Automatically create thumbnail, if thumbnail dimensions are available</li>
		<li>Fully compatible to the standard image upload-form: Form data is posted to the same script (txp_image.php) for processing</li>
		<li>Enables image uploads directly from the write-tab (Article detail/edit view)</li>
		<li>Automatically assign uploaded images to the current article&#8217;s images-field (only if DropLoader has been opened directly from the write-tab)</li>
		<li>Supports localisation (l10n) using textpacks.</li>
		<li>DropLoader can also be used by other Textpattern plugins, e.g. from file- or image-pickers/selectors.</li>
	</ul>

<h2>Requirements</h2>

<p>Textpattern 4.4.1+.</p>

<p><strong>Important:</strong> To be able to edit preferences, the plugin <em>soo_plugin_pref</em> is required. Hard coded default preferences apply, if soo_plugin_pref is not installed.</p>

<h2>Author</h2>

<p>Andreas Blaser (<a href="http://www.blaser-it.ch/" rel=" rel="1"">web</a>)</p>

<h2>Installation</h2>

<p>This plugin can be installed and activated as usual on the plugins-tab.</p>

<p>DropLoader requires some additional resources (jQuery plugins, stylesheet), which are installed when the plugin is activated.</p>

<p><strong>Note:</strong><br />
When upgrading abl_droploader from a previous version, you may need to clear the browser cache.</p>

<p>The following files will be installed on plugin activation:</p>

	<ul>
		<li>/ (site-root)
		<ul>
			<li>res/
			<ul>
				<li>css/
				<ul>
					<li>abl.droploader-app.css</li>
				</ul></li>
				<li>js/
				<ul>
					<li>jquery.filedrop.js</li>
					<li>jquery.droploader.js</li>
					<li>abl.droploader-app.js</li>
				</ul></li>
				</ul></li>
				</ul></li>
				</ul>

<h2>Localisation</h2>

<p>DropLoader uses textpacks for localisation. The distribution package already contains textpacks in english (en-gb) and german (de-de).</p>

<p>The name of all language strings begins with &#8216;abl_droploader_&#8217;.</p>

<p><strong>Note:</strong> Starting with Version 0.13, the english textpack will also be installed under the language-key for the current site language, if this is <strong>not</strong> en-gb or de-de. These strings can then be translated to the desired language, for example using the snippet editor of the <span class="caps">MLP</span> (Multi-Language Pack).</p>

<p>Thanks to Stef Dawson for this tip!</p>

<h2>Changelog</h2>

<h4>Version 0.19 (2013-02-21)</h4>

	<ul>
		<li>Write Pane: Rendering of the droploader open-link changed. Function &#8216;wrapRegion&#8217; will be used, if available (Txp 4.6).</li>
		<li>Correction in jquery.droploader.js: Ignore drop-events when the drop-area is not visible.</li>
		<li>Cleanup php and javascripts (remove commented code).</li>
	</ul>

<h4>Version 0.18 (2013-02-19)</h4>

	<ul>
		<li>Resolved a compatibility issue with jQuery 1.9 (Txp 4.6-<span class="caps">SVN</span>): Use &#8216;delegate&#8217; instead of &#8216;live&#8217; for event-handler attachment (live-method has been removed in jQuery 1.9). The delegate-method is available in jQuery Versions 1.4.2 or newer.</li>
	</ul>

<h4>Version 0.17 (2013-01-26)</h4>

	<ul>
		<li>Changed plugin load order from 5 (default) to 9 (low priority). The reason is, that there may be other plugins (like <strong>smd_thumbnail</strong>), that uses the callback <strong>image_uploaded</strong> from txp_image.php. Because DropLoader does a <span class="caps">PHP</span> exit within that callback, other plugins <strong>must be called before DropLoader</strong>. Otherwise the callback-event is not fired for these plugins.</li>
		<li>Added a new option &#8216;article-image fields&#8217;, which is a comma separated list of article-image <span class="caps">CSS</span> field-id&#8217;s (default: #article-image). When multiple fields are given, the image-ids&#8217;s are inserted into all of these fields. Use #custom-n for the article custom fields, where &#8220;n&#8221; is the field-number.</li>
		<li>Corrected a typo in the textpack: <em>abl_droploader_prefs_custom_stylesheetb</em> renamed to <em>abl_droploader_prefs_custom_stylesheet</em>.</li>
		<li>New entry added to the textpack for the new option in the prefs-panel.</li>
		<li>Correction in installation procedure: properly add new options to the prefs. Version 0.16 failed to do that correctly.</li>
	</ul>

<h4>Version 0.16 (2013-01-16)</h4>

	<ul>
		<li>Enable DropLoader also after editing an image. I forget to change this in 0.15.</li>
		<li>Added two new options for a more flexible UI styling. Option &#8216;use default styles&#8217; (default: yes) and &#8216;custom stylesheet&#8217; (default: empty) which is a stylesheet (path/filename) of your own. The custom stylesheet will be included <em>after</em> the default stylesheet, if this is also enabled.</li>
		<li>Two entries added to the textpack for the new options in the prefs-panel.</li>
		<li>Added an installation note concerning upgrading DropLoader and browser caching.</li>
		<li>Cleaned up jquery.droploader.js (comments removed)</li>
	</ul>

<h4>Version 0.15 (2013-01-07)</h4>

	<ul>
		<li>Do not override thumb dimensions on installation if they are already set.</li>
		<li><del>Enable DropLoader also after editing an image.</del></li>
		<li>Better error handling in JavaScript after upload error.</li>
		<li>Default for option &#8220;reload image list after upload&#8221; changed to false, because pages may contain post-data.</li>
	</ul>

<h4>Version 0.14 (2013-01-02)</h4>

	<ul>
		<li>Avoid display of a <span class="caps">JSON</span>-String like &#8216;{&#8220;status&#8221;: 1,&#8220;image_id&#8221;: 123}&#8217;, for example after editing a single image.</li>
		<li>Enable DropLoader also on empty image-list (no images, no search result), after doing list operations (multi-edit) and after changing the page-size.</li>
		<li>Better integration with admin themes:
		<ul>
			<li>Avoid overlay of the close-button for the <strong>Hive</strong> admin-theme (Txp 4.5.x).</li>
			<li>Write Tab: Rendering of open-link changed.</li>
		</ul></li>
		</ul>

<h3>Version 0.13 (2012-05-25)</h3>

	<ul>
		<li>Installation procedure corrected for international users (see &#8216;Localisation&#8217; above).</li>
	</ul>

<h3>Version 0.12 (2012-05-22)</h3>

	<ul>
		<li>Edit preferences: Show default values (not editable) if soo_plugin_pref is not installed.</li>
		<li>Thumbnail size not set: If thumbnail defaults where never set (e.g. in a fresh Txp-install), DropLoader sets default values (thumb_w/h: 100/100, thumb_crop: 1) for these preferences upon installation.</li>
	</ul>

<h3>Version 0.11 (2012-05-21)</h3>

	<ul>
		<li>fix for &#8220;white screen&#8221; if soo_plugin_pref is not installed: Use hard-coded defaults.</li>
		<li>Text of Open trigger changed from DropLoader to Upload Images</li>
	</ul>

<h3>Version 0.1 (2012-05-18)</h3>

	<ul>
		<li>Initial release</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>