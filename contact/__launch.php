<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid email address
        if( ! empty($request['email_recipient']) && ! Guardian::check($request['email_recipient'], '->email')) {
            Notify::error($speak->notify_invalid_email);
        }
        $request['email_recipient'] = Text::parse($request['email_recipient'], '->broken_entity');
        if( ! isset($request['html_parser'])) $request['html_parser'] = false;
        unset($request['token']); // Remove token from request array
        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS . 'config.txt');
            Notify::success(Config::speak('notify_success_updated', $speak->plugin) . ' <a class="pull-right" href="' . $config->url . '/' . $request['slug'] . '" target="_blank">' . Jot::icon('eye') . ' ' . $speak->view . '</a>');
            Session::kill('error_input');
        } else {
            // `Notify::errors()` cannot be used after redirection, so I use
            //  sessions for handling the `Guardian::wayback()` in `configurator.php`
            Session::set('error_input', true);
        }
        Guardian::kick(File::D($config->url_current));
    }
});