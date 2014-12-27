<?php

// Load the configuration file
$contact_config = File::open(PLUGIN . DS . 'contact' . DS . 'states' . DS . 'config.txt')->unserialize();

if($config->url_current == $config->url . '/' . $contact_config['slug']) {

    Weapon::add('shell_after', function() {
        echo O_BEGIN . '<style>
.contact-form input[name="math"] {width:4em}
@media (max-width:500px) {
  .contact-form .grid {
    float:none;
    display:block;
    width:auto;
    margin:0;
    padding:0;
    text-align:left;
  }
  .contact-form input[name="math"] {width:100%}
  .contact-form .form-label {margin-bottom:1.3846153846153846em}
  .contact-form .grid-group:last-child .grid:first-child {display:none}
}
</style>' . O_END;
    }, 11);

    if($contact_config['html_parser']) {
        Weapon::add('sword_before', function() use($contact_config) {
            echo O_BEGIN . '<script>DASHBOARD.is_html_parser_enabled = ' . ($contact_config['html_parser'] ? 'true' : 'false') . ';</script>' . O_END;
        }, 12);
    }

    if($contact_config['text_editor']) {
        Weapon::add('shell_after', function() use($config) {
            if( ! Asset::loaded('manager/shell/editor.css')) echo Asset::stylesheet('manager/shell/editor.css');
            if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
        }, 11);
        Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
            if( ! Asset::loaded('manager/sword/editor/editor.min.js')) echo Asset::javascript('manager/sword/editor/editor.min.js');
            if( ! Asset::loaded('manager/sword/editor/mte.min.js')) echo Asset::javascript('manager/sword/editor/mte.min.js');
            echo O_BEGIN . '<script>(function(a,b){var c=b.getElementById(\'contact-form\').getElementsByTagName(\'textarea\')[0];c.className=c.className+= \' code\';new MTE(c,{shortcut:1,toolbarClass:\'editor-toolbar cf\'})})(window,document);</script>' . O_END;
        }, 11);
    }

    if($request = Request::post()) {

        if(Session::get('contact_form_token') !== $request['token']) {
            Notify::error($speak->notify_invalid_token);
            Guardian::kick($config->url_current);
        }

        // Check for empty subject field
        if(trim($request['subject']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_subject)));
        }

        // Check for empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_name)));
        }

        // Check for empty email field
        if(trim($request['email']) !== "") {
            // Check for invalid email address
            if( ! Guardian::check($request['email'])->this_is_email) {
                Notify::error($speak->notify_invalid_email);
            }
        } else {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_email)));
        }

        // Check for empty message field
        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_message)));
        }

        // Check for math answer
        if( ! Guardian::checkMath($request['math'])) {
            Notify::error($speak->notify_invalid_math_answer);
        }

        // Check for characters length in subject field
        if(Guardian::check($request['subject'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_subject)));
        }

        // Check for characters length in name field
        if(Guardian::check($request['name'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_name)));
        }

        // Check for characters length in email field
        if(Guardian::check($request['email'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_email)));
        }

        // Check for characters length in message field
        if(Guardian::check($request['message'], 4000)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_message)));
        }

        // Check for spam email and spam keywords in contact message
        $fucking_words = explode(',', $config->spam_keywords);
        foreach($fucking_words as $spam) {
            $fuck = trim($spam);
            if($fuck !== "") {
                if(
                    $request['email'] == $fuck || // Block by email address
                    Get::IP() != 'N/A' && Get::IP() == $fuck || // Block by IP address
                    strpos(strtolower($request['message']), strtolower($fuck)) !== false // Block by message word(s)
                ) {
                    Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $fuck . '</strong>');
                    break;
                }
            }
        }

        if( ! Notify::errors()) {

            if(empty($contact_config['email_recipient'])) {
                $contact_config['email_recipient'] = $config->author_email;
            }

            $header  = "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $header .= "From: " . $request['email'] . "\r\n";
            $header .= "Reply-To: " . $request['email'] . "\r\n";
            $header .= "Return-Path: " . $request['email'] . "\r\n";
            $header .= "X-Mailer: PHP/" . phpversion();

            $th = 'font:inherit;text-align:right;vertical-align:top;margin:0;padding:0.5em 0.8em;font-weight:normal;background-color:#ccc;width:150px;';
            $td = 'font:inherit;text-align:left;vertical-align:top;margin:0;padding:0.5em 0.8em;font-weight:normal;background-color:#eee;';

            $message  = '<html><head><meta charset="utf-8"></head><body><table style="width:100%;margin:0;padding:0;border:none;border-collapse:separate;border-spacing:2px;font:normal normal 13px/1.4 Helmet,FreeSans,Sans-Serif;color:black">';
            $message .= '<tr><th style="' . $th . '">' . $speak->date . '</th><td style="' . $td . '">' . date('Y/m/d H:i:s') . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->contact_name . '</th><td style="' . $td . '">' . strip_tags($request['name']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->contact_email . '</th><td style="' . $td . '">' . strip_tags($request['email']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->contact_subject . '</th><td style="' . $td . '">' . strip_tags($request['subject']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->contact_message . '</th><td style="' . $td . '">' . ($contact_config['html_parser'] ? Text::parse($request['message'])->to_html : $request['message']) . '</td></tr>';
            $message .= '</table></body></html>';

            if(mail(Text::parse($contact_config['email_recipient'])->to_decoded_html, $contact_config['email_subject'] . ': ' . strip_tags($request['subject']), $message, $header)) {
                Notify::success(Config::speak('notify_success_submitted', array($speak->email)));
            } else {
                Notify::error($speak->error . '.');
            }

        }

        Guardian::kick($config->url_current . '#contact-form');

    }

    $contact_form_token = sha1(uniqid(mt_rand(), true));
    Session::set('contact_form_token', $contact_form_token);

    ob_start();
    require PLUGIN . DS . 'contact' . DS . 'workers' . DS . 'form.php';
    $contact_html = ob_get_contents();
    ob_end_clean();

    // Replace string `{{contact_form}}` in the
    // selected page with the HTML markup of contact form
    Filter::add('content', function($content) use($contact_html) {
        return str_replace('{{contact_form}}', $contact_html, $content);
    });

}


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/contact/update', function() use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        // Check for invalid email address
        if( ! empty($request['email_recipient']) && ! Guardian::check($request['email_recipient'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
        }

        if( ! isset($request['text_editor'])) $request['text_editor'] = false;
        if( ! isset($request['html_parser'])) $request['html_parser'] = false;

        $request['email_recipient'] = Text::parse($request['email_recipient'])->to_ascii;

        unset($request['token']); // Remove token from request array

        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(PLUGIN . DS . 'contact' . DS . 'states' . DS . 'config.txt');
            Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
            Session::kill('error_input');
        } else {
            // `Notify::errors()` cannot be used after redirection, so I use
            //  sessions for handling the `Guardian::wayback()` in `configurator.php`
            Session::set('error_input', true);
        }

        Guardian::kick(dirname($config->url_current));

    }

});