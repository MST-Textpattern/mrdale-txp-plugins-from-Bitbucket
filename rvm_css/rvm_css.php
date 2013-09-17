<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rvm_css';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '1.0';
$plugin['author'] = 'Ruud van Melick';
$plugin['author_uri'] = 'http://vanmelick.com/';
$plugin['description'] = 'Static CSS caching';

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

$plugin['flags'] = '2';

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

if (txpinterface == 'admin')
{
  register_callback('rvm_css_save', 'css', 'css_save');
  register_callback('rvm_css_save', 'css', 'css_save_posted');
  register_callback('rvm_css_save', 'css', 'del_dec');
  register_callback('rvm_css_delete', 'css', 'css_delete');
  register_callback('rvm_css_prefs', 'prefs', '', 1);
  register_callback('rvm_css_cleanup', 'plugin_lifecycle.rvm_css', 'deleted');
}


function rvm_css($atts)
{
  global $txp_error_code, $s, $path_to_site, $rvm_css_dir, $version;

  extract(lAtts(array(
    'format' => 'url',
    'media'  => 'screen',
    'n'      => '',
    'name'   => '',
    'rel'    => 'stylesheet',
    'title'  => '',
  ), $atts));

  if ($n === '' and $name === '')
  {
    if ($s)
    {
      $name = safe_field('css', 'txp_section', "name='".doSlash($s)."'");
    }
    else
    {
      $name = 'default';
    }
  }
  elseif ($name === '')
  {
    $name = $n;
  }

  if ($format === 'link' and strpos($name, ',') !== false)
  {
    $names = do_list($name);
    $css = '';

    foreach ($names as $name)
    {
      $atts['name'] = $name;
      $css .= rvm_css($atts);
    }

    return $css;
  }

  $file = $rvm_css_dir.'/'.strtolower(sanitizeForUrl($name)).'.css';

  if (empty($rvm_css_dir) or !is_readable($path_to_site.'/'.$file))
  {
    if (version_compare($version, '4.3.0', '>='))
    {
      unset($atts['n']);
      $atts['name'] = $name;
    }
    else
    {
      unset($atts['name']);
      $atts['n'] = $name;
    }

    return css($atts);
  }

  if ($format === 'link')
  {
    return '<link rel="'.$rel.'" type="text/css"'.
      ($media ? ' media="'.$media.'"' : '').
      ($title ? ' title="'.$title.'"' : '').
      ' href="'.hu.$file.'" />';
  }

  return hu.$file;
}


function rvm_css_save()
{
  global $path_to_site, $rvm_css_dir;

  $name = (ps('copy') or ps('savenew')) ? ps('newname') : ps('name');
  $filename = strtolower(sanitizeForUrl($name));

  if (empty($rvm_css_dir) or !$filename)
  {
    return;
  }

  $css = safe_field('css', 'txp_css', "name='".doSlash($name)."'");

  if ($css)
  {
    if (preg_match('!^[a-zA-Z0-9/+]*={0,2}$!', $css))
    {
      $css = base64_decode($css);
    }

    $file = $path_to_site.'/'.$rvm_css_dir.'/'.$filename;

    if (class_exists('lessc'))
    {
      $handle = fopen($file.'.less', 'wb');
      fwrite($handle, $css);
      fclose($handle);
      chmod($file.'.less', 0644);

      $less = new lessc();
      //$less->setFormatter('compressed');
      $less->setImportDir($path_to_site.'/'.$rvm_css_dir.'/');

      try
      {
        $css  = $less->parse($css);
      }
      catch (Exception $ex)
      {
        echo "lessphp fatal error: ".$ex->getMessage();
        return;
      }
    }

    $handle = fopen($file.'.css', 'wb');
    fwrite($handle, $css);
    fclose($handle);
    chmod($file.'.css', 0644);
  }
}


function rvm_css_delete()
{
  global $path_to_site, $rvm_css_dir;

  if (safe_field('css', 'txp_css', "name='".doSlash(ps('name'))."'"))
  {
    return;
  }

  $name = strtolower(sanitizeForUrl(ps('name')));
  $file = $path_to_site.'/'.$rvm_css_dir.'/'.$name;

  if (!empty($rvm_css_dir) and $name)
  {
    unlink($file.'.css');

    if (class_exists('lessc'))
    {
      unlink($file.'.less');
    }
  }
}


function rvm_css_prefs()
{
  global $textarray;

  $textarray['rvm_css_dir'] = 'Style directory';

  if (!safe_field ('name', 'txp_prefs', "name='rvm_css_dir'"))
  {
    safe_insert('txp_prefs', "prefs_id=1, name='rvm_css_dir', val='css', type=1, event='admin', html='text_input', position=20");
  }
}


function rvm_css_cleanup()
{
    safe_delete('txp_prefs', "name='rvm_css_dir'");
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<p>This plugin automatically stores a copy of your style sheet(s) as a static file. Static files are served several orders of magnitude faster than <span class="caps">PHP</span> files (200 times faster, in my own tests on a FastCGI-based server).</p>

	<p>Post-installation steps:
	<ol>
		<li>Activate the plugin.</li>
		<li>Create a directory for the static style sheet files in the root of your textpattern installation. You should make sure that <span class="caps">PHP</span> is able to write to that directory.</li>
		<li>Visit the <a href="index.php?event=prefs&amp;step=advanced_prefs">advanced preferences</a> and make sure the &#8220;Style directory&#8221; preference contains the directory you created in step 2. This path is always a relative path (to the directory of your root textpattern installation).</li>
		<li>Visit the <a href="index.php?event=css">style</a> tab and save each of your style sheets again (saving creates the static file).</li>
		<li>Replace all occurrences of <code>&lt;txp:css /&gt;</code> with <code>&lt;txp:rvm_css /&gt;</code>.</li>
	</ol></p>

	<p>The <code>&lt;txp:rvm_css /&gt;</code> tag supplied by this plugin has the exact same attributes as the built-in <code>&lt;txp:css /&gt;</code> tag and can be used as a drop-in replacement.</p>

	<p>Note: because not all characters are allowed in filenames, avoid using non-alphanumeric characters in style sheet names.</p>

	<p>If you wish to use <a href="http://lesscss.org/"><span class="caps">LESS</span></a> syntax in your stylesheets, you need to do the following (this could be implemented as a plugin):
	<ol>
		<li>Download the file <a href="https://raw.github.com/leafo/lessphp/master/lessc.inc.php">lessc.inc.php</a> (version 0.3.5 or higher) from the <a href="http://leafo.net/lessphp/">lessphp</a> website and upload it to your website.</li>
		<li>Edit your config.php file and add this just below the &#8216;$txpcfg&#8217; configuration lines: <code>require &#39;/path/to/lessc.inc.php&#39;;</code></li>
	</ol></p>

	<p>Things you should know when using <span class="caps">LESS</span> syntax:
	<ol>
		<li>Remember to save each style sheet anew each time you update the lesssc.inc.php file.</li>
		<li>If your <span class="caps">LESS</span> code cannot be parsed, an error will be shown at the bottom of css admin tab and the static css file will not be updated.</li>
		<li>When specifying only the filename in an &#8220;@import &#8216;style.less&#8217;&#8221; statement, the plugin assumes it&#8217;s in the &#8220;Style directory&#8221;. Do not leave out the &#8216;.less&#8217; extension!</li>
	</ol></p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>