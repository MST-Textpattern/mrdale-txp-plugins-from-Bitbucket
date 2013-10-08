<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'ebl-image-edit';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '2.3';
$plugin['author'] = 'Eric Limegrover + mrdale + MarcoK';
$plugin['author_uri'] = 'http://www.syserror.net/';
$plugin['description'] = 'Advanced Image Editing Plugin for Textpattern';

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
$plugin['type'] = '4';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '1';

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
if (@txpinterface == 'admin')
	{
		//add_privs('eblimgeditext', '1');
		//register_tab("extensions", "eblimgeditext", "Image Edit");
		//register_callback("ebl_image_edit_ext", "eblimgeditext");
		add_privs('plugin_prefs.ebl_image_edit', '1');
		register_callback("ebl_image_edit_ext", "plugin_prefs.ebl_image_edit");

	switch (gps('event'))
	{
		case 'eblrotateleft':
			ebl_img_edit(gps('imgid'), 'left', 'rotate'); exit();
			break;
		case 'eblrotateright':
			ebl_img_edit(gps('imgid'), 'right', 'rotate'); exit();
			break;
		case 'eblrotateup':
			ebl_img_edit(gps('imgid'), 'up', 'rotate'); exit();
			break;
		case 'eblimgcrop':
			ebl_img_edit(gps('imgid'), '', 'crop'); exit();
			break;
		case 'eblimgcrop':
			ebl_processUpload(gps('uploadType'));
			break;
		case 'ebltmbcrop':
			ebl_img_edit(gps('imgid'), '', 'thumbnail'); exit();
			break;
		case 'eblrszimg':
			ebl_img_edit(gps('imgid'), '', 'rsz'); exit();
			break;
		case 'eblbackupimg':
			eblbackupimg(gps('imgid'));
			break;
		case 'eblrestoreimg':
			eblrestoreimg(gps('imgid'));
			break;
		case 'eblwmcreate':
			ebl_img_edit(gps('imgid'), '', 'watermark'); exit();
			break;
	}
}

function ebl_image_edit_ext()
{

	$step = gps('step');


	$message = (is_callable($step)) ? $step() : '';

	echo pagetop("EBL Image Edit Preferences", $message);

	echo 	'<div style="margin: 0 auto; width: 400px;">'.n.
			ebl_list_cropStyles().
			'</div>';
}

function ebl_list_cropStyles()
{
	global $txp_user, $path_to_site, $img_dir;
	$backupdir 	= $path_to_site . '/' . $img_dir . '/backup/';
		if(!file_exists($backupdir)) {

		$createEBLtable = safe_query("CREATE TABLE `".PFX."ebl_crop` (".
									"`name` varchar(64) NOT NULL,".
									"`width` varchar(16) NOT NULL,".
									"`height` varchar(16) NOT NULL,".
									"`thumb` varchar(1) NOT NULL default '0',".
									"UNIQUE KEY `name` (`name`)".
									");");

		$rs = safe_insert('ebl_crop', "`name` = 'Image Crop', `width` = '500',`height` = '500', `thumb` = '0'");
		$rs = safe_insert('ebl_crop', "`name` = 'Thumbnail Square', `width` = '100',`height` = '100', `thumb` = '1'");

			$mkdir = (@mkdir($backupdir)) ? TRUE : FALSE;
			if($mkdir) {
				echo "<p  style=\"text-align: center; \">$backupdir did not exist previously. Now created.</p>";
			} else {
				echo "<p style=\"text-align: center; font-weight: bold; \">Unable to create $backupdir. Please check your folder permissions.</p>";
			}
		}



	echo n.n.hed(gTxt('Crop Styles'), 1, ' style="text-align: center; margin-top:2em; font-weight: bold;"').
		n.n.startTable('list','','txp-list').
		n.'<thead>'.tr(
			n.hCell(gTxt('name')).
			n.hCell(gTxt('width')).
			n.hCell().
			n.hCell(gTxt('height')).
			n.hCell(gTxt('thumbnail')).
			n.hCell(gTxt('delete'))
		).'</thead>';

	$rs = safe_rows_start('*', 'ebl_crop', '1=1 ORDER BY `name`');

	$out = '';

	if ($rs)
	{
		while ($a = nextRow($rs))
		{
			$isChecked = ($a['thumb'] == 1) ? 'yes' : 'no';
			$out .= n.tr(
					td( htmlspecialchars($a['name'])).
					td( htmlspecialchars($a['width'])).
					td( htmlspecialchars(' X ')).
					td( htmlspecialchars($a['height'])).
					td( $isChecked).
					td(
						dLink('plugin_prefs.ebl_image_edit', 'ebl_cropdelete', 'stylename', $a['name'])
					)
				);
		}
	}

	echo form($out);

	echo n.tr(
		form(
			td( fInput('text', 'name', '', 'edit','','',10) ).
			td( fInput('text', 'width', '', 'edit','','',5) ).
			td().
			td( fInput('text', 'height', '', 'edit','','',5) ).
			td( '<input name="eblistmb" class="checkbox" type="checkbox">').
			td( fInput('submit', 'add', gTxt('add'), 'smallerbox') ).
			n.eInput('plugin_prefs.ebl_image_edit').
			n.sInput('ebl_cropsavenew')
		)
	);

	echo n.endTable();
}

function selectStyles($thumb = '0') {

	$rs = safe_rows_start('*', 'ebl_crop', 'thumb='.$thumb.' ORDER BY `name`');

	$out = "<select name=\"customsize\" id=\"customsize$thumb\">' + ".n.
		   "'<option value=\"Custom\">Custom</option>' + ".n;

	if ($rs)
	{
		while ($a = nextRow($rs))
		{
			$width	= $a['width'];
			$height	= $a['height'];
			$name 	= $a['name'];

			$out .= "'<option value=".$width."x".$height."\>".htmlspecialchars($name)." : $width x $height </option>' + ".n;
		}
	}
	$out .= "'</select>";

	return $out;
}
function ebl_cropsavenew()
{

	extract(doSlash(psa(array('name', 'width','height','eblistmb'))));

	if ($name && is_numeric($width) && is_numeric($height))
	{
		$eblistmb = ($eblistmb == "on") ? 1 : 0;

		$rs = @safe_insert('ebl_crop', "
			`name`	 = '$name',
			`width`	 = '$width',
			`height` = '$height',
			`thumb`  = '$eblistmb'");

		return ($rs) ?
			"New crop style created" :
			"<b>Error:</b> Duplicate Name";

	} else {

		return "<b>Width</b> and <b>Height</b> must be numeric values";
	}

	return FALSE;
}

function ebl_cropdelete()
{
	$name = ps('stylename');
	return (safe_delete('ebl_crop', "`name` = '".doSlash($name)."'")) ?
		"<b>Deleted</b> $name" :
		"<b>Error</b> Unable to delete $name";
}


if(gps('step') == 'image_edit' || gps('step') == 'thumbnail_insert' || gps('step') == 'image_replace' || gps('step') == 'thumbnail_create' || gps('step') == 'thumbnail_delete') {
	register_callback('ebl_imgcrop', 'admin_side', 'head_end');
}

function ebl_imgcrop() {

	global $path_to_site,$img_dir,$prefs;

	$id=gps('id');
	$imagedir = hu . $img_dir . '/';

	$rs = @safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

	if ($rs) {
		extract($rs);
		$filename = $id.$ext;
		$imgsrc = $imagedir.$filename;
		$tmbsrc = $imagedir.$id.'t'.$ext;
		$backup 	= $path_to_site . '/' . $img_dir . '/backup/'.$filename;
	}

	$restore=(!file_exists($backup))?'$("#eblrestore").hide();':'';


	$customThumbSize = selectStyles(1);
	$customImgSize = selectStyles(0);

	$eblwmprefs=get_pref('eblwatermark');
	$eblwmval=(!empty($eblwmprefs))?$eblwmval=unserialize($eblwmprefs):array('wm'=>'watermark','top'=>'100','left'=>'100');

	echo <<< EOF
<script src="jcrop/jquery.Jcrop.pack.js"></script>
<link href="jcrop/jquery.Jcrop.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#eblimgprocess {
	margin: 5px;
	font-weight: bold;
	font-size: 14pt;
	color: #550000;
	padding: 0 0 0 20px;
	background: url('jcrop/processing.gif') left no-repeat;
}
a { padding: 0 0 3px;}
a:visited {} a:hover {} a:active { border: 0; }
a:focus { outline: 0; text-decoration: none;}
.jcrop-holder{margin:20px auto 40px}
</style>
<script type="text/javascript">
$(document).ready(function () { // init everything

    $("#image_container .fullsize-image img").attr('id', 'mainImg'); // Give our image an ID

    $('.thumbnail-edit img').attr('id', 'thumb'); // Give our thumbnail image an ID
    $('.thumbnail-edit').attr('id', 'thumbTD'); // name the TD that contains the image so we can swap it out.

    if (!$('#thumbTD').length) {
        $(".thumbnail-upload").attr('id', 'thumbTD');
    }

    //prepare Button Container
    $('#image_container').prepend('<section id="eblcropui" class="txp-edit"></section>');

    $('#eblcropui').append('<section role="region" id="eblcontainer_group" class="txp-details" aria-labelledby="eblcontainer_group-label"><h3 id="eblcontainer_group-label">Edit Image</h3><div role="group" id="eblcontainer"></div></section>');

    //insert Button
    $('#eblcontainer').append(
        '<p class="nav-tertiary">' +
        '<a href="#" id="eblcroplnk" class="navlink">Crop</a>' +
        '<a href="#" id="ebltmblnk" class="navlink">Thumbnail</a>' +
        '<a href="#" id="eblrotatelnk" class="navlink">Rotate</a>' +
        '<a href="#" id="eblresizelnk" class="navlink">Resize</a>' +
        '<a href="#" id="eblwmlnk" class="navlink">Watermark</a>' +
        '<a href="#" id="eblbackuplnk" class="navlink">Backup</a>' +
        '</p>'
    );

    //insert Div for function
    $('#eblcontainer').append('<div id="eblwmctrl"></div><div id="eblcropdata"></div><div id="eblcropctrl"></div><div id="ebltmbctrl"></div><div id="eblrotatectrl"></div><div id="eblresize"></div><div id="eblbackupimg"></div><div id="eblhiddendata"></div><div id="eblimgprocess"> Processing...</div>').children('div').hide();;



    //------------------------------------------
    // CROP Control
    $('#eblcropctrl').append(
        '<input type="hidden" id="eblcropHh" name="eblcropH" value="" />' +
        '<input type="hidden" id="eblcropWh" name="eblcropW" value="" />' +
        '<input type="hidden" id="eblcropXh" name="eblcropXh" value="" />' +
        '<input type="hidden" id="eblcropYh" name="eblcropYh" value="" />'
    );
    $('#eblcropctrl').append(
        '<p>Use predefined size: $customImgSize | <input name="aspectratio" type="checkbox" id="imgaspectratio" value="" /> Lock Aspect Ratio </p>' +
        '<p><input type="submit" name="$id" id="eblimgcrop" value="Crop Original" class="smallerbox" /></p>'
    );
    $('#eblcropdata').append(
        '<p>Current Crop Area (W: <span style="font-weight: bold;" id="eblcropW">0</span>) x (H: <span style="font-weight: bold;" id="eblcropH">0</span>)</p>'
    );

    // set our defaults
    window.crop = false;
    window.tmbcrop = false;

    $('#mainImg').wrap('<div id="imgDiv"></div');
    $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);



    function removeCrop() {
        // Removes jCrop if found.
        if ($('.jcrop-holder').length) {
            var x = $('#imgDiv');

            // generate random number to append to img url to avoid cache issues
            var rand = Math.random();

            // hide Width/Height data
            $('#eblcropdata').hide();

            // replace removed content with just the image.
            x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
            window.myCrop = '';
        }

        window.cropOn = false;
        window.crop = false;
        window.tmbcrop = false;
    }

    function addCrop() {
        // Bind jCrop to #mainImg
        var aspectratio = $('#aspectratio').attr('checked') ? 1 : 0; // Thanks Manfre

        // Display H X W
        $('#eblcropdata').show();

        // check if the jcrop holder is already active. Load if not active.
        if (!$('.jcrop-holder').length) {
            window.myCrop = $.Jcrop('#mainImg', {
                onSelect: showCoords,
                onChange: showCoords,
                onDblClick: function () {
                    if ($('#eblcropctrl').is(':visible')) {
                        $('#eblimgcrop').click();
                    } else if ($('#ebltmbctrl').is(':visible')) {
                        $('#ebltmbcrop').click();
                    }
                },
                aspectRatio: aspectratio
            });
            myCrop.setSelect([0, 0, 100, 150]);
        }
        window.cropOn = true;
    }


    $('#eblcroplnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#eblcropctrl').toggle();
        $('#eblcontainer div').not('#eblcropctrl').hide();


        if (window.crop && window.cropOn) { // user closed main crop function
            window.crop = false;
            removeCrop(); // remove the crop binding
        } else if (!window.crop && window.cropOn) { // user switched from thumb crop
            window.tmbcrop = false;
            window.crop = true;
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            $('#customsize0').val('Custom');
            myCrop.setOptions({
                aspectRatio: 0
            });
            myCrop.animateTo([0, 0, 100, 150]);
        } else { // user opened main crop function
            addCrop(); // add crop binding
            window.crop = true;
        }
    });

    $('#eblimgcrop').click(function () {
        var id = $('#eblimgcrop').attr('name');
        var H = $('#eblcropHh').attr('value');
        var W = $('#eblcropWh').attr('value');
        var X = $('#eblcropXh').attr('value');
        var Y = $('#eblcropYh').attr('value');
        $('#eblimgprocess').show();
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblimgcrop&imgid=" + id,
            data: "&eblcropXh=" + X + "&eblcropYh=" + Y + "&eblcropW=" + W + "&eblcropH=" + H,
            success: function (html) {
                $('#eblimgprocess').hide();
                if (html.match(/success/)) {
                    removeCrop();
                    $('#eblcropctrl').toggle();
                } else {
                    alert(html);
                }
            }
        });
    });
    $('#customsize0,#customsize1').change(function () {
        var size = $(this).val();
        if (!size.match("Custom")) {
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', false).attr('checked', true);
            var x = size.split('x')[0];
            var y = size.split('x')[1];

            myCrop.animateTo([0, 0, x, y]);

            var aspectratio = x / y;

            var opt = {
                aspectRatio: aspectratio
            }

            myCrop.setOptions(opt);
        } else {
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            myCrop.setOptions({
                aspectRatio: 0
            });
        }

    });

    $('#imgaspectratio,#tmbaspectratio').change(function () {
        if ($(this).attr('checked')) {
            if (this.id === 'imgaspectratio') {
                var size = $('#customsize0').val();
            } else {
                var size = $('#customsize1').val();
            }

            var x = size.split('x')[0];
            var y = size.split('x')[1];

            var aspectratio = x / y;
            myCrop.animateTo([0, 0, x, y]);

        } else {
            var aspectratio = 0;

        }

        var opt = {
            aspectRatio: aspectratio
        }
        myCrop.setOptions(opt);
    });



    //------------------------------------------
    // THUMB Control
    $('#ebltmbctrl').append(
        '</p><p>Use predefined size: $customThumbSize | ' +
        '<input name="aspectratio" type="checkbox" id="tmbaspectratio" value="" /> Lock Aspect Ratio </p></p><p> Thumbnail Dimensions: ' +
        '( W: <input name="ebltmbW" type="text" id="ebltmbW" size="5" maxlength="4" value="100" /> ) x ' +
        '( H: <input name="ebltmbH" type="text" id="ebltmbH" size="5" maxlength="4" value="100" /> )' +
        '| <input name="cropthumb" type="checkbox" id="cropthumb" checked="true"/> Resize Thumbnail ' +
        '</p><p>' +
        '<input type="button" name="$id" id="ebltmbcrop" value="Create Thumbnail" class="smallerbox" />' +
        '</p>'
    );

    $('#ebltmblnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#ebltmbctrl').toggle();
        $('#eblcontainer div').not('#ebltmbctrl').hide();

        if (window.tmbcrop && window.cropOn) { // user closed thumb crop
            window.tmbcrop = false;
            removeCrop();
        } else if (!window.tmbcrop && window.cropOn) { // user switched from main crop
            window.tmbcrop = true;
            window.crop = false;
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            $('#customsize1').val('Custom');
            myCrop.setOptions({
                aspectRatio: 0
            });
            myCrop.animateTo([0, 0, 100, 150]);
        } else { // user opens thumb crop
            addCrop();
            window.tmbcrop = true;
        }

    });

    $('#ebltmbcrop').click(function () {
        var id = $('#eblimgcrop').attr('name');
        var H = $('#eblcropHh').attr('value');
        var W = $('#eblcropWh').attr('value');
        var X = $('#eblcropXh').attr('value');
        var Y = $('#eblcropYh').attr('value');

        var tH = $('#ebltmbH').attr('value');
        var tW = $('#ebltmbW').attr('value');

        $('#eblimgprocess').show();
        var rszTmb = $('#cropthumb').attr('checked') ? 'rsz' : 'no';

        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=ebltmbcrop&imgid=" + id,
            data: "&rszTmb=" + rszTmb + "&eblcropXh=" + X + "&eblcropYh=" + Y + "&eblcropW=" + W + "&eblcropH=" + H + "&ebltmbH=" + tH + "&ebltmbW=" + tW,
            success: function (html) {
                $('#eblimgprocess').hide();
                if (html.match(/success/)) {
                    removeCrop();
                    $('#thumbTD #thumb').remove();
                    $('#thumbTD').prepend('<img src="$tmbsrc?' + rand + '" id="thumb" class="content-image" />');
                    $('#ebltmbctrl').toggle();
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#cropthumb').change(function () {
        var cropthumb = $(this).attr('checked') ? false : true;

        if (cropthumb) {
            $('#ebltmbW, #ebltmbH').attr({
                disabled: "disabled",
                value: "###"
            });
        } else {
            $('#ebltmbW, #ebltmbH').removeAttr("disabled");
            $('#ebltmbW, #ebltmbH').attr("value", "100");
        }
    });



    //------------------------------------------
    // Rotate Control
    $('#eblrotatectrl').append(
        '<p>Rotate <input type="button" name="$id" id="rotateright" value="Clockwise 90&deg;" class="smallerbox" /> ' +
        '<input type="button" name="$id" id="rotateleft" value="Counterclockwise 90&deg; " class="smallerbox" /> ' +
        '<input type="button" name="$id" id="rotateup" value="180 &deg; " class="smallerbox" />' +
        '</p>'
    );

    $('#eblrotatelnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#eblrotatectrl').toggle();
        $('#eblcontainer div').not('#eblrotatectrl').hide();

        removeCrop();
        window.myCrop = ' ';
        window.crop = false;
    });

    $('#rotateleft').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');

        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateleft&imgid=" + id,
            success: function (html) {
                if (html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#rotateright').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');
        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateright&imgid=" + id,
            success: function (html) {
                if (html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#rotateup').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');
        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateup&imgid=" + id,
            success: function (html) {
                if (html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                } else {
                    alert(html);
                }
            }
        });
    });



    //------------------------------------------
    // BACKUP Control
    $('#eblbackupimg').append(
        '<p><input type="button" name="$id" id="eblbackup" value="Backup" class="smallerbox" /> ' +
        '<input type="button" name="$id" id="eblrestore" value="Restore" class="smallerbox" />' +
        '</p>'
    );
    $restore

    $('#eblbackuplnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#eblbackupimg').toggle();
        $('#eblcontainer div').not('#eblbackupimg').hide();

        removeCrop();
        window.myCrop = ' ';
        window.crop = false;
    });

    $('#eblbackup').click(function () {
        var id = $(this).attr('name');
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblbackupimg&imgid=" + id,
            success: function (html) {
                $('#eblrestore').show();
                if (html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    alert("image backed up");
                } else {
                    $('#eblimgprocess').hide();
                    alert("Error : " + id + "  |   " + html);
                }
            }
        });
    });

    $('#eblrestore').click(function () {
        var id = $(this).attr('name');
        $('#eblimgprocess').show();
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrestoreimg&imgid=" + id,
            success: function (html) {
                if (html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                    alert("image restored");
                } else {
                    $('#eblimgprocess').hide();
                    alert("Error : " + html);
                }
            }
        });
    });



    //------------------------------------------
    // RESIZE Control
    $('#eblresize').append(
        '<p>Current Image Size : W[ <span id="origW"></span> ] x H[ <span id="origH"></span> ]</p>' +
        '<p><label for="eblrszW">Width: </label><input type="text" name="eblrszW" id="eblrszW" /> x ' +
        '<label for="eblrszH">Height: </label><input type="text" name="eblrszH" id="eblrszH" /> ' +
        '<input type="button" name="$id" id="eblrszimg" value="Resize" class="smallerbox" />' +
        '</p>'
    );

    $('#eblresizelnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#eblresize').toggle();
        $('#eblcontainer div').not('#eblresize').hide();

        var imgW = $('#mainImg').width();
        var imgH = $('#mainImg').height();

        $('#origW').text(imgW);
        $('#origH').text(imgH);

        removeCrop();
    });

    $('#eblrszimg').click(function () {
        var id = $('#eblrszimg').attr('name');
        var H = $('#eblrszH').attr('value');
        var W = $('#eblrszW').attr('value');

        $('#eblimgprocess').show();

        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrszimg&imgid=" + id,
            data: "&eblrszH=" + H + "&eblrszW=" + W,
            success: function (html) {
                $('#eblimgprocess').hide();
                if (html.match(/success/)) {
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                    //
                } else {
                    alert(html);
                }
            }
        });
    });



    //------------------------------------------
    // WATERMARK Control
    $('#eblwmctrl').append(
        '<p><label for="eblmwimg">Immagine: $imagedir</label><input type="text" name="eblwmimg" id="eblwmimg" value="$eblwmval[wm]"/></p>' +
        '<p><label for="eblwmT">Top: </label><input type="text" name="eblwmT" id="eblwmT" value="$eblwmval[top]"/> px ' +
        '<label for="eblwmL">Left: </label><input type="text" name="eblwmL" id="eblwmL" value="$eblwmval[left]" /> px </p>' +
        '<p><input type="button" name="$id" id="eblwatermark" value="Watermark" class="smallerbox" /></p>'
    );


    $('#eblwmlnk').click(function () {
        $(this).siblings().css('font-weight', 'normal');
        $(this).css('font-weight', 'bold');
        $('#eblwmctrl').toggle();
        $('#eblcontainer div').not('#eblwmctrl').hide();
        removeCrop();
    });

    $('#eblwatermark').click(function () {
        var id = $(this).attr('name');
        var wm = $('#eblwmimg').attr('value');
        var T = $('#eblwmT').attr('value');
        var L = $('#eblwmL').attr('value');

        $('#eblimgprocess').show();

        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblwmcreate&imgid=" + id,
            data: "&eblwmT=" + T + "&eblwmL=" + L + "&eblwmimg=" + wm,
            success: function (html) {
                $('#eblimgprocess').hide();
                if (html.match(/success/)) {
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand + '" id="mainImg" />');
                    //
                } else {
                    alert(html);
                }
            }
        });
    });


});

function showCoords(c) {
    $('#eblcropX').text(c.x);
    $('#eblcropXh').val(c.x);
    $('#eblcropY').text(c.y);
    $('#eblcropYh').val(c.y);
    $('#eblcropX2').text(c.x2);
    $('#eblcropX2h').val(c.x2);
    $('#eblcropY2').text(c.y2);
    $('#eblcropY2h').val(c.y2);

    var imgW = zeroPad(c.w, 4);
    var imgH = zeroPad(c.h, 4);

    $('#eblcropW').text(imgW);
    $('#eblcropWh').val(c.w);
    $('#eblcropH').text(imgH);
    $('#eblcropHh').val(c.h);
};

function zeroPad(num, count) {
    var numZeropad = num + '';
    while (numZeropad.length < count) {
        numZeropad = "0" + numZeropad;
    }
    return numZeropad;
}

</script>
EOF;

}

function eblrestoreimg($id)
{

	global $path_to_site,$img_dir;

	$rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

	if ($rs) {
		$backupdir 	= $path_to_site . '/' . $img_dir . '/backup/';
		$imagedir	= $path_to_site . '/' . $img_dir . '/';

		extract($rs);
		$filename = $id.$ext;
		$imgsrc = $imagedir.$filename;

		if(copy($backupdir.$filename, $imagedir.$filename))
		{
			list($width, $height) = getimagesize($backupdir.$filename);
			if(safe_update('txp_image', "w = '".$width."', h = '".$height."'", "id = $id"))
			{
				echo "success"; exit();
			} else {
				echo "File Copied but db not updated";
			}
		}
	} else {
		echo "Database Error";
	}

	exit();
}

function eblbackupimg($id)
{
	global $path_to_site,$img_dir;

	$rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

	if ($rs) {
		$backupdir 	= $path_to_site . '/' . $img_dir . '/backup/';
		$imagedir	= $path_to_site . '/' . $img_dir . '/';

		extract($rs);
		$filename = $id.$ext;
		$imgsrc = $imagedir.$filename; echo $imagedir.$filename;

		$cr = copy($imagedir.$filename,$backupdir.$filename);
		echo "success";
	} else {
		echo "Database Error";
	}

	exit();
}

function ebl_img_edit ($id, $direction, $action)
{
	global $path_to_site,$img_dir;

	$imagedir = $path_to_site . '/' . $img_dir . '/';

	$rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

	if ($rs) {
		extract($rs);
		$filename = $id.$ext;

		switch (strtolower($ext)) {
			case '.jpg':
				$srcimage = imagecreatefromjpeg($imagedir . $filename);
				break;
			case '.gif':
				$srcimage = imagecreatefromgif($imagedir . $filename);
				break;
			case '.png':
				$srcimage = imagecreatefrompng($imagedir . $filename);
				break;
		}

		if($action=='watermark'){ //top and left was based on full image.
			$eblwm['top']=gps('eblwmT');
			$eblwm['left']=gps('eblwmL');
			$eblwm['wm']=gps('eblwmimg');

			set_pref('eblwatermark', serialize($eblwm), 'admin',2);
			if (file_exists($imagedir.$eblwm['wm'])) {	//first step WM must be a PNG!
				$wmimg=@imagecreatefrompng($imagedir.$eblwm['wm']);
				$wmimg_w = imagesx($wmimg);
				$wmimg_h = imagesy($wmimg);
				imagecopy($srcimage, $wmimg,$eblwm['left'],$eblwm['top'], 0, 0, $wmimg_w, $wmimg_h);
				$newimg=$srcimage;
			}
		}

		if($action == 'rotate') { // rotate image

			/** Ternary equivalent of  if / elseif / else : Done for simple shorthand. Assumes anything not right / left = 180 **/
			$degrees = ($direction == 'right') ? -90 : (($direction == 'left') ? 90 : 180);
			$newimg  = imagerotate($srcimage, $degrees, 0);
		} elseif ($action == 'crop') { // primary image cropping

			//gather crop variables from hidden fields.
			$cropX = gps('eblcropXh');
			$cropY = gps('eblcropYh');
			$targW = $cropW = gps('eblcropW');
			$targH = $cropH = gps('eblcropH');

			// prevents division by zero & invalid crop widths.
			$value = (int)$targH + (int)$targW;
			if($value < 30) {
				echo "invalid crop values detected";
				imagedestroy($srcimage);
				exit();
			}

			/** Create base canvas **/
			$newimg = imagecreatetruecolor($targW,$targH);

			/** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
			imagecopyresampled($newimg, $srcimage,0,0, $cropX, $cropY, $targW, $targH, $cropW, $cropH);

		} elseif ($action == 'thumbnail') { // create thumbnail

			//gather crop variables from hidden fields.
			$cropX = gps('eblcropXh');
			$cropY = gps('eblcropYh');
			$cropH = gps('eblcropH');
			$cropW = gps('eblcropW');

			// prevents division by zero & invalid crop widths.
			$value = (int)$cropH + (int)$cropW;
			if($value < 4) {
				echo "invalid crop values detected";
				imagedestroy($srcimage);
				exit();
			}

			if(gps('rszTmb') == "rsz") { // are we resizing the the thumbnail?
				$targH = gps('ebltmbH');
				$targW = gps('ebltmbW');

			// did someone forget to input a height or width? If so, default to crop width
			if($targH < 1) { $targH = $cropH; }
			if($targW < 1) { $targW = $cropW; }

				// determine the correct scale value
				$scale = min($targW / $cropW,$targH / $cropH);
				// are the sides equal? If not, scale to fit.
				if ($scale < 1) {
					$targW = ceil($scale * $cropW);
					$targH = ceil($scale * $cropH);
				}
			} elseif (gps('rszTmb') != 'rsz') { // if not, we'll just use the crop-area size to create our thumbnail
				$targH = $cropH;
				$targW = $cropW;
			}

			/** Create base canvas **/
			$newimg = imagecreatetruecolor($targW,$targH);
			/** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
			imagecopyresampled($newimg, $srcimage,0,0, $cropX, $cropY, $targW, $targH, $cropW, $cropH);

		} elseif ($action == 'rsz') { // general resize
			$targH = gps('eblrszH');
			$targW = gps('eblrszW');

			if(($targH + $targW) < 1) {
				echo " ERROR : Dimensions must be entered.";
				return false;
			}
			// did someone forget to input a height or width? If so, we'll default to the original size for the missing value.
			if($targH < 1) { $targH = $h; }
			if($targW < 1) { $targW = $w; }
			// checks to see if the targeted values are larger than the original, if so, reset to original value as the longest side.
			if($targH > $h) { $targH = $h; }
			if($targW > $w) { $targW = $w; }

			$scale = min($targW / $w, $targH / $h);
			// are the sides equal? If not, scale to fit.
			if ($scale < 1) {
				$targW = ceil($scale * $w);
				$targH = ceil($scale * $h);
			} else { // image is smaller than requested scale. Abort.
				imagedestroy($srcimage);
				echo "Image smaller than parameters entered";
				return;
			}

			/** Create base canvas **/
			$newimg = imagecreatetruecolor($targW,$targH);
			/** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
			imagecopyresampled($newimg, $srcimage,0,0,0,0, $targW, $targH, $w, $h);
		}

		$t = ($action == 'thumbnail') ? 't' : '';

		$filename = $id.$t.$ext;

		switch (strtolower($ext)) {
			case ($ext == '.jpg' || $ext == '.jpeg'):
				$fileresult = imagejpeg($newimg,$imagedir . $filename,'100');
				break;
			case ".gif":
				$fileresult = imagegif($newimg,$imagedir . $filename);
				break;
			case ".png":
				$fileresult = imagepng($newimg,$imagedir . $filename);
				break;
		}

		imagedestroy($srcimage);
	}

	if($action != 'thumbnail')
	{
		list ($width,$height) = getimagesize($imagedir . $filename);
		$rs = safe_update('txp_image', "h = '$height', w = '$width'", "id = $id");
	} else {
		list ($width,$height) = getimagesize($imagedir . $filename);
		$rs = safe_update('txp_image', "thumb_h = '$height', thumb_w = '$width', thumbnail = '1'", "id = $id");
	}

	echo ($fileresult && $rs) ? "success" : 'ERROR ';
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1><span class="caps">EBL</span> Image Edit</h1>

	<h2>Advanced Image Editing Plugin for Textpattern</h2>

	<p><span>T</span> his plugin introduced advanced image editing functionality to Textpattern 4.07 and has now been updated to work nicely with the Hive theme of Textpattern 4.6.
 Users may now custom crop, resize, rotate, and create custom thumbnails directly within Textpattern.</p>

	<h2>Table of contents</h2>

	<ol>
		<li><a href="#help-section01" rel="nofollow">List of features</a></li>
		<li><a href="#help-section02" rel="nofollow">Plugin requirements</a></li>
		<li><a href="#help-section03" rel="nofollow">Installation</a></li>
		<li><a href="#help-section04" rel="nofollow">Usage Instructions</a></li>
	</ol>

	<h2>List of features</h2>

	<p>p.This plugin allows for functions previously only available in image-editing programins. It allows for users, at any terminal which they&#8217;re logged in to to do the following:</p>

	<ul>
		<li><strong>Cropping</strong>: The original uploaded image can be edited to cut out parts that are not desired and keep only those important facets of the original image.</li>
		<li><strong>Thumbnail Crop</strong>: Make a thumbnail show not the entire image, but just enough to pique interest.</li>
		<li><strong>Rotate</strong>: For those pictures that are sideways or upside down. Users may rotate the image clockwise90&amp;deg;, counterclockwise90&amp;deg; or rotate a full 18090&amp;deg;.</li>
		<li><strong>Resize</strong>: Many digital images are just too large to be shown on the web. This option allows for excessively sized images to be scaled down. Built into the resizing function is the ability to maintain the original height to width ratio, so images will never seem squished one way or the other.</li>
		<li><strong>Image Backups</strong>: New for <em>2.0</em> is the feature to save an original copy of the the image to a temp folder so that you may restore it at any time. The image must first be saved by clicking on backup, and then may be restored at any time.</li>
	</ul>

	<h2>Plugin requirements</h2>

	<p>ebl_image_edit 2.3 minimum requirements:</p>

	<ul>
		<li>Textpattern 4.5</li>
		<li>Jquery &gt; 1.7.2</li>
		<li>JCrop http://deepliquid.com/content/Jcrop_Download.html</li>
	</ul>

	<h2>Installation</h2>

	<p>The general behavior stands: paste plugin code to the plugin installer textarea and run the automatic setup. Then just activate the plugin and you are ready to use new tags that plugin includes like others.</p>

	<p>Upload Jcrop folder to the Textpattern directory (e.g., <code>/textpattern/</code> )</p>

	<p>p.Once the plugin has been activated, navigate to <em>Plugins -&gt; <span class="caps">EBL</span> Image Edit -&gt; Options</em>. The temp folder and db field will be created. The plugin will be ready for use at this point.</p>

	<h2>Usage Instructions</h2>

	<p>Further help can be found online in the textpattern forum topic: <a href="http://forum.textpattern.com/viewtopic.php?id=29547" rel="nofollow">Ebl Image Edit</a></p>

	<ul>
		<li><strong>Crop: This tab permits the cropping of images. Click and drag across the image to select the crop area. While selecting the crop region, the size of the crop area willbe displayed in as Width X Height. This is measured in pixels. You&#8217;ll note that the area outside of the crop region is dark, whereas the area inside is lighter. This is the intended effect. Everything within the crop area will be selected and will replace the original image once the *Crop Original</strong> button is clicked.
	<ul>
		<li><ins>Predefined size</ins>: This drop-down box allows the user to select pre-defined crop area selections. They may be moved around anywhere on the image. If needed, these areas can be sized differently, but the aspect ratio of the original will remain. <em>(Found in Thumbnail Crop as well.)</em></li>
		<li><ins>Lock Aspect Ratio</ins>: Unchecking this box will allow for a different aspect ratio than what was selected in the drop down box. <em>(Found in Thumbnail Crop as well.)</em></li>
	</ul></li>
		<li><strong>Thumbnail Crop</strong>: Nearly identical in function to the main image crop, the thumbnail crop allows for a selected region to be highlighted to act as the primary image thumbnail. To create the cropped thumbnail, select the crop-area. If you want the image resized to a specific dimension, check <em>Resize Thumbnail</em> and edit the size accordingly. Once you are satisified with the crop area and the options, click on the Create Thumbnail button.</li>
		<li><strong>Rotate</strong>: This tab allows three seperate methods of rotating. 1. Rotate Clockwise 90&amp;deg; (Turn Right), 2. Counter-Clockwise 90&amp;deg; (Left Turn) or 3. 180&amp;deg; (Flip). Clicking any of these buttons will rotate the original button. <em>Do not repeatedly rotate the images as this will gradually degrade image quality</em>.&lt;/li&gt;</li>
		<li><strong>Resize</strong>: This tab allows the for a longest-side resize of an original image. Simply input the desired dimensions and the image will be sized down to fit. Note, if the image is smaller in any one dimension that which was entered, it will not be sized up to fit, but rather, sized down in proportion with the longest side. If the image is entirely smaller than the dimensions entered, it will not be resized.</li>
		<li><strong>Watermark</strong>: This tab allows the for insert an watermark image onto the original image. Select the <em><span class="caps">PNG</span></em> watermark from <code>www.yoursite.com/images/</code> then insert the relative position of watermark. The value of image and position are stored on txp_prefs table.</li>
		<li><strong>Backup</strong>: This tab allows for the backing-up of an image into a backup folder located within your image directory. When active, uno button are displayed. The Backup will immediately copy the associated image to the backup directory and overwrite any identically named file. If Backup image exist Restore button will show. Clicking Restore will copy that image back to the main image directory but will still maintain the copied file in this location.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>