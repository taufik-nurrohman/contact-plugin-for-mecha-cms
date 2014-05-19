<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><?php echo $speak->plugin_contact_title_select_page; ?></p>
  <p>
    <select name="slug" class="input-block">
    <?php

    $options = array();
    $states = Session::get('error_input') === true ? Guardian::wayback() : unserialize(File::open(PLUGIN . '/contact/states/config.txt')->read());
    if($s_pages = Get::pages('ASC')) {
        foreach($s_pages as $s_page) {
            list($s_time, $s_kind, $s_slug) = explode('_', basename($s_page, '.txt'));
            $options[] = $s_slug; // take the page slug
        }
        foreach($options as $option) {
            echo '<option value="' . $option . '"' . ($option == $states['slug'] ? ' selected' : "") . '>' . $config->url . '/' . $option . '</option>';
        }
        if(empty($states['email_recipient'])) {
            $states['email_recipient'] = $config->author_email;
        }
    } else {
        echo '<option disabled>' . Config::speak('notify_empty', array(strtolower($speak->pages))) . '</option>';
    }

    ?>
    </select>
  </p>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_recipient; ?></span>
    <span class="grid span-4"><input name="email_recipient" type="email" class="input-block" value="<?php echo Text::parse($states['email_recipient'])->to_decoded_html; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_contact_title_subject; ?></span>
    <span class="grid span-4"><input name="email_subject" type="text" class="input-block" value="<?php echo $states['email_subject']; ?>"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-2 form-label">&nbsp;</span>
    <span class="grid span-4"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>