<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mck_article_image';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.8.3';
$plugin['author'] = 'Marco Casalegno';
$plugin['author_uri'] = 'http://www.kreatore.it/';
$plugin['description'] = 'Show a button to help to speed up access to image selection on Write Tab';

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
// ----------------------------------------------------
// Admin side plugin
// ----------------------------------------------------
if (txpinterface == 'admin'){
 mck_image_img();
 _extract_img();
//insert icon for calling jquery
register_callback('mck_list_image_add_call','article_ui','article_image');
//insert script for using img
register_callback('mck_image_js','admin_side', "head_end");
//insert css for floating div
register_callback('mck_image_css','admin_side','head_end');
}
//---------------------------------------------------------
/**
	Generates and stores the images required by the design.
	Image URI is: http://example.com/textpattern/
*/
function mck_image_img(){
if(gps('mck_image_img') != 'image.png')
			return;
$file = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ
bWFnZVJlYWR5ccllPAAAAixJREFUeNqkU01rE1EUPTOZmSTzkXTamjaJKRiLBVMoERS60CpKsYgL
UQruutQfUQh0V8SVoBuh2y7UlUjBjVgFEQqiZBEritRStY2J02ZM5s0b75up0k2zaC9c3p03c847
98x9UhAEOEzIOGQo05XKVVpzB8SvK77v5x/Nzd0/CPr67OxtxWdM4uTD2bsSZAlQYySLssMAYU8q
SZmgPWq22A+UsgDjwPSpAAKrMMZkn74cOypBU4A0AfoNYOM3kFSjZ2FzowWMUqNjeaDVAQRGYBXm
eSFBzniFE9ljsI1cePLIADBgRUQuAYp9kZI2i0hDAsKGBIxzPF95iNcJHZfL13C+dDGULBQJ2a4H
9OqA50d7uira4BGBJwiIrbZaQ0+6B4vOAprONxSODCHbW0AmZSOp0dGSHoG1qLU/PIAXEnQ6sk9s
7WYeW9sKduoaHjfeI5lYhaEbMA0TpmnCMi0sJ4hc+4UzfWU8OD0Pgf2voJi9BE3TEI/Hoes6LMtE
Om3BtlOUFtUGltbuYXJiHC+WVyAw/xTEOCnYG0HosA+X3JNlF89a8/i5XoMky2j6TbS3OSaXpvC1
9P2G0qjXtZiq4smdm/sOzJWnHzEykQnrTbaJ4xfssK5+qhYkY3j4lqzrQ90mzj33Y4pn2oOiPjkz
Olhd+LARvvjC3oiFfEVm9z50z4rxLve5FIh1F6PR0IL+LnYona45rjiwg7Lzcoumib9FTFrEGvf/
CjAASeHuI7U+I90AAAAASUVORK5CYII=';
  ob_start();
  ob_end_clean();
  header('Content-type: image/png');
  echo base64_decode($file);
  exit();
}

//---------------------------------------------------------
/**
	Function called by ajax. Print all images into a div
*/
function _extract_img(){
if(gps('extractimg') != 'ajax')
  return;

$val=explode(',',gps('val'));
//estraggo tutte le categorie immagini
$qcat = safe_rows('name', 'txp_category','type="image" AND parent="root"');
//inizialize $cl
$cl[]='<option value="">Show all</option>';
foreach($qcat as $cat){
$cl[]='<option>'.$cat['name'].'</option>';
}

//   Extract all image that have a thumbnail
  $qimg = safe_rows('id,name,ext,thumb_w,thumb_h,category,alt', 'txp_image','thumbnail=1 ORDER BY id DESC');
  $out[]='<legend>'.gtxt('category').': <select>'.join('',$cl).'</select></legend><div id="mck_images_list">';
  //set initial variable
  foreach ($qimg as $image){
  //if checked set variable
  $high=(in_array($image['id'], $val))?'selected':'';

  $out[] = '<span><img src="'.imagesrcurl($image['id'], $image['ext'], true).'" alt="'.$image['alt'].'" title="'.imagesrcurl($image['id'], $image['ext'], true).'"'. //($image['thumb_w'] ? "width='$image[thumb_w]' height='$image[thumb_h]'" : ''). ' />
  'width="100px" height="100px" class="'.$image['category'].' content-image '.$high.'" id="'.$image['id'].'" /><ins class="'.$high.'">#'.$image['id'].'</ins></span>';
  }
  $out[]='</div><p class="txp-buttons"><a href="#" class="mck_update">'.gtxt('update').'</a></p>';
  ob_start();
  ob_end_clean();
  header('Content-type: text/html');
  echo implode(" ",$out);
  exit();
}

//---------------------------------------------------------
/**
	Replace original article-image input. Chose between TXP 4.x<4.5
*/
function mck_list_image_add_call($event,$step,$data,$rs){
global $prefs;
  //create image list window!
  $list_image='<fieldset id="mck_images_window" style="display:none;"></fieldset>';
  $img_show= array();

if(!empty($rs['Image'])){
	$list=explode(',',$rs['Image']);
	foreach($list as $img){
		//check if is an internal image
  	if(is_numeric($img)){
  	  $q=safe_row('id,ext','txp_image','id='.$img);
  	  $img_show[]='<img src="'.imagesrcurl($q['id'], $q['ext'], true).'" width="50px" height="50px" class="content-image"/>';
  	}
		//check if is an external image
  	if((substr($img,0,4)=="http")){
    	$img_show[]='<img src="'.$img.'" width="50px" height="50px" class="content-image" />';
		  }
	}
}

  //replace with hacked input field
  $replace='<input type="text" value="$1" name="Image" size="22" class="edit" style="width:120px" id="article-image" /> <img class="mck_image_call" src="./?mck_image_img=image.png" align="right" width="16px" height="16px" style="padding-top:8px;cursor:pointer;"/><br /><div class="mck_show_image">'.join(' ',$img_show).'</div>'.$list_image;

  //check TXP version for input replace
  if($prefs['version'] < "4.5.0"){
  $find='#<input type="text" value="([^"]*)" name="Image" size="22" class="edit" id="article-image" />#';
  }else{
  $find='#<input type="text" name="Image" size="32" id="article-image" value="([^"]*)" />#';
  }

  return preg_replace($find,$replace,$data);
}

//---------------------------------------------------------
/**
	Print on the page the JS code that use Jquery to work with images
*/
function mck_image_js(){
global $event;
if($event !== 'article') {
	return;
}
global $prefs;
  echo <<<JS_CODE
  <script type="text/javascript">
$(document).ready(function() {

    var siteurl = '$prefs[siteurl]',
        img_dir = '$prefs[img_dir]',
        wrapper = $('#mck_images_window'),
        ch = $('img', wrapper);

    // Fade in Background
    $('body').append('<div id="mck_iu_fade"></div>'); //Add the fade layer to bottom of the body tag.
    $('#mck_iu_fade').show().css({
        'filter': 'alpha(opacity=80)'
    }); // show the fade layer -  fix the IE Bug on fading transparencies

    //on img click open window
    $(".mck_image_call").on('click', function() {
        wrapper.html('<span class="spinner"></span>').toggle('slow', function() {
            if ($('.mck_image_call').is(':visible')) {
                $.post("./?extractimg=ajax&val=" + $("#article-image").val(), function(data) {
                    wrapper.html(data);
                }, 'html');
            }
        });
    });

    //get category select
    //If empty show all images else hide images on different category
    wrapper.on('change', 'select', function() {
        var vc = $(this).val();
        if (vc == '') {
            $("span",wrapper).show();
        }
        else {
            $("span",wrapper).show();
            $("span",wrapper).has("img:not(." + vc + ")").hide();
        }
    });

    //when click on image, set class and reorder the image list
    wrapper.on('click', 'img', function() {
        if (ch == '') {
            ch = $('img', wrapper);
        }
        $(this).toggleClass('selected');
        $(this).next().toggleClass('selected');
        class_arr = this.getAttribute("class").split(" ");;
        if (jQuery.inArray("selected", class_arr)) {
            ch = ch.not(this); //remove from jQuery 'array'
            Array.prototype.push.call(ch, this); //add to end of jQuery 'array'
        }
    });

    //when input click, print array
    wrapper.on('click', "a.mck_update", function(e) {
        e.preventDefault();
        var str = ch.filter(".selected").map(function(i, el) {
            return el.id;
        }).get().join(",");
        $("#article-image").val(str).change();
        $('#mck_images_window').toggle('slow');
        ch = '';
    });

    //update article image preview
    $("#article-image").change(function() {
        var values = $(this).val().split(',');
        if (values < 1) {
            $(".mck_show_image").html('');
            return;
        }
        var x, out = '',
            val;
        for (x in values) {
            val = values[x];
            if (val.substr(0, 4) == "http") {
                out = out + '<img src="' + val + '" width="50px" height="50px" class="content-image"/>';
            } else {
                out = out + ' <img src="http://' + siteurl + '/' + img_dir + '/' + val + 't.jpg" width="50px" height="50px" class="content-image"/>';
            }
        }
        $(".mck_show_image").html(out);
    });


});
    </script>

JS_CODE;
}

//---------------------------------------------------------
/**
	Print css code for floating div
*/
function mck_image_css(){
echo '
<style type="text/css">
div#image_group{position:relative;} /*Original TXP Hacking*/
#mck_images_window{margin:0 !important;padding:5%;position:absolute;z-index:9999999;top:-5px !important;left:0px;width:100%;height:100%;background:rgba(0,0,0,.7)}
#mck_images_list{margin:0;width:95%;height:95%;overflow:auto;}
#mck_images_list img{border:1px solid #ccc;margin:5px;cursor:pointer;}
#mck_images_list span{position:relative;}
#mck_images_list ins{position:absolute;left:5px;top:-17px;background:#fff;padding:2px 5px;border:1px solid #ccc;}
#mck_images_list ins.selected {background:#FABF2D;}
#mck_images_window legend{float:left;margin:-2% 0 2% -0; color:white;}
#mck_images_window legend select{width:120px;}
#mck_images_window .txp-buttons{position:absolute;top:2%;left:250px;}
</style>';
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>Mck_article_image</h1>

	<p>This plugin provides an enhancement to the standard <span class="caps">TXP</span> Write tab for the article image.</p>

	<h2>Table of contents</h2>

	<ul>
		<li><a href="#intro" rel="nofollow">Intro and Description</a></li>
		<li><a href="#requirements" rel="nofollow">Requirements</a></li>
		<li><a href="#installation-and-usage" rel="nofollow">Installation and usage</a></li>
		<li><a href="#changelog" rel="nofollow">Changelog</a></li>
		<li><a href="#known-issues" rel="nofollow">Know Issues</a></li>
		<li><a href="#thanks-to" rel="nofollow">Thanks to</a></li>
	</ul>

	<h2>Intro and Description</h2>

	<p>This plugin display under <em>article image</em> a button to help speed up access to image&#8217;s selection.<br />
A div will show on a page and you can select your <em>article image</em> by clicking on.<br />
If you prefer you can insert image&#8217;id or an external link directly on input field as normal.<br />
A little preview image will appear under input field.</p>

	<p>From 0.7 release you have a select box for show only the image category you prefer.</p>

	<p>From 0.8 release you can chose different image. The order of the images is one with which you will click on.</p>

	<h2>Requirements</h2>

	<p>At least</p>

	<ul>
		<li>Textpattern 4.3+</li>
		<li>Jquery 1.7.1+</li>
	</ul>

	<p>Recommended:</p>

	<ul>
		<li><span class="caps">PHP</span> 5.1.2+</li>
		<li>Textpattern 4.5+</li>
		<li>Jquery 1.8.2+</li>
	</ul>

	<h2>Installation and usage</h2>

	<p>The general behavior stands: paste plugin code to the plugin installer textarea and run the automatic setup. Then just activate the plugin and you are ready to use the button under article image on Write tab.</p>

	<h2>Changelog</h2>

	<ul>
		<li>0.8.2 Fix language error on popup window&#8217;s buttons (thanks to &#8216;shayne&#8217;)</li>
		<li>0.8.1	Alter sort images form <span class="caps">ASC</span> do <span class="caps">DESC</span></li>
		<li>0.8 	Chose multiple images &#8211; Require Jquery 1.7.2 or</li>
		<li>0.7 	Plugin update for <span class="caps">TXP</span> 4.5</li>
		<li>0.6 	Redesign css layout, add Jquery category filter</li>
		<li>0.5 	Change load method: now will load all image by Ajax</li>
		<li>0.4 	Insert ‘Close’ button</li>
		<li>0.3 	Fix error on image call</li>
		<li>0.2 	Insert width height into image thumbnail and changed setting css.</li>
		<li>0.1 	First Release</li>
	</ul>

	<h2>Know Issues</h2>

	<p>The image preview work only if the image is <em>.jpg</em><br />
I&#8217;m serching a way to check if image exist or an alternative to get the correct extension only for JS preview.</p>

	<h2>Thanks to</h2>

	<p>For write the code of this plugin I have take a look at the code found in the answords of <a href="http://www.kreatore.it/stackoverflow.com" rel="nofollow">stackoverflow.com</a> and I had obtain help in th <a href="http://www.kreatore.it/html.forum.it" rel="nofollow">html.forum.it</a></p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>