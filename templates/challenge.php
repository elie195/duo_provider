<?php
/**
 * Template for Duo Universal Prompt v4.
 *
 * Two states:
 *   1. First load: show "Authenticate with Duo" button that opens the Duo-hosted prompt.
 *   2. Returning from Duo callback (duo_code_pending = true): auto-submit the hidden form
 *      so ownCloud calls verifyChallenge() immediately without user interaction.
 */
?>

<?php if (!empty($_['duo_error'])): ?>
<div class="duo-error">
    <p><?php p($l->t('Duo authentication is currently unavailable.')); ?></p>
    <p><small><?php p($_['duo_error']); ?></small></p>
</div>
<?php elseif ($_['duo_code_pending']): ?>
<!-- Returning from Duo: auto-submit to trigger verifyChallenge() -->
<form method="POST" id="duo-verify-form">
    <input type="hidden" name="challenge" value="duo_redirect_complete" />
</form>
<p><?php p($l->t('Completing Duo authentication…')); ?></p>
<script>
    document.getElementById('duo-verify-form').submit();
</script>
<?php else: ?>
<!-- Initial load: send user to Duo -->
<form method="POST" id="duo-verify-form" style="display:none;">
    <input type="hidden" name="challenge" value="duo_redirect_complete" />
</form>
<p><?php p($l->t('You will be redirected to Duo Security to complete authentication.')); ?></p>
<p>
    <a href="<?php p($_['duo_auth_url']); ?>" class="button primary">
        <?php p($l->t('Authenticate with Duo')); ?>
    </a>
</p>
<?php endif; ?>
