<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mrd_h2t';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1.2';
$plugin['author'] = 'Dale Chapman';
$plugin['author_uri'] = 'http://chapmancordova.com';
$plugin['description'] = 'Convert html to text preserving linebreaks and links';

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
function mrd_stripslash($atts, $thing='') {
	 $con=stripslashes(parse($thing));
   return $con;
}

function mrd_strip($atts, $thing='') {
	 $con=preg_replace("/\s+/", " ", parse($thing));
   return $con;
}

function mrd_quoter($atts, $thing='') {
   $con = str_replace(chr(34), chr(39), parse($thing));
   return $con;
}

function mrd_dequoter($atts, $thing='') {
   $con = str_replace(chr(34), "&#34;", parse($thing));
   $con = str_replace(chr(39), "&#39;", parse($thing));
   return $con;
}


function mrd_h2t($atts, $thing='') {
extract(lAtts(array(
      'columns'  => '70',
      'baseurl'  => 'http://'.$GLOBALS['prefs']['siteurl'],
   ),$atts));

  $thing = parse($thing);
  $h2t = new html2text($thing);
  $h2t->set_base_url('http://'.$baseurl);
  $h2t->width = $columns;
  return $h2t->get_text();
}

function mrd_entity_d($atts, $thing='') {
   return html_entity_decode(parse($thing));
}

function mrd_entity_e($atts, $thing='') {
   return htmlentities(parse($thing));
}


/*************************************************************************
 *                                                                       *
 * class.html2text.inc                                                   *
 *                                                                       *
 *************************************************************************
 *                                                                       *
 * Converts HTML to formatted plain text                                 *
 *                                                                       *
 * Copyright (c) 2005-2007 Jon Abernathy <jon@chuggnutt.com>             *
 * All rights reserved.                                                  *
 *                                                                       *
 * This script is free software; you can redistribute it and/or modify   *
 * it under the terms of the GNU General Public License as published by  *
 * the Free Software Foundation; either version 2 of the License, or     *
 * (at your option) any later version.                                   *
 *                                                                       *
 * The GNU General Public License can be found at                        *
 * http://www.gnu.org/copyleft/gpl.html.                                 *
 *                                                                       *
 * This script is distributed in the hope that it will be useful,        *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          *
 * GNU General Public License for more details.                          *
 *                                                                       *
 * Author(s): Jon Abernathy <jon@chuggnutt.com>                          *
 *                                                                       *
 * Last modified: 08/08/07                                               *
 *                                                                       *
 *************************************************************************/


/**
 *  Takes HTML and converts it to formatted, plain text.
 *
 *  Thanks to Alexander Krug (http://www.krugar.de/) to pointing out and
 *  correcting an error in the regexp search array. Fixed 7/30/03.
 *
 *  Updated set_html() function's file reading mechanism, 9/25/03.
 *
 *  Thanks to Joss Sanglier (http://www.dancingbear.co.uk/) for adding
 *  several more HTML entity codes to the $search and $replace arrays.
 *  Updated 11/7/03.
 *
 *  Thanks to Darius Kasperavicius (http://www.dar.dar.lt/) for
 *  suggesting the addition of $allowed_tags and its supporting function
 *  (which I slightly modified). Updated 3/12/04.
 *
 *  Thanks to Justin Dearing for pointing out that a replacement for the
 *  <TH> tag was missing, and suggesting an appropriate fix.
 *  Updated 8/25/04.
 *
 *  Thanks to Mathieu Collas (http://www.myefarm.com/) for finding a
 *  display/formatting bug in the _build_link_list() function: email
 *  readers would show the left bracket and number ("[1") as part of the
 *  rendered email address.
 *  Updated 12/16/04.
 *
 *  Thanks to Wojciech Bajon (http://histeria.pl/) for submitting code
 *  to handle relative links, which I hadn't considered. I modified his
 *  code a bit to handle normal HTTP links and MAILTO links. Also for
 *  suggesting three additional HTML entity codes to search for.
 *  Updated 03/02/05.
 *
 *  Thanks to Jacob Chandler for pointing out another link condition
 *  for the _build_link_list() function: "https".
 *  Updated 04/06/05.
 *
 *  Thanks to Marc Bertrand (http://www.dresdensky.com/) for
 *  suggesting a revision to the word wrapping functionality; if you
 *  specify a $width of 0 or less, word wrapping will be ignored.
 *  Updated 11/02/06.
 *
 *  *** Big housecleaning updates below:
 *
 *  Thanks to Colin Brown (http://www.sparkdriver.co.uk/) for
 *  suggesting the fix to handle </li> and blank lines (whitespace).
 *  Christian Basedau (http://www.movetheweb.de/) also suggested the
 *  blank lines fix.
 *
 *  Special thanks to Marcus Bointon (http://www.synchromedia.co.uk/),
 *  Christian Basedau, Norbert Laposa (http://ln5.co.uk/),
 *  Bas van de Weijer, and Marijn van Butselaar
 *  for pointing out my glaring error in the <th> handling. Marcus also
 *  supplied a host of fixes.
 *
 *  Thanks to Jeffrey Silverman (http://www.newtnotes.com/) for pointing
 *  out that extra spaces should be compressed--a problem addressed with
 *  Marcus Bointon's fixes but that I had not yet incorporated.
 *
 *    Thanks to Daniel Schledermann (http://www.typoconsult.dk/) for
 *  suggesting a valuable fix with <a> tag handling.
 *
 *  Thanks to Wojciech Bajon (again!) for suggesting fixes and additions,
 *  including the <a> tag handling that Daniel Schledermann pointed
 *  out but that I had not yet incorporated. I haven't (yet)
 *  incorporated all of Wojciech's changes, though I may at some
 *  future time.
 *
 *  *** End of the housecleaning updates. Updated 08/08/07.
 *
 *  @author Jon Abernathy <jon@chuggnutt.com>
 *  @version 1.0.0
 *  @since PHP 4.0.2
 */
class html2text
{

    /**
     *  Contains the HTML content to convert.
     *
     *  @var string $html
     *  @access public
     */
    var $html;

    /**
     *  Contains the converted, formatted text.
     *
     *  @var string $text
     *  @access public
     */
    var $text;

    /**
     *  Maximum width of the formatted text, in columns.
     *
     *  Set this value to 0 (or less) to ignore word wrapping
     *  and not constrain text to a fixed-width column.
     *
     *  @var integer $width
     *  @access public
     */
    var $width = 70;

    /**
     *  List of preg* regular expression patterns to search for,
     *  used in conjunction with $replace.
     *
     *  @var array $search
     *  @access public
     *  @see $replace
     */
    var $search = array(
        "/\r/",                                  // Non-legal carriage return
        "/[\n\t]+/",                             // Newlines and tabs
        '/[ ]{2,}/',                             // Runs of spaces, pre-handling
        '/<script[^>]*>.*?<\/script>/i',         // <script>s -- which strip_tags supposedly has problems with
        '/<style[^>]*>.*?<\/style>/i',           // <style>s -- which strip_tags supposedly has problems with
        //'/<!-- .* -->/',                         // Comments -- which strip_tags might have problem a with
        '/<h[123][^>]*>(.*?)<\/h[123]>/ie',      // H1 - H3
        '/<h[456][^>]*>(.*?)<\/h[456]>/ie',      // H4 - H6
        '/<p[^>]*>/i',                           // <P>
        '/<br[^>]*>/i',                          // <br>
        '/<b[^>]*>(.*?)<\/b>/ie',                // <b>
        '/<strong[^>]*>(.*?)<\/strong>/ie',      // <strong>
        '/<i[^>]*>(.*?)<\/i>/i',                 // <i>
        '/<em[^>]*>(.*?)<\/em>/i',               // <em>
        '/(<ul[^>]*>|<\/ul>)/i',                 // <ul> and </ul>
        '/(<ol[^>]*>|<\/ol>)/i',                 // <ol> and </ol>
        '/<li[^>]*>(.*?)<\/li>/i',               // <li> and </li>
        '/<li[^>]*>/i',                          // <li>
        '/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie',
                                                 // <a href="">
        '/<hr[^>]*>/i',                          // <hr>
        '/(<table[^>]*>|<\/table>)/i',           // <table> and </table>
        '/(<tr[^>]*>|<\/tr>)/i',                 // <tr> and </tr>
        '/<td[^>]*>(.*?)<\/td>/i',               // <td> and </td>
        '/<th[^>]*>(.*?)<\/th>/ie',              // <th> and </th>
        '/&(nbsp|#160);/i',                      // Non-breaking space
        '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
                                                 // Double quotes
        '/&(apos|rsquo|lsquo|#8216|#8217);/i',   // Single quotes
        '/&gt;/i',                               // Greater-than
        '/&lt;/i',                               // Less-than
        '/&(amp|#38);/i',                        // Ampersand
        '/&(copy|#169);/i',                      // Copyright
        '/&(trade|#8482|#153);/i',               // Trademark
        '/&(reg|#174);/i',                       // Registered
        '/&(mdash|#151|#8212);/i',               // mdash
        '/&(ndash|minus|#8211|#8722);/i',        // ndash
        '/&(bull|#149|#8226);/i',                // Bullet
        '/&(pound|#163);/i',                     // Pound sign
        '/&(euro|#8364);/i',                     // Euro sign
        '/&[^&;]+;/i',                           // Unknown/unhandled entities
        '/[ ]{2,}/'                              // Runs of spaces, post-handling
    );

    /**
     *  List of pattern replacements corresponding to patterns searched.
     *
     *  @var array $replace
     *  @access public
     *  @see $search
     */
    var $replace = array(
        '',                                     // Non-legal carriage return
        ' ',                                    // Newlines and tabs
        ' ',                                    // Runs of spaces, pre-handling
        '',                                     // <script>s -- which strip_tags supposedly has problems with
        '',                                     // <style>s -- which strip_tags supposedly has problems with
        //'',                                     // Comments -- which strip_tags might have problem a with
        "strtoupper(\"\n\n\\1\n\n\")",          // H1 - H3
        "ucwords(\"\n\n\\1\n\n\")",             // H4 - H6
        "\n\n",                               // <P>
        "\n",                                   // <br>
        'strtoupper("\\1")',                    // <b>
        'strtoupper("\\1")',                    // <strong>
        '_\\1_',                                // <i>
        '_\\1_',                                // <em>
        "\n\n",                                 // <ul> and </ul>
        "\n\n",                                 // <ol> and </ol>
        "\t* \\1\n",                            // <li> and </li>
        "\n\t* ",                               // <li>
        '$this->_build_link_list("\\1", "\\2")',
                                                // <a href="">
        "\n-------------------------\n",        // <hr>
        "\n\n",                                 // <table> and </table>
        "\n",                                   // <tr> and </tr>
        "\t\t\\1\n",                            // <td> and </td>
        "strtoupper(\"\t\t\\1\n\")",            // <th> and </th>
        ' ',                                    // Non-breaking space
        '"',                                    // Double quotes
        "'",                                    // Single quotes
        '>',
        '<',
        '&',
        '(c)',
        '(tm)',
        '(R)',
        '--',
        '-',
        '*',
        'Â£',
        'EUR',                                  // Euro sign. â‚¬ ?
        '',                                     // Unknown/unhandled entities
        ' '                                     // Runs of spaces, post-handling
    );

    /**
     *  Contains a list of HTML tags to allow in the resulting text.
     *
     *  @var string $allowed_tags
     *  @access public
     *  @see set_allowed_tags()
     */
    var $allowed_tags = '';

    /**
     *  Contains the base URL that relative links should resolve to.
     *
     *  @var string $url
     *  @access public
     */
    var $url;

    /**
     *  Indicates whether content in the $html variable has been converted yet.
     *
     *  @var boolean $_converted
     *  @access private
     *  @see $html, $text
     */
    var $_converted = false;

    /**
     *  Contains URL addresses from links to be rendered in plain text.
     *
     *  @var string $_link_list
     *  @access private
     *  @see _build_link_list()
     */
    var $_link_list = '';

    /**
     *  Number of valid links detected in the text, used for plain text
     *  display (rendered similar to footnotes).
     *
     *  @var integer $_link_count
     *  @access private
     *  @see _build_link_list()
     */
    var $_link_count = 0;

    /**
     *  Constructor.
     *
     *  If the HTML source string (or file) is supplied, the class
     *  will instantiate with that source propagated, all that has
     *  to be done it to call get_text().
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @access public
     *  @return void
     */
    function html2text( $source = '', $from_file = false )
    {
        if ( !empty($source) ) {
            $this->set_html($source, $from_file);
        }
        $this->set_base_url();
    }

    /**
     *  Loads source HTML into memory, either from $source string or a file.
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @access public
     *  @return void
     */
    function set_html( $source, $from_file = false )
    {
        $this->html = $source;

        if ( $from_file && file_exists($source) ) {
            $fp = fopen($source, 'r');
            $this->html = fread($fp, filesize($source));
            fclose($fp);
        }

        $this->_converted = false;
    }

    /**
     *  Returns the text, converted from HTML.
     *
     *  @access public
     *  @return string
     */
    function get_text()
    {
        if ( !$this->_converted ) {
            $this->_convert();
        }

        return $this->text;
    }

    /**
     *  Prints the text, converted from HTML.
     *
     *  @access public
     *  @return void
     */
    function print_text()
    {
        print $this->get_text();
    }

    /**
     *  Alias to print_text(), operates identically.
     *
     *  @access public
     *  @return void
     *  @see print_text()
     */
    function p()
    {
        print $this->get_text();
    }

    /**
     *  Sets the allowed HTML tags to pass through to the resulting text.
     *
     *  Tags should be in the form "<p>", with no corresponding closing tag.
     *
     *  @access public
     *  @return void
     */
    function set_allowed_tags( $allowed_tags = '' )
    {
        if ( !empty($allowed_tags) ) {
            $this->allowed_tags = $allowed_tags;
        }
    }

    /**
     *  Sets a base URL to handle relative links.
     *
     *  @access public
     *  @return void
     */
    function set_base_url( $url = '' )
    {
        if ( empty($url) ) {
            if ( !empty($_SERVER['HTTP_HOST']) ) {
                $this->url = 'http://' . $_SERVER['HTTP_HOST'];
            } else {
                $this->url = '';
            }
        } else {
            // Strip any trailing slashes for consistency (relative
            // URLs may already start with a slash like "/file.html")
            if ( substr($url, -1) == '/' ) {
                $url = substr($url, 0, -1);
            }
            $this->url = $url;
        }
    }

    /**
     *  Workhorse function that does actual conversion.
     *
     *  First performs custom tag replacement specified by $search and
     *  $replace arrays. Then strips any remaining HTML tags, reduces whitespace
     *  and newlines to a readable format, and word wraps the text to
     *  $width characters.
     *
     *  @access private
     *  @return void
     */
    function _convert()
    {
        // Variables used for building the link list
        $this->_link_count = 0;
        $this->_link_list = '';

        $text = trim(stripslashes($this->html));

        // Run our defined search-and-replace
        $text = preg_replace($this->search, $this->replace, $text);

        // Strip any other HTML tags
        $text = strip_tags($text, $this->allowed_tags);

        // Bring down number of empty lines to 2 max
        $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

        // Add link list
        if ( !empty($this->_link_list) ) {
            $text .= "\n\nLinks:\n------\n" . $this->_link_list;
        }

        // Wrap the text to a readable format
        // for PHP versions >= 4.0.2. Default width is 75
        // If width is 0 or less, don't wrap the text.
        if ( $this->width > 0 ) {
            $text = wordwrap($text, $this->width);
        }

        $this->text = $text;

        $this->_converted = true;
    }

    /**
     *  Helper function called by preg_replace() on link replacement.
     *
     *  Maintains an internal list of links to be displayed at the end of the
     *  text, with numeric indices to the original point in the text they
     *  appeared. Also makes an effort at identifying and handling absolute
     *  and relative links.
     *
     *  @param string $link URL of the link
     *  @param string $display Part of the text to associate number with
     *  @access private
     *  @return string
     */
    function _build_link_list( $link, $display )
    {
        if ( substr($link, 0, 7) == 'http://' || substr($link, 0, 8) == 'https://' ||
             substr($link, 0, 7) == 'mailto:' ) {
            $this->_link_count++;
            $this->_link_list .= "[" . $this->_link_count . "] $link\n";
            $additional = ' [' . $this->_link_count . ']';
        } elseif ( substr($link, 0, 11) == 'javascript:' ) {
            // Don't count the link; ignore it
            $additional = '';
        // what about href="#anchor" ?
        } else {
            $this->_link_count++;
            $this->_link_list .= "[" . $this->_link_count . "] " . $this->url;
            if ( substr($link, 0, 1) != '/' ) {
                $this->_link_list .= '/';
            }
            $this->_link_list .= "$link\n";
            $additional = ' [' . $this->_link_count . ']';
        }

        return $display . $additional;
    }

}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>mrd_html_convert</h1>

<h3>Convert HTML to plain text</h3>

<p>This container tag will convert the enclosed text from html to text with line breaks. It will also set the base url on any relative links to that of your textpattern site root.</p>

<p>Credits: This is basically a wrapper around an excellent script by Jon Abernathy <a href="mailto:jon@chuggnutt.com">email</a>. Jeremy "Igner" Amos basically co-wrote (read wrote) the plugin.</p>

<h3>Attributes</h3>
<ul>
<li><strong>columns:</strong> an integer to set the maximum width before text wraps, <em>default is 70</em> <br/>eg. <code>columns="30"</code></li>
<li><strong>baseurl:</strong> a string to set the base url for all relative links,  excluding the http://<em>default is Textpattern's site url</em> <br/>eg. <code>baseurl="yoursite.com"</code></li>
</ul>

<h3>Example: The following code</h3>
<pre><code>
&lt;txp:mrd_h2t baseurl="fruityanimals.com" columns="20"&gt;
&lt;h4&gt;A subhead&lt;/h4&gt;
&lt;p&gt;some nice&lt;strong&gt;sample&lt;/strong&gt; text &lt;em&gt;right here&lt;/em&gt; with a &lt;a href=&quot;/monkey/butt.html&quot;&gt;link&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;another paragraph here&lt;/p&gt;
&lt;/txp:mrd_h2t&gt;
</code></pre>

<h3>Should result in:</h3>
<pre>A subhead
some nice sample
text right here with
a link [1]

another paragraph
here

Links
-----
[1] http://fruityanimals.com/monkey/butt.html
</pre>
# --- END PLUGIN HELP ---
-->
<?php
}
?>