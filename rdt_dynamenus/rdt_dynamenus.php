<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rdt_dynamenus';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.9';
$plugin['author'] = 'Richard Tietjen';
$plugin['author_uri'] = 'http://publishingpipelines.com/';
$plugin['description'] = 'Dynamic Section and Article menus';

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

/*
  Licensed under the GPL

*/

/* no lAtts before rc2 */
if (!function_exists('lAtts')) {
  function lAtts($pairs, $atts) { // trying this out as well
    foreach($pairs as $name => $default) {
      $out[$name] = isset($atts[$name]) ? $atts[$name] : $default;
    }
    return ($out) ? $out : false;
  }
}

function rdt_article_menu ($atts) {
/*
  Based on Coke Harrington's chh_article_custom plugin

*/

    global $pretext, $prefs;

    extract($prefs);
    extract($pretext);

    extract(lAtts(
       array(
	 'wraptag' => 'ul',
	 'break' => 'li',  // BR can't carry .active state
	 'class' => 'menu',  // ul class='menu'
	 'active' => 'active',
	 'id' => 'articles', // ul id='articles'

	 'limit' => '100',
	 'sortby' => 'Posted',
	 'sortdir' => 'desc',
	 'dateposted' => 'to_date',
	 'section' => '', 		// good for testing
	 'status' => 'live',
	 ),$atts));

    $status      = strtolower($status);
    $section   = empty($section)   ? '' : doSlash($section);

    // Sections
    $sections = '';
    if (empty($section) and !empty($s) and $s == 'default') {
        $sections = ' ' . filterFrontPage() . ' ';
    }	// this one:
    elseif (empty($section) and !empty($s)) {
        $sections = " AND Section = '" . doSlash($s) . "' ";
    }
    elseif (!empty($section)) {
        foreach (explode(',', $section) as $section) {
            $sectparts[] = " (section = '" . doSlash($section) . "') ";
        }
        $sections = ' AND (' . join(' OR ', $sectparts) . ') ';
    }

    // Status
    $statmap = array('draft' => 1, 'hidden' => 2, 'pending' => 3,
										 'live' => 4, 'sticky' => 5);
    foreach (explode(',', $status) as $stat) {
        if (array_key_exists($stat, $statmap)) {
            $statparts[] = ' Status = ' . $statmap[strtolower($stat)];
        }
    }
    $status = isset($statparts)
		 ? '(' . join(' OR ', $statparts) . ')'
		 : ' status = 4 ';


    // Upcoming articles.
    switch ($dateposted) {
        case 'future':
            $posted = 'AND Posted > now()';
            break;
        case 'to_date':
            $posted = 'AND Posted < now()';
        case 'all':
            $posted = '';
            break;
        default:
            $posted = 'AND Posted < now()';
    }

    $query = " FROM " . PFX . "textpattern WHERE $status $posted $sections" ;

// print 'SELECT *, unix_timestamp(Posted) as uPosted'.  "$query ORDER BY $sortby $sortdir LIMIT $limit\n";

    $rs = getRows('SELECT *, unix_timestamp(Posted) as uPosted'.
                  "$query ORDER BY $sortby $sortdir LIMIT $limit");

    if ($rs) {
      $count = 0;
        foreach($rs as $a) {
            extract($a);

            /* if no ID we're in list mode, at a section, so tag first
         article with class=active
            */
            $count = $count+1;
	    if ($pretext['id'] == $ID) {
	    $tagatts[] = " class='$active'";
	    }  elseif (empty($pretext['id']) and  $count == 1) {
	      $tagatts[] = " class='$active'";
	    } else {
	    $tagatts[] = "";
	    }

	    if ( function_exists('permlinkurl') ) {
	     // rc3 only:
	      $linkref =  permlinkurl($a);
	    } else {
	      $linkref = formatPermLink($ID, $Section)
	      . stripSpace($Title);
	    }

	    $link = tag($Title,'a', " href='$linkref'");
	    $out[] = ($break != 'br')
	      ? tag($link, $break, implode('',$tagatts))
	      : "$link<br />";
	    unset($tagatts);
        } // end record set

	// return UL list:
	$id = (!empty($id)) ? ' id="'.$id.'"' : '';
	$class = (!empty($class)) ? " class='$class'" : '';
	return tag(implode("\n", $out),$wraptag,$id.$class);
    }
    return '';
}

function rdt_section_menu($atts) {
/*
  based on
  Marshall Potter's mdp_sectionmenu and mdp_lib.php code
  see http://greenrift.textdrive.com/projects/ for updates
*/
  global $s, $pfr;
  $out = array();

  // needed for post RC3?, rhu or hu
# nuts, use hu.   $pfr = $pfr ? $pfr : rhu;

  extract(lAtts(array(
      'wraptag'   => 'ul',
      'break'  => 'li',
      'class'  => 'menu',
      'active' => 'active',
      'id'     => 'sections',
      'noshow' => '',
      'default'=> '', // make default section home, at top of list
      'home'   => '', // put home section at top of menu, it better exist
      'order'  => '',
      'testsection' => '',	// testing
   ),$atts));

// pass in fake current section
  $s = $testsection ?  $testsection  : $s;

  $noshow = explode(',',$noshow);

  $rs = safe_rows('*','txp_section','name!="default"');
//  $rs = safe_rows('name, title','txp_section','name!="default"');
  $have_section_titles = false;	// does this txp support section titles?

  if($rs) {
    foreach($rs as $a) {
      $sections[$a['name']] = $a['title'] ? $a['title'] : $a['name'];
      if ( $a['title'] ) {$have_section_titles = true ; }
    }
  } else {
    $sections=false;
    return false ;
  }
 //  if(!$sections) {return false ; }

  ksort($sections);

  if ($default) {    // contains title label.  use 'default' not '.' key?
    $sections = array_merge( array('default' => $default) , $sections );
  }
  elseif($home) {			// deprecate
    $tmphome = $sections[$home];
    unset( $sections[$home]);
    $sections = array_merge( array($home => $tmphome) , $sections );
  }


  if($order) {		// deprecate cuz you can control order by section names
    $new_s = array();
    $new_order=explode(',',$order);
    foreach($new_order as $n => $nval) {
      if( array_key_exists(strtolower($nval), $sections) ) {
	$new_s[$nval] = $sections[$nval];
      }
    }
    $sections = $new_s;
  }

  if($order) {		// deprecate cuz you can control order by section names
    $new_s = array();
    $new_order=explode(',',$order);
    foreach($new_order as $n => $nval) { // $n= 0,1,2
      $new_s[$nval] = $sections[$nval];
    }
    $sections = $new_s;
  }

  foreach($sections as $sect => $stitle) {

    // !! $s is current articles's section
    // highlite active section link
    $tagatts[] = ($s == strtolower($sect)) ? " class='$active'" : "";

    if ($home ) {		// back compat ?  and ! $default
      $tagatts[] = ($s == 'default' and $sect == $home) ? " class='$active'" : '';
    }

    $tagatts[] = ' id="menu_'.strtolower($sect).'"';

    if(! in_array($sect,$noshow)) {
      //multi-word section titles should use whitespace:nowrap
      $content = $stitle ; // contains r350+ title now
      if (! $have_section_titles) { // older RC
	$content = preg_replace("/[_-]/"," ",$sect);
	$content = ucwords($content);
      }

      // ingrid's fix:
      if($GLOBALS['permlink_mode'] == 'messy') {
	$linkref =  ' href="'.hu.'?s='.$sect.'"';
      } else {
	$linkref =  ' href="'.hu.$sect.'"';
      }

      // for order="default,xxx,yy": remove /default
      $linkref = preg_replace("/\/default/","/",$linkref);

      $link = tag($content,'a', $linkref);
      $out[] = ($break != 'br')
	?   "\n".tag($link, $break, implode('',$tagatts))
        : "\n".$link.'<br />';
    }

    unset($tagatts);
  }

  $id = (!empty($id)) ? ' id="'.$id.'"' : '';
  $class = (!empty($class)) ? " class='$class $s'" : " class='$s'";
  return tag(implode('', $out),$wraptag,$id.$class);

}


# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>Dynamic Section and Article menu tags</h1>

	<p>The <strong>rdt_dynamenus</strong> plugin automagically generates section-aware menus of sections and articles and thus ensures that those menus are updated whenver new sections or articles are added to the website.</p>

	<p>Two tags, <strong>rdt_article_menu</strong> and <strong>rdt_section_menu</strong>, together enable easy maintenance of 2 dimensional websites where <em>section</em> and <em>article</em> are the two axes.  They eliminate the need either to handcode links and categories, as is needed for menus generated by <strong>txp:linklist</strong>, or to hardwire multiple sections into different invocations of the <strong>txp:article_custom</strong> tag.  The <strong>rdt_section_menu</strong> is aware of the current section and marks it with a CSS class named &#8216;active&#8217; by default.  The <strong>rdt_article_menu</strong> shows only articles assigned to the current section and marks the currently displayed article with the CSS &#8216;active&#8217; class.</p>

	<h1>Tag: rdt_article_menu</h1>

	<p>The rdt_article_menu tag retrieves a list of article permlinks assigned to the current section.  It generates an HTML list and marks the current article with class=&#8217;active&#8217;.</p>

	<p>It&#8217;s less flexible than the built-in article_custom tag but much simpler.</p>

	<h3>Usage</h3>

<code><pre>
  &lt;txp:rdt_article_menu
   wraptag="ul"
   break="li"
   class="menu"
   active="active"
   id="articles"
   limit="100"
   sortby="Posted"
   sortdir="desc"
   dateposted="to_date"
   section=""
   status="live"
  /&gt;
</pre></code>

	<h3>Tag attributes</h3>

	<p><strong>wraptag</strong>  An HTML list environment, default: UL</p>

	<p><strong>break</strong> I strongly advise against changing from LI, the default</p>

	<p><strong>class</strong> CSS class for the list environment, default: menu</p>

	<p><strong>active</strong> Controls the CSS class for active menu item; default is &#8216;active&#8217;; active parameter can specify &#8216;current&#8217; or whatever.</p>

	<p><strong>id</strong> CSS ID for the list environment, default: articles</p>

	<p><strong>limit</strong> number of articles to show, default: 100</p>

	<p><strong>sortby</strong> all the fields accepted by article_custom, default: Posted</p>

	<p><strong>sortdir</strong> ASC or DESC, ascending or descending.  Default: desc.  Make sure to keep the 2 sort attributes synchronized with any corresponding <txp:article  limit="1" sortby="Posted"    sortdir="desc"/> .</p>

	<p><strong>dateposted</strong> future, to_date, or all.  default: all</p>

	<p><strong>section</strong> sections to select from; normally leave this empty</p>

	<p><strong>status</strong> draft, hidden, pending, of live. Default:live</p>

	<h3>Sample CSS implementation</h3>

<pre><code>
  &lt;txp:rdt_article_menu wraptag="ul" id="articles" /&gt;
</code></pre>

	<p>The following CSS formats the article titles into a vertical list<br />
without bullets but an outdented first line instead.</p>

<pre><code>
  #articles {
    list-style: none;
    margin: 0em;
    padding: 0;
    line-height:120%;
  }
  #articles li {
    text-indent: -1.25em;
    padding-left: 1.25em;
  }
  #articles li.active {
    border: 1px solid;
  }
</code></pre>

	<h1>Tag: rdt_section_menu</h1>

	<p>The rdt_section_menu tag formats the site&#8217;s section names inside an HTML list and marks the current section with class=&#8217;active&#8217;.  In the latest 1.0rc3 release candidates, section title fields let you specify multi-word display text.  In older TXP versions, you can create multi-word section titles by separating the words with a hyphen.  rdt_section_menu will show the  &#8217;-&#8217; as a space.</p>

	<h3>Usage</h3>

<pre><code>
  &lt;txp:rdt_section_menu
      wraptag="ul"
      break="li"
      class="menu"
      active="active"
      id="sections"
      default=""
      noshow=""
      home=""
      order=""
   /&gt;
</code></pre>

	<h3>Tag attributes</h3>

	<p><strong>wraptag</strong>  An HTML list environment, default: UL</p>

	<p><strong>break</strong> I strongly advise against changing from LI, the default</p>

	<p><strong>class</strong> CSS class for the list environment, default: menu</p>

	<p><strong>active</strong> Controls the CSS class name for active menu item; default is &#8216;active&#8217;; active parameter can specify &#8216;current&#8217; or whatever.</p>

	<p><strong>id</strong> CSS ID for the list environment, default: sections</p>

	<p><strong>noshow</strong> optional.  specify list of sections to exclude &#8220;sect1,sect2&#8221;</p>

	<p><strong>default</strong> does 2 things: inserts a link to Textpattern&#8217;s default section at the beginning of the section menu and defines a section title to display for the default section.  <strong>default</strong> overrides <strong>home</strong> attribute.</p>

	<p><strong>home</strong> optional and deprecated. <strong>home</strong> indicates the name of your front page section and sorts its link to the top of the menu list.  The default value, <code>home=''</code>, prevents sorting a particular section to the top of the section list.</p>

	<p><strong>order</strong> optional. specify section sequence to override alphabetical order: &#8220;sect2,sect1&#8221;.  The tradeoff is: you lose automatic coordination with presentation->sections.  If your version of Textpattern supports the section titles, it seems better to control the sort order by tweaking the section names.  You can use the &#8216;default&#8217; section name to include the default section.</p>

	<h3>Sample CSS implementation</h3>

<pre><code>
 &lt;txp:rdt_section_menu  home="home" class="menu" id="sections" wraptag="ul"/&gt;
</code></pre>

	<p>The following CSS code defines a horizontal section menu where the active section is highlighted with a skyblue box.</p>

<pre><code>

  #sections {
   clear:both;
    margin: 0;  padding: 0;
    height: 2em;
    line-height: 2em;
    margin: 0;  padding: 0;
    list-style: none;
  }

  #sections li {
    float:left;
    padding:0;
    text-align: center;
    white-space:nowrap;
  }

  #sections a {
    color: #574;
    font-size: 1.2em;
    font-weight:bold;
    width: 9em;
    display:block;
  }
  #sections a:hover {
   border: 1px solid black;
   margin:-1px;
   background: skyblue;
  }

  #sections .active a, #sections .active a:hover  {
    cursor: default;
   color: #574;
   border: 0px;  /* turn off border */
   margin: 0px;
   background: #feb;
  }

</code></pre>

	<p>&#8220;http://creativecommons.org/licenses/by-sa/1.0&#8221; </p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>