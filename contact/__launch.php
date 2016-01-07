<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid email address
        if(trim($request['email_recipient']) !== "" && ! Guardian::check($request['email_recipient'], '->email')) {
            Notify::error($speak->notify_invalid_email);
        }
        $request['email_recipient'] = Text::parse($request['email_recipient'], '->broken_entity');
        unset($request['token']); // Remove token from request array
        File::write($request['css'])->saveTo(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'form.css');
        unset($request['css']); // Remove CSS from request array
        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(__DIR__ . DS . 'states' . DS . 'config.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', $speak->plugin) . ($request['slug'] ? ' <a class="pull-right" href="' . Filter::colon('page:url', $config->url . '/' . $request['slug']) . '" target="_blank">' . Jot::icon('eye') . ' ' . $speak->view . '</a>' : ""));
            Session::kill('error_input');
        } else {
            // `Notify::errors()` can't be used after redirection, so I use
            //  sessions for handling the `Guardian::wayback()` in `configurator.php`
            Session::set('error_input', true);
        }
        Guardian::kick(File::D($config->url_current));
    }
});