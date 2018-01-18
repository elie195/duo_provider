<?php
script('duo', 'duo');
style('duo', 'style');
?>

<div id="duo">
  <div class="section">
    <h2 class="app-name">Duo MFA</h2>
    <div class="box" id="duo-settings">
      <input class="fake" id="fake-username" type="text" name="username">
      <input class="fake" id="fake-password" type="password" name="password">
      <p><label for="ikey-input">IKEY: </label>
      <input class="duo-text indent" id="ikey-input" type="text" value="<?php p($_['ikey'])?>" placeholder="DIXXXXXXXXXXXXXXXXXX" autocomplete="off" /></p>
      <p><label for="skey-input">SKEY: </label>
      <input class="duo-text indent" id="skey-input" type="text" value="<?php p($_['skey'])?>" placeholder="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" autocomplete="off" /></p>
      <p><label for="host-input">Hostname: </label>
      <input class="duo-text indent" id="host-input" type="text" value="<?php p($_['host'])?>" placeholder="api-XXXXXXXX.duosecurity.com" autocomplete="off" /></p>
      <p><label for="akey-input">AKEY: </label>
      <input class="duo-text indent" id="akey-input" type="text" value="<?php p($_['akey'])?>" autocomplete="off" /><input type="button" id="gen-btn" title="Generate" value="Generate" /></p>
      <hr>
      <div id="advcd-div">
      <p><label for="ip-bypass-list">IP Bypass List  <small>(one <strong>IP</strong> or <strong>Network (CIDR)</strong> per line):</label>
      <!-- Ugly like this due to spaces when elements are separated with new lines -->
      <textarea id="ip-bypass-list" rows="7" placeholder="192.168.0.0/24"><?php foreach(explode(',', $_['ipList']) as $value): ?><?php p($value . "\r\n")?><?php endforeach; ?><?php foreach(explode(',', $_['networkList']) as $value): ?><?php p($value . "\r\n")?><?php endforeach; ?></textarea>
      <input id="ip-bypass-checkbox" class="checkbox indent" type="checkbox" <?php p($_['ipEnabled']==true ? 'checked' : '')?>>
      <label for="ip-bypass-checkbox"> IP Bypass Enabled?  <small>(advanced)</small></label></p>
      <input id="ldap-bypass-checkbox" class="checkbox indent" type="checkbox" <?php p($_['ldapEnabled']==true ? 'checked' : '')?>>
      <label for="ldap-bypass-checkbox"> LDAP Bypass Enabled?  <small>(advanced)</small></label></p>
      <input id="netbios-domain-checkbox" class="checkbox indent" type="checkbox" <?php p($_['netbiosEnabled']==true ? 'checked' : '')?>>
      <label for="netbios-domain-checkbox"> Prepend NetBIOS domain?  <small>(advanced)</small></label></p>
      <p><label id="netbios-label" for="netbios-input">NetBIOS Domain: </label>
      <input class="duo-text indent" id="netbios-input" type="text" value="<?php p($_['netbiosDomain'])?>" placeholder="DOMAIN"></p>
      <!-- End advanced options area -->
      </div>
      <br>
      <p><input id="enabled-checkbox" class="checkbox indent" type="checkbox" <?php p($_['globalEnabled']==true ? 'checked' : '')?>>
      <label for="enabled-checkbox"> Duo Enabled?</label></p>
      <br>
      <p><input type="button" id="save-btn" title="Save settings" value="Save">
      <input type="button" id="advcd-btn" title="Show advanced options" value="Show advanced">
      <input type="button" id="reset-btn" title="Clear all settings" value="Clear settings"></p>
    </div>
    <div id="success-msg" hidden>
      <p><strong style="color: green">Successfully saved config</strong></p>
    </div>
    <div id="reset-success-msg" hidden>
      <p><strong style="color: green">Successfully reset config</strong></p>
    </div>
  </div>
</div>
