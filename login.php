<?php
require_once __DIR__.'/components/layout.php';
$next=safe_next(query('next'));
if ($signed=current_user()) redirect($signed['role']==='admin' && $next==='home.php'?'admin/adminDash.php':$next);
$signup=query('mode')==='signup';$state=form_state($signup?'signup':'login');$errors=$state['errors'];$values=$state['values'];
page_start($signup?'Create your account':'Welcome back','login');
?>
<main id="main" class="container auth-layout"><section class="auth-intro"><h1><?= $signup?'Fresh starts<br>begin here.':'Your next fresh<br>start awaits.' ?></h1><p>Book the care your clothes need. Keep your details and your laundry orders in one place.</p><div class="auth-art" aria-hidden="true"><?= icon('wash',110) ?><span><?= icon('leaf',48) ?></span></div><a class="text-link" href="<?= h(url('package.php')) ?>">Explore services first <?= icon('arrow',18) ?></a></section>
<section class="auth-panel" aria-labelledby="auth-heading"><div class="auth-tabs"><a href="<?= h(url('login.php?next='.rawurlencode($next))) ?>" <?= !$signup?'aria-current="page"':'' ?>>Sign in</a><a href="<?= h(url('login.php?mode=signup&next='.rawurlencode($next))) ?>" <?= $signup?'aria-current="page"':'' ?>>Create account</a></div><h2 id="auth-heading"><?= $signup?'Make yourself at home.':'Good to see you again.' ?></h2><p><?= $signup?'A few details, then you are ready to book.':'Enter your details to pick up where you left off.' ?></p>
<?php if(isset($errors['form'])): ?><div class="notice notice-error" role="alert"><?= h($errors['form']) ?></div><?php endif ?>
<form method="post" action="<?= h(url($signup?'db/db_signup.php':'db/db_login.php')) ?>" class="stack-form" data-submit-form>
<?= csrf_field() ?><input type="hidden" name="next" value="<?= h($next) ?>">
<?php if($signup) field('name','Full name','text',$values['name']??'',$errors,'required maxlength="100" autocomplete="name"'); ?>
<?php field('username','Username','text',$values['username']??'',$errors,'required maxlength="'.($signup?'32':'50').'" autocomplete="username"'.($signup?' minlength="3" pattern="[a-zA-Z0-9_-]+"':'')); ?>
<?php if($signup): ?>
<?php field('email','Email address','email',$values['email']??'',$errors,'required maxlength="255" autocomplete="email"'); ?>
<?php field('phone','Phone number','tel',$values['phone']??'',$errors,'required minlength="7" maxlength="15" autocomplete="tel"'); ?>
<?php endif ?>
<div class="field"><label for="password">Password</label><div class="password-field"><input id="password" name="password" type="password" required <?= $signup?'minlength="10" maxlength="72" autocomplete="new-password"':'maxlength="1024" autocomplete="current-password"' ?> <?= isset($errors['password'])?'aria-invalid="true" aria-describedby="error-password"':'' ?>><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-pressed="false">Show</button></div><?= error_text($errors,'password') ?><?php if($signup): ?><p class="field-hint">Use at least 10 characters. Avoid reusing a password from another account.</p><?php endif ?></div>
<button class="button button-block" type="submit" data-loading-label="<?= $signup?'Creating account...':'Signing in...' ?>"><?= $signup?'Create your account':'Sign in' ?> <?= icon('arrow') ?></button>
<?php if($signup): ?><p class="form-footnote">This is a demo. Use test contact details. <a href="<?= h(url('privacy.php')) ?>">How your details are used</a>.</p><?php endif ?>
</form></section></main>
<?php page_end() ?>
