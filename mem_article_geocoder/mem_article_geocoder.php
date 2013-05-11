<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ('abc' is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_article_geocoder';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 0;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Goecodes an address in custom fields and stores the lat/lng in more custom fields';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
# $plugin['order'] = 5;

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the admin side
$plugin['type'] = '1';

// Plugin 'flags' signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use.
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '2';

$plugin['textpack'] = <<< EOT
EOT;

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. Article Custom Field Geocoder

h2. Summary

Uses Google maps to geocode an address found in custom fields. The latitude and longitude are stored back in the custom fields. \
Custom fields are configured by editing the plugin.

h2. License

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
define('MEM_CF_ADDRESS', 'custom-16');
define('MEM_CF_CITY', 'custom-22');
define('MEM_CF_STATE', 'custom-23');
define('MEM_CF_ZIP', 'custom-24');
define('MEM_CF_LATITUDE', 'custom-26');
define('MEM_CF_LONGITUDE', 'custom-28');

/****** DO NOT EDIT BELOW THIS LINE **********/

if (@txpinterface == 'admin')
{
	register_callback('mem_article_geocoder_install', 'plugin_lifecycle.mem_article_geocoder', 'installed');

	function mem_article_geocoder_install()
	{
		safe_update('txp_plugin', "status = 1", "name = 'mem_article_geocoder'");
	}

	register_callback('mem_article_geocoder_js', 'admin_side', 'head_end');
}

function mem_article_geocoder_js($event, $step)
{
	$address = MEM_CF_ADDRESS;
	$city = MEM_CF_CITY;
	$state = MEM_CF_STATE;
	$zip = MEM_CF_ZIP;
	$lat = MEM_CF_LATITUDE;
	$lng = MEM_CF_LONGITUDE;

	$js = <<< EOJS
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
(function($){
	$(function(){
		var geocoder = new google.maps.Geocoder();
		var address = $('#{$address}');
		var city = $('#{$city}');
		var state = $('#{$state}');
		var zip = $('#{$zip}');
		var lat = $('#{$lat}');
		var lng = $('#{$lng}');
		$('#{$address}, #{$city}, #{$state}, #{$zip}').blur(function(){
			var a = address.val() + ' ' + city.val() + ' ' + state.val() + ' ' + zip.val();
			geocoder.geocode( { 'address': a }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var x = results[0].geometry.location.lat();
					var y = results[0].geometry.location.lng();
					lat.val(x);
					lng.val(y);
					if (console && console.log){
						console.log('Geocoded address "' + a + '" ' + x + ',' + y);
					}
				} else {
					if (console && console.log){
						console.log('Geocode failed with status ' + status + '; address=' + a);
					}
				}
			});
		});
	});
})(jQuery);
</script>
EOJS;

	echo $js;
}
# --- END PLUGIN CODE ---

?>
