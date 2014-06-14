<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'glz_custom_fields';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '1.4.0-beta';
$plugin['author'] = 'Gerhard Lazu';
$plugin['author_uri'] = 'http://gerhardlazu.com';
$plugin['description'] = 'Unlimited, super special custom fields.';

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
$plugin['type'] = '5';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '3';

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

# glz_custom_fields v1.4.0-beta1
# Unlimited, super special custom fields.
#
# Gerhard Lazu
# http://gerhardlazu.com
#
# Contributors: Randy Levine, Sam Weiss, Luca Botti, Manfre, Vladimir Siljkovic, Julian Reisenberger, Steve Dickinson, Stef Dawson, Jean-Pol Dupont
# Minimum requirements: Textpattern 4.5.1

// Including helper files. If we can't have classes, we will use includes


// -------------------------------------------------------------
// messages that will be available throughout this plugin
function glz_custom_fields_gTxt($get, $atts = array()) {
  $lang = array(
    'no_name'                => 'Ooops! <strong>custom set</strong> must have a name',
    'deleted'                => '<strong>{custom_set_name}</strong> was deleted',
    'reset'                  => '<strong>{custom_set_name}</strong> was reset',
    'created'                => '<strong>{custom_set_name}</strong> was created',
    'updated'                => '<strong>{custom_set_name}</strong> was updated',
    'exists'                 => 'Ooops! <strong>{custom_set_name}</strong> already exists',
    'doesnt_exist'           => 'Ooops! <strong>{custom_set_name}</strong> is not set',
    'field_problems'         => 'Ooops! <strong>{custom_set_name}</strong> has some problems. <a href="?event=glz_custom_fields">Go fix it</a>.',
    'custom_set'             => 'Text Input', # custom sets in TXP 4.2.0 are by type custom_set by default...
    'text_input'             => 'Text Input',
    'select'                 => 'Select',
    'multi-select'           => 'Multi-Select',
    'textarea'               => 'Textarea',
    'checkbox'               => 'Checkbox',
    'radio'                  => 'Radio',
    'date-picker'            => 'Date Picker',
    'time-picker'            => 'Time Picker',
    'custom-script'          => 'Custom Script',
    'type_not_supported'     => 'Type not supported',
    'no_do'                  => 'Ooops! No action specified for method, abort.',
    'not_specified'          => 'Ooops! {what} is not specified',
    'searchby_not_set'       => '<strong>searcby</strong> cannot be left blank',
    'jquery_missing'         => 'Upgrade TXP to at least 4.0.5 or put <strong>jquery.js</strong> in your /textpattern folder. <a href="http://jquery.com" title="jQuery website">jQuery website</a>',
    'check_path'             => 'Make sure all your paths are correct. Check <strong>config.php</strong> and the Admin tab (mainly Advanced).',
    'no_articles_found'      => 'No articles with custom fields have been found.',
    'migration_success'      => 'Migrating custom fields was successful',
    'migration_skip'         => '<strong>custom_fields</strong> table already has data in it, migration skipped.',
    'search_section_created' => '<strong>search</strong> section has been created',
    'custom_sets_all_input'  => 'All custom sets have been set back to input',
    'preferences_updated'    => 'Plugin preferences have been updated',
    'not_found'              => 'Ooops! <strong>{file}</strong> cannot be found, check path',
    'not_callable'           => 'Ooops! <strong>{function}()</strong> cannot be called. Ensure <strong>{file}</strong> can be executed.'
  );

  $out = ( strstr($lang[$get], "Ooops!") ) ? // Ooops! would appear 0 in the string...
      "<span class=\"red\">{$lang[$get]}</span>" :
      $lang[$get];

  return strtr($out, $atts);
}





// -------------------------------------------------------------
// I would do this through a factory class, but some folks are still running PHP4...
function glz_custom_fields_MySQL($do, $name='', $table='', $extra='') {
  if ( !empty($do) ) {
    switch ( $do ) {
      case 'all':
        return glz_all_custom_sets();
        break;

      case 'values':
        return glz_values_custom_field($name, $extra);
        break;

      case 'all_values' :
        return glz_all_existing_custom_values($name, $extra);
        break;

      case 'article_customs':
        return glz_article_custom_fields($name, $extra);
        break;

      case 'next_custom':
        return glz_next_empty_custom();
        break;

      case 'new':
        glz_new_custom_field($name, $table, $extra);
        glz_custom_fields_update_count();
        break;

      case 'update':
        return glz_update_custom_field($name, $table, $extra);
        break;

      case 'reset':
        return glz_reset_custom_field($name, $table, $extra);
        break;

      case 'delete':
        glz_delete_custom_field($name, $table);
        glz_custom_fields_update_count();
        break;

      case 'check_migration':
        return glz_check_migration();
        break;

      case 'mark_migration':
        return glz_mark_migration();
        break;

      case 'custom_set_exists':
        return glz_check_custom_set_exists($name);
        break;

      case 'plugin_preferences':
        return glz_plugin_preferences($name);
        break;

      case 'update_plugin_preferences':
        return glz_update_plugin_preferences($name);
        break;
    }
  }
  else
    trigger_error(glz_custom_fields_gTxt('no_do'));
}


function glz_all_custom_sets() {
  $all_custom_sets = getRows("
    SELECT
      `name` AS custom_set,
      `val` AS name,
      `position`,
      `html` AS type
    FROM
      `".PFX."txp_prefs`
    WHERE
      `event`='custom'
    ORDER BY
      `position`
  ");

  foreach ( $all_custom_sets as $custom_set ) {
    $out[$custom_set['custom_set']] = array(
      'name'      => $custom_set['name'],
      'position'  => $custom_set['position'],
      'type'      => $custom_set['type']
    );
  }

  return $out;
}


function glz_values_custom_field($name, $extra) {
  global $prefs;

  if ( is_array($extra) ) {
    extract($extra);

    if ( !empty($name) ) {
      switch ( $prefs['values_ordering'] ) {
        case "ascending":
          $orderby = "value ASC";
          break;
        case "descending":
          $orderby = "value DESC";
          break;
        default:
          $orderby = "id ASC";
      }


      $arr_values = getThings("
        SELECT
          `value`
        FROM
          `".PFX."custom_fields`
        WHERE
          `name` = '{$name}'
        ORDER BY
          {$orderby}
      ");

      if ( count($arr_values) > 0 ) {
        // decode all special characters e.g. ", & etc. and use them for keys
        foreach ( $arr_values as $key => $value )
          $arr_values_formatted[glz_return_clean_default(htmlspecialchars($value))] = stripslashes($value);

        // if this is a range, format ranges accordingly
        return glz_format_ranges($arr_values_formatted, $custom_set_name);
      }
    }
  }
  else
    trigger_error(glz_custom_fields_gTxt('not_specified', array('{what}' => "extra attributes")));
}


function glz_all_existing_custom_values($name, $extra) {
  if ( is_array($extra) ) {
    extract(lAtts(array(
      'custom_set_name'   => "",
      'status'            => 4
    ),$extra));

    // we might want to check the custom field values for all articles - think initial migration
    $status_condition = ($status == 0) ? "<> ''" : "= '$status'";

    if ( !empty($name) ) {
      $arr_values = getThings("
        SELECT DISTINCT
          `$name`
        FROM
          `".PFX."textpattern`
        WHERE
          `Status` $status_condition
        AND
          `$name` <> ''
        ORDER BY
          `$name`
        ");

      // trim all values
      foreach ( $arr_values as $key => $value )
        $arr_values[$key] = trim($value);

      // DEBUG
      // dmp($arr_values);

      // prepare our array for checking. We need a single string to check for | instances - seems quickest.
      $values_check = join('::', $arr_values);

      // DEBUG
      // dmp($values_check);

      // check if some of the values are multiple ones
      if ( strstr($values_check, '|') ) {
        // initialize $out
        $out = array();
        // put all values in an array
        foreach ( $arr_values as $value ) {
          $arr_values = explode('|', $value);
          $out = array_merge($out, $arr_values);
        }
        // keep only the unique ones
        $out = array_unique($out);
        // keys and values need to be the same
        $out = php4_array_combine($out, $out);
      }

      // check if this is a range
      else if ( strstr($values_check, '-') && strstr($custom_set_name, 'range') )
        // keys won't have the unit ($, £, m<sup>3</sup>, etc.) values will
        $out = glz_format_ranges($arr_values, $custom_set_name);
      else
        // keys and values need to be the same
        $out = php4_array_combine($arr_values, $arr_values);

      // calling stripslashes on all array values
      array_map('glz_array_stripslashes', $out);

      return $out;
    }
  }
  else
    trigger_error(glz_custom_fields_gTxt('not_specified', array('{what}' => "extra attributes")));
}


function glz_article_custom_fields($name, $extra) {
  if ( is_array($extra) ) {
    // see what custom fields we need to query for
    foreach ( $extra as $custom => $custom_set )
      $select[] = glz_custom_number($custom);

    // prepare the select elements
    $select = implode(',', $select);

    $arr_article_customs = getRow("
      SELECT
        $select
      FROM
        `".PFX."textpattern`
      WHERE
        `ID`='$name'
    ");

    return $arr_article_customs;
  }
  else
    trigger_error(glz_custom_fields_gTxt('not_specified', array('{what}' => "extra attributes")));
}


function glz_new_custom_field($name, $table, $extra) {
  if ( is_array($extra) ) {
    extract($extra);
    // DRYing up, we'll be using this variable quiet often
    $custom_set = ( isset($custom_field_number) ) ?
      "custom_{$custom_field_number}_set" :
      $custom_set;

    if ( ($table == PFX."txp_prefs") ) {
      // if this is a new field without a position, use the $custom_field_number
      if (empty($custom_set_position)) $custom_set_position = $custom_field_number;
      $query = "
        INSERT INTO
          `".PFX."txp_prefs` (`prefs_id`, `name`, `val`, `type`, `event`, `html`, `position`)
        VALUES
          ('1', '{$custom_set}', '{$name}', '1', 'custom', '{$custom_set_type}', {$custom_set_position})
      ";
    }
    else if ( $table == PFX."txp_lang" ) {
      $query = "
        INSERT INTO
          `".PFX."txp_lang` (`id`,`lang`,`name`,`event`,`data`,`lastmod`)
        VALUES
          ('','{$lang}','{$custom_set}','prefs','{$name}',now())
      ";
    }
    else if ( $table == PFX."textpattern" ) {
      $column_type = ( $custom_set_type == "textarea" ) ? "TEXT" : "VARCHAR(255)";
      $query = "
        ALTER TABLE
          `".PFX."textpattern`
        ADD
          `custom_{$custom_field_number}` {$column_type} NOT NULL DEFAULT ''
      ";
    }
    else if ( $table == PFX."custom_fields" ) {
      $arr_values = array_unique(array_filter(explode("\r\n", $value), 'glz_arr_empty_values'));

      if ( is_array($arr_values) && !empty($arr_values) ) {
        $size_arr_values = count($arr_values);
        $insert = '';
        foreach ( $arr_values as $key => $value ) {
          // don't insert empty values
          if ( !empty($value) )
            // make sure special characters are escaped before inserting them in the database
            $value = addslashes(addslashes(trim($value)));
            // if this is the last value, query will have to be different
            $insert .= ($key+1 != $size_arr_values ) ?
              "('{$custom_set}','{$value}'), " :
              "('{$custom_set}','{$value}')";
        }
        $query = "
          INSERT INTO
            `".PFX."custom_fields` (`name`,`value`)
          VALUES
            {$insert}
        ";
      }
    }
    if ( isset($query) && !empty($query) )
      safe_query($query);
  }
  else
    trigger_error(glz_custom_fields_gTxt('not_specified', array('{what}' => "extra attributes")));
}


function glz_update_custom_field($name, $table, $extra) {
  if ( is_array($extra) )
    extract($extra);

  if ( ($table == PFX."txp_prefs") ) {
    safe_query("
      UPDATE
        `".PFX."txp_prefs`
      SET
        `val` = '{$custom_set_name}',
        `html` = '{$custom_set_type}',
        `position` = '{$custom_set_position}'
      WHERE
        `name`='{$name}'
    ");
  }
  else if ( ($table == PFX."textpattern") ) {
    $column_type = ( $custom_set_type == "textarea" ) ? "TEXT" : "VARCHAR(255)";
    safe_query("
      ALTER TABLE
        `".PFX."textpattern`
      MODIFY
        `{$custom_field}` {$column_type} NOT NULL DEFAULT ''
    ");
  }
}


function glz_reset_custom_field($name, $table, $extra) {
  if ( is_array($extra) )
    extract($extra);

  if ( $table == PFX."txp_prefs" ) {
    safe_query("
      UPDATE
        `".PFX."txp_prefs`
      SET
        `val` = '',
        `html` = 'text_input'
      WHERE
        `name`='{$name}'
    ");
  }
  else if ( $table == PFX."textpattern" ) {
    safe_query("UPDATE `".PFX."textpattern` SET `{$name}` = ''");
    safe_query("ALTER TABLE `".PFX."textpattern` MODIFY `{$custom_field}` VARCHAR(255) NOT NULL DEFAULT ''");
  }
}


function glz_delete_custom_field($name, $table) {
  // remember, custom fields under 10 MUST NOT be deleted
  if ( glz_custom_digit($name) > 10 ) {
    if ( in_array($table, array(PFX."txp_prefs", PFX."txp_lang", PFX."custom_fields")) ) {
      $query = "
        DELETE FROM
          `{$table}`
        WHERE
          `name`='{$name}'
      ";
    }
    else if ( $table == PFX."textpattern" ) {
      $query = "
        ALTER TABLE
          `".PFX."textpattern`
        DROP
          `{$name}`
      ";
    }
    safe_query($query);
  }
  else {
    if ( $table == PFX."txp_prefs" )
      glz_custom_fields_MySQL("reset", $name, $table);
    else if ( ($table == PFX."custom_fields") ) {
      safe_query("
        DELETE FROM
          `{$table}`
        WHERE
          `name`='{$name}'
      ");
    }
  }
}


// -------------------------------------------------------------
// checks if custom_fields table has any values in it
function glz_check_migration() {
  return getThing("
    SELECT
      COUNT(*)
    FROM
      `".PFX."custom_fields`
  ");
}


// -------------------------------------------------------------
// make a note of glz_custom_fields migration in txp_prefs
function glz_mark_migration() {
  set_pref("migrated", "1", "glz_custom_f");
}


// -------------------------------------------------------------
// check if one of the special custom fields exists
function glz_check_custom_set_exists($name) {
  if ( !empty($name) ) {
    return getThing("
      SELECT
        `name`, `val`
      FROM
        `".PFX."txp_prefs`
      WHERE
        `html` = '{$name}'
      AND
        `name` LIKE 'custom_%'
      ORDER BY
        `name`
    ");
  }
}


// -------------------------------------------------------------
// updates max_custom_fields
function glz_custom_fields_update_count() {
  set_pref('max_custom_fields', safe_count("txp_prefs", "event='custom'"));
}

// -------------------------------------------------------------
// returns all plugin preferences
function glz_plugin_preferences($arr_preferences) {
	$r = safe_rows_start('name, val', 'txp_prefs', "event = 'glz_custom_f'");
	if ($r) {
		while ($a = nextRow($r)) {
			$out[$a['name']] = stripslashes($a['val']);
		}
	}
  return $out;
}

// -------------------------------------------------------------
// updates all plugin preferences
function glz_update_plugin_preferences($arr_preferences) {
  // die(dmp($arr_preferences));
  foreach ($arr_preferences as $preference => $value) {
    set_pref($preference, addslashes(addslashes(trim($value))), "glz_custom_f", 10); // 10 so that it won't appear under TXP's prefs tab
  }
}






// -------------------------------------------------------------
// goes through all custom sets, returns the first one which is not being used
function glz_next_empty_custom() {
  global $all_custom_sets;

  foreach ( $all_custom_sets as $custom => $custom_set ) {
    if ( empty($custom_set['name']) )
      return $custom;
  }
}


// -------------------------------------------------------------
// edit/delete buttons in custom_fields table require a form each
function glz_form_buttons($action, $value, $custom_set, $custom_set_name, $custom_set_type, $custom_set_position, $onsubmit='') {
  $onsubmit = ($onsubmit) ?
    'onsubmit="'.$onsubmit.'"' :
    '';

  return
    '<form method="post" action="index.php" '.$onsubmit.'>
      <input name="custom_set" value="'.$custom_set.'" type="hidden" />
      <input name="custom_set_name" value="'.$custom_set_name.'" type="hidden" />
      <input name="custom_set_type" value="'.$custom_set_type.'" type="hidden" />
      <input name="custom_set_position" value="'.$custom_set_position.'" type="hidden" />
      <input name="event" value="glz_custom_fields" type="hidden" />
      <input name="'.$action.'" value="'.$value.'" type="submit" />
    </form>';
}


// -------------------------------------------------------------
// the types our custom fields can take
function glz_custom_set_types() {
  return array(
    'normal' => array(
      'text_input',
      'checkbox',
      'radio',
      'select',
      'multi-select',
      'textarea'),
    'special' => array(
      'date-picker',
      'time-picker',
      'custom-script')
  );
}


// -------------------------------------------------------------
// outputs only custom fields that have been set, i.e. have a name assigned to them
function glz_check_custom_set($all_custom_sets, $step) {
  $out = array();

  foreach ($all_custom_sets as $key => $custom_field) {
    if (!empty($custom_field['name'])) {
      if ( ($step == "body") && ($custom_field['type'] == "textarea") )
        $out[$key] = $custom_field;
      else if ( ($step == "custom_fields") && ($custom_field['type'] != "textarea") ) {
        $out[$key] = $custom_field;
      }
    }
  }

  return $out;
}


// -------------------------------------------------------------
// removes { } from values which are marked as default
function glz_return_clean_default($value) {
  $pattern = "/^.*\{(.*)\}.*/";

  return preg_replace($pattern, "$1", $value);
}


// -------------------------------------------------------------
// return our default value from all custom_field values
function glz_default_value($all_values) {
  if ( is_array($all_values) ) {
    preg_match("/(\{.*\})/", join(" ", $all_values), $default);
    return ( (!empty($default) && $default[0]) ? $default[0] : '');
  }
}


// -------------------------------------------------------------
// calling the above function in an array context
function glz_clean_default_array_values(&$value) {
  $value = glz_return_clean_default($value);
}


// -------------------------------------------------------------
// custom_set without "_set" e.g. custom_1_set => custom_1
// or custom set formatted for IDs e.g. custom-1
function glz_custom_number($custom_set, $delimiter="_") {
  $custom_field = substr($custom_set, 0, -4);

  if ($delimiter != "_")
    $custom_field = str_replace("_", $delimiter, $custom_field);

  return $custom_field;
}


// -------------------------------------------------------------
// custom_set digit e.g. custom_1_set => 1
function glz_custom_digit($custom_set) {
  $out = explode("_", $custom_set);
  // $out[0] will always be custom
  return $out[1];
}


// -------------------------------------------------------------
// removes empty values from arrays - used for new custom fields
function glz_arr_empty_values($value) {
  if ( !empty($value) )
    return $value;
}


// -------------------------------------------------------------
// returns the custom set from a custom set name e.g. "Rating" gives us custom_1_set
function glz_get_custom_set($value) {
  global $all_custom_sets;

  // go through all custom fields and see if the one we're looking for exists
  foreach ( $all_custom_sets as $custom => $custom_set ) {
    if ( $custom_set['name'] == $value )
      return $custom;
  }
  // if it doesn't, return error message
  trigger_error(glz_custom_fields_gTxt('doesnt_exist', array('{custom_set_name}' => $value)));
}


// -------------------------------------------------------------
// get the article ID, EVEN IF it's newly saved
function glz_get_article_id() {
  return ( !empty($GLOBALS['ID']) ?
    $GLOBALS['ID'] :
    gps('ID') );
}


// -------------------------------------------------------------
// helps with range formatting - just DRY
function glz_format_ranges($arr_values, $custom_set_name) {
  //initialize $out
  $out = '';
  foreach ( $arr_values as $key => $value ) {
    $out[$key] = ( strstr($custom_set_name, 'range') ) ?
      glz_custom_fields_range($value, $custom_set_name) :
      $value;
  }
  return $out;
}


// -------------------------------------------------------------
// acts as a callback for the above function
function glz_custom_fields_range($custom_value, $custom_set_name) {
  // last part of the string will be the range unit (e.g. $, &pound;, m<sup>3</sup> etc.)
  $nomenclature = array_pop(explode(' ', $custom_set_name));

  // see whether range unit should go after
  if ( strstr($nomenclature, '(after)') ) {
    // trim '(after)' from the measuring unit
    $nomenclature = substr($nomenclature, 0, -7);
    $after = 1;
  }

  // check whether it's a range or single value
  $arr_value = explode('-', $custom_value);
  if ( is_array($arr_value) ) {
    // initialize $out
    $out = '';
    foreach ( $arr_value as $value ) {
      // check whether nomenclature goes before or after
      $out[] = ( !isset($after) ) ?
        $nomenclature.number_format($value) :
        number_format($value).$nomenclature;
    }
    return implode('-', $out);
  }
  // our range is a single value
  else {
    // check whether nomenclature goes before or after
    return ( !isset($after) ) ?
      $nomenclature.number_format($value) :
      number_format($value).$nomenclature;
  }
}


// -------------------------------------------------------------
// returns the next available number for custom set
function glz_custom_next($arr_custom_sets) {
  $arr_extra_custom_sets = array();
  foreach ( array_keys($arr_custom_sets) as $extra_custom_set) {
    $arr_extra_custom_sets[] = glz_custom_digit($extra_custom_set);
  }
  // order the array
  sort($arr_extra_custom_sets);

  for ( $i=0; $i < count($arr_extra_custom_sets); $i++ ) {
    if ($arr_extra_custom_sets[$i] > $i+1)
      return $i+1;
  }

  return count($arr_extra_custom_sets)+1;
}


// -------------------------------------------------------------
// checks if the custom field name isn't already taken
function glz_check_custom_set_name($arr_custom_fields, $custom_set_name, $custom_set='') {
  foreach ( $arr_custom_fields as $custom => $arr_custom_set ) {
    if ( ($custom_set_name == $arr_custom_set['name']) && (!empty($custom_set) && $custom_set != $custom) )
      return TRUE;
  }

  return FALSE;
}


// -------------------------------------------------------------
// formats the custom set output based on its type
function glz_format_custom_set_by_type($custom, $custom_id, $custom_set_type, $arr_custom_field_values, $custom_value = "", $default_value = "") {
  if ( is_array($arr_custom_field_values) )
    $arr_custom_field_values = array_map('glz_array_stripslashes', $arr_custom_field_values);

  switch ( $custom_set_type ) {
    // these are the normal custom fields
    case "text_input":
      return array(
        fInput("text", $custom, $custom_value, "edit", "", "", "22", "", $custom_id),
        'glz_custom_field'
      );

    case "select":
      return array(
        glz_selectInput($custom, $custom_id, $arr_custom_field_values, $custom_value, $default_value),
        'glz_custom_select_field'
      );

    case "multi-select":
      return array(
        glz_selectInput($custom, $custom_id, $arr_custom_field_values, $custom_value, $default_value, 1),
        'glz_custom_multi-select_field'
      );

    case "checkbox":
      return array(
        glz_checkbox($custom, $arr_custom_field_values, $custom_value, $default_value),
        'glz_custom_checkbox_field'
      );

    case "radio":
      return array(
        glz_radio($custom, $custom_id, $arr_custom_field_values, $custom_value, $default_value),
        'glz_custom_radio_field'
      );

    case "textarea":
      return array(
        text_area($custom, 100, 500, $custom_value, $custom_id),
        'glz_text_area_field'
      );

    // here start the special custom fields, might need to refactor the return, starting to repeat itself
    case "date-picker":
      return array(
        fInput("text", $custom, $custom_value, "edit date-picker", "", "", "22", "", $custom_id),
        'glz_custom_date-picker_field clearfix'
      );

    case "time-picker":
      return array(
        fInput("text", $custom, $custom_value, "edit time-picker", "", "", "22", "", $custom_id),
        'glz_custom_time-picker_field'
      );

    case "custom-script":
      global $custom_scripts_path;
      return array(
        glz_custom_script($custom_scripts_path."/".reset($arr_custom_field_values), $custom, $custom_id, $custom_value),
        'glz_custom_field_script'
      );

    // a type has been passed that is not supported yet
    default:
      return array(
        glz_custom_fields_gTxt('type_not_supported'),
        'glz_custom_field'
      );
  }
}


// -------------------------------------------------------------
// had to duplicate the default selectInput() because trimming \t and \n didn't work + some other mods & multi-select
function glz_selectInput($name = '', $id = '', $arr_values = '', $custom_value = '', $default_value = '', $multi = '') {
  if ( is_array($arr_values) ) {
    global $prefs;
    $out = array();

    // if there is no custom_value coming from the article, let's use our default one
    if ( empty($custom_value) )
      $custom_value = $default_value;

    foreach ($arr_values as $key => $value) {
      $selected = glz_selected_checked('selected', $key, $custom_value, $default_value);
      $out[] = "<option value=\"$key\"{$selected}>$value</option>";
    }

    // we'll need the extra attributes as well as a name that will produce an array
    if ($multi) {
      $multi = ' multiple="multiple" size="'.$prefs['multiselect_size'].'"';
      $name .= "[]";
    }

    return "<select id=\"".glz_idify($id)."\" name=\"$name\" class=\"list\"$multi>".
      ($default_value ? '' : "<option value=\"\"$selected>&nbsp;</option>").
      ( $out ? join('', $out) : '').
      "</select>";
  }
  else
    return glz_custom_fields_gTxt('field_problems', array('{custom_set_name}' => $name));
}


// -------------------------------------------------------------
// had to duplicate the default checkbox() to keep the looping in here and check against existing value/s
function glz_checkbox($name = '', $arr_values = '', $custom_value = '', $default_value = '') {
  if ( is_array($arr_values) ) {
    $out = array();

    // if there is no custom_value coming from the article, let's use our default one
    if ( empty($custom_value) )
      $custom_value = $default_value;

    foreach ( $arr_values as $key => $value ) {
      $checked = glz_selected_checked('checked', $key, $custom_value);

      // Putting an additional span around the input and label combination so the two can be floated together as a pair for left-right, left-right,... arrangement of checkboxes and radio buttons. Thanks Julian!
      $out[] = "<span><input type=\"checkbox\" name=\"{$name}[]\" value=\"$key\" class=\"checkbox\" id=\"".glz_idify($key)."\"{$checked} /><label for=\"".glz_idify($key)."\">$value</label></span><br />";
    }

    return join('', $out);
  }
  else
    return glz_custom_fields_gTxt('field_problems', array('{custom_set_name}' => $name));
}


// -------------------------------------------------------------
// had to duplicate the default radio() to keep the looping in here and check against existing value/s
function glz_radio($name = '', $id = '', $arr_values = '', $custom_value = '', $default_value = '') {
  if ( is_array($arr_values) ) {
    $out = array();

    // if there is no custom_value coming from the article, let's use our default one
    if ( empty($custom_value) )
      $custom_value = $default_value;

    foreach ( $arr_values as $key => $value ) {
      $checked = glz_selected_checked('checked', $key, $custom_value);

      // Putting an additional span around the input and label combination so the two can be floated together as a pair for left-right, left-right,... arrangement of checkboxes and radio buttons. Thanks Julian!
      $out[] = "<span><input type=\"radio\" name=\"$name\" value=\"$key\" class=\"radio\" id=\"{$id}_".glz_idify($key)."\"{$checked} /><label for=\"{$id}_".glz_idify($key)."\">$value</label></span><br />";
    }

    return join('', $out);
  }
  else
    return glz_custom_fields_gTxt('field_problems', array('{custom_set_name}' => $name));
}


// -------------------------------------------------------------
// checking if this custom field has selected or checked values
function glz_selected_checked($nomenclature, $value, $custom_value = '') {
  // we're comparing against a key which is a "clean" value
  $custom_value = htmlspecialchars($custom_value);

  // make an array if $custom_value contains multiple values
  if ( strpos($custom_value, '|') )
    $arr_custom_value = explode('|', $custom_value);

  if ( isset($arr_custom_value) )
    $out = ( in_array($value, $arr_custom_value) ) ? " $nomenclature=\"$nomenclature\"" : "";
  else
    $out = ($value == $custom_value) ? " $nomenclature=\"$nomenclature\"" : "";

  return $out;
}


//-------------------------------------------------------------
// button gets more consistent styling across browsers rather than input type="submit"
// included in this plugin until in makes it into TXP - if that ever happens...
function glz_fButton($type, $name, $contents='Submit', $value, $class='', $id='', $title='', $onClick='', $disabled = false) {
  $o  = '<button type="'.$type.'" name="'.$name.'"';
  $o .= ' value="'.htmlspecialchars($value).'"';
  $o .= ($class)    ? ' class="'.$class.'"' : '';
  $o .= ($id)       ? ' id="'.$id.'"' : '';
  $o .= ($title)    ? ' title="'.$title.'"' : '';
  $o .= ($onClick)  ? ' onclick="'.$onClick.'"' : '';
  $o .= ($disabled) ? ' disabled="disabled"' : '';
  $o .= '>';
  $o .= $contents;
  $o .= '</button>';
  return $o;
}


//-------------------------------------------------------------
// evals a PHP script and displays output right under the custom field label
function glz_custom_script($script, $custom, $custom_id, $custom_value) {
  if ( is_file($script) ) {
    include_once($script);
    $custom_function = basename($script, ".php");
    if ( is_callable($custom_function) ) {
      return call_user_func_array($custom_function, array($custom, $custom_id, $custom_value));
    }
    else
      return glz_custom_fields_gTxt('not_callable', array('{function}' => $custom_function, '{file}' => $script));
  }
  else
    return glz_custom_fields_gTxt('not_found', array('{file}' => $script));

}


// -------------------------------------------------------------
// PHP4 doesn't come with array_combine... Thank you redbot!
function php4_array_combine($keys, $values) {
  $result = array(); // initializing the array

  foreach ( array_map(null, $keys, $values) as $pair ) {
    $result[$pair[0]] = $pair[1];
  }

  return $result;
}


// -------------------------------------------------------------
// converts all values into id safe ones
function glz_idify($value) {
  $patterns[0] = "/\s/";
  $replacements[0] = "-";
  $patterns[1] = "/[^a-zA-Z0-9\-]/";
  $replacements[1] = "";

  return preg_replace($patterns, $replacements, strtolower($value));
}


// -------------------------------------------------------------
// strips slashes in arrays, used in conjuction with e.g. array_map
function glz_array_stripslashes(&$value) {
  return stripslashes($value);
}


// -------------------------------------------------------------
// returns all sections/categories that are searchable
function glz_all_searchable_sections_categories($type) {
  $type = (in_array($type, array('category', 'section')) ? $type : 'section');
  $condition = "";

  if ( $type == "section" )
    $condition .= "searchable='1'";
  else
    $condition .= "name <> 'root' AND type='article'";

  $result = safe_rows('*', "txp_{$type}", $condition);

  $out = array();
  foreach ($result as $value) {
    $out[$value['name']] = $value['title'];
  }

  return $out;
}

// -------------------------------------------------------------
// will leave only [A-Za-z0-9 ] in the string
function glz_clean_string($string) {
  if ($string)
    return preg_replace('/[^A-Za-z0-9\s\_\-]/', '', $string);
}





// -------------------------------------------------------------
// replaces the default custom fields under write tab
function glz_custom_fields_replace($event, $step, $data, $rs) {
  global $all_custom_sets, $date_picker;
  // get all custom fields & keep only the ones which are set, filter by step
  $arr_custom_fields = glz_check_custom_set($all_custom_sets, $step);

  // DEBUG
  // dmp($arr_custom_fields);

  $out = ' ';

  if ( is_array($arr_custom_fields) && !empty($arr_custom_fields) ) {
    // get all custom fields values for this article
    $arr_article_customs = glz_custom_fields_MySQL("article_customs", glz_get_article_id(), '', $arr_custom_fields);

    // DEBUG
    // dmp($arr_article_customs);

    if ( is_array($arr_article_customs) )
      extract($arr_article_customs);

    // let's see which custom fields are set
    foreach ( $arr_custom_fields as $custom => $custom_set ) {
      // get all possible/default value(s) for this custom set from custom_fields table
      $arr_custom_field_values = glz_custom_fields_MySQL("values", $custom, '', array('custom_set_name' => $custom_set['name']));

      // DEBUG
      // dmp($arr_custom_field_values);

      //custom_set formatted for id e.g. custom_1_set => custom-1 - don't ask...
      $custom_id = glz_custom_number($custom, "-");
      //custom_set without "_set" e.g. custom_1_set => custom_1
      $custom = glz_custom_number($custom);

      // if current article holds no value for this custom field and we have no default value, make it empty
      $custom_value = (!empty($$custom) ? $$custom : '');
      // DEBUG
      // dmp("custom_value: {$custom_value}");

      // check if there is a default value
      // if there is, strip the { }
      $default_value = glz_return_clean_default(glz_default_value($arr_custom_field_values));
      // DEBUG
      // dmp("default_value: {$default_value}");

      // now that we've found our default, we need to clean our custom_field values
      if (is_array($arr_custom_field_values))
        array_walk($arr_custom_field_values, "glz_clean_default_array_values");

      // DEBUG
      // dmp($arr_custom_field_values);

      // the way our custom field value is going to look like
      list($custom_set_value, $custom_class) = glz_format_custom_set_by_type($custom, $custom_id, $custom_set['type'], $arr_custom_field_values, $custom_value, $default_value);

      // DEBUG
      // dmp($custom_set_value);

      $out .= graf(
        "<label for=\"$custom_id\">{$custom_set['name']}</label><br />$custom_set_value", " class=\"$custom_class\""
      );
    }
  }

  // DEBUG
  // dmp($out);

  // if we're writing textarea custom fields, we need to include the excerpt as well
  if ($step == "body") {
    $out = $data.$out;
  }

  return $out;
}


// -------------------------------------------------------------
// prep our custom fields for the db (watch out for multi-selects, checkboxes & radios, they might have multiple values)
function glz_custom_fields_before_save() {
  // keep only the custom fields
  foreach ($_POST as $key => $value) {
    //check for custom fields with multiple values e.g. arrays
    if ( strstr($key, 'custom_') && is_array($value) ) {
      $value = implode($value, '|');
      // feed our custom fields back into the $_POST
      $_POST[$key] = $value;
    }
  }
  // DEBUG
  // dmp($_POST);
}


// -------------------------------------------------------------
// adds the css & js we need
function glz_custom_fields_css_js() {
  global $glz_notice, $date_picker, $time_picker, $prefs;

  // here come our custom stylesheetz
  $css = '<link rel="stylesheet" type="text/css" media="all" href="//'.$prefs['siteurl'].'/plugins/glz_custom_fields/glz_custom_fields.css">'.n;
  // and here come our javascriptz
  $js = '';
  if ( $date_picker ) {
    $css .= '<link rel="stylesheet" type="text/css" media="all" href="'.$prefs['datepicker_url'].'/datePicker.css" />'.n;
    foreach (array('date.js', 'datePicker.js') as $file) {
      $js .= '<script type="text/javascript" src="'.$prefs['datepicker_url']."/".$file.'"></script>'.n;
    }
    $js .= <<<EOF
<script type="text/javascript">
$(function() {
  if ($(".date-picker").length > 0) {
    try {
      Date.firstDayOfWeek = {$prefs['datepicker_first_day']};
      Date.format = '{$prefs['datepicker_format']}';
      Date.fullYearStart = '19';
      $(".date-picker").datePicker({startDate:'{$prefs['datepicker_start_date']}'});
    } catch(err) {
      $('#messagepane').html('<a href="//{$prefs['siteurl']}/textpattern/?event=plugin_prefs.glz_custom_fields">Fix the DatePicker jQuery plugin</a>');
    }
  }
});
</script>
EOF;
  }
  if ( $time_picker ) {
    $css .= '<link rel="stylesheet" type="text/css" media="all" href="'.$prefs['timepicker_url'].'/timePicker.css" />'.n;
    $js .= '<script type="text/javascript" src="'.$prefs['timepicker_url'].'/timePicker.js"></script>'.n;
    $js .= <<<EOF
<script type="text/javascript">
$(function() {
  if ($(".time-picker").length > 0) {
    try {
      $(".time-picker").timePicker({
        startTime:'{$prefs['timepicker_start_time']}',
        endTime: '{$prefs['timepicker_end_time']}',
        step: {$prefs['timepicker_step']},
        show24Hours: {$prefs['timepicker_show_24']}
      });
    } catch(err) {
      $('#messagepane').html('<a href="//{$prefs['siteurl']}/textpattern/?event=plugin_prefs.glz_custom_fields">Fix the TimePicker jQuery plugin</a>');
    }
  }
});
</script>
EOF;
  }
  $js .= '<script type="text/javascript" src="//'.$prefs['siteurl'].'/plugins/glz_custom_fields/glz_custom_fields.js"></script>';

  // displays the notices we have gathered throughout the entire plugin
  if ( count($glz_notice) > 0 ) {
    // let's turn our notices into a string
    $glz_notice = join("<br />", array_unique($glz_notice));

    $js .= '<script type="text/javascript">
    <!--//--><![CDATA[//><!--

    $(document).ready(function() {
      // add our notices
      $("#messagepane").html(\''.$glz_notice.'\');
    });
    //--><!]]>
    </script>';
  }

  echo $js.n.t.$css.n.t;
}


// -------------------------------------------------------------
// we are setting up the pre-requisite values for glz_custom_fields
function before_glz_custom_fields() {
  // we will be reusing these globals across the whole plugin
  global $all_custom_sets, $glz_notice, $prefs, $date_picker, $time_picker;

  // glz_notice collects all plugin notices
  $glz_notice = array();

  // let's get all custom field sets from prefs
  $all_custom_sets = glz_custom_fields_MySQL("all");

  // let's see if we have a date-picker custom field (first of the special ones)
  $date_picker = glz_custom_fields_MySQL("custom_set_exists", "date-picker");

  // let's see if we have a time-picker custom field
  $time_picker = glz_custom_fields_MySQL("custom_set_exists", "time-picker");
}


// -------------------------------------------------------------
// bootstrapping routines, run through plugin_lifecycle
function glz_custom_fields_install() {
  global $all_custom_sets, $glz_notice, $prefs;

  // default custom fields are set to custom_set
  // need to change this because it confuses our set_types()
  safe_query("
    UPDATE
      `".PFX."txp_prefs`
    SET
      `html` = 'text_input'
    WHERE
      `event` = 'custom'
    AND
      `html` = 'custom_set'
  ");

  // set plugin preferences
  $arr_plugin_preferences = array(
    'values_ordering'       => "custom",
    'multiselect_size'      => "5",
    'datepicker_url'        => hu."plugins/glz_custom_fields/jquery.datePicker",
    'datepicker_format'     => "dd/mm/yyyy",
    'datepicker_first_day'  => 1,
    'datepicker_start_date' => "01/01/1990",
    'timepicker_url'        => hu."plugins/glz_custom_fields/jquery.timePicker",
    'timepicker_start_time' => "00:00",
    'timepicker_end_time'   => "23:30",
    'timepicker_step'       => 30,
    'timepicker_show_24'    => true,
    'custom_scripts_path'   => $prefs['path_to_site']."/plugins/glz_custom_fields"
  );
  glz_custom_fields_MySQL("update_plugin_preferences", $arr_plugin_preferences);

  // let's update plugin preferences, make sure they won't appear under Admin > Preferences
  safe_query("
    UPDATE
      `".PFX."txp_prefs`
    SET
      `type` = '10'
    WHERE
      `event` = 'glz_custom_f'
  ");

  // if we don't have a search section, let's create it because we'll need it when searching by custom fields
  if( !getRow("SELECT name FROM `".PFX."txp_section` WHERE name='search'") ) {
    safe_query("
      INSERT INTO
        `".PFX."txp_section` (`name`, `page`, `css`, `in_rss`, `on_frontpage`, `searchable`, `title`)
      VALUES
        ('search', 'default', 'default', '0', '0', '0', 'Search')
    ");
    // add a notice that search section has bee created
    $glz_notice[] = glz_custom_fields_gTxt("search_section_created");
  }

  // if we don't have the custom_fields table, let's create it
  if ( !getRows("SHOW TABLES LIKE '".PFX."custom_fields'") ) {
    safe_query("
      CREATE TABLE `".PFX."custom_fields` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL default '',
        `value` varchar(255) NOT NULL default '',
        PRIMARY KEY (id),
        KEY (`name`)
      ) ENGINE=MyISAM
    ");
  }
  else {
    // if there isn't and id column, add it
    if ( !getRows("SHOW COLUMNS FROM ".PFX."custom_fields LIKE 'id'") ) {
      safe_query("
        ALTER TABLE `".PFX."custom_fields`
          ADD `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT KEY
      ");
    }

   // if we have definitely migrated using this function, skip everything
   if ( isset($prefs['migrated']) )
     return;
   // abort the migration if there are values in custom_fields table, we don't want to overwrite anything
   else if ( glz_custom_fields_MySQL('check_migration') > 0 ) {
     // DEBUG
     // dmp(glz_custom_fields_MySQL('check_migration'));
     $glz_notice[] = glz_custom_fields_gTxt("migration_skip");
     // make a note of this migration in txp_prefs
     glz_custom_fields_MySQL('mark_migration');
     return;
   }

   // go through all values in custom field columns in textpattern table one by one
   foreach ($all_custom_sets as $custom => $custom_set) {
     // check only for custom fields that have been set
     if ( $custom_set['name'] ) {
       // get all existing custom values for ALL articles
       $all_values = glz_custom_fields_MySQL('all_values', glz_custom_number($custom), '', array('custom_set_name' => $custom_set['name'], 'status' => 0));
       // if we have results, let's create SQL queries that will add them to custom_fields table
       if ( count($all_values) > 0 ) {
         // initialize insert
         $insert = '';
         foreach ( $all_values as $escaped_value => $value ) {
           // don't insert empty values or values that are over 255 characters
           // values over 255 characters hint to a textarea custom field
           if ( !empty($escaped_value) && strlen($escaped_value) < 255 )
             // if this is the last value, query will have to be different
             $insert .= ( end($all_values) != $value ) ?
               "('{$custom}','{$escaped_value}')," :
               "('{$custom}','{$escaped_value}')";
         }
         $query = "
           INSERT INTO
             `".PFX."custom_fields` (`name`,`value`)
           VALUES
             {$insert}
         ";
         if ( isset($query) && !empty($query) ) {
           // create all custom field values in custom_fields table
           safe_query($query);
           // update the type of this custom field to select (might want to make this user-adjustable at some point)
           glz_custom_fields_MySQL("update", $custom, PFX."txp_prefs", array(
             'custom_set_name'   => $custom_set['name'],
             'custom_set_type'   => "select",
             'custom_set_position' => $custom_set['position']
           ));
           $glz_notice[] = glz_custom_fields_gTxt("migration_success");
         }
       }
     }
   }

   // make a note of this migration in txp_prefs
   glz_custom_fields_MySQL('mark_migration');
  }
}





global $event;

// globals, expensive operations mostly
before_glz_custom_fields();

if (@txpinterface == "admin") {

  // INSTALL ROUTINES
  // checks if all tables exist and everything is setup properly
  add_privs('glz_custom_fields_install', "1");
  register_callback("glz_custom_fields_install", "plugin_lifecycle.glz_custom_fields", "installed");

  // we'll be doing this only on the pages that we care about, not everywhere
  if ( in_array($event, array("article", "prefs", "glz_custom_fields", "plugin_prefs.glz_custom_fields")) ) {
    // we need some stylesheets & JS
    add_privs('glz_custom_fields_css_js', "1,2,3,4,5,6");
    register_callback('glz_custom_fields_css_js', "admin_side", 'head_end');

    // we need to make sure that all custom field values will be converted to strings first - think checkboxes & multi-selects etc.
    if ( (gps("step") == "edit") || (gps("step") == "create") ) {
      add_privs('glz_custom_fields_before_save', "1,2,3,4,5,6");
      register_callback('glz_custom_fields_before_save', "article", '', 1);
    }
  }

  // Custom Fields tab under Extensions
  add_privs('glz_custom_fields', "1,2");
  register_tab("extensions", 'glz_custom_fields', "Custom Fields");
  register_callback('glz_custom_fields', "glz_custom_fields");

  // plugin preferences
  add_privs('plugin_prefs.glz_custom_fields', "1,2");
  register_callback('glz_custom_fields_preferences', 'plugin_prefs.glz_custom_fields');


  // YES, finally the default custom fields are replaced by the new, pimped ones : )
  add_privs('glz_custom_fields_replace', "1,2,3,4,5,6");
  register_callback('glz_custom_fields_replace', 'article_ui', 'custom_fields');
  // YES, now we have textarea custom fields as well ; )
  register_callback('glz_custom_fields_replace', 'article_ui', 'body');
}

// -------------------------------------------------------------
// everything is happening in this function... generates the content for Extensions > Custom Fields
function glz_custom_fields() {
  global $event, $all_custom_sets, $glz_notice, $prefs;

  // we have $_POST, let's see if there is any CRUD
  if ( $_POST ) {
    $incoming = stripPost();
    // DEBUG
    // die(dmp($incoming));
    extract($incoming);

    // create an empty $value if it's not set in the $_POST
    if ( !isset($value) )
      $value = '';

    // we are deleting a new custom field
    if ( gps('delete') ) {
      glz_custom_fields_MySQL("delete", $custom_set, PFX."txp_prefs");
      glz_custom_fields_MySQL("delete", $custom_set, PFX."txp_lang");
      glz_custom_fields_MySQL("delete", $custom_set, PFX."custom_fields");

      glz_custom_fields_MySQL("delete", glz_custom_number($custom_set), PFX."textpattern");

      $glz_notice[] = glz_custom_fields_gTxt("deleted", array('{custom_set_name}' => $custom_set_name));
    }

    // we are resetting one of the mighty 10
    if ( gps('reset') ) {
      glz_custom_fields_MySQL("reset", $custom_set, PFX."txp_prefs");
      glz_custom_fields_MySQL("delete", $custom_set, PFX."custom_fields");

      glz_custom_fields_MySQL("reset", glz_custom_number($custom_set), PFX."textpattern", array(
        'custom_set_type' => $custom_set_type,
        'custom_field' => glz_custom_number($custom_set)
      ));

      $glz_notice[] = glz_custom_fields_gTxt("reset", array('{custom_set_name}' => $custom_set_name));
    }

    // we are adding a new custom field
    if ( gps("custom_field_number") ) {
      $custom_set_name = gps("custom_set_name");

      // if no name was specified, abort
      if ( !$custom_set_name )
        $glz_notice[] = glz_custom_fields_gTxt("no_name");
      else {
        $custom_set_name = glz_clean_string($custom_set_name);

        $name_exists = glz_check_custom_set_name($all_custom_sets, $custom_set_name);

        // if name doesn't exist
        if ( $name_exists == FALSE ) {
          glz_custom_fields_MySQL("new", $custom_set_name, PFX."txp_prefs", array(
            'custom_field_number' => $custom_field_number,
            'custom_set_type'     => $custom_set_type,
            'custom_set_position' => $custom_set_position
          ));
          glz_custom_fields_MySQL("new", $custom_set_name, PFX."txp_lang", array(
            'custom_field_number' => $custom_field_number,
            'lang'                => $GLOBALS['prefs']['language']
          ));
          glz_custom_fields_MySQL("new", $custom_set_name, PFX."textpattern", array(
            'custom_field_number' => $custom_field_number,
            'custom_set_type' => $custom_set_type
          ));
          // there are custom fields for which we do not need to touch custom_fields table
          if ( !in_array($custom_set_type, array("textarea", "text_input")) ) {
            glz_custom_fields_MySQL("new", $custom_set_name, PFX."custom_fields", array(
              'custom_field_number' => $custom_field_number,
              'value'               => $value
            ));
          }

          $glz_notice[] = glz_custom_fields_gTxt("created", array('{custom_set_name}' => $custom_set_name));
        }
        // name exists, abort
        else
          $glz_notice[] = glz_custom_fields_gTxt("exists", array('{custom_set_name}' => $custom_set_name));
      }
    }

    // we are editing an existing custom field
    if ( gps('save') ) {
      if ( !empty($custom_set_name) ) {
        $custom_set_name = glz_clean_string($custom_set_name);
        $name_exists = glz_check_custom_set_name($all_custom_sets, $custom_set_name, $custom_set);
        // if name doesn't exist we'll need to create a new custom_set
        if ( $name_exists == FALSE ) {
          glz_custom_fields_MySQL("update", $custom_set, PFX."txp_prefs", array(
            'custom_set_name'     => $custom_set_name,
            'custom_set_type'     => $custom_set_type,
            'custom_set_position' => $custom_set_position
          ));

          // custom sets need to be changed based on their type
          glz_custom_fields_MySQL("update", $custom_set, PFX."textpattern", array(
            'custom_set_type' => $custom_set_type,
            'custom_field' => glz_custom_number($custom_set)
          ));

          // for textareas we do not need to touch custom_fields table
          if ( $custom_set_type != "textarea" ) {
            glz_custom_fields_MySQL("delete", $custom_set, PFX."custom_fields");
            glz_custom_fields_MySQL("new", $custom_set_name, PFX."custom_fields", array(
              'custom_set'  => $custom_set,
              'value'       => $value
            ));
          }

          $glz_notice[] = glz_custom_fields_gTxt("updated", array('{custom_set_name}' => $custom_set_name));
        }
        // name exists, abort
        else
          $glz_notice[] = glz_custom_fields_gTxt("exists", array('{custom_set_name}' => $custom_set_name));
      }
      else
        $glz_notice[] = glz_custom_fields_gTxt('no_name');
    }

    // need to re-fetch data since things modified
    $all_custom_sets = glz_custom_fields_MySQL("all");

  }

  pagetop("Custom Fields");

  // the table with all custom fields follows
  echo
    n.'<div class="listtables">'.n.
    '  <table class="txp-list glz_custom_fields">'.n.
    '    <thead>'.n.
    '      <tr>'.n.
    '        <th>Position</th>'.n.
    '        <th>Name</th>'.n.
    '        <th>Type</th>'.n.
    '        <th>&nbsp;</th>'.n.
    '      </tr>'.n.
    '    </thead>'.n.
    '    <tbody>'.n;

  // looping through all our custom fields to build the table
  $i = 0;
  foreach ( $all_custom_sets as $custom => $custom_set ) {
    // first 10 fields cannot be deleted, just reset
    if ( $i < 10 ) {
      // can't reset a custom field that is not set
      $reset_delete = ( $custom_set['name'] ) ?
        glz_form_buttons("reset", "Reset", $custom, htmlspecialchars($custom_set['name']), $custom_set['type'], '', 'return confirm(\'By proceeding you will RESET ALL data in `textpattern` and `custom_fields` tables for `'.$custom.'`. Are you sure?\');') :
        NULL;
    }
    else {
      $reset_delete = glz_form_buttons("delete", "Delete", $custom, htmlspecialchars($custom_set['name']), $custom_set['type'], '', 'return confirm(\'By proceeding you will DELETE ALL data in `textpattern` and `custom_fields` tables for `'.$custom.'`. Are you sure?\');');
    }

    $edit = glz_form_buttons("edit", "Edit", $custom, htmlspecialchars($custom_set['name']), $custom_set['type'], $custom_set['position']);

    echo
    '      <tr>'.n.
    '        <td class="custom_set_position">'.$custom_set['position'].'</td>'.n.
    '        <td class="custom_set_name">'.$custom_set['name'].'</td>'.n.
    '        <td class="type">'.(($custom_set['name']) ? glz_custom_fields_gTxt($custom_set['type']) : '').'</td>'.n.
    '        <td class="events">'.$reset_delete.sp.$edit.'</td>'.n.
    '      </tr>'.n;

    $i++;
  }

  echo
    '    </tbody>'.n.
    '  </table>'.n;
    '</div>'.n;

  // the form where custom fields are being added/edited
  $legend = gps('edit') ?
    'Edit '.gps('custom_set') :
    'Add new custom field';

  $custom_field = gps('edit') ?
    '<input name="custom_set" value="'.gps('custom_set').'" type="hidden" />' :
    '<input name="custom_field_number" value="'.glz_custom_next($all_custom_sets).'" type="hidden" />';

  $custom_set = gps('edit') ?
    gps('custom_set') :
    NULL;

  $custom_name = gps('edit') ?
    gps('custom_set_name') :
    NULL;

  $custom_set_position = gps('edit') ?
    gps('custom_set_position') :
    NULL;

  $arr_custom_set_types = glz_custom_set_types();

  $custom_set_types = NULL;
  foreach ( $arr_custom_set_types as $custom_type_group => $custom_types ) {
    $custom_set_types .= '<optgroup label="'.ucfirst($custom_type_group).'">'.n;
    foreach ($custom_types as $custom_type) {
      $selected = ( gps('edit') && gps('custom_set_type') == $custom_type ) ?
        ' selected="selected"' :
        NULL;
      $custom_set_types .= '<option value="'.$custom_type.'"'.$selected.'>'.glz_custom_fields_gTxt($custom_type).'</option>'.n;
    }
    $custom_set_types .= '</optgroup>'.n;
  }
  // fetching the values for this custom field
  if ( gps('edit') ) {
    if ( $custom_set_type == "text_input" )
      $arr_values = glz_custom_fields_MySQL('all_values', glz_custom_number($custom_set), '', array('custom_set_name' => $custom_set_name, 'status' => 4));
    else
      $arr_values = glz_custom_fields_MySQL("values", $custom_set, '', array('custom_set_name' => $custom_set_name));

    $values = ( $arr_values ) ?
      implode("\r\n", $arr_values) :
      '';
  }
  else
    $values = '';

  $action = gps('edit') ?
    '<input name="save" value="Save" type="submit" class="submit" />' :
    '<input name="add_new" value="Add new" type="submit" class="submit" />';
  // this needs to be different for a script
  $value = ( isset($custom_set_type) && $custom_set_type == "custom-script" ) ?
    '<input type="text" name="value" id="value" value="'.$values.'" class="left"/><span class="right"><em>Relative path from your website\'s public folder</em></span>' :
    '<textarea name="value" id="value" class="left">'.$values.'</textarea><span class="right"><em>Each value on a separate line</em> <br /><em>One {default} value allowed</em></span>';

  // ok, all is set, let's build the form
  echo
    '<form method="post" action="index.php" id="add_edit_custom_field">'.n.
    '<input name="event" value="glz_custom_fields" type="hidden" />'.n.
    $custom_field.n.
    '<fieldset>'.n.
    ' <legend>'.$legend.'</legend>'.n.
    ' <p class="clearfix">
        <label for="custom_set_name" class="left">Name:</label>
        <input type="text" name="custom_set_name" value="'.htmlspecialchars($custom_name).'" id="custom_set_name" class="left" />
        <span class="right"><em>Only word characters allowed</em></span>
      </p>'.n.
    ' <p class="clearfix">
        <label for="custom_set_type" class="left">Type:</label>
        <select name="custom_set_type" id="custom_set_type" class="left">
    '.      $custom_set_types.'
        </select>
      </p>'.n.
    ' <p class="clearfix">
        <label for="custom_set_position" class="left">Position:</label>
        <input type="text" name="custom_set_position" value="'.htmlspecialchars($custom_set_position).'" id="custom_set_position" class="left" />
        <span class="right"><em>Automatically assigned if blank</em></span>
      </p>'.n.
    ' <p class="clearfix">
        <label for="value" class="left">Value:</label>
    '.  $value.'
      </p>'.n.
    ' '.$action.n.
    '</fieldset>'.n.
    '</form>'.n;
}


// -------------------------------------------------------------
// glz_custom_fields preferences
function glz_custom_fields_preferences() {
  global $event, $glz_notice;

  if ( $_POST && gps('save') ) {
    glz_custom_fields_MySQL("update_plugin_preferences", $_POST['glz_custom_fields_prefs']);
    $glz_notice[] = glz_custom_fields_gTxt("preferences_updated");
    // need to re-fetch from db because this has changed since $prefs has been populated
  }
  $current_preferences = glz_custom_fields_MySQL('plugin_preferences');

  pagetop("glz_custom_fields Preferences");

  // custom_fields
  $arr_values_ordering = array(
    'ascending'   => "Ascending",
    'descending'  => "Descending",
    'custom'      => "As entered"
  );
  $values_ordering = '<select name="glz_custom_fields_prefs[values_ordering]" id="glz_custom_fields_prefs_values_ordering">';
  foreach ( $arr_values_ordering as $value => $title ) {
    $selected = ($current_preferences['values_ordering'] == $value) ? ' selected="selected"' : '';
    $values_ordering .= "<option value=\"$value\"$selected>$title</option>";
  }
  $values_ordering .= "</select>";
  $multiselect_size = '<input type="text" name="glz_custom_fields_prefs[multiselect_size]" id="glz_custom_fields_prefs_multiselect_size" value="'.$current_preferences['multiselect_size'].'" />';
  $custom_scripts_path_error = ( @fopen($current_preferences['custom_scripts_path'], "r") ) ?
    '' :
    '<br /><em class="red">Folder does not exist, please create it.</em>';

  // jquery.datePicker
  $datepicker_url_error = ( @fopen($current_preferences['datepicker_url']."/datePicker.js", "r") ) ?
    '' :
    '<br /><em class="red">Folder does not exist, please create it.</em>';
  $arr_date_format = array("dd/mm/yyyy", "mm/dd/yyyy", "yyyy-mm-dd", "dd mm yy");
  $date_format = '<select name="glz_custom_fields_prefs[datepicker_format]" id="glz_custom_fields_prefs_datepicker_format">';
  foreach ( $arr_date_format as $format ) {
    $selected = ($current_preferences['datepicker_format'] == $format) ? ' selected="selected"' : '';
    $date_format .= "<option value=\"$format\"$selected>$format</option>";
  }
  $date_format .= "</select>";

  $arr_days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
  $first_day = '<select name="glz_custom_fields_prefs[datepicker_first_day]" id="glz_custom_fields_prefs_datepicker_first_day">';
  foreach ( $arr_days as $key => $day ) {
    $selected = ($current_preferences['datepicker_first_day'] == $key) ? ' selected="selected"' : '';
    $first_day .= "<option value=\"$key\"$selected>$day</option>";
  }
  $first_day .= "</select>";

  $start_date = '<input type="text" name="glz_custom_fields_prefs[datepicker_start_date]" id="glz_custom_fields_prefs_datepicker_start_date" value="'.$current_preferences['datepicker_start_date'].'" />';

  // jquery.timePicker
  $timepicker_url_error = ( @fopen($current_preferences['timepicker_url']."/timePicker.js", "r") ) ?
    '' :
    '<br /><em class="red">Folder does not exist, please create it.</em>';
  $arr_time_format = array('true' => "24 hours", 'false' => "12 hours");
  $show_24 = '<select name="glz_custom_fields_prefs[timepicker_show_24]" id="glz_custom_fields_prefs_timepicker_show_24">';
  foreach ( $arr_time_format as $value => $title ) {
    $selected = ($current_preferences['timepicker_show_24'] == $value) ? ' selected="selected"' : '';
    $show_24 .= "<option value=\"$value\"$selected>$title</option>";
  }
  $show_24 .= "</select>";

  $out = <<<EOF
<form action="index.php" method="post">
<table id="list" class="glz_custom_fields_prefs" cellpadding="0" cellspacing="0" align="center">
  <tbody>
    <tr class="heading">
      <td colspan="2"><h2 class="pref-heading">Custom Fields</h2></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_values_ordering">Order for custom field values</th>
      <td>{$values_ordering}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_multiselect_size">Multi-select field size</th>
      <td>{$multiselect_size}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_custom_scripts_path">Custom scripts path</th>
      <td><input type="text" name="glz_custom_fields_prefs[custom_scripts_path]" id="glz_custom_fields_prefs_custom_scripts_path" value="{$current_preferences['custom_scripts_path']}" />{$custom_scripts_path_error}</td>
    </tr>

    <tr class="heading">
      <td colspan="2"><h2 class="pref-heading left">Date Picker</h2> <a href="http://www.kelvinluck.com/assets/jquery/datePicker/v2/demo/index.html" title="A flexible unobtrusive calendar component for jQuery" class="right">jQuery datePicker</a></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_datepicker_url">Date Picker plugin URL</th>
      <td><input type="text" name="glz_custom_fields_prefs[datepicker_url]" id="glz_custom_fields_prefs_datepicker_url" value="{$current_preferences['datepicker_url']}" />{$datepicker_url_error}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_datepicker_format">Date format</th>
      <td>{$date_format}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_datepicker_first_day">First day of week</th>
      <td>{$first_day}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_datepicker_start_date">Start date</th>
      <td>{$start_date}<br /><em class="grey">MUST be the same as "Date format"</em></td>
    </tr>

    <tr class="heading">
      <td colspan="2"><h2 class="pref-heading left">Time Picker</h2> <a href="http://labs.perifer.se/timedatepicker/" title="jQuery time picker" class="right">jQuery timePicker</a></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_timepicker_url">Time Picker plugin URL</th>
      <td><input type="text" name="glz_custom_fields_prefs[timepicker_url]" id="glz_custom_fields_prefs_timepicker_url" value="{$current_preferences['timepicker_url']}" />{$timepicker_url_error}</td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_timepicker_start_time">Start time</th>
      <td><input type="text" name="glz_custom_fields_prefs[timepicker_start_time]" id="glz_custom_fields_prefs_timepicker_start_time" value="{$current_preferences['timepicker_start_time']}" /></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_timepicker_end_time">End time</th>
      <td><input type="text" name="glz_custom_fields_prefs[timepicker_end_time]" id="glz_custom_fields_prefs_timepicker_end_time" value="{$current_preferences['timepicker_end_time']}" /></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_timepicker_step">Step</th>
      <td><input type="text" name="glz_custom_fields_prefs[timepicker_step]" id="glz_custom_fields_prefs_timepicker_step" value="{$current_preferences['timepicker_step']}" /></td>
    </tr>
    <tr>
      <th scope="row"><label for="glz_custom_fields_prefs_timepicker_step">Time format</th>
      <td>{$show_24}</td>
    </tr>

    <tr>
      <td colspan="2" class="noline">
        <input class="publish" type="submit" name="save" value="Save" />
        <input type="hidden" name="event" value="plugin_prefs.glz_custom_fields" />
      </td>
    </tr>
  </tbody>
</table>
EOF;

  echo $out;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>glz_custom_fields, unlimited custom fields</h1>
<p>This plugin provides unlimited custom fields in Textpattern. You can create custom fields as selects, multi-selects, checkboxes, radio buttons and textareas - as well as the default text input fields. Predefine values can be set for custom fields and you can select a single default value (selects, multi-selects, checkboxes and radio buttons only).</p>
<p><strong>If you want to submit bug reports and feature requests, please visit <a href="http://github.com/gerhard/glz_custom_fields" title="glz_custom_fields on GitHub">glz_custom_fields on GitHub</a></strong>. There is also a handy section on <a href="http://wiki.github.com/gerhard/glz_custom_fields/" title="glz_custom_fields wiki">glz_custom_fields tips and tricks</a>.</p>
<h2>Time and date pickers</h2>
<p>Time and date pickers are pretty straightforward to use. To set the required JavaScript and styling properly, please refer to the <span class="information">INSTALL</span> file that came with this plugin.</p>
<h2>Custom scripts</h2>
<p>This is a rather complex and long sought-for piece of functionality, please refer to the <span class="information">my_image.php</span> file which contains inline explanations on how they should be used. The file came with the plugin, in the <span class="information">scripts/glz_custom_fields</span> folder.</p>
<h2>Default values</h2>
<p>You can now define a single default value by adding curly brackets around a value like so: <code>{default value}</code>.</p>
<h2>Ranges</h2>
<p>If you want to define ranges, create a new custom field with the following name e.g. <code>Price range &amp;pound;(after)</code>.</p>
<ul>
  <li><code>range</code> - this is really important and <strong class="warning">must</strong> be present for the range to work correctly.</li>
  <li><code>&amp;pound;</code> - don't use straight symbols (like <code>&euro;</code> or <code>&pound;</code>), instead use entity ones e.g. <code>&amp;euro;</code> and <code>&amp;pound;</code> are valid symbols.</li>
  <li><code>(after)</code> - don't leave any spaces after e.g. $ and (after). This basically says where to add the measuring unit (be it a currency or something like m<sup>3</sup> etc.) - <code>(before)</code> is default.</li>
</ul>
<p>Ranges are defined 10-20, 21-30 etc. (no measuring units - they get pulled from the custom set name).</p>
<h2>Support</h2>
<p>If anything goes wrong or you discover a bug:</p>
<ol>
  <li>report an issue on <a href="http://github.com/gerhard/glz_custom_fields/issues" title="glz_custom_fields_public issues" rel="external">GitHub</a>.</li>
  <li>look for help in the Textpatterb forum, <a href="http://forum.textpattern.com/viewtopic.php?id=23996" rel="external">glz_custom_fields thread</a>.</li>
  <li>ping me via <a href="http://www.twitter.com/gerhardlazu" rel="external">Twitter</a>. I'm using it around the clock, it's great for short messages!</li>
  <li>drop a line to <a href="mailto:gerhard@lazu.co.uk?Subject:glz_custom_fields needs your attention" rel="external">gerhard@lazu.co.uk</a> (sometimes things can get really hectic on my end, please be patient).</li>
</ol>
# --- END PLUGIN HELP ---
-->
<?php
}
?>