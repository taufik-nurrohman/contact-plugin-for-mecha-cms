<form class="contact-form" id="contact-form" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Notify::read(); ?>
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->contact_subject; ?></span>
    <span class="grid span-5"><input name="subject" type="text" class="input-block" value="<?php echo Guardian::wayback('subject'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->contact_name; ?></span>
    <span class="grid span-5"><input name="name" type="text" class="input-block" value="<?php echo Guardian::wayback('name'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->contact_email; ?></span>
    <span class="grid span-5"><input name="email" type="email" class="input-block" value="<?php echo Guardian::wayback('email'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->contact_message; ?></span>
    <span class="grid span-5"><textarea name="message" class="input-block"><?php echo Guardian::wayback('message'); ?></textarea></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo Guardian::math(); ?> =</span>
    <span class="grid span-5"><input name="math" type="text" value="" autocomplete="off"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1">&nbsp;</span>
    <span class="grid span-5"><button class="btn btn-success btn-send" type="submit"><i class="fa fa-send"></i> <?php echo $speak->send; ?></button></span>
  </div>
</form>