<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <p><?php echo $speak->plugin_contact_title_select_page; ?></p>
  <p>
    <select name="slug" class="input-block">
    <?php

    $options = array();
    $contact_config = Session::get('error_input') === true ? Guardian::wayback() : File::open(PLUGIN . DS . 'contact' . DS . 'states' . DS . 'config.txt')->unserialize();
    if($s_pages = Get::pages()) {
        foreach($s_pages as $s_page) {
            list($s_time, $s_kind, $s_slug) = explode('_', basename($s_page, '.txt'));
            $options[] = $s_slug; // Take the page slug
        }
        sort($options);
        foreach($options as $option) {
            echo '<option value="' . $option . '"' . ($option == $contact_config['slug'] ? ' selected' : "") . '>' . $config->url . '/' . $option . '</option>';
        }
        if(trim($contact_config['email_recipient']) === "") {
            $contact_config['email_recipient'] = $config->author_email;
        }
    } else {
        echo '<option disabled>' . Config::speak('notify_empty', array(strtolower($speak->pages))) . '</option>';
    }

    ?>
    </select>
  </p>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_recipient; ?></span>
    <span class="grid span-4"><input name="email_recipient" type="email" class="input-block" value="<?php echo Text::parse($contact_config['email_recipient'])->to_decoded_html; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_subject; ?></span>
    <span class="grid span-4"><input name="email_subject" type="text" class="input-block" value="<?php echo $contact_config['email_subject']; ?>"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <span class="grid span-4"><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>