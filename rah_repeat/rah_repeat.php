<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rah_repeat';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.8.1';
$plugin['author'] = 'Jukka Svahn';
$plugin['author_uri'] = 'http://rahforum.biz';
$plugin['description'] = 'Iterations made easy';

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


/**
 * Rah_repeat plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2009-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_repeat
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	function rah_repeat($atts, $thing=NULL) {
		global $rah_repeat, $variable;
		
		extract(lAtts(array(
			'delimiter' => ',',
			'value' => '',
			'limit' => NULL,
			'offset' => 0,
			'wraptag' => '',
			'break' => '',
			'class' => '',
			'duplicates' => 0,
			'sort' => '',
			'exclude' => NULL,
			'trim' => 1,
			'range' => '',
			'assign' => NULL,
		), $atts));
		
		if($range && strpos($range, ',')) {
			$values = call_user_func_array('range', do_list($range));
		}
		
		else {
			$values = explode($delimiter, $value);
		}
		
		if($trim) {
			$values = doArray($values, 'trim');
		}
		
		if($duplicates) {
			$values = array_unique($values);
		}
		
		if($exclude !== NULL) {
			$exclude = explode($delimiter, $exclude);
			
			if($trim) {
				$exclude = doArray($exclude, 'trim');
			}
			
			$values = array_diff($values, $exclude);
		}
		
		if($sort && $sort = doArray(doArray(explode(' ', trim($sort), 2), 'trim'), 'strtoupper')) {
		
			if(count($sort) == 2 && defined('SORT_'.$sort[0])) {
				sort($values, constant('SORT_'.$sort[0]));
			}
			
			if(end($sort) == 'DESC') {
				$values = array_reverse($values);
			}
		}
		
		$values = array_slice($values, $offset, $limit);
		
		if($assign !== NULL) {
			foreach(do_list($assign) as $key => $var) {
				$value = isset($values[$key]) ? $values[$key] : '';
				$variable[$var] = $value;
			}
		}
		
		if(empty($values) || $thing === NULL) {
			return;
		}

		$count = count($values);

		$i = 0;
		$out = array();

		foreach($values as $string) {
			$i++;
			$parent = $rah_repeat;

			$rah_repeat = 
				array(
					'string' => $string,
					'first' => ($i == 1),
					'last' => ($count == $i),
				);

			$out[] = parse($thing);
			$rah_repeat = $parent;
		}

		unset($rah_repeat);
		return doWrap($out, $wraptag, $break, $class);
	}

/**
 * Returns the current value
 * @return string
 */

	function rah_repeat_value($atts) {
		global $rah_repeat;
		
		extract(lAtts(array(
			'escape' => 0,
		), $atts));
		
		if(!isset($rah_repeat['string'])) {
			return;
		}
		
		if($escape) {
			return htmlspecialchars($rah_repeat['string']);
		}

		return $rah_repeat['string'];
	}

/**
 * Checks if the item is the first
 * @return string User-markup
 */

	function rah_repeat_if_first($atts, $thing='') {
		global $rah_repeat;
		return parse(EvalElse($thing, $rah_repeat['first'] == true));
	}

/**
 * Checks if the item is the last
 * @return string User-markup
 */

	function rah_repeat_if_last($atts, $thing='') {
		global $rah_repeat;
		return parse(EvalElse($thing, $rah_repeat['last'] == true));
	}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>rah_repeat</h1>

<p><a href="http://rahforum.biz/plugins/rah_repeat" rel=" rel="nofollow"">Project page</a> | <a href="http://twitter.com/gocom" rel=" rel="nofollow"">Twitter</a> | <a href="https://github.com/gocom/rah_repeat" rel=" rel="nofollow"">GitHub</a> | <a href="http://forum.textpattern.com/viewtopic.php?id=32384" rel=" rel="nofollow"">Support forum</a> | <a href="http://rahforum.biz/donate/rah_repeat" rel=" rel="nofollow"">Donate</a></p>

<p>Rah_repeat is a <a href="http://www.textpattern.com" rel=" rel="nofollow"">Textpattern <span class="caps">CMS</span></a> plugin used for iterations. The plugin splits a provided value to smaller chunks and iterates overs, just like you would expect from a for each loop in any programming language. With the plugin you can turn a simple comma-separated list of values into advanced <span class="caps">HTML</span> output, or extract parts of a value as <a href="http://textpattern.net/wiki/index.php?title=variable" rel=" rel="nofollow"">variables</a>.</p>

<h2>Requirements</h2>

<p>Rah_repeat&#8217;s minimum requirements:</p>

	<ul>
		<li>Textpattern 4.4.1 or newer.</li>
		<li><span class="caps">PHP</span> 5.2 or newer.</li>
	</ul>

<h2>Installing</h2>

<p>Rah_repeat&#8217;s installation follows the standard plugin installation steps.</p>

	<ol>
		<li>Download the plugin installation code.</li>
		<li>Copy and paste the installation code into the <em>Install plugin</em> box of your Textpattern Plugin pane.</li>
		<li>Run the automated setup.</li>
		<li>After the setup is done, activate the plugin. Done.</li>
	</ol>

<h2>Basics</h2>

<pre><code>&lt;txp:rah_repeat range=&quot;min, max, step&quot; value=&quot;value1, value2, ...&quot; assign=&quot;variable1, variable2, ...&quot;&gt;
	...contained statement...
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Rah_repeat&#8217;s main job is primely iterating over values. Its iteration power can used to create lists or extract subsets of data. The plugin can come very handy when you have a <a href="http://textpattern.net/wiki/index.php?title=custom_field" rel=" rel="nofollow"">custom field</a> that contains comma-separated list of values which you want to present as a <span class="caps">HTML</span> list or extract as individual separate values.</p>

<p>The values you want to iterate over are provided to the tag with the <code>value</code> attribute, each individual subset value separated from each other with the <code>delimiter</code>, defaulting to a comma. The current value that is being iterated over can be returned using the <code>rah_repeat_value</code> tag, wrapped in <code>rah_repeat</code> block. The following would generate a <span class="caps">HTML</span> list from comma-separated list of <code>red, blue, green</code>.</p>

<pre><code>&lt;txp:rah_repeat value=&quot;red, blue, green&quot; wraptag=&quot;ul&quot; break=&quot;li&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>In addition to iterating over values and creating lists, the tag can also be used to extract values and assign each one to a <a href="http://textpattern.net/wiki/index.php?title=variable" rel=" rel="nofollow"">variable</a> tag. This can be done using the <code>rah_repeat</code> tag&#8217;s <code>assign</code> attribute. The attribute takes a comma-separated list of variable names that will be created, each containing one of the values.</p>

<pre><code>&lt;txp:rah_repeat value=&quot;red, blue, green&quot; assign=&quot;color1, color2, color3&quot; /&gt;
</code></pre>

<p>The above would extra each of the colors as a variable. These variables would be named as <code>color1</code>, <code>color2</code> and <code>color3</code>. Using <code>&lt;txp:variable name=&quot;color1&quot; /&gt;</code> would return <code>red</code>.</p>

<h2>Tags and attributes</h2>

<p>The plugin comes with a total of four tags. The main tag <code>rah_repeat</code>, a single tag <code>rah_repeat_value</code>, and two conditionals <code>rah_repeat_if_first</code> and <code>rah_repeat_if_last</code>.</p>

<h3>rah_repeat</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;value1, value2, ...&quot;&gt;
	...contained statement...
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>The <code>&lt;txp:rah_repeat&gt;</code> tag is the plugin&#8217;s main tag. It&#8217;s a container tag used for iterations. Attributes for it are as follows.</p>

<p><strong>value</strong><br />
Sets the values that are passed to the tag. Multiple values are separated with the <code>delimiter</code> which by default is a comma (<code>,</code>). This attribute or either <code>range</code> is required.<br />
Example: <code>value=&quot;dog,cat,human&quot;</code> Default: <code>&quot;&quot;</code></p>

<p><strong>range</strong><br />
Creates a list of values containing a range of elements. Using <code>range</code> overrides <code>value</code> attribute. It works identically to <span class="caps">PHP</span>&#8217;s <a href="http://php.net/manual/en/function.range.php" rel=" rel="nofollow"">range</a> function and uses same sequence syntax as it. The attribute&#8217;s value consists of three parts: <code>minimum</code>, <code>maximum</code> and <code>step</code>, which are separated by a comma. All but <code>step</code> are required.<br />
Example: <code>range=&quot;1, 10&quot;</code> Default: undefined</p>

<p><strong>delimiter</strong><br />
Sets the delimiter that is used to split the provided <code>value</code> into a list. Default delimiter is comma (<code>,</code>).<br />
Example: <code>delimiter=&quot;|&quot;</code> Default: <code>&quot;,&quot;</code></p>

<p><strong>assign</strong><br />
Assigns values as Textpattern&#8217;s <a href="http://textpattern.net/wiki/index.php?title=variable" rel=" rel="nofollow"">variables</a>. Takes a comma-separated list of variable names: <code>variable1, variable2, variable3, ...</code>.<br />
Example: <code>assign=&quot;label, value&quot;</code> Default: <code>unset</code></p>

<p><strong>duplicates</strong><br />
Removes duplicate values from the list. If the attribute is set to <code>1</code>, only first occurrence of the value is used and duplicates are stripped off.<br />
Example: <code>duplicates=&quot;1&quot;</code> Default: <code>&quot;0&quot;</code></p>

<p><strong>exclude</strong><br />
Exclude certain values from the list. The attribute takes a comma (or <code>delimiter</code>, if <code>delimiter</code> is changed) separated list of values.<br />
Example: <code>exclude=&quot;foo,bar&quot;</code> Default: undefined</p>

<p><strong>trim</strong><br />
Trims values from extra whitespace. This can be particularly helpful if the provided values are from user-input (e.g. from an article field), or the values just have extra whitespace, and the resulting output has to be clean (i.e. used in <span class="caps">XML</span>, JavaScript or to a <a href="http://textpattern.net/wiki/index.php?title=variable" rel=" rel="nofollow"">variable</a> comparison). If you want to keep whitespace intact, you can use this attribute. By default the option is on, and values are trimmed.<br />
Example: <code>trim=&quot;0&quot;</code> Default: <code>&quot;1&quot;</code></p>

<p><strong>sort</strong><br />
Sorts the values. If the attribute is used, all values are rearranged to the specified order. Available options are <code>regular</code> (sorts without checking the type), <code>numeric</code> (sorts in a numeric order), <code>string</code> (sorts as strings) and <code>locale_string</code> (sorts according server&#8217;s locale settings). All the values can be  followed by the sorting direction, either <code>desc</code> and <code>asc</code>. By default the option isn&#8217;t used (unset), and the values are returned in the order they were supplied.<br />
Example: <code>sort=&quot;regular asc&quot;</code> Default: <code>&quot;&quot;</code></p>

<p><strong>offset</strong><br />
The number of items to skip. Default is <code>0</code> (none).<br />
Example: <code>offset=&quot;5&quot;</code> Default: <code>&quot;0&quot;</code></p>

<p><strong>limit</strong><br />
The number of items are displayed. By default there is no limit, and all items are returned.<br />
Example: <code>limit=&quot;10&quot;</code> Default: undefined</p>

<p><strong>wraptag</strong><br />
The (X)HTML tag (without brackets) used to wrap the output.<br />
Example: <code>wraptag=&quot;div&quot;</code> Default: <code>&quot;&quot;</code></p>

<p><strong>break</strong><br />
The (X)HTML tag (without brackets) or a string used to separate list items.<br />
Example: <code>&quot;break=&quot;br&quot;</code> Default: <code>&quot;&quot;</code></p>

<p><strong>class</strong><br />
The (X)HTML class applied to the <code>wraptag</code>. Default is unset.<br />
Example: <code>class=&quot;plugin&quot;</code> Default: <code>&quot;&quot;</code></p>

<h3>rah_repeat_value</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;value1, value2, ...&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Rah_repeat_value a single tag, used to display a iterated value. The tag should be used inside a <code>&lt;txp:rah_repeat&gt;&lt;/txp:rah_repeat&gt;</code> block. The tag has a single attribute, <code>escape</code>.</p>

<p><strong>escape</strong><br />
If set to <code>1</code>, <span class="caps">HTML</span> and Textpattern markup are escaped, and special characters are converted to <span class="caps">HTML</span> entities. By default this option is off.<br />
Example: <code>escape=&quot;1&quot;</code> Default: <code>&quot;0&quot;</code></p>

<h3>rah_repeat_if_first</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;value1, value2, ...&quot;&gt;
	&lt;txp:rah_repeat_if_first&gt;
		Fist item.
	&lt;/txp:rah_repeat_if_first&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>The <code>&lt;txp:rah_repeat_if_first&gt;</code> tag is a container, and has no attributes. It&#8217;s a conditional tag that checks if the current item is the first one.</p>

<h3>rah_repeat_if_last</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;value1, value2, ...&quot;&gt;
	&lt;txp:rah_repeat_if_last&gt;
		Last item.
	&lt;/txp:rah_repeat_if_last&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>The <code>&lt;txp:rah_repeat_if_last&gt;</code> tag is a container, and has no attributes. It&#8217;s a conditional tag that checks if the current item is the last one.</p>

<h2>Examples</h2>

<h3>Simple usage example</h3>

<p>This example turns simple comma separated list of <code>dog,cat,human</code> into a <span class="caps">HTML</span> list.</p>

<pre><code>&lt;txp:rah_repeat wraptag=&quot;ul&quot; break=&quot;li&quot; value=&quot;dog, cat, human&quot;&gt;
	A &lt;txp:rah_repeat_value /&gt;.
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>The above returns:</p>

<pre><code>&lt;ul&gt;
	&lt;li&gt;A dog.&lt;/li&gt;
	&lt;li&gt;A cat.&lt;/li&gt;
	&lt;li&gt;A human.&lt;/li&gt;
&lt;/ul&gt;
</code></pre>

<h3>Using tags as values</h3>

<p>As of Textpattern version 4.0.7, you can use tags inside tags.</p>

<p>Let&#8217;s say that you have comma separated list of items stored inside article&#8217;s <a href="http://textpattern.net/wiki/index.php?title=custom_field" rel=" rel="nofollow"">custom field</a>. For example, list of &#8220;Nameless&#8221; video service&#8217;s video IDs (<code>ID1, ID2, ID3, ID4</code>), and you want to embed each of those as a playable video.</p>

<p>We pass the custom field hosting the video IDs to rah_repeat tag (with the <code>value</code> attribute), and place the video player code inside the container:</p>

<pre><code>&lt;txp:rah_repeat value='&lt;txp:custom_field name=&quot;MyCustomFieldName&quot; /&gt;'&gt;
	&lt;object width=&quot;600&quot; height=&quot;380&quot;&gt;
		&lt;param name=&quot;movie&quot; value=&quot;http://example.com/v/&lt;txp:rah_repeat_value /&gt;&quot;&gt;&lt;/param&gt;
		&lt;embed src=&quot;http://example.com/v/&lt;txp:rah_repeat_value /&gt;&quot; width=&quot;600&quot; height=&quot;380&quot;&gt;&lt;/embed&gt;
	&lt;/object&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>The above code would output 4 embedded players (one for each clip), displaying the videos specified with the custom field.</p>

<h3>Taking advantage of offset and limit attributes</h3>

<p>First display two items, then some text between, two more items, some more text and then the rest of the items.</p>

<pre><code>&lt;txp:rah_repeat value='&lt;txp:custom_field name=&quot;MyCustomFieldName&quot; /&gt;' limit=&quot;2&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
&lt;p&gt;Some text here.&lt;/p&gt;
&lt;txp:rah_repeat value='&lt;txp:custom_field name=&quot;MyCustomFieldName&quot; /&gt;' offset=&quot;2&quot; limit=&quot;4&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
&lt;p&gt;Some another cool phrase here.&lt;/p&gt;
&lt;txp:rah_repeat value='&lt;txp:custom_field name=&quot;MyCustomFieldName&quot; /&gt;' offset=&quot;4&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<h3>Repeat inside repeat</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;group1|item1|item2, group2|item1|item2&quot;&gt;
	&lt;ul&gt;
		&lt;txp:rah_repeat value='&lt;txp:rah_repeat_value /&gt;' delimiter=&quot;|&quot;&gt;
			&lt;li&gt;&lt;txp:rah_repeat_value /&gt;&lt;/li&gt;
		&lt;/txp:rah_repeat&gt;
	&lt;/ul&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Returns two <span class="caps">HTML</span> lists:</p>

<pre><code>&lt;ul&gt;
	&lt;li&gt;group1&lt;/li&gt;
	&lt;li&gt;item1&lt;/li&gt;
	&lt;li&gt;item2&lt;/li&gt;
&lt;/ul&gt;
&lt;ul&gt;
	&lt;li&gt;group2&lt;/li&gt;
	&lt;li&gt;item1&lt;/li&gt;
	&lt;li&gt;item2&lt;/li&gt;
&lt;/ul&gt;
</code></pre>

<h3>Basic usage of the if_first and the if_last tags</h3>

<p>With the conditional tags <code>&lt;txp:rah_repeat_if_first /&gt;</code> and <code>&lt;txp:rah_repeat_if_last&gt;</code> we can test which value is the first and which is the last.</p>

<pre><code>&lt;txp:rah_repeat value=&quot;item1, item2, item3, item4, item5&quot; wraptag=&quot;ul&quot; break=&quot;li&quot;&gt;
	&lt;txp:rah_repeat_if_first&gt;First: &lt;/txp:rah_repeat_if_first&gt;
	&lt;txp:rah_repeat_if_last&gt;Last: &lt;/txp:rah_repeat_if_last&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Returns:</p>

<pre><code>&lt;ul&gt;
	&lt;li&gt;First: item1&lt;/li&gt;
	&lt;li&gt;item2&lt;/li&gt;
	&lt;li&gt;item3&lt;/li&gt;
	&lt;li&gt;item4&lt;/li&gt;
	&lt;li&gt;Last: item5&lt;/li&gt;
&lt;/ul&gt;
</code></pre>

<h3>Remove duplicate values</h3>

<pre><code>&lt;txp:rah_repeat duplicates=&quot;1&quot; value=&quot;foo, bar, bar, foo, bar, bar, foo, foobar&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Returns: <code>foo, bar, foobar</code></p>

<h3>Arrange the values from lowest to highest</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;b, a, c&quot; sort=&quot;regular asc&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Returns: <code>a, b, c</code></p>

<h3>Excluding values</h3>

<pre><code>&lt;txp:rah_repeat value=&quot;foo, bar, foobar&quot; exclude=&quot;foo,bar&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Returns: <code>foobar</code></p>

<h3>Using range attribute</h3>

<p>With the <code>range</code> it&#8217;s possible to create a range of elements with out specifying each. For example generating list of alphabet (A-z) can be done with range.</p>

<pre><code>&lt;txp:rah_repeat range=&quot;a, z, 1&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Or listing number from 0 to 10.</p>

<pre><code>&lt;txp:rah_repeat range=&quot;0, 10, 1&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<p>Or values <code>0</code>, <code>2</code>, <code>4</code>, and <code>6</code>.</p>

<pre><code>&lt;txp:rah_repeat range=&quot;0, 6, 2&quot;&gt;
	&lt;txp:rah_repeat_value /&gt;
&lt;/txp:rah_repeat&gt;
</code></pre>

<h3>Assign variables with assign attribute</h3>

<p>The <code>assign</code> attribute allows exporting split values as <a href="http://textpattern.net/wiki/index.php?title=variable" rel=" rel="nofollow"">variables</a>.</p>

<pre><code>&lt;txp:rah_repeat value=&quot;JavaScript, jQuery, 1.8.0&quot; assign=&quot;language, framework, version&quot; /&gt;
</code>
<code>&lt;txp:variable name=&quot;language&quot; /&gt;
&lt;txp:variable name=&quot;framework&quot; /&gt;
</code>
<code>&lt;txp:if_variable name=&quot;version&quot; value=&quot;1.8.0&quot;&gt;
	Version is 1.8.0.
&lt;/txp:if_variable&gt;
</code></pre>

<h2>Changelog</h2>

<h3>Version 0.8.1 &#8211; 2012/08/25</h3>

	<ul>
		<li>Fixed: <code>range</code> attribute. It ignored any options and always created an list of 1-10.</li>
	</ul>

<h3>Version 0.8 &#8211; 2012/08/24</h3>

	<ul>
		<li>Fixed: made the <code>sort</code> attribute&#8217;s direction optional.</li>
		<li>Added: <code>exclude</code> can now take and exclude empty strings (<code>&quot;&quot;</code>) and zeros (<code>0</code>).</li>
		<li>Added: <code>range</code> attribute. Allows generating automated lists (<code>range=&quot;min, max, step&quot;</code>).</li>
		<li>Added: <code>assign</code> attribute. Allows extracting values as variables.</li>
		<li>Added: <code>escape</code> attribute to <code>&lt;txp:rah_repeat_value /&gt;</code>.</li>
		<li>Added: Support for natural ordering (<code>sort=&quot;natural&quot;</code>).</li>
		<li>Changed: Now <code>trim</code> is enabled by default. Previously values weren&#8217;t trimmed from white-space by default.</li>
		<li>Changed: Renamed <code>locale</code> sorting option to <code>LOCALE_STRING</code>.</li>
		<li>Changed: Order can be reversed with out re-sorting by using <code>sort=&quot;desc&quot;</code>.</li>
		<li>Now requires <span class="caps">PHP</span> 5.2 (or newer).</li>
	</ul>

<h3>Version 0.7 &#8211; 2011/12/02</h3>

	<ul>
		<li>Added: <code>trim</code> attribute. When set to <code>1</code>, provided values are trimmed from surrounding whitespace.</li>
		<li>Fixed: &#8220;locale&#8221; sorting option. Previously it sorted values as a string, not by locale options.</li>
		<li>Changed: limit&#8217;s default to <span class="caps">NULL</span>. Leave limit unset if you only want offset without limit, or use a high value.</li>
		<li>Improved: Better offset and limit functionality. Now slices the list of values before staring to build the markup.</li>
	</ul>

<h3>Version 0.6 &#8211; 2010/05/09</h3>

	<ul>
		<li>Added: <code>exclude</code> attribute.</li>
		<li>Fixed: <code>&lt;txp:rah_repeat_if_last&gt;</code> tag. Issue was caused by v0.5 update.</li>
	</ul>

<h3>Version 0.5 &#8211; 2010/05/08</h3>

	<ul>
		<li>Changed offset&#8217;s default value from <code>unset</code> to <code>0</code>.</li>
		<li>Added: <code>sort</code> attribute.</li>
		<li>Added: <code>duplicates</code> attribute.</li>
	</ul>

<h3>Version 0.4 &#8211; 2009/11/30</h3>

	<ul>
		<li>Fixed: now returns old parent global, if two tags are used inside each other, instead of defining it empty.</li>
		<li>Added: <code>&lt;txp:rah_repeat_if_first&gt;</code>.</li>
		<li>Added: <code>&lt;txp:rah_repeat_if_last&gt;</code>.</li>
	</ul>

<h3>Version 0.3 &#8211; 2009/11/28</h3>

	<ul>
		<li>Added: <code>wraptag</code> attribute.</li>
		<li>Added: <code>break</code> attribute.</li>
		<li>Added: <code>class</code> attribute.</li>
	</ul>

<h3>Version 0.2 &#8211; 2009/11/23</h3>

	<ul>
		<li>Added: <code>limit</code> attribute.</li>
		<li>Added: <code>offset</code> attribute.</li>
	</ul>

<h3>Version 0.1 &#8211; 2009/11/20</h3>

	<ul>
		<li>Initial release.</li>
	</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>