<?php

// Load the configuration data
$contact_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();

if(Route::is($contact_config['slug'])) {
    // Add contact form CSS
    Weapon::add('shell_after', function() {
        echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'form.css');
    }, 11);
    // Submitting ...
    if($request = Request::post()) {
        // Validate token ...
        if( ! isset($request['token']) || Session::get('contact_form_token') !== $request['token']) {
            Notify::error($speak->notify_invalid_token);
            Guardian::kick($config->url_current);
        }
        // Check for empty subject field
        if(trim($request['subject']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->plugin_contact->subject));
        }
        // Check for empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->plugin_contact->name));
        }
        // Check for empty email field
        if(trim($request['email']) !== "") {
            // Check for invalid email address
            if( ! Guardian::check($request['email'], '->email')) {
                Notify::error($speak->notify_invalid_email);
            }
        } else {
            Notify::error(Config::speak('notify_error_empty_field', $speak->plugin_contact->email));
        }
        // Check for empty message field
        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->plugin_contact->message));
        }
        // Check for math answer
        if( ! Guardian::checkMath($request['math'])) {
            Notify::error($speak->notify_invalid_math_answer);
        }
        // Check for characters length in subject field
        if(Guardian::check($request['subject'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->plugin_contact->subject));
        }
        // Check for characters length in name field
        if(Guardian::check($request['name'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->plugin_contact->name));
        }
        // Check for characters length in email field
        if(Guardian::check($request['email'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->plugin_contact->email));
        }
        // Check for characters length in message field
        if(Guardian::check($request['message'], '->too_long', 4000)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->plugin_contact->message));
        }
        // Check for spam email and spam keywords in contact message
        $fucking_words = explode(',', $config->keywords_spam);
        foreach($fucking_words as $spam) {
            if($fuck = trim($spam)) {
                if(
                    $request['email'] === $fuck || // Block by email address
                    Get::IP() === $fuck || // Block by IP address
                    strpos(strtolower($request['message']), strtolower($fuck)) !== false // Block by message word(s)
                ) {
                    Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $fuck . '</strong>');
                    break;
                }
            }
        }
        if( ! Notify::errors()) {
            if(trim($contact_config['email_recipient']) === "") {
                $contact_config['email_recipient'] = $config->author->email;
            }
            $header  = "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $header .= "From: " . strip_tags($request['name']) . " <" . $request['email'] . ">\r\n";
            $header .= "Reply-To: " . $request['email'] . "\r\n";
            $header .= "Return-Path: " . $request['email'] . "\r\n";
            $header .= "X-Mailer: PHP/" . phpversion();
            $th = 'font:inherit;text-align:right;vertical-align:top;margin:0;padding:0.5em 0.8em;font-weight:normal;background-color:#ccc;width:150px;';
            $td = 'font:inherit;text-align:left;vertical-align:top;margin:0;padding:0.5em 0.8em;font-weight:normal;background-color:#eee;';
            $message  = '<html><head><meta charset="utf-8"></head><body><table style="width:100%;margin:0;padding:0;border:none;border-collapse:separate;border-spacing:2px;font:normal normal 13px/1.4 Helmet,FreeSans,Sans-Serif;color:black">';
            $message .= '<tr><th style="' . $th . '">' . $speak->date . '</th><td style="' . $td . '">' . date('Y/m/d H:i:s') . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->plugin_contact->name . '</th><td style="' . $td . '">' . strip_tags($request['name']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->plugin_contact->email . '</th><td style="' . $td . '">' . strip_tags($request['email']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->plugin_contact->subject . '</th><td style="' . $td . '">' . strip_tags($request['subject']) . '</td></tr>';
            $message .= '<tr><th style="' . $th . '">' . $speak->plugin_contact->message . '</th><td style="' . $td . '">' . Text::parse($request['message'], '->html') . '</td></tr>';
            $message .= '</table></body></html>';
            if(mail(Text::parse($contact_config['email_recipient'], '->decoded_html'), $contact_config['email_subject'] . ': ' . strip_tags($request['subject']), $message, $header)) {
                Notify::success(Config::speak('notify_success_submitted', $speak->email));
            } else {
                Notify::error($speak->error . '.');
            }
        }
        Guardian::kick($config->url_current . '#contact-form');
    }
    // Loading cargo ...
    $contact_form_token = Guardian::hash();
    Session::set('contact_form_token', $contact_form_token);
    ob_start();
    require __DIR__ . DS . 'workers' . DS . 'form.php';
    $contact_html = ob_get_clean();
    // Replace string `{{contact_form}}` in the
    // selected page with the HTML markup of contact form
    Filter::add('page:content', function($content) use($contact_html) {
        if( ! Text::check($content)->has('{{contact_form}}')) {
            return $content . $contact_html;
        }
        return str_replace('{{contact_form}}', $contact_html, $content);
    }, 9);
}