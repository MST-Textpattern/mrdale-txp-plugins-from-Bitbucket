<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'glx_if';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.7';
$plugin['author'] = 'Johan Nilsson';
$plugin['author_uri'] = 'http://johan.galaxen.net/';
$plugin['description'] = 'Some conditional tags';

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
function glx_if_frontpage($atts, $thing)
{
    global $pretext;
    return parse(EvalElse($thing, $pretext["s"] == "default" &&
                                  empty($pretext["c"]) &&
                                  empty($pretext["q"]) &&
                                  empty($pretext["pg"])));
}

function glx_if_not_frontpage($atts, $thing)
{
    global $pretext;
    return parse(EvalElse($thing, $pretext["s"] != "default" &&
                                  empty($pretext["c"]) &&
                                  empty($pretext["q"]) &&
                                  empty($pretext["pg"])));
}

function glx_if_section_frontpage($atts, $thing)
{
  global $pretext, $is_article_list;
  $condition = (empty($pretext["c"]) && $is_article_list == true) ? true : false;
  return parse(EvalElse($thing, $condition));
}

function hak_wine_val($atts, $thing='')
{
	global $zem_contact_values, $thiswineitem;

	extract(lAtts(array(
			'name'	=>	'',
			'wraptag'	=>	'',
	),$atts));

	if (empty($name) && is_array($thiswineitem))
		return $thiswineitem['quantity'];

	if (!isset($zem_contact_values[$name]))
		return '';

	return $zem_contact_values[$name];
}

function glx_if_not_section_frontpage($atts, $thing)
{
    global $pretext, $is_article_list;
    return parse(EvalElse($thing, !empty($pretext["s"]) &&
                                  $is_article_list == false));
}

function glx_if_search($atts, $thing)
{
    global $pretext;
    return (!empty($pretext["q"])) ? parse($thing) : "";
}

// This function is written by jase
function glx_if_not_search($atts, $thing)
{
    global $pretext;
    return (empty($pretext['q'])) ? parse($thing) : "";
}

function glx_if_category_list($atts, $thing)
{
    global $pretext, $is_article_list;
    return (!empty($pretext["c"]) && $is_article_list == true) ? parse($thing) : "";
}

function glx_if_comments_open($atts, $thing)
{
    global $thisarticle;
    $id = $thisarticle["thisid"];
    $rs = safe_row("*", "textpattern", "ID='$id' AND Annotate=1");
    $output = "";
    if ($rs)
    {
        $output = parse($thing);
    }
    return $output;
}

/*
ignorecomments: If this is set to false the plugin will ingnore any
comments, if set to true the text will only show if there is no
comments already. It is false as default
*/
function glx_if_comments_closed($atts, $thing)
{
    if (is_array($atts)) extract($atts);
    global $thisarticle;
    $ignoreComments = (empty($ignorecomments)) ? false : true;
    $id = $thisarticle["thisid"];
    $output = "";
    $rs = safe_row("*", "textpattern", "ID= $id AND Annotate=0");
    if ($rs)
    {
        if ($ignoreComments)
        {
            $rs2 = safe_row("COUNT( discussid ) AS num_of_comments", "txp_discuss", "parentid = $id");
            if ($rs2)
            {
                if ($rs2[0] == 0)
                {
                    $output = parse($thing);
                }
            }
        }
        else
        {
            $output = parse($thing);
        }
    }
    return $output;
}

/*
If this tag is not enclosed with other tags or text it will output
how many comments that has been recorded before the article was closed.
well, that didnt work very well so that lines are just commented out.
instead just use <txp:comments_count />
*/
function glx_if_comments_closed_comments($atts, $thing ="")
{
    if (is_array($atts)) extract($atts);
    global $thisarticle;
    $id = $thisarticle["thisid"];
    $numOfComments = 0;
    $output = "";
    $rs = getRow("SELECT COUNT( ".PFX."txp_discuss.discussid ) AS num_of_comments
                  FROM ".PFX."txp_discuss
                  LEFT JOIN ".PFX."textpattern ON ".PFX."txp_discuss.parentid = ".PFX."textpattern.ID
                  WHERE ".PFX."textpattern.ID = $id AND ".PFX."textpattern.Annotate = 0");
    if ($rs)
    {
        foreach ($rs as $row)
        {
            if ($row[0] != 0)
            {
                //$numOfComments = $row[0];
                $output = parse($thing);
            }
        }
    }
    //return ($thing) ? $output : ($numOfComments != 0) ? "$numOfComments" : "";
    return $output;
}

/*
This function was requested on the TXP Forum by lee.
It takes two attributes
value: the value to compare with
operator: how to comapare
*/
function glx_if_comments_count($atts, $thing)
{
    if (is_array($atts)) extract($atts);
    global $thisarticle;

    $value = (empty($value)) ? 0 : $value;
    $operator = (empty($operator)) ? "" : $operator;
    $output = "";

    switch ($operator)
    {
        case "equal_to":
            if ($value == $thisarticle['comments_count'])
                $output = parse($thing);
        break;
        case "not_equal_to":
            if($value != $thisarticle['comments_count'])
                $output = parse($thing);
        break;
        case "less_than":
            if ($value < $thisarticle['comments_count'])
            $output = parse($thing);
        break;
        case "greater_than":
            if ($value < $thisarticle['comments_count'])
            $output = parse($thing);
        break;
        case "less_than_or_equal_to":
            if ($value <= $thisarticle['comments_count'])
            $output = parse($thing);
        break;
        case "greater_than_or_equal_to":
            if ($value >= $thisarticle['comments_count'])
            $output = parse($thing);
        break;
    }

    return $output;
}

function glx_if_image_display($atts, $thing)
{
    global $p;
    return parse(EvalElse($thing, !empty($p)));
}
function glx_if_not_image_display($atts, $thing)
{
    global $p;
    return parse(EvalElse($thing, empty($p)));
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>glx_if</h1>

	<p>This plugin comes with 11 different conditional tags. Most of them could be used in either a page template or in a form.</p>

	<p><a href="#glx_if_frontpage">glx_if_frontpage</a><br />
If we are on the websites frontpage<br />
<a href="#glx_if_not_frontpage">glx_if_not_frontpage</a><br />
If we are not on the websites frontpage, searchpage or category list. hmmm this could be the same as if_individual_article, well i cant remember why i did this one. But it is there for you to play with.<br />
<a href="#glx_if_section_frontpage">glx_if_section_frontpage</a><br />
If we are on a sections frontpage<br />
<a href="#glx_if_not_section_frontpage">glx_if_not_section_frontpage</a><br />
If we are not on the sections frontpage but in a section<br />
<a href="#glx_if_search">glx_if_search</a><br />
If the user are doing a search<br />
<a href="#glx_if_not_search">glx_if_not_search</a><br />
If a search not have been attempted<br />
<a href="#glx_if_category_list">glx_if_category_list</a><br />
If we are on a category list<br />
<a href="#glx_if_comments_open">glx_if_comments_open</a><br />
If comments are open for this article<br />
<a href="#glx_if_comments_closed">glx_if_comments_closed</a><br />
If comments are closed for this article<br />
<a href="#glx_if_comments_closed_comments">glx_if_comments_closed_comments</a><br />
If comments are closed for this article, but there are comments left before<br />
<a href="#glx_if_comments_count">glx_if_comments_count</a><br />
Oputput text if comments are equal to something</p>

	<h3>Example</h3>

	<p>Below are some example of how the above tags could be used.</p>

	<h4 id="glx_if_frontpage">glx_if_frontpage</h4>

	<p>Output text only on the frontpage, this is used on the default page template</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_frontpage&gt;</code></li>
		<li><code>&lt;p&gt;Welcome to this sites frontpage&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_frontpage&gt;</code></li>
	</ol>

	<h4 id="glx_if_not_frontpage">glx_if_not_frontpage</h4>

	<p>If we are not on the websites frontpage, searchpage or category list.</p>

	<h4 id="glx_if_section_frontpage">glx_if_section_frontpage</h4>

	<p>Output text if we are on a sections frontpage</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_section_frontpage&gt;</code></li>
		<li><code>&lt;p&gt;Welcome to this section&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_section_frontpage&gt;</code></li>
	</ol>

	<h4 id="glx_if_not_section_frontpage">glx_if_not_section_frontpage</h4>

	<p>Output text if we are <em>not</em> on a sections frontpage but in a section.</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_not_section_frontpage&gt;</code></li>
		<li><code>&lt;p&gt;We are in a section but not on its frontpage&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_not_section_frontpage&gt;</code></li>
	</ol>

	<p>Note! To have this to work on my own page in combination with glx_if_section_frontpage, I had to put it above the glx_if_section_frontpage tag.</p>

	<h4 id="glx_if_search">glx_if_search</h4>

	<p>Say you have your search input on your archive page and want to have the search input above the search result, then you could use it like this on your default page template</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_search&gt;</code></li>
		<li><code>&lt;txp:search_input button="Search" size="15" wraptag="p" /&gt;</code></li>
		<li><code>&lt;/txp:glx_if_search&gt;</code></li>
	</ol>

	<h4 id="glx_if_not_search">glx_if_not_search</h4>

	<p>If a search <em>not</em> have been attempted</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_not_search&gt;</code></li>
		<li><code>&lt;p&gt;If no search has been done&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_not_search&gt;</code></li>
	</ol>

	<p>Thanks to <a href="http://www.star29.net/">jase</a> for the glx_if_not_search function.</p>

	<h4 id="glx_if_category_list">glx_if_category_list</h4>

	<p>Output text if we are on a category list page</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_category_list&gt;</code></li>
		<li><code>&lt;p&gt;Articles in this category&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_category_list&gt;</code></li>
	</ol>

	<h4 id="glx_if_comments_open">glx_if_comments_open</h4>

	<p>Oputput text if comments are open.</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_open&gt;</code></li>
		<li><code>&lt;txp:comments_invite /&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_open&gt;</code></li>
	</ol>

	<h4 id="glx_if_comments_closed">glx_if_comments_closed</h4>

	<p>Oputput text if comments are closed</p>

	<p><strong>Attribute</strong><br />
<code>ingorecomments</code><br />
Set this to false to ignore if comments have been left before. This is very handy if you want this tag to work with if_comments_closed_comments<br />
default value: true</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_closed&gt;</code></li>
		<li><code>&lt;p&gt;Comments are closed for this article&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_closed&gt;</code></li>
	</ol>

	<h4 id="glx_if_comments_closed_comments">glx_if_comments_closed_comments</h4>

	<p>Oputput text if comments are closed but there is comments left before.</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_closed_comments&gt;</code></li>
		<li><code>&lt;li&gt;Comments closed but there are &lt;txp:comments_count /&gt; old comments&lt;/li&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_closed_comments&gt;</code></li>
	</ol>

	<h4 id="glx_if_comments_count">glx_if_comments_count</h4>

	<p>Oputput text if comments are equal to something</p>

	<p><strong>Attribute</strong><br />
<code>value</code><br />
What value to compare to, must be a int<br />
default value: 0<br />
<code>operator</code><br />
What to compare with, the following values are available</p>

	<ol class="code">
		<li><code>equal_to</code></li>
		<li><code>not_equal_to</code></li>
		<li><code>less_than</code></li>
		<li><code>greater_than</code></li>
		<li><code>less_than_or_equal_to</code></li>
		<li><code>greater_than_or_equal_to</code></li>
	</ol>

	<p>And some example of how to use glx_if_comments_count</p>

	<p>Equal to 0 comments</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_count operator="equal_to" value="0"&gt;</code></li>
		<li><code>&lt;p&gt;No comments yet, you could be the first.&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_count&gt;</code></li>
	</ol>

	<p>Equal to 1 comment</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_count operator="equal_to" value="1"&gt;</code></li>
		<li><code>&lt;p&gt;There is only one comment&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_count&gt;</code></li>
	</ol>

	<p>More than 2 comments</p>

	<ol class="code">
		<li><code>&lt;txp:glx_if_comments_count operator="greater_than" value="2"&gt;</code></li>
		<li><code>&lt;p&gt;There are more than two comments&lt;/p&gt;</code></li>
		<li><code>&lt;/txp:glx_if_comments_count&gt;</code></li>
	</ol>
# --- END PLUGIN HELP ---
-->
<?php
}
?>