<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'jmd_csv';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Jon-Michael Deldin';
$plugin['author_uri'] = 'http://jmdeldin.com';
$plugin['description'] = 'Batch-import articles from a CSV';

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

if (txpinterface === 'admin')
{
    global $textarray;
    add_privs('jmd_csv', 1);
    register_callback('jmd_csv', 'jmd_csv');
    register_tab('extensions', 'jmd_csv', 'csv Import');
    // i18n
    $textarray = array_merge($textarray, array(
        'jmd_csv_file' => 'CSV file:',
        'jmd_csv_file_error' => 'Error reading CSV',
        'jmd_csv_import' => 'Import',
        'jmd_csv_import_csv' => 'Import CSV',
        'jmd_csv_imported' => 'CSV imported successfully.',
    ));
}

/**
 * Interface for the CSV import.
 *
 * @param string $event
 * @param string $step
 */
function jmd_csv($event, $step)
{
    global $jmd_csv, $file_base_path;
    ob_start('jmd_csv_head');
    $jmd_csv = new JMD_CSV();
    if ($step === 'import')
    {
        $file = gps('file');
        if ($file)
        {
            $handle = fopen($file_base_path . DS . $file, 'r');
            if ($handle)
            {
                $jmd_csv->import($handle, gps('status'));
                $msg = gTxt('jmd_csv_imported');
            }
            else
            {
                $msg = gTxt('jmd_csv_file_error');
            }
        }
    }
    pageTop('jmd_csv', (isset($msg) ? $msg : ''));

    $gTxt = 'gTxt';
    $out = <<<EOD
<fieldset id="jmd_csv">
    <legend>{$gTxt('jmd_csv_import_csv')}</legend>
    <div>
        <label>{$gTxt('jmd_csv_file')}
            {$jmd_csv->fileList()}
        </label>
    </div>
    <div>
        <label>{$gTxt('import_status')}
            {$jmd_csv->statusList()}
        </label>
    </div>
    <button type="submit">{$gTxt('jmd_csv_import')}</button>
</fieldset>
EOD;
    echo form($out . eInput('jmd_csv') . sInput('import'));
}

/**
 * Inserts CSS into the head.
 *
 * @param string $buffer
 */
function jmd_csv_head($buffer)
{
    $find = '</head>';
    $insert = <<<EOD
<style type="text/css">
#jmd_csv
{
    margin: 0 auto;
    padding: 0.5em;
    width: 50%;
}
    #jmd_csv legend
    {
        font-weight: 900;
    }
    #jmd_csv div
    {
        margin: 0 0 1em;
    }
</style>
EOD;

    return str_replace($find, $insert . $find, $buffer);
}


class JMD_CSV
{
    /**
     * Returns a select box of available CSVs.
     */
    public function fileList()
    {
        $files = safe_column('filename', 'txp_file',
            'category="jmd_csv"');
        if ($files)
        {
            $out = '<select name="file">';
            foreach ($files as $file)
            {
                $out .= '<option value="' . $file . '">' . $file . '</option>';
            }
            $out .= '</select>';

            return $out;
        }
    }

    /**
     * Returns a select box of article-statuses.
     */
    public function statusList()
    {
        $statuses = array(
            'draft' => 1,
            'hidden' => 2,
            'pending' => 3,
            'live' => 4,
            'sticky' => 5,
        );
        $out = '<select name="status">';
        foreach ($statuses as $key => $value)
        {
            $out .= '<option value="' . $value .'">' . gTxt($key) . '</option>';
        }
        $out .= '</select>';

        return $out;
    }

    /**
     * Reads a CSV and inserts it into the textpattern table.
     *
     * @param resource $handle File opened with fopen()
     * @param int $status Article status.
     */
    public function import($handle, $status)
    {
        global $prefs, $txp_user;
        $row = 1;
        while (($csv = fgetcsv($handle, 0, ',')) !== FALSE)
        {
            $fields = count($csv);
            if ($row === 1)
            {
                for ($i = 0; $i < $fields; $i++)
                {
                    $header[$i] = $csv[$i];
                }
            }
            else
            {
                $insert = '';
                foreach ($header as $key => $value)
                {
                    // escape all fields
                    $csv[$key] = doSlash($csv[$key]);
                    if ($value === 'Title')
                    {
                        $url_title = stripSpace($csv[$key], 1);
                    }
                    if ($value === 'Body' || $value === 'Excerpt')
                    {
                        $insert .= "{$value}_html='{$csv[$key]}',";
                    }
                    $insert .= "{$value}='{$csv[$key]}',";
                }
                $uid = md5(uniqid(rand(),true));
                $insert .= <<<EOD
AuthorID='{$txp_user}',
LastModID='{$txp_user}',
AnnotateInvite='{$prefs['comments_default_invite']}',
url_title='{$url_title}',
uid='{$uid}',
feed_time=now(),
Posted=now(),
LastMod=now(),
Status={$status},
textile_body=0,
textile_excerpt=0
EOD;
                safe_insert('textpattern', $insert);
            }
            $row++;
        }
    }
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>jmd_csv</h1>

	<p><a href="http://forum.textpattern.com/viewtopic.php?id=27782" rel="nofollow">Forum thread</a>, <a href="http://www.bitbucket.org/jmdeldin/jmd_csv/" rel="nofollow">hg repo</a></p>

	<p><strong>Requires:</strong> <span class="caps">PHP</span> 5, <span class="caps">TXP</span> 4.0.6</p>

	<p>jmd_csv imports rows from <span class="caps">CSV</span> files as Textpattern articles.</p>

	<h2>Instructions</h2>

	<ol>
		<li>Create a file category, &#8220;jmd_csv&#8221;</li>
		<li>Content&gt;Files: Upload a file to the &#8220;jmd_csv&#8221; category</li>
		<li>Extensions&gt;jmd_csv: Click import</li>
	</ol>

	<h2><span class="caps">CSV</span> template</h2>

	<p>The plugin requires you specify a header row. An example header row:</p>

<pre><code>Title, Body, Excerpt, Section, Category1, Category2, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10
</code></pre>

	<p>Note: You may remove Category* and custom_*.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>