<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_select_page . ' ' . Jot::info($speak->plugin_contact_description_select_page); ?></span>
    <?php

    $options = array();
    $contact_css = Session::get('error_input') === true ? Guardian::wayback('css') : File::open(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'form.css')->read();
    $contact_config = Session::get('error_input') === true ? Guardian::wayback() : File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
    if(trim($contact_config['email_recipient']) === "") {
        $contact_config['email_recipient'] = $config->author->email;
    }
    if($s_pages = Get::pages()) {
        foreach($s_pages as $s_page) {
            list($s_time, $s_kind, $s_slug) = explode('_', File::N($s_page));
            $options[$s_slug] = Get::pageAnchor($s_page)->title;
        }
        asort($options);
    }
    $options = array("" => '&mdash; ' . $speak->none . ' &mdash;') + $options;

    ?>
    <span class="grid span-4"><?php echo Form::select('slug', $options, $contact_config['slug'], array('class' => 'select-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_recipient; ?></span>
    <span class="grid span-4"><?php echo Form::email('email_recipient', Text::parse($contact_config['email_recipient'], '->decoded_html'), null, array(
        'class' => 'input-block'
    )); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_subject; ?></span>
    <span class="grid span-4"><?php echo Form::text('email_subject', $contact_config['email_subject'], null, array(
        'class' => 'input-block'
    )); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_css; ?></span>
    <span class="grid span-4"><?php echo Form::textarea('css', $contact_css, null, array('class' => array('textarea-block', 'textarea-expand', 'code'))); ?></span>
  </label>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <span class="grid span-4"><?php echo Jot::button('action', $speak->update); ?></span>
  </div>
</form>