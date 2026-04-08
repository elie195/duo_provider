<?php if (!empty($_['duo_error'])): ?>
<div class="duo-error">
    <p><?php p($l->t('Duo authentication is currently unavailable.')); ?></p>
    <p><small><?php p($_['duo_error']); ?></small></p>
</div>
<?php elseif ($_['duo_code_pending']): ?>
<form method="POST" id="duo-verify-form">
    <input type="hidden" name="challenge" value="duo_redirect_complete" />
</form>
<p><?php p($l->t('Completing Duo authentication…')); ?></p>
<?php else: ?>
<!-- <p><?php p($l->t('You will be redirected to Duo Security to complete authentication.')); ?></p> -->
<p>
    <a href="<?php p($_['duo_auth_url']); ?>" class="button primary">
        <?php p($l->t('Authenticate with Duo')); ?>
    </a>
</p>
<?php endif; ?>
