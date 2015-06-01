<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . basename(__DIR__) . '/update', function() use($config, $speak) {
    if( ! Guardian::happy()) {
        Shield::abort();
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid email address
        if( ! empty($request['email_recipient']) && ! Guardian::check($request['email_recipient'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
        }
        $request['email_recipient'] = Text::parse($request['email_recipient'], '->ascii');
        if( ! isset($request['html_parser'])) $request['html_parser'] = false;
        unset($request['token']); // Remove token from request array
        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'config.txt');
            Notify::success(Config::speak('notify_success_updated', $speak->plugin));
            Session::kill('error_input');
        } else {
            // `Notify::errors()` cannot be used after redirection, so I use
            //  sessions for handling the `Guardian::wayback()` in `configurator.php`
            Session::set('error_input', true);
        }
        Guardian::kick(dirname($config->url_current));
    }
});