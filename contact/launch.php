<?php

// Load the configuration file
$contact_config = File::open(PLUGIN . DS . 'contact' . DS . 'states' . DS . 'config.txt')->unserialize();

if($config->url_current == $config->url . '/' . $contact_config['slug']) {

    Weapon::add('shell_after', function() {
        echo '<style>
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
</style>';
    }, 11);

    if($request = Request::post()) {

        if(Session::get('contact_form_token') !== $request['token']) {
            Notify::error($speak->notify_invalid_token);
            Guardian::kick($config->url_current);
        }

        // Checks for empty subject field
        if(trim($request['subject']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_subject)));
        }

        // Checks for empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_name)));
        }

        // Checks for empty email field
        if(trim($request['email']) !== "") {
            if( ! Guardian::check($request['email'])->this_is_email) {
                Notify::error($speak->notify_invalid_email);
            }
        } else {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_email)));
        }

        // Checks for empty message field
        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->contact_message)));
        }

        // Checks for math answer
        if( ! Guardian::checkMath($request['math'])) {
            Notify::error($speak->notify_invalid_math_answer);
        }

        // Checks for characters length in subject field
        if(strlen($request['subject']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_subject)));
        }

        // Checks for characters length in name field
        if(strlen($request['name']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_name)));
        }

        // Checks for characters length in email field
        if(strlen($request['email']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_email)));
        }

        // Checks for characters length in message field
        if(strlen($request['message']) > 4000) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->contact_message)));
        }

        // Checks for spam email and spam keywords in contact message
        $keywords = explode(',', $config->spam_keywords);
        foreach($keywords as $spam) {
            if((trim($spam) !== "" && $request['email'] == trim($spam)) || (trim($spam) !== "" && strpos(strtolower($request['message']), strtolower(trim($spam))) !== false)) {
                Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $spam . '</strong>');
                break;
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
            $message .= '<tr><th style="' . $th . '">' . $speak->contact_message . '</th><td style="' . $td . '">' . Text::parse($request['message'])->to_html . '</td></tr>';
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
    include PLUGIN . DS . 'contact' . DS . 'workers' . DS . 'form.php';
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

        // Checks for invalid email address
        if( ! empty($request['email_recipient']) && ! Guardian::check($request['email_recipient'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
        }

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