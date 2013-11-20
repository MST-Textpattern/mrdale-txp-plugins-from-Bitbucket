<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'zem_contact_reborn';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '4.5.0.0';
$plugin['author'] = 'TXP Community';
$plugin['author_uri'] = 'http://forum.textpattern.com/viewtopic.php?id=23728';
$plugin['description'] = 'Form mailer for Textpattern';

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

$plugin['textpack'] = <<<EOT
#@public
zem_contact_checkbox => Checkbox
zem_contact_contact => Contact
zem_contact_email => Email
zem_contact_email_subject => {site} > Inquiry
zem_contact_email_thanks => Thank you, your message has been sent.
zem_contact_field_missing => Required field, &#8220;<strong>{field}</strong>&#8221;, is missing.
zem_contact_form_expired => The form has expired, please try again.
zem_contact_form_used => The form was already submitted, please fill out a new form.
zem_contact_general_inquiry => General inquiry
zem_contact_invalid_email => &#8220;<strong>{email}</strong>&#8221; is not a valid email address.
zem_contact_invalid_host => &#8220;<strong>{host}</strong>&#8221; is not a valid email host.
zem_contact_invalid_utf8 => &#8220;<strong>{field}</strong>&#8221; contains invalid UTF-8 characters.
zem_contact_invalid_value => Invalid value for &#8220;<strong>{field}</strong>&#8221;, &#8220;<strong>{value}</strong>&#8221; is not one of the available options.
zem_contact_mail_sorry => Sorry, unable to send email.
zem_contact_maxval_warning => &#8220;<strong>{field}</strong>&#8221; must not exceed {value}.
zem_contact_max_warning => &#8220;<strong>{field}</strong>&#8221; must not contain more than {value} characters.
zem_contact_message => Message
zem_contact_minval_warning => &#8220;<strong>{field}</strong>&#8221; must be at least {value}.
zem_contact_min_warning => &#8220;<strong>{field}</strong>&#8221; must contain at least {value} characters.
zem_contact_name => Name
zem_contact_option => Option
zem_contact_radio => Radio
zem_contact_recipient => Recipient
zem_contact_refresh => Follow this link if the page does not refresh automatically.
zem_contact_secret => Secret
zem_contact_send => Send
zem_contact_send_article => Send article
zem_contact_spam => We do not accept spam, thank you!
zem_contact_text => Text
zem_contact_to_missing => &#8220;<strong>To</strong>&#8221; email address is missing.
EOT;

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
function zem_contact($atts, $thing = '')
{
	global $sitename, $prefs, $production_status, $zem_contact_from,
		$zem_contact_recipient, $zem_contact_error, $zem_contact_submit,
		$zem_contact_form, $zem_contact_labels, $zem_contact_values, $zem_contact_encrypt, $enable_msg_callback;

	extract(zem_contact_lAtts(array(
		'class'        => 'zemContactForm',
		'copysender'   => 0,
    'alsocopy'     => '',
		'form'		     => '',
    'copy_form'    => '',
    'encrypt' => 0,
    'from'         => '',
		'from_form'    => '',
		'label'        => gTxt('zem_contact_contact'),
		'redirect'     => '',
		'show_error'   => 1,
		'show_input'   => 1,
		'send_article' => 0,
		'subject'      => gTxt('zem_contact_email_subject', array('{site}' => html_entity_decode($sitename,ENT_QUOTES))),
		'subject_form' => '',
    'message_form' => '',
		'html_message_form'	=> '',
		'to'           => '',
		'to_form'      => '',
		'thanks'       => graf(gTxt('zem_contact_email_thanks')),
		'thanks_form'  => '',
		'enable_msg_callback' => 0
	), $atts));

	unset($atts['show_error'], $atts['show_input']);
	$zem_contact_form_id = md5(serialize($atts).preg_replace('/[\t\s\r\n]/','',$thing));
	$zem_contact_submit = (ps('zem_contact_form_id') == $zem_contact_form_id);
    $zem_contact_encrypt = $encrypt;

	if (!is_callable('mail'))
	{
		return ($production_status == 'live') ?
			gTxt('zem_contact_mail_sorry') :
			gTxt('warn_mail_unavailable');
	}

	static $headers_sent = false;
	if (!$headers_sent) {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',time()-3600*24*7).' GMT');
		header('Expires: '.gmdate('D, d M Y H:i:s',time()+600).' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		$headers_sent = true;
	}

	$nonce   = mysql_real_escape_string(ps('zem_contact_nonce'));
	$renonce = false;

	if ($zem_contact_submit)
	{
		safe_delete('txp_discuss_nonce', 'issue_time < date_sub(now(), interval 10 minute)');
		if ($rs = safe_row('used', 'txp_discuss_nonce', "nonce = '$nonce'"))
		{
			if ($rs['used'])
			{
				unset($zem_contact_error);
				$zem_contact_error[] = gTxt('zem_contact_form_used');
				$renonce = true;
				$_POST = array();
				$_POST['zem_contact_submit'] = TRUE;
				$_POST['zem_contact_form_id'] = $zem_contact_form_id;
				$_POST['zem_contact_nonce'] = $nonce;
			}
		}
		else
		{
			$zem_contact_error[] = gTxt('zem_contact_form_expired');
			$renonce = true;
		}
	}

	if ($zem_contact_submit and $nonce and !$renonce)
	{
		$zem_contact_nonce = $nonce;
	}

	elseif (!$show_error or $show_input)
	{
		$zem_contact_nonce = md5(uniqid(rand(), true));
		safe_insert('txp_discuss_nonce', "issue_time = now(), nonce = '$zem_contact_nonce'");
	}

	$form = ($form) ? fetch_form($form) : $thing;

	if (empty($form))
	{
		$form = '
<txp:zem_contact_text label="'.gTxt('zem_contact_name').'" /><br />
<txp:zem_contact_email /><br />'.
($send_article ? '<txp:zem_contact_email send_article="1" label="'.gTxt('zem_contact_recipient').'" /><br />' : '').
'<txp:zem_contact_textarea /><br />
<txp:zem_contact_submit />
';
	}

	$form = parse($form);

	callback_event('zemcontact.parsed');

	if ($to_form)
	{
		$to = parse(fetch_form($to_form));
	}

	if (!$to and !$send_article)
	{
		return gTxt('zem_contact_to_missing');
	}

	$out = '';

	if (!$zem_contact_submit) {
	  # don't show errors or send mail
	}

	elseif (!empty($zem_contact_error))
	{
		if ($show_error or !$show_input)
		{
			$out .= n.'<ul class="zemError">';

			foreach (array_unique($zem_contact_error) as $error)
			{
				$out .= n.t.'<li>'.$error.'</li>';
			}

			$out .= n.'</ul>';

			if (!$show_input) return $out;
		}
	}

	elseif ($show_input and is_array($zem_contact_form))
	{
		/// load and check spam plugins/
		callback_event('zemcontact.submit');
		$evaluation =& get_zemcontact_evaluator();
		$clean = $evaluation->get_zemcontact_status();
		if ($clean != 0) {
			return gTxt('zem_contact_spam');
		}

		if ($from_form)
		{
			$from = parse(fetch_form($from_form));
		}

		if ($subject_form)
		{
			$subject = parse(fetch_form($subject_form));
		}

		$sep = !is_windows() ? "\n" : "\r\n";

		$msg = array();

		foreach ($zem_contact_labels as $name => $label)
		{
			if (!trim($zem_contact_values[$name])) continue;
			$msg[] = $label.': '.$zem_contact_values[$name];
		}

		if ($send_article)
		{
			global $thisarticle;
			$subject = str_replace('&#38;', '&', $thisarticle['title']);
			$msg[] = permlinkurl($thisarticle);
			$msg[] = $subject;
			$s_ar = array('&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8217;', '&#8242;', '&#8243;', '&#8230;', '&#8211;', '&#8212;', '&#215;', '&#8482;', '&#174;', '&#169;', '&lt;', '&gt;', '&quot;', '&amp;', '&#38;', "\t", '<p');
			if ($prefs['override_emailcharset'] and is_callable('utf8_decode')) {
				$r_ar = array("'", "'", '"', '"', "'", "'", '"', '...', '-', '--', 'x', '[tm]', '(r)', '(c)', '<', '>', '"', '&', '&', ' ', "\n<p");
			}
			else
			{
				$r_ar = array('‘', '’', '“', '”', '’', '?', '?', '…', '–', '—', '×', '™', '®', '©', '<', '>', '"', '&', '&', ' ', "\n<p");
			}
			$msg[] = trim(strip_tags(str_replace($s_ar,$r_ar,(trim(strip_tags($thisarticle['excerpt'])) ? $thisarticle['excerpt'] : $thisarticle['body']))));
			if (empty($zem_contact_recipient))
			{
				return gTxt('zem_contact_field_missing', array('{field}' => gTxt('zem_contact_recipient')));
			}
			else
			{
				$to = $zem_contact_recipient;
			}
		}

		$msg = join("\n\n", $msg);
		$msg = str_replace("\r\n", "\n", $msg);
		$msg = str_replace("\r", "\n", $msg);
		$msg = str_replace("\n", $sep, $msg);

		if ($from)
		{
			$reply = $zem_contact_from;
		}

		else
		{
			$from = $zem_contact_from;
			$reply = '';
		}

		$from    = zem_contact_strip($from);
		$to      = zem_contact_strip($to);
		$subject = zem_contact_strip($subject);
		$reply   = zem_contact_strip($reply);
		$msg     = zem_contact_strip($msg, FALSE);

		if ($prefs['override_emailcharset'] and is_callable('utf8_decode'))
		{
			$charset = 'ISO-8859-1';
			$subject = utf8_decode($subject);
			$msg     = utf8_decode($msg);
		}

		else
		{
			$charset = 'UTF-8';
		}

		$content_type = 'text/plain';
		$mime_boundary = '';

		$GLOBALS['hak_wine_hide_cc'] = false;

		$has_plain_msg = !empty($message_form);
		$has_html_msg = !empty($html_message_form);

		if ($has_plain_msg)
		{
			$plain_msg = parse_form($message_form);
		}
		if ($has_html_msg)
		{
			$html_msg = parse_form($html_message_form);
		}

		// block text/html when encrypting due to known enigmail issues
		if ( ($has_plain_msg && $has_html_msg) && !$zem_contact_encrypt)
		{
			$content_type = 'multipart/alternative';
			$mime_boundary = '----------SDLkjasldfkjaldsfn234rlk';

			if ($charset != 'UTF-8' && is_callable('utf8_decode'))
			{
				$plain_msg = utf8_decode($plain_msg);
				$html_msg = utf8_decode($html_msg);
			}

			if ($enable_msg_callback == '1')
			{
				$evaluation->set_zemcontact_msg($plain_msg);
				callback_event('zemcontact.msg');
				$plain_msg = $evaluation->get_zemcontact_msg();

				$evaluation->set_zemcontact_msg($html_msg);
				callback_event('zemcontact.msg');
				$html_msg = $evaluation->get_zemcontact_msg();
			}

			$msg = <<< EOM

--{$mime_boundary}
Content-Type: text/plain; charset={$charset}
Content-Transfer-Encoding: 8bit


$plain_msg


--{$mime_boundary}
Content-Type: text/html; charset={$charset}
Content-Transfer-Encoding: 8bit


$html_msg

--{$mime_boundary}--
EOM;
		}
		// block text/html when encrypting due to known enigmail issues
		else if ($has_html_msg && !$zem_contact_encrypt)
		{
			$content_type = 'text/html';
			$msg = ($charset != 'UTF-8' && is_callable('utf8_decode')) ? utf8_decode($html_msg) : $html_msg;
		}
		else if ($has_plain_msg)
		{
			$content_type = 'text/plain';
			$msg = ($charset != 'UTF-8' && is_callable('utf8_decode')) ? utf8_decode($plain_msg) : $plain_msg;
		}
		else
		{
			$content_type = 'text/plain';
			$msg = join('\n', zem_build_msg_array());
		}

		if ( ($zem_contact_encrypt || !($has_plain_msg && $has_html_msg)) && $enable_msg_callback == '1' )
		{
			$evaluation->set_zemcontact_msg($msg);
			callback_event('zemcontact.msg');
			$msg = $evaluation->get_zemcontact_msg();
		}

		$msg = str_replace("\r\n", "\n", $msg);
		$msg = str_replace("\r", "\n", $msg);
		$msg = str_replace("\n", $sep, $msg);
		$msg     = zem_contact_strip($msg, FALSE);

		$subject = zem_contact_mailheader($subject, 'text');

		$headers = 'From: '.$from.
			($reply ? ($sep.'Reply-To: '.$reply) : '').
			$sep.'X-Mailer: Textpattern (zem_contact_reborn)'.
			$sep.'X-Originating-IP: '.zem_contact_strip((!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].' via ' : '').$_SERVER['REMOTE_ADDR']).
			$sep.'Content-Transfer-Encoding: 8bit'.
			$sep.'Content-Type: '.$content_type.';'.
						(!empty($mime_boundary) ? ' boundary="'.$mime_boundary.'"': ' charset='.$charset);

		safe_update('txp_discuss_nonce', "used = '1', issue_time = now()", "nonce = '$nonce'");

		if (mail($to, $subject, $msg, $headers))
		{
			$_POST = array();

			if ($copysender and $zem_contact_from)
			{
				$GLOBALS['hak_wine_hide_cc'] = true;
        // we want the raw $msg again so empty it out

        unset($msg);

				$mime_boundary = '----------SDLkjasldfkjaldsfn234rlk';

				if ($has_plain_msg)
				{
					$content_type = 'text/plain';
					$plain_msg = parse_form($message_form);
				}
				if ($has_html_msg)
				{
					$content_type = 'text/html';
					$html_msg = parse_form($html_message_form);
				}

				if ($has_plain_msg && $has_html_msg)
				{
					$content_type = 'multipart/alternative';

					if ($charset != 'UTF-8' && is_callable('utf8_decode'))
					{
						$plain_msg = utf8_decode($plain_msg);
						$html_msg = utf8_decode($html_msg);
					}

					$msg = <<< EOM
--{$mime_boundary}
Content-Type: text/plain; charset="$charset"
Content-Transfer-Encoding: 8bit

$plain_msg

--{$mime_boundary}
Content-Type: text/html; charset="$charset"
Content-Transfer-Encoding: 8bit

$html_msg

--{$mime_boundary}--
EOM;

				}
				else if ($has_html_msg)
				{
					$msg = ($charset != 'UTF-8' && is_callable('utf8_decode')) ? utf8_decode($html_msg) : $html_msg;
				}
				else if ($has_plain_msg)
				{
					$msg = ($charset != 'UTF-8' && is_callable('utf8_decode')) ? utf8_decode($plain_msg) : $plain_msg;
				}
				else
				{
	        $msg = zem_build_msg_array();
				}

				$msg = trim($msg);
				$msg = str_replace("\r\n", "\n", $msg);
				$msg = str_replace("\r", "\n", $msg);
				$msg = str_replace("\n", $sep, $msg);
				$msg = zem_contact_strip($msg, FALSE);


				$headers = 'From: '.$from.
					($reply ? ($sep.'Reply-To: '.$reply) : '').
					$sep.'X-Mailer: Textpattern (zem_contact_reborn)'.
					$sep.'X-Originating-IP: '.zem_contact_strip((!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].' via ' : '').$_SERVER['REMOTE_ADDR']).
					$sep.'Content-Transfer-Encoding: 8bit'.
					$sep.'Content-Type: '.$content_type.';'.
								(!empty($mime_boundary) ? ' boundary="'.$mime_boundary.'"': ' charset='.$charset);


        $_POST = array();
				mail(zem_contact_strip($zem_contact_from), $subject, $msg, $headers);

        if (!empty($alsocopy)) {
            mail(zem_contact_strip($alsocopy), $subject, $msg, $headers);
			}
			}

			$_POST = array();

			if ($redirect)
			{
				while (@ob_end_clean());
				$uri = hu.ltrim($redirect,'/');
				if (empty($_SERVER['FCGI_ROLE']) and empty($_ENV['FCGI_ROLE']))
				{
					txp_status_header('303 See Other');
					header('Location: '.$uri);
					header('Connection: close');
					header('Content-Length: 0');
				}
				else
				{
					$uri = htmlspecialchars($uri);
					$refresh = gTxt('zem_contact_refresh');
					echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>$sitename</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="0;url=$uri" />
</head>
<body>
<a href="$uri">$refresh</a>
</body>
</html>
END;
				}
				exit;
			}

			else
			{
				return '<div class="zemThanks" id="zcr'.$zem_contact_form_id.'">' .
					($thanks_form ? fetch_form($thanks_form) : $thanks) .
					'</div>';
			}
		}

		else
		{
			$out .= graf(gTxt('zem_contact_mail_sorry'));
		}
	}

	if ($show_input and !$send_article or gps('zem_contact_send_article'))
	{
		return '<form method="post"'.((!$show_error and $zem_contact_error) ? '' : ' id="zcr'.$zem_contact_form_id.'"').' class="'.$class.'" action="'.htmlspecialchars(serverSet('REQUEST_URI')).'#zcr'.$zem_contact_form_id.'">'.
			( $label ? n.'<fieldset>' : n.'<div>' ).
			( $label ? n.'<legend>'.htmlspecialchars($label).'</legend>' : '' ).
			$out.
			n.'<input type="hidden" name="zem_contact_nonce" value="'.$zem_contact_nonce.'" />'.
			n.'<input type="hidden" name="zem_contact_form_id" value="'.$zem_contact_form_id.'" />'.
			$form.
			callback_event('zemcontact.form').
			( $label ? (n.'</fieldset>') : (n.'</div>') ).
			n.'</form>';
	}

	return '';
}

function zem_contact_strip($str, $header = TRUE) {
	if ($header) $str = strip_rn($str);
	return preg_replace('/[\x00]/', ' ', $str);
}

function zem_contact_text($atts)
{
	global $zem_contact_error, $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'type'         => 'text',
		'class'        => 'zemText',
		'break'        => br,
		'default'      => '',
		'isError'      => '',
		'label'        => gTxt('zem_contact_text'),
		'max'          => 100,
		'min'          => 0,
		'step'         => '',
		'name'         => '',
		'required'     => 1,
		'pattern'      => '',
		'placeholder'  => '',
		'autofocus'    => '',
		'autocomplete' => '',
		'size'         => '',
	), $atts));

	$size = intval($size);

	$numeric_types = array(
		'date',
		'datetime',
		'datetime-local',
		'month',
		'number',
		'range',
		'time',
		'week',
	);

	if (empty($name)) $name = zem_contact_label2name($label);

	if ($zem_contact_submit)
	{
		$value = trim(ps($name));
		$utf8len = preg_match_all("/./su", $value, $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen($value))
		{
			if (!$utf8len)
			{
				$zem_contact_error[] = gTxt('zem_contact_invalid_utf8', array('{field}' => $hlabel));
				$isError = "errorElement";
			}

			elseif ($min and (!in_array($type, $numeric_types)) and $utf8len < $min)
			{
				$zem_contact_error[] = gTxt('zem_contact_min_warning', array('{field}' => $hlabel, '{value}' => $min));
				$isError = "errorElement";
			}

			elseif ($max and (!in_array($type, $numeric_types)) and $utf8len > $max)
			{
				$zem_contact_error[] = gTxt('zem_contact_max_warning', array('{field}' => $hlabel, '{value}' => $max));
				$isError = "errorElement";
			}

			elseif ($min and (in_array($type, $numeric_types)) and $value < $min)
			{
				$zem_contact_error[] = gTxt('zem_contact_minval_warning', array('{field}' => $hlabel, '{value}' => $min));
				$isError = "errorElement";
			}

			elseif ($max and (in_array($type, $numeric_types)) and $value > $max)
			{
				$zem_contact_error[] = gTxt('zem_contact_maxval_warning', array('{field}' => $hlabel, '{value}' => $max));
				$isError = "errorElement";
			}

			else
			{
				zem_contact_store($name, $label, $value);
			}
		}
		elseif ($required)
		{
			$zem_contact_error[] = gTxt('zem_contact_field_missing', array('{field}' => $hlabel));
			$isError = "errorElement";
		}
	}

	else
	{
		$value = $default;
	}

	$doctype = get_pref('doctype');
	$min_att = $max_att = $step_att = '';

	if ($doctype !== 'xhtml') {
		$min_att = ($min !== '') ? ' min="'.$min.'"' : '';
		$max_att = ($max !== '') ? ' max="'.$max.'"' : '';
		$step_att = ($step !== '') ? ' step="'.$step.'"' : '';
	}

	$size = ($size) ? ' size="'.$size.'"' : '';
	$maxlength = ($max && !in_array($type, $numeric_types)) ? ' maxlength="'.$max.'"' : '';
	$pattern = ($pattern) ? ' pattern="'.$pattern.'"' : '';
	$placeholder = ($placeholder) ? ' placeholder="'.$placeholder.'"' : '';
	$autofocus = ($autofocus) ? ' autofocus="'.$autofocus.'"' : '';
	$autocomplete = ($autocomplete) ? ' autocomplete="'.$autocomplete.'"' : '';
	$zemRequired = $required ? 'zemRequired' : '';

        return '<label for="'.$name.'" class="'.$class.' '.$zemRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<input type="'.$type.'" id="'.$name.'" class="'.$class.' '.$zemRequired.$isError.'" name="'.$name.'" value="'.htmlspecialchars($value).'"'.$size.$maxlength.$pattern.$placeholder.$autofocus.$autocomplete.$min_att.$max_att.$step_att.' />';
}

function zem_contact_textarea($atts)
{
	global $zem_contact_error, $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'break'       => br,
		'class'       => 'zemTextarea',
		'cols'        => 58,
		'default'     => '',
		'isError'     => '',
		'label'       => gTxt('zem_contact_message'),
		'max'         => 10000,
		'min'         => 0,
		'name'        => '',
		'required'    => 1,
		'placeholder' => '',
		'autofocus'   => '',
		'rows'        => 8
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$cols = intval($cols);
	$rows = intval($rows);

	if (empty($name)) $name = zem_contact_label2name($label);

	if ($zem_contact_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen(ltrim($value)))
		{
			if (!$utf8len)
			{
				$zem_contact_error[] = gTxt('zem_contact_invalid_utf8', array('{field}' => $hlabel));
				$isError = "errorElement";
			}

			elseif ($min and $utf8len < $min)
			{
				$zem_contact_error[] = gTxt('zem_contact_min_warning', array('{field}' => $hlabel, '{value}' => $min));
				$isError = "errorElement";
			}

			elseif ($max and $utf8len > $max)
			{
				$zem_contact_error[] = gTxt('zem_contact_max_warning', array('{field}' => $hlabel, '{value}' => $max));
				$isError = "errorElement";
				#$value = join('',array_slice($utf8ar[0],0,$max));
			}

			else
			{
				zem_contact_store($name, $label, $value);
			}
		}

		elseif ($required)
		{
			$zem_contact_error[] = gTxt('zem_contact_field_missing', array('{field}' => $hlabel));
			$isError = "errorElement";
		}
	}

	else
	{
		$value = $default;
	}

	$zemRequired = $required ? 'zemRequired' : '';
	$placeholder = ($placeholder) ? ' placeholder="'.$placeholder.'"' : '';
	$autofocus = ($autofocus) ? ' autofocus="'.$autofocus.'"' : '';

	return '<label for="'.$name.'" class="'.$class.' '.$zemRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<textarea id="'.$name.'" class="'.$class.' '.$zemRequired.$isError.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'"'.$placeholder.$autofocus.'>'.htmlspecialchars($value).'</textarea>';
}

function zem_contact_email($atts)
{
	global $zem_contact_error, $zem_contact_submit, $zem_contact_from, $zem_contact_recipient;

	extract(zem_contact_lAtts(array(
		'type'         => 'email',
		'default'      => '',
		'isError'      => '',
		'label'        => gTxt('zem_contact_email'),
		'max'          => 100,
		'min'          => 0,
		'name'         => '',
		'required'     => 1,
		'break'        => br,
		'class'        => '',
		'size'         => '',
		'placeholder'  => '',
		'autofocus'    => '',
		'autocomplete' => '',
		'send_article'	=> 0
	), $atts));

	if (empty($name)) $name = zem_contact_label2name($label);

	$email = $zem_contact_submit ? trim(ps($name)) : $default;

	if ($zem_contact_submit and strlen($email))
	{
		if (!is_valid_email($email))
		{
			$zem_contact_error[] = gTxt('zem_contact_invalid_email', array('{email}' => htmlspecialchars($email)));
			$isError = "errorElement";
		}

		else
		{
			preg_match("/@(.+)$/", $email, $match);
			$domain = $match[1];

			if (is_callable('checkdnsrr') and checkdnsrr('textpattern.com.','A') and !checkdnsrr($domain.'.','MX') and !checkdnsrr($domain.'.','A'))
			{
				$zem_contact_error[] = gTxt('zem_contact_invalid_host', array('{host}' => htmlspecialchars($domain)));
				$isError = "errorElement";
			}

			else
			{
				if ($send_article) {
					$zem_contact_recipient = $email;
				}
				else {
					$zem_contact_from = $email;
				}
			}
		}
	}

	return zem_contact_text(array(
		'type'         => $type,
		'default'      => $email,
		'isError'      => $isError,
		'label'        => $label,
		'max'          => $max,
		'min'          => $min,
		'name'         => $name,
		'required'     => $required,
		'break'        => $break,
		'class'        => $class,
		'size'         => $size,
		'placeholder'  => $placeholder,
		'autofocus'    => $autofocus,
		'autocomplete' => $autocomplete,
	));
}

function zem_contact_select($atts)
{
	global $zem_contact_error, $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'name'      => '',
		'break'     => ' ',
		'class'     => 'zemSelect',
		'delimiter' => ',',
		'isError'   => '',
		'label'     => gTxt('zem_contact_option'),
		'list'      => gTxt('zem_contact_general_inquiry'),
		'required'  => 1,
		'selected'  => '',
		'autofocus' => '',
	), $atts));

	if (empty($name)) $name = zem_contact_label2name($label);

	$list = array_map('trim', explode($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$list)));

	if ($zem_contact_submit)
	{
		$value = trim(ps($name));

		if (strlen($value))
		{
			if (in_array($value, $list))
			{
				zem_contact_store($name, $label, $value);
			}

			else
			{
				$zem_contact_error[] = gTxt('zem_contact_invalid_value', array('{field}' => htmlspecialchars($label), '{value}' => htmlspecialchars($value)));
				$isError = "errorElement";
			}
		}

		elseif ($required)
		{
			$zem_contact_error[] = gTxt('zem_contact_field_missing', array('{field}' => htmlspecialchars($label)));
			$isError = "errorElement";
		}
	}
	else
	{
		$value = $selected;
	}

	$out = '';

	foreach ($list as $item)
	{
		$out .= n.t.'<option'.($item == $value ? ' selected="selected">' : '>').(strlen($item) ? htmlspecialchars($item) : ' ').'</option>';
	}

	$zemRequired = $required ? 'zemRequired' : '';
	$autofocus = ($autofocus) ? ' autofocus="'.$autofocus.'"' : '';

	return '<label for="'.$name.'" class="'.$class.' '.$zemRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		n.'<select id="'.$name.'" name="'.$name.'" class="'.$class.' '.$zemRequired.$isError.'"'.$autofocus.'>'.
			$out.
		n.'</select>';
}

function zem_contact_checkbox($atts)
{
	global $zem_contact_error, $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'break'     => ' ',
		'class'     => 'zemCheckbox',
		'checked'   => 0,
		'isError'   => '',
		'label'     => gTxt('zem_contact_checkbox'),
		'name'      => '',
		'required'  => 1,
		'autofocus' => '',
	), $atts));

	if (empty($name)) $name = zem_contact_label2name($label);

	if ($zem_contact_submit)
	{
		$value = (bool) ps($name);

		if ($required and !$value)
		{
			$zem_contact_error[] = gTxt('zem_contact_field_missing', array('{field}' => htmlspecialchars($label)));
			$isError = "errorElement";
		}

		else
		{
			zem_contact_store($name, $label, $value ? gTxt('yes') : gTxt('no'));
		}
	}

	else {
		$value = $checked;
	}

	$zemRequired = $required ? 'zemRequired' : '';
	$autofocus = ($autofocus) ? ' autofocus="'.$autofocus.'"' : '';

	return '<input type="checkbox" id="'.$name.'" class="'.$class.' '.$zemRequired.$isError.'" name="'.$name.'"'.
		($value ? ' checked="checked"' : '').$autofocus.' />'.$break.
		'<label for="'.$name.'" class="'.$class.' '.$zemRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>';
}

function zem_contact_serverinfo($atts)
{
	global $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'label'		=> '',
		'name'		=> ''
	), $atts));

	if (empty($name)) $name = zem_contact_label2name($label);

	if (strlen($name) and $zem_contact_submit)
	{
		if (!$label) $label = $name;
		zem_contact_store($name, $label, serverSet($name));
	}
}

function zem_contact_secret($atts, $thing = '')
{
	global $zem_contact_submit;

	extract(zem_contact_lAtts(array(
		'name'	=> '',
		'label'	=> gTxt('zem_contact_secret'),
		'value'	=> ''
	), $atts));

	$name = zem_contact_label2name($name ? $name : $label);

	if ($zem_contact_submit)
	{
		if ($thing) $value = trim(parse($thing));
		zem_contact_store($name, $label, $value);
	}

	return '';
}

function zem_contact_radio($atts)
{
	global $zem_contact_error, $zem_contact_submit, $zem_contact_values;

	extract(zem_contact_lAtts(array(
		'break'     => ' ',
		'class'     => 'zemRadio',
		'checked'   => 0,
		'group'     => '',
		'label'     => gTxt('zem_contact_option'),
		'name'      => '',
		'autofocus' => '',
	), $atts));

	static $cur_name = '';
	static $cur_group = '';

	if (!$name and !$group and !$cur_name and !$cur_group) {
		$cur_group = gTxt('zem_contact_radio');
		$cur_name = $cur_group;
	}
	if ($group and !$name and $group != $cur_group) $name = $group;

	if ($name) $cur_name = $name;
	else $name = $cur_name;

	if ($group) $cur_group = $group;
	else $group = $cur_group;

	$id   = 'q'.md5($name.'=>'.$label);
	$name = zem_contact_label2name($name);

	if ($zem_contact_submit)
	{
		$is_checked = (ps($name) == $id);

		if ($is_checked or $checked and !isset($zem_contact_values[$name]))
		{
			zem_contact_store($name, $group, $label);
		}
	}

	else
	{
		$is_checked = $checked;
	}

	$autofocus = ($autofocus) ? ' autofocus="'.$autofocus.'"' : '';

	return '<input value="'.$id.'" type="radio" id="'.$id.'" class="'.$class.' '.$name.'" name="'.$name.'"'.$autofocus.
		( $is_checked ? ' checked="checked" />' : ' />').$break.
		'<label for="'.$id.'" class="'.$class.' '.$name.'">'.htmlspecialchars($label).'</label>';
}

function zem_contact_send_article($atts)
{
	if (!isset($_REQUEST['zem_contact_send_article'])) {
		$linktext = (empty($atts['linktext'])) ? gTxt('zem_contact_send_article') : $atts['linktext'];
		$join = (empty($_SERVER['QUERY_STRING'])) ? '?' : '&';
		$href = $_SERVER['REQUEST_URI'].$join.'zem_contact_send_article=yes';
		return '<a href="'.htmlspecialchars($href).'">'.htmlspecialchars($linktext).'</a>';
	}
	return;
}

function zem_contact_submit($atts, $thing)
{
	extract(zem_contact_lAtts(array(
		'button' => 0,
		'label'  => gTxt('zem_contact_send'),
		'class'  => 'zemSubmit',
	), $atts));

	$label = htmlspecialchars($label);

	if ($button or strlen($thing))
	{
		return '<button type="submit" class="'.$class.'" name="zem_contact_submit" value="'.$label.'">'.($thing ? trim(parse($thing)) : $label).'</button>';
	}
	else
	{
		return '<input type="submit" class="'.$class.'" name="zem_contact_submit" value="'.$label.'" />';
	}
}

function zem_contact_lAtts($arr, $atts)
{
	foreach(array('button', 'copysender', 'checked', 'required', 'send_article', 'show_input', 'show_error') as $key)
	{
		if (isset($atts[$key]))
		{
			$atts[$key] = ($atts[$key] === 'yes' or intval($atts[$key])) ? 1 : 0;
		}
	}
	if (isset($atts['break']) and $atts['break'] == 'br') $atts['break'] = '<br />';
	return lAtts($arr, $atts);
}

class zemcontact_evaluation
{
	var $status;
        var $msg;

	function zemcontact_evaluation() {
		$this->status = 0;
	}

	function add_zemcontact_status($check) {
		$this->status += $check;
	}

	function get_zemcontact_status() {
		return $this->status;
	}

        function set_zemcontact_msg ($msg) {
            $this->msg = $msg;
        }

        function get_zemcontact_msg() {
            return $this->msg;
        }

}

function &get_zemcontact_evaluator()
{
	static $instance;

	if(!isset($instance)) {
		$instance = new zemcontact_evaluation();
	}
	return $instance;
}

function zem_contact_label2name($label)
{
	$label = trim($label);
	if (strlen($label) == 0) return 'invalid';
	if (strlen($label) <= 32 and preg_match('/^[a-zA-Z][A-Za-z0-9:_-]*$/', $label)) return $label;
	else return 'q'.md5($label);
}

function zem_contact_store($name, $label, $value)
{
	global $zem_contact_form, $zem_contact_labels, $zem_contact_values;
	$zem_contact_form[$label] = $value;
	$zem_contact_labels[$name] = $label;
	$zem_contact_values[$name] = $value;
}

function zem_contact_mailheader($string, $type)
{
	global $prefs;
	if (!strstr($string,'=?') and !preg_match('/[\x00-\x1F\x7F-\xFF]/', $string)) {
		if ("phrase" == $type) {
			if (preg_match('/[][()<>@,;:".\x5C]/', $string)) {
				$string = '"'. strtr($string, array("\\" => "\\\\", '"' => '\"')) . '"';
			}
		}
		elseif ("text" != $type) {
			trigger_error('Unknown encode_mailheader type', E_USER_WARNING);
		}
		return $string;
	}
	if ($prefs['override_emailcharset']) {
		$start = '=?ISO-8859-1?B?';
		$pcre  = '/.{1,42}/s';
	}
	else {
		$start = '=?UTF-8?B?';
		$pcre  = '/.{1,45}(?=[\x00-\x7F\xC0-\xFF]|$)/s';
	}
	$end = '?=';
	$sep = is_windows() ? "\r\n" : "\n";
	preg_match_all($pcre, $string, $matches);
	return $start . join($end.$sep.' '.$start, array_map('base64_encode',$matches[0])) . $end;
}


function zem_build_msg_array()
{
		global $zem_contact_labels, $zem_contact_values;

		$msg = array();
		$in_lot = false;

		foreach ($zem_contact_labels as $name => $label)
		{
			if (!trim($zem_contact_values[$name]))
			{
				$in_lot = false;
				continue;
			}

			if ($in_lot && ($zem_contact_values[$name] == '$0.00' || empty($zem_contact_values[$name])))
			{
				// pop secret
				array_pop($msg);
				// pop lot
				array_pop($msg);
				// skip cost
			}
			else
			{
			    $val = $zem_contact_values[$name];

			    if ( (($name=='clubDiscountField' || $name=='caseDiscountField') && ($val=='$0.00' || $val='-$0.00'))
			        || ($name=='shippingState' && $val=='Choose') )
	            {
	                // skip it
	            }
	            else
	            {
    				$msg[] = $label.': '.$val;
    			}
			}
			$in_lot = strncasecmp($name,'lot-',4) == 0;
		}

		return $msg;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<style>
   li code {font-weight: bold;}
   pre {padding: 0.5em 1em; background: #eee; border: 1px dashed #ccc;}
   h1, h2, h3, h3 code {font-family: sans-serif; font-weight: bold;}
   h1, h2, h3 {margin-left: -1em;}
   h2, h3 {margin-top: 2em;}
   h1 {font-size: 3em;}
   h2 {font-size: 2em;}
   h3 {font-size: 1.5em;}
   li a code {font-weight: normal;}
   .required, .warning {color:red;}
 </style>

	<h1 id="top">Zem Contact Reborn</h1>

	<p>Please reports bugs and problems with this plugin in <a href="http://forum.textpattern.com/viewtopic.php?id=23728">this forum thread</a>.</p>

	<h2 id="contents">Contents</h2>

	<ul>
		<li><a href="#features">Features</a>
		<li><a href="#start">Getting started</a>
	<ul>
		<li><a href="#contactform">Contact form</a></li>
	</ul></li>
	<ul>
		<li><a href="#sendarticle">Send article</a></li>
		<li><a href="#tags">Tags</a>
		<li><a href="#zc"> <code>&#60;txp:zem_contact /&#62;</code> </a></li>
		<li><a href="#zc_text"> <code>&#60;txp:zem_contact_text /&#62;</code> </a></li>
		<li><a href="#zc_email"> <code>&#60;txp:zem_contact_email /&#62;</code> </a></li>
		<li><a href="#zc_textarea"> <code>&#60;txp:zem_contact_textarea /&#62;</code> </a></li>
		<li><a href="#zc_submit"> <code>&#60;txp:zem_contact_submit /&#62;</code> </a></li>
		<li><a href="#zc_select"> <code>&#60;txp:zem_contact_select /&#62;</code> </a></li>
		<li><a href="#zc_checkbox"> <code>&#60;txp:zem_contact_checkbox /&#62;</code> </a></li>
		<li><a href="#zc_radio"> <code>&#60;txp:zem_contact_radio /&#62;</code> </a></li>
		<li><a href="#zc_secret"> <code>&#60;txp:zem_contact_secret /&#62;</code> </a></li>
		<li><a href="#zc_server_info"> <code>&#60;txp:zem_contact_serverinfo /&#62;</code> </a></li>
	</ul></li>
	<ul>
		<li><a href="#zc_send_article"> <code>&#60;txp:zem_contact_send_article /&#62;</code> </a></li>
		<li><a href="#advanced">Advanced examples</a>
		<li><a href="#show_error">Separate input and error forms</a></li>
		<li><a href="#subject_form">User selectable subject field</a></li>
	</ul></li>
	<ul>
		<li><a href="#to_form">User selectable recipient, without showing email addresses</a></li>
	</ul></li>
		<li><a href="#styling">Styling</a></li>
		<li><a href="#history">History</a></li>
		<li><a href="#credits">Credits</a></li>
	</ul>
	<ul>
		<li><a href="#api">Plugin <span class="caps"><span class="caps">API</span></span> and callback events</a></li>
	</ul>

	<h2 id="features">Features</h2>

	<ul>
		<li>Arbitrary text fields can be specified, with min/max/required settings for validation.</li>
		<li>Email address validation, including a check for a valid MX record (Unix only).</li>
		<li>Safe escaping of input data.</li>
		<li>UTF-8 safe.</li>
		<li>Accessible form layout, including <code>&#60;label&#62;</code>, <code>&#60;legend&#62;</code> and <code>&#60;fieldset&#62;</code> tags.</li>
		<li>Various classes and ids to allow easy styling of all parts of the form.</li>
		<li>A separate language plug-in to enable easy localisation.</li>
	</ul>
	<ul>
		<li>Spam prevention <span class="caps"><span class="caps">API</span></span> (used by Tranquillo&#8217;s <code>pap_contact_cleaner</code> plugin).</li>
	</ul>

	<p><a href="#top">Back to top</a></p>

	<h2 id="start">Getting started</h2>

	<h3 id="contactform">Contact form</h3>

	<p>The simplest form is shown below, which produces a default form with Name, Email and Message fields. Email will be delivered to recipient@example.com, with the user&#8217;s supplied email as the &#8220;From:&#8221; address.</p>

<pre><code>&#60;txp:zem_contact to=&#34;recipient@example.com&#34; /&#62;
</code></pre>

	<p>To specify fields explicitly, use something like this:</p>

<pre><code>&#60;txp:zem_contact to=&#34;recipient@example.com&#34;&#62;
  &#60;txp:zem_contact_email /&#62;
  &#60;txp:zem_contact_text label=&#34;Phone&#34; min=7 max=15/&#62;
  &#60;txp:zem_contact_textarea label=&#34;Your question&#34; /&#62;
  &#60;txp:zem_contact_submit label=&#34;Send&#34; /&#62;
&#60;/txp:zem_contact&#62;
</code></pre>

	<p>Alternatively, place the field specifications in a Textpattern form, and call it like this:</p>

<pre><code>&#60;txp:zem_contact to=&#34;recipient@example.com&#34; form=&#34;mycontactform&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="sendarticle">Send article</h3>

	<p>Within the context of an individual article, this plugin can be used to send the article (or excerpt, if it exists) to an email address specified by the visitor. This requires at least two tags:
	<ul>
		<li><code>zem_contact</code>, to create form that is initially hidden by setting the <code>send_article</code> attribute.</li>
	</ul>
	<ul>
		<li><code>zem_contact_send_article</code>, to create a &#8216;send article&#8217; link which reveals the aforementioned form when clicked.</li>
	</ul></p>

<pre><code>&#60;txp:zem_contact send_article=&#34;1&#34; /&#62;
&#60;txp:zem_contact_send_article /&#62;
</code></pre>

	<p>By default the form contains fields for your name and email address, the recipient&#8217;s email address and a personal message, but similar to contact forms you can create your own form layout. Some things you need to know:
	<ul>
		<li>Set the <code>send_article</code> attribute to <code>1</code> in the <code>zem_contact</code> tag.</li>
	</ul>
	<ul>
		<li>Use a <code>zem_contact_email</code> tag with the <code>send_article</code> attribute set to <code>1</code>. This field will be used as the recipient email address.</li>
	</ul></p>

<pre><code>&#60;txp:zem_contact to=&#34;you@example.com&#34; send_article=&#34;1&#34;&#62;
  &#60;txp:zem_contact_email label=&#34;Recipient Email&#34; send_article=&#34;1&#34; /&#62;
  &#60;txp:zem_contact_email label=&#34;Your Email&#34; /&#62;
  &#60;txp:zem_contact_submit label=&#34;Send Article&#34; /&#62;
&#60;/txp:zem_contact&#62;
</code>
<code>&#60;txp:zem_contact_send_article /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h2 id="tags">Tags</h2>

	<p><a href="#zc"><code>&#60;txp:zem_contact /&#62;</code></a> produces a flexible, customisable email contact form. It is intended for use as an enquiry form for commercial and private sites, and includes several features to help reduce common problems with such forms (invalid email addresses, missing information).</p>

	<p><a href="#zc_send_article"><code>&#60;txp:zem_contact_send_article /&#62;</code></a> can be used to create a &#8220;send article&#8221; link within an article form, connecting it to the contact form.</p>

	<p>All other tags provided by this plugin can only be used inside a <code>&#60;txp:zem_contact&#62;</code> &#8230; <code>&#60;/txp:zem_contact&#62;</code> container tag or in a Textpattern form used as the <code>form</code> attribute in the <code>&#60;txp:zem_contact /&#62;</code> tag.</p>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc"><code>&#60;txp:zem_contact /&#62;</code></h3>

	<p>May be used as a self-closing or container tag. Place this where you want the input form to go. Status and error messages, if any, will be displayed before the form.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>to=&#34;email address&#34;</code> <span class="required">required</span><br />
Recipient email address.</li>
	</ul>
	<ul>
		<li><code>to_form=&#34;form name&#34;</code><br />
Use specified form (overrides <strong>to</strong> attribute).</li>
	</ul>

	<ul>
		<li><code>from=&#34;email address&#34;</code><br />
Email address used in the &#8220;From:&#8221; field when sending email. Defaults to the sender&#8217;s email address. If specified, the sender&#8217;s email address will be placed in the &#8220;Reply-To:&#8221; field instead.</li>
	</ul>
	<ul>
		<li><code>from_form=&#34;form name&#34;</code><br />
Use specified form (overrides <strong>from</strong> attribute).</li>
	</ul>

	<ul>
		<li><code>subject=&#34;subject text&#34;</code><br />
Subject used when sending an email. Default is the site name.</li>
	</ul>
	<ul>
		<li><code>subject_form=&#34;form name&#34;</code><br />
Use specified form (overrides <strong>subject</strong> attribute).</li>
	</ul>

	<ul>
		<li><code>thanks=&#34;text&#34;</code><br />
Message shown after successfully submitting a message. Default is <strong>Thank you, your message has been sent</strong>.</li>
		<li><code>thanks_form=&#34;form name&#34;</code><br />
Use specified form (overrides <strong>thanks</strong> attribute).</li>
	</ul>
	<ul>
		<li><code>redirect=&#34;URL&#34;</code><br />
Redirect to specified <span class="caps"><span class="caps">URL</span></span> (overrides <strong>thanks</strong> and <strong>thanks_form</strong> attributes). <span class="caps"><span class="caps">URL</span></span> must be relative to the Textpattern Site <span class="caps"><span class="caps">URL</span></span>. Example: <em>redirect=&#8220;monkey&#8221;</em> would redirect to http://example.com/monkey.</li>
	</ul>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Label for the contact form. If set to an empty string, display of the fieldset and legend tags will be suppressed. Default is <strong>Contact</strong>.</li>
		<li><code>send_article=&#34;boolean&#34;</code><br />
Whether to use this form to <a href="#article">send an article</a>. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>0</strong> (no).</li>
	</ul>
	<ul>
		<li><code>copysender=&#34;boolean&#34;</code><br />
Whether to send a copy of the email to the sender&#8217;s address. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>0</strong> (no).</li>
	</ul>

	<ul>
		<li><code>form=&#34;form name&#34;</code><br />
Use specified form, containing the layout of the contact form fields.</li>
		<li><code>show_input=&#34;boolean&#34;</code><br />
 Whether to display the form input fields. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>
	<ul>
		<li><code>show_error=&#34;boolean&#34;</code><br />
 Whether to display error and status messages. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>

	<h4>Examples</h4>

	<p>See <a href="#contactform">Getting started</a> and <a href="#advanced">Advanced examples</a>.</p>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_text"><code>&#60;txp:zem_contact_text /&#62;</code></h3>

	<p>Creates a text input field and corresponding <code>&#60;label&#62;</code> tag. The input value will be included in the email, preceded by the label.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Text label displayed to the user. Default is <strong>Text</strong>.</li>
		<li><code>type=&#34;value&#34;</code><br />
Field type, as defined by the W3C, such as 'text', 'number', 'range', 'date', etc. Default is 'text'.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and input field. Default is <code>&#60;br /&#62;</code>. Use <code>break=&#34;&#34;</code> to put the label and input field on the same line.</li>
		<li><code>default=&#34;value&#34;</code><br />
Default value when no input is provided.</li>
		<li><code>min=&#34;number&#34;</code><br />
Minimum input length in characters, or minimum field value for numeric types. Default is <strong>0</strong>.</li>
		<li><code>max=&#34;number&#34;</code><br />
Maximum input length in characters, or maximum field value for numeric types. Default is <strong>100</strong>.</li>
		<li><code>step=&#34;number&#34;</code><br />
Interval between successive permissible values in numeric input controls.</li>
		<li><code>size=&#34;integer&#34;</code><br />
Size of the input field as displayed to the user.</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the input field. Default is zemText.</li>
		<li><code>placeholder=&#34;text&#34;</code><br />
Placeholder text to put in the input field.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the field to receive focus.</li>
		<li><code>autocomplete=&#34;boolean&#34;</code><br />
Permit the field to offer entries from previously supplied content.</li>
		<li><code>pattern=&#34;regular expression&#34;</code><br />
Javascript regular expression with which to validate the field contents.</li>
	</ul>
	<ul>
		<li><code>required=&#34;boolean&#34;</code><br />
Whether this text field must be filled out. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>

	<h4>Example</h4>

<pre><code>&#60;txp:zem_contact_text label=&#34;Your name&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_email"><code>&#60;txp:zem_contact_email /&#62;</code></h3>

	<p>Input field for user&#8217;s email address.</p>

	<p>The entered email address will automatically be validated to make sure it is of the form &#8220;abc@xxx.yyy[.zzz]&#8221;. On non-Windows servers, a test will be done to verify that an A or MX record exists for the domain. Neither test prevents spam, but it does help detecting accidental typing errors.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Text label displayed to the user. Default is <strong>Email</strong>.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag.</li>
		<li><code>type=&#34;value&#34;</code><br />
Field type, as defined by the W3C. Suitable values are 'text' or 'email'. Default is 'email'.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and input field. Default is <code>&#60;br /&#62;</code>. Use <code>break=&#34;&#34;</code> to put the label and input field on the same line.</li>
		<li><code>default=&#34;value&#34;</code><br />
Default value when no input is provided.</li>
		<li><code>min=&#34;integer&#34;</code><br />
Minimum input length in characters. Default is <strong>0</strong>.</li>
		<li><code>max=&#34;integer&#34;</code><br />
Maximum input length in characters. Default is <strong>100</strong>.</li>
		<li><code>size=&#34;integer&#34;</code><br />
Size of the input field as displayed to the user.</li>
		<li><code>required=&#34;boolean&#34;</code><br />
Whether this text field must be filled out. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the field. Default is zemText.</li>
		<li><code>placeholder=&#34;text&#34;</code><br />
Placeholder text to put in the field.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the field to receive initial focus.</li>
		<li><code>autocomplete=&#34;boolean&#34;</code><br />
Permit the field to offer entries from previously supplied content.</li>
	</ul>
	<ul>
		<li><code>send_article=&#34;boolean&#34;</code><br />
Whether this field is used as the recipient email address when using the send_article function. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>0</strong> (no).</li>
	</ul>

	<h4>Example</h4>

<pre><code>&#60;txp:zem_contact_email label=&#34;Your email address&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_textarea"><code>&#60;txp:zem_contact_textarea /&#62;</code></h3>

	<p>Creates a textarea.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Text label displayed to the user. Default is <strong>Message</strong>.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and input field. Default is <code>&#60;br /&#62;</code>. Use <code>break=&#34;&#34;</code> to put the label and input field on the same line.</li>
		<li><code>default=&#34;value&#34;</code><br />
Default value when no input is provided.</li>
		<li><code>cols=&#34;integer&#34;</code><br />
Column width. Default is <strong>58</strong>.</li>
		<li><code>rows=&#34;integer&#34;</code><br />
Row height. Default is <strong>8</strong>.</li>
		<li><code>min=&#34;integer&#34;</code><br />
Minimum input length in characters. Default is <strong>0</strong>.</li>
		<li><code>max=&#34;integer&#34;</code><br />
Maximum input length in characters. Default is <strong>10000</strong>.</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the textarea. Default is zemTextarea.</li>
		<li><code>placeholder=&#34;text&#34;</code><br />
Placeholder text to put in the textarea.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the textarea to receive initial focus.</li>
	</ul>
	<ul>
		<li><code>required=&#34;boolean&#34;</code><br />
Whether this text field must be filled out. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>

	<h4>Example</h4>

	<p>Textarea that is 40 chars wide, 10 lines high, with a customized label:</p>

<pre><code>&#60;txp:zem_contact_textarea cols=&#34;40&#34; rows=&#34;10&#34; label=&#34;Your question&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_submit"><code>&#60;txp:zem_contact_submit /&#62;</code></h3>

	<p>Creates a submit button.<br />
When used as a container tag, a &#8220;button&#8221; element will be used instead of an &#8220;input&#8221; element.</p>

	<h4>Attributes:</h4>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Text shown on the submit button. Default is &#8220;Send&#8221;.</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the button. Default is zemSubmit</li>
	</ul>
	<ul>
		<li><code>button=&#34;boolean&#34;</code><br />
<em>Deprecated. Use a container tag if you want a button element.</em></li>
	</ul>

	<h4>Examples</h4>

	<p>Standard submit button:</p>

<pre><code>&#60;txp:zem_contact_submit /&#62;
</code></pre>

	<p>Submit button with your own text:</p>

<pre><code>&#60;txp:zem_contact_submit label=&#34;Send&#34; /&#62;
</code></pre>

	<p>Usage as a container tag, which allows you to use Textpattern tags and <span class="caps">HTML</span> markup in the submit button:</p>

<pre><code>&#60;txp:zem_contact_submit&#62;&#60;strong&#62;Send&#60;/strong&#62; question&#60;/txp:zem_contact_submit&#62;
</code></pre>

<pre><code>&#60;txp:zem_contact_submit&#62;&#60;img src=&#34;path/to/img.png&#34; alt=&#34;submit&#34;&#62;&#60;/txp:zem_contact_submit&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_select"><code>&#60;txp:zem_contact_select /&#62;</code></h3>

	<p>Creates a drop-down selection list.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>list=&#34;comma-separated values&#34;</code> <span class="required">required</span><br />
List of items to show in the select box.</li>
		<li><code>selected=&#34;value&#34;</code><br />
List item that is selected by default.</li>
		<li><code>label=&#34;text&#34;</code><br />
Text label displayed to the user. Default is <strong>Option</strong>.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and input field. Default is <code>&#60;br /&#62;</code>. Use <code>break=&#34;&#34;</code> to put the label and input field on the same line.</li>
		<li><code>delimiter=&#34;character&#34;</code><br />
Separator character used in the <strong>list</strong> attribute. Default is <strong>,</strong> (comma).</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the field. Default is zemSelect.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the field to receive initial focus.</li>
	</ul>
	<ul>
		<li><code>required=&#34;boolean&#34;</code><br />
Whether a non-empty option must be selected. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>

	<h4>Examples</h4>

	<p>Select list labeled &#8216;Department&#8217;, containing three options and a blank option (due to the comma before &#8216;Marketing&#8217;) shown by default, forcing the user to make a selection.</p>

<pre><code>&#60;txp:zem_contact_select label=&#34;Department&#34; list=&#34;,Marketing,Sales,Support&#34; /&#62;
</code></pre>

	<p>Select list containing three options with &#8216;Marketing&#8217; selected by default.</p>

<pre><code>&#60;txp:zem_contact_select list=&#34;Marketing,Sales,Support&#34; selected=&#34;Marketing&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_checkbox"><code>&#60;txp:zem_contact_checkbox /&#62;</code></h3>

	<p>Creates a check box.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Text label displayed to the user. Default is <strong>Checkbox</strong>.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and input field. Default is <code>&#60;br /&#62;</code>. Use <code>break=&#34;&#34;</code> to put the label and input field on the same line.</li>
		<li><code>checked=&#34;boolean&#34;</code><br />
Whether this box is checked when first displayed. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is &#8220;0&#8221; (no).</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the field. Default is zemCheckbox.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the field to receive initial focus.</li>
	</ul>
	<ul>
		<li><code>required=&#34;boolean&#34;</code><br />
Whether this checkbox must be filled out. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>1</strong> (yes).</li>
	</ul>

	<h4>Examples</h4>

	<p>Shrink-wrap agreement which must be checked by the user before the email will be sent.</p>

<pre><code>&#60;txp:zem_contact_checkbox label=&#34;I accept the terms and conditions&#34; /&#62;
</code></pre>

	<p>Optional checkboxes:</p>

<pre><code>Select which operating systems are you familiar with:&#60;br /&#62;
&#60;txp:zem_contact_checkbox label=&#34;Windows&#34; required=&#34;0&#34; /&#62;&#60;br /&#62;
&#60;txp:zem_contact_checkbox label=&#34;Unix/Linux/BSD&#34; required=&#34;0&#34; /&#62;&#60;br /&#62;
&#60;txp:zem_contact_checkbox label=&#34;MacOS&#34; required=&#34;0&#34; /&#62;&#60;br /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_radio"><code>&#60;txp:zem_contact_radio /&#62;</code></h3>

	<p>Creates a radio button.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>group=&#34;text&#34;</code> <span class="required">required</span><br />
Text used in the email to describe this group of radio buttons. This attribute value is remembered for subsequent radio buttons, so you only have to set it on the first radio button of a group. Default is <strong>Radio</strong>.</li>
		<li><code>label=&#34;text&#34;</code> <span class="required">required</span><br />
Text label displayed to the user as radio button option.</li>
		<li><code>name=&#34;value&#34;</code><br />
Field name, as used in the <span class="caps"><span class="caps">HTML</span></span> input tag. This attribute value is remembered for subsequent radio buttons, so you only have to set it on the first radio button of a group. If it hasn&#8217;t been set at all, it will be derived from the <code>group</code> attribute.</li>
		<li><code>break=&#34;tag&#34;</code><br />
Break tag between the label and field. Default is a space.</li>
		<li><code>class=&#34;classname&#34;</code><br />
CSS class to apply to the field. Default is zemRadio.</li>
		<li><code>autofocus=&#34;boolean&#34;</code><br />
Set the field to receive initial focus.</li>
	</ul>
	<ul>
		<li><code>checked=&#34;boolean&#34;</code><br />
Whether this radio option is checked when the form is first displayed. Available values: <strong>1</strong> (yes), <strong>0</strong> (no). Default is <strong>0</strong> (no).</li>
	</ul>

	<h4>Example</h4>

	<p>Group mutually exclusive radio buttons by setting the <code>group</code> attribute on the first radio button in a group. Only the chosen radio button from each group will be used in the email message. The message will be output in the form <strong>group: label</strong> for each of the chosen radio buttons.</p>

<pre><code>&#60;txp:zem_contact_radio label=&#34;Medium&#34; group=&#34;I like my steak&#34; /&#62;
&#60;txp:zem_contact_radio label=&#34;Rare&#34; /&#62;
&#60;txp:zem_contact_radio label=&#34;Well done&#34; /&#62;
</code>
<code>&#60;txp:zem_contact_radio label=&#34;Wine&#34; group=&#34;With a glass of&#34; /&#62;
&#60;txp:zem_contact_radio label=&#34;Beer&#34; /&#62;
&#60;txp:zem_contact_radio label=&#34;Water&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_secret"><code>&#60;txp:zem_contact_secret /&#62;</code></h3>

	<p>This tag has no effect on the form or <span class="caps">HTML</span> output, but will include additional information in the email. It can be used as a self-closing tag or as a container tag.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>name=&#34;text&#34;</code><br />
Used internally. Set this only if you have multiple &#8216;secret&#8217; form elements with identical labels.</li>
		<li><code>label=&#34;text&#34;</code><br />
Used to identify the field in the email. Default is <strong>Secret</strong>.</li>
	</ul>
	<ul>
		<li><code>value=&#34;value&#34;</code><br />
Some text you want to add to the email.</li>
	</ul>

	<h4>Examples</h4>

	<p>Usage as a self-closing tag</p>

<pre><code>&#60;txp:zem_contact_secret value=&#34;The answer is 42&#34; /&#62;
</code></pre>

	<p>Usage as a container tag</p>

<pre><code>&#60;txp:zem_contact_secret label=&#34;Dear user&#34;&#62;
  Please provide a useful example for this tag!
&#60;/txp:zem_contact_secret&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_serverinfo"><code>&#60;txp:zem_contact_serverinfo /&#62;</code></h3>

	<p>This tag has no effect on the form or <span class="caps">HTML</span> output, but will include additional information in the email based on the <span class="caps">PHP</span> $_SERVER variable.</p>

	<h4>Attributes</h4>

	<ul>
		<li><code>name=&#34;value&#34;</code> <span class="required">required</span><br />
Name of the server variable. See the <a href="http://php.net/manual/reserved.variables.php#reserved.variables.server"><span class="caps">PHP</span> manual</a> for a full list.</li>
	</ul>
	<ul>
		<li><code>label=&#34;text&#34;</code><br />
Used to identify the field in the email. Defaults to the value of the <strong>name</strong> attribute.</li>
	</ul>

	<h4>Examples</h4>

	<p>Add the IP address of the visitor to the email</p>

<pre><code>&#60;txp:zem_contact_serverinfo name=&#34;REMOTE_ADDR&#34; label=&#34;IP number&#34; /&#62;
</code></pre>

	<p>Add the name of the visitor&#8217;s browser to the email</p>

<pre><code>&#60;txp:zem_contact_serverinfo name=&#34;HTTP_USER_AGENT&#34; label=&#34;Browser&#34; /&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="zc_send_article"><code>&#60;txp:zem_contact_send_article /&#62;</code></h3>

	<p>Use this tag in your individual article form, where you want the &#8220;send article&#8221; link to be displayed.</p>

	<h4>Attributes:</h4>

	<ul>
		<li><code>linktext=&#34;text&#34;</code><br />
Text displayed for the link. Default is <strong>send article</strong></li>
	</ul>

	<h4>Examples:</h4>

	<p>See <a href="#sendarticle">Getting started</a></p>

	<p><a href="#top">Back to top</a></p>

	<h2 id="advanced">Advanced examples</h2>

	<h3 id="show_error">Separate input and error forms</h3>

	<p>Using <code>show_input</code> and <code>show_error</code> to display the form and error messages on different parts of a page. A form is used to make sure the contents of both forms are identical, otherwise they would be seen as two independent forms. The first form only shows errors (no input), the second form only shows the input fields (no errors).</p>

<pre><code>&#60;div id=&#34;error&#34;&#62;
  &#60;txp:zem_contact form=&#34;contact_form&#34; show_input=&#34;0&#34; /&#62;
&#60;/div&#62;
</code>
<code>&#60;div id=&#34;inputform&#34;&#62;
  &#60;txp:zem_contact form=&#34;contact_form&#34; show_error=&#34;0&#34; /&#62;
&#60;/div&#62;
</code></pre>

	<p>Apart from the <code>show_error</code> and <code>show_input</code> attributes, all other attributes must be 100% identical in both forms, otherwise they would be seen as two unrelated forms.</p>

	<p><a href="#top">Back to top</a></p>

	<h3 id="subject_form">User selectable subject field</h3>

	<p>Specify the <code>subject_form</code> attribute and create a form which includes a <code>zem_contact_select</code> tag:</p>

<pre><code>&#60;txp:zem_contact to=&#34;you@example.com&#34; subject_form=&#34;my_subject_form&#34; /&#62;
  &#60;txp:zem_contact_text label=&#34;Name&#34; /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_email /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_select label=&#34;Choose Subject&#34; list=&#34;,Question,Feedback&#34; /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_textarea label=&#34;Message&#34; /&#62;&#60;br /&#62;
&#60;/txp:zem_contact&#62;
</code></pre>

	<p>Create a Textpattern form called &#8220;my_subject_form&#8221;, containing:</p>

<pre><code>&#60;txp:php&#62;
  global $zem_contact_form;
  echo $zem_contact_form[&#39;Choose Subject&#39;];
&#60;/txp:php&#62;
</code></pre>

	<p>The <code>label</code> used in the <code>zem_contact_select</code> tag must be identical to the corresponding variable in the <code>subject_form</code>. Here we used <code>Choose subject</code>.</p>

	<p>If you&#8217;d prefer to add a common prefix for all subjects, use a <code>subject_form</code> containing:</p>

<pre><code>&#60;txp:php&#62;
  global $zem_contact_form;
  echo &#39;My common prefix - &#39; . $zem_contact_form[&#39;Choose Subject&#39;];
&#60;/txp:php&#62;
</code></pre>

	<p><a href="#top">Back to top</a></p>

	<h3 id="to_form">User selectable recipient, without showing email address</h3>

	<p>Specify the <code>to_form</code> attribute and create a form which includes a <code>zem_contact_select</code> tag:</p>

<pre><code>&#60;txp:zem_contact to_form=&#34;my_zem_contact_to_form&#34;&#62;
  &#60;txp:zem_contact_text label=&#34;Name&#34; /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_email /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_select label=&#34;Department&#34; list=&#34;,Support,Sales&#34; /&#62;&#60;br /&#62;
  &#60;txp:zem_contact_textarea label=&#34;Message&#34; /&#62;&#60;br /&#62;
&#60;/txp:zem_contact&#62;
</code></pre>

	<p>Create a Textpattern form called &#8220;my_zem_contact_to_form&#8221;, containing:</p>

<pre><code>&#60;txp:php&#62;
  global $zem_contact_form;
  switch($zem_contact_form[&#39;Department&#39;])
  {
    case &#39;Support&#39;:
      echo &#39;crew@example.com&#39;;
      break;
    case &#39;Sales&#39;:
      echo &#39;showmethemoney@example.com&#39;;
      break;
    default:
      echo &#39;someone@example.com&#39;;
  }
&#60;/txp:php&#62;
</code></pre>

	<p>The <code>label</code> used in the <code>zem_contact_select</code> tag must be identical to the corresponsing variable in the <code>to_form</code>. Here we used <code>Department</code>.</p>

	<p>A &#8216;default&#8217; email address in the <code>to_form</code> is specified to ensure that a valid email address is used in cases where you add or change a select/radio option and forget to update the <code>to_form</code>.</p>

	<p class="warning">Never use tags like <code>zem_contact_text</code>, <code>zem_contact_email</code> or <code>zem_contact_textarea</code> for setting the recipient address, otherwise your form can be abused to send spam to any email address!</p>

	<p><a href="#top">Back to top</a></p>

	<h2 id="styling">Styling</h2>

	<p>The form itself has a class <strong>zemContactForm</strong> set on the <code>FORM</code> <span class="caps">HTML</span> tag.</p>

	<p>The list of error messages (if any) has a class <strong>zemError</strong> set on the <code>UL</code> <span class="caps">HTML</span> tag that encloses the list of errors.</p>

	<p>All form elements and corresponding labels have the following classes (or ids set):
	<ol>
		<li>One of <strong>zemText</strong>, <strong>zemTextarea</strong>, <strong>zemSelect</strong>, <strong>zemRadio</strong>, <strong>zemCheckbox</strong>, <strong>zemSubmit</strong>. It should be obvious which class is used for which form element (and corresponding label).</li>
		<li><strong>zemRequired</strong> or <strong>errorElement</strong> or <strong>zemRequirederrorElement</strong>, depending on whether the form element is required, an error was found in whatever the visitor entered&#8230; or both.</li>
	</ol>
	<ol>
		<li>An individual &#8220;id&#8221; or &#8220;class&#8221; set to the value of the <code>name</code> attribute of the corresponding tag. When styling forms based on this class, you should explicitly set the <code>name</code> attribute because automatically generated names may change in newer zem_contact_reborn versions.</li>
	</ol></p>

	<p><a href="#top">Back to top</a></p>

	<h2 id="history">History</h2>

	<p>Only the changes that may affect people who upgrade are detailed below.<br />
To save space, links to forum topics that detail all the changes in each version have been added.</p>

	<ul>
		<li>10 sep 2013: <strong>version 4.5.0.0</strong>
	<ul>
		<li>HTML 5 attributes added: <code>placeholder</code>, <code>autofocus</code>, <code>autocomplete</code>, <code>type</code>, <code>pattern</code></li>
		<li>CSS <code>class</code> attribute allows overriding built-in class names</li>
		<li>Textpack replaces zem_contact_lang plugin</li>
		<li>explode() replaces deprecated split()</li>
	</ul></li>
	</ul>
	<ul>
		<li>14 feb 2007: <strong>version 4.0.3.19</strong> <a href="http://forum.textpattern.com/viewtopic.php?id=21144">changelog</a>
	<ul>
		<li><a href="#sendarticle">send_article</a> functionality revised, requiring changes when upgrading from earlier versions</li>
		<li>New language strings: &#8216;send_article&#8217; and &#8216;recipient&#8217; (replaces &#8216;receiver&#8217;)</li>
		<li>Sets of radio buttons require the new <a href="#zc_radio">group</a> attribute</li>
		<li>Yes/No values deprecated in favor for the <span class="caps"><span class="caps">TXP</span></span> standard 1/0 values (yes/no still work in this version)</li>
	</ul></li>
	</ul>
	<ul>
		<li>20 nov 2006: <strong>version 4.0.3.18</strong> <a href="http://forum.textpattern.com/viewtopic.php?id=19823">changelog</a>
	<ul>
		<li>IDs &#8216;zemContactForm&#8217; and &#8216;zemSubmit&#8217; have changed to classes to allow multiple forms per page</li>
	</ul></li>
	<ul>
		<li>New language strings: &#8216;form_used&#8217;, &#8216;invalid_utf8&#8217;, &#8216;max_warning&#8217;, &#8216;name&#8217;, &#8216;refresh&#8217;, &#8216;secret&#8217;</li>
	</ul></li>
		<li>12 mar 2006: <strong>version 4.0.3.17</strong> <a href="http://forum.textpattern.com/viewtopic.php?id=13416">changelog</a></li>
		<li>11 feb 2006: <strong>version .16</strong></li>
		<li>06 feb 2006: <strong>version .15</strong>
		<li>03 feb 2006: <strong>version .14</strong></li>
	<ul>
		<li>Requires separate zem_contact_lang plugin</li>
	</ul></li>
		<li>29 jan 2006: <strong>version .12</strong></li>
		<li>27 jan 2006: <strong>version .11</strong></li>
		<li>23 jan 2006: <strong>version .09 and .10</strong></li>
		<li>23 jan 2006: <strong>version .08</strong></li>
		<li>17 jan 2006: <strong>version .07</strong></li>
		<li>16 jan 2006: <strong>version .05 and .06</strong></li>
		<li>15 jan 2006: <strong>version .04</strong></li>
		<li>10 jan 2006: <strong>version .03</strong></li>
	</ul>
	<ul>
		<li>19 dec 2005: <strong>version .02</strong></li>
	</ul>

	<p><a href="#top">Back to top</a></p>

	<h2 id="credits">Credits</h2>

	<ul>
		<li><strong>zem</strong> wrote the zem_contact 0.6 plugin on which this plugin was initially based.</li>
		<li><strong>Mary</strong> completely revised the plugin code.</li>
		<li><strong>Stuart</strong> Turned it into a plugin, added a revised help text and additional code. Maintained all plugin versions till 4.0.3.17.</li>
		<li><strong>wet</strong> added the zem_contact_radio tag.</li>
		<li><strong>Tranquillo</strong> added the anti-spam <span class="caps"><span class="caps">API</span></span> and zem_contact_send_article functionality.</li>
		<li><strong>aslsw66</strong>, <strong>jdykast</strong> and others (?) provided additional code</li>
		<li><strong>Ruud</strong> cleaned up and audited the code to weed out bugs and completely revised the help text. Maintainer of versions 4.0.3.18 and up.</li>
	</ul>
	<ul>
		<li>Supported and tested to destruction by the Textpattern community.</li>
	</ul>

	<p><a href="#top">Back to top</a></p>

	<h2 id="api">Zem Contact Reborn&#8217;s <span class="caps">API</span></h2>

	<p>The plugin <span class="caps">API</span> of zem contact, developed by Tranquillo, is similar to the comments <span class="caps">API</span> of Textpattern, which is explained in the Textbook <a href="http://textpattern.net/wiki/index.php?title=Plugin_Development_Topics">Plugin Development Topics</a> and <a href="http://textpattern.net/wiki/index.php?title=Combat_Comment_Spam">Combat Comment Spam</a>.</p>

	<p>Two callback events exist in zem_contact_reborn:
	<ul>
		<li><code>zemcontact.submit</code> is called after the form is submitted and the values are checked if empty or valid email addresses, but before the mail is sent.</li>
	</ul>
	<ul>
		<li><code>zemcontact.form</code> let&#8217;s you insert content in the contact form as displayed to the visitor.</li>
	</ul></p>

	<p>For reference here are the commands that will be interesting to plugin developers:</p>

<pre><code>// This will call your function before the form is submitted
// So you can analyse the submitted data
register_callback(&#39;abc_myfunction&#39;,&#39;zemcontact.submit&#39;);
</code>
<code>// This will call your function and add the output (use return $mystuff)
// to the contact-form.
register_callback(&#39;abc_myotherfunction2&#39;,&#39;zemcontact.form&#39;);
</code>
<code>// To get hold of the form-variables you can use
global zem_contact_form;
</code>
<code>// With the following two lines you can tell zem_contact if your
// plugin found spam
$evaluator =&#38; get_zemcontact_evaluator();
</code>
<code>// The passed value must be non-zero to mark the content as spam.
// Value must be a number between 0 and 1.
$evaluator -&#62; add_zemcontact_status(1);
</code></pre>

	<p>Multiple plugins can be active at the same time and each of them can mark the submitted content as spam and prevent the form from being submitted.</p>

	<p><strong>An example of a plug-in connecting to Zem Contact Reborn&#8217;s API:</strong></p>

<pre><code>register_callback(&#39;pap_zemcontact_form&#39;,&#39;zemcontact.form&#39;);
register_callback(&#39;pap_zemcontact_submit&#39;,&#39;zemcontact.submit&#39;);
</code>
<code>function pap_zemcontact_form() {
  $field = &#39;&#60;div style=&#34;display:none&#34;&#62;&#39;.
    finput(&#39;text&#39;,&#39;phone&#39;,ps(&#39;phone&#39;),&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;phone&#39;).&#39;&#60;br /&#62;&#39;.
    finput(&#39;text&#39;,&#39;mail&#39;,ps(&#39;mail&#39;),&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;&#39;,&#39;mail&#39;).&#39;&#60;/div&#62;&#39;;
  return $field;&#60;/code&#62;
}
</code>
<code>function pap_zemcontact_submit() {
  $checking_mail_field = trim(ps(&#39;mail&#39;));
  $checking_phone_field = trim(ps(&#39;phone&#39;));
</code>
<code>  $evaluation =&#38; get_zemcontact_evaluator();
</code>
<code>  // If the hidden fields are filled out, the contact form won&#39;t be submitted!
  if ($checking_mail_field != &#39;&#39; or $checking_phone_field != &#39;&#39;) {
    $evaluation -&#62; add_zemcontact_status(1);
  }
  return;
}
</code></pre>

	<p><a href="#top">Back to top</a></p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>