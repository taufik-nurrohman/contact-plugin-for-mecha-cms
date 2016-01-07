<form class="contact-form" id="contact-form" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Notify::read(); ?>
  <?php echo Form::hidden('token', $contact_form_token); ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_contact->subject; ?></span>
    <span class="grid span-5"><?php echo Form::text('subject', Guardian::wayback('subject'), null, array('class' => 'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_contact->name; ?></span>
    <span class="grid span-5"><?php echo Form::text('name', Guardian::wayback('name'), null, array('class' => 'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_contact->email; ?></span>
    <span class="grid span-5"><?php echo Form::email('email', Guardian::wayback('email'), null, array('class' => 'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_contact->message; ?></span>
    <span class="grid span-5"><?php echo Form::textarea('message', Guardian::wayback('message'), null, array('class' => 'textarea-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo Guardian::math(); ?> =</span>
    <span class="grid span-5"><?php echo Form::text('math', "", null, array('autocomplete' => 'off')); ?></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5"><?php echo Form::button(Cell::i("", array(
        'class' => array(
            'fa',
            'fa-send'
        )
    )) . ' ' . $speak->send, null, null, null, array(
        'class' => array(
            'btn',
            'btn-construct'
        )
    )); ?></span>
  </div>
</form>