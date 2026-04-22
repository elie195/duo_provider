# Duo 2FA provider for ownCloud

## About

Two-factor authentication (2FA) framework was added to ownCloud 9.1. This project leverages this new framework to integrate Duo 2FA into ownCloud.

This plugin has been updated to use Duo Security's Web SDK v4 since v2 has been deprecated. The newer framework drops the use of iframes in favor of an HTTP redirect flow (similar to OAuth/OIDC). Check out Duo's [SDK documentation](https://duo.com/docs/duoweb#upgrading-from-web-sdk-2) for more info about the SDK changes.

## Upgrading from 2.X (v2 SDK) releases to 3.X (v4 SDK)

### **IMPORTANT** Since the plugin now uses a redirect flow rather than an iframe, it is necessary to add/change the following config in `<owncloud_root>/config/config.php`:

```
'http.cookie.samesite' => 'Lax',
```

This change is necessary in order for Duo to be able to pass its authentication response back to the plugin for verification. This is because ownCloud sets `SameSite=Strict` on cookies by default, so a redirect flow like this isn't possible with this setting (the session cookie isn't sent back to ownCloud when Duo performs the redirect back to ownCloud unless it's set to `Lax`, in which case it will send these cookies when top-level redirects are performed like in this case).

`Lax` still protects against CSRF on POST requests and it only allows the cookie to be sent on top-level GET navigations. `Strict` simply does not work for OAuth/OIDC redirect flows like this.

---

Existing v2 applications configured in the Duo Admin panel should still work with this new version. The **AKEY** config value is no longer used in v4. However, **IKEY** and **SKEY** have had their names changed to **Client ID** and **Client Secret**, respectively. So it's possible to use the same **IKEY** and **SKEY** values provided by Duo in this new version.

If the plugin detects existing **IKEY** and **SKEY** values, it will automatically migrate these values to the new **Client ID** and **Client Secret** fields in the settings panel. According to Duo, once a v2 application tries to authenticate for the first time using the v4 framework, the application will get migrated to v4 behind-the-scenes on Duo's side (I have not tested this however).

## Requirements

- PHP >=7.4
- Duo application settings (Client ID, Client Secret, API Hostname)
- ownCloud 10.0 or later (https://github.com/owncloud/core)

## Installation

### Automatically through ownCloud Marketplace (ownCloud 10.0+)

1. Download the "Duo Two-Factor Provider" app from the [ownCloud Marketplace](https://marketplace.owncloud.com/):

   ![Image of Duo in Marketplace](https://github.com/elie195/duo_provider/raw/dev/screenshots/market_duo.png)

2. Follow steps 2, 3, and 4 from the "Manually" section

### Manually

1. Clone this repo to the 'apps/duo' directory of your ownCloud installation. i.e.:

   ```
   cd /var/www/owncloud/apps && git clone https://github.com/elie195/duo_provider.git duo
   ```

2. Ensure the app is enabled in the ownCloud GUI

   ![Image of Duo app in settings](https://github.com/elie195/duo_provider/raw/master/screenshots/duo.PNG)

3. Configure your own **Client ID**, **Client Secret**, **API Host** values under **Settings** > **Admin section** > **Additional**:

   ![Image of Duo settings](https://github.com/elie195/duo_provider/raw/master/screenshots/settings.png)

4. Add/modify the `http.cookie.samesite` setting in `config.php` setting it to `Lax`.

   ![Image of ownCloud config](https://github.com/elie195/duo_provider/raw/master/screenshots/oc_config.png)

## Notes

**HTTPS _MUST_ be enabled on your ownCloud server for this plugin to work!**

**The "Clear settings" button in the settings will also disable the Duo plugin itself! Once the plugin is disabled, its settings won't show up on the "Additional" settings page. You must re-enable the app from the "Apps" settings page to get the Duo settings to show up again.**

### LDAP integration

If you're using LDAP, the 2FA won't work right off the bat, since ownCloud refers to LDAP users via their UUID, so I'm not able to pass the plaintext username to Duo, and the authentication fails. See issue #2 for more details.

To change the LDAP settings so that the internal identifier uses the username instead of the UUID, do the following (I'm using AD LDAP, so the attributes are named accordingly): Go into "Expert" mode in the ownCloud LDAP settings, and set "Internal Username Attribute" to "sAMAccountName". Note that this only affects new users. Existing users must be deleted and recreated, so use at your own risk.

### Added features

- April 22, 2026: Bugfix
- April 8, 2026: Migrated plugin from iframe-based SDK v2 to SDK v4 with Universal Prompt
- August 21, 2017: Added a "Generate" button in the Admin panel for the AKEY field. This allows an administrator to easily generate a new AKEY.
- August 12, 2017: Added ability to prepend usernames with a custom NetBIOS domain name before usernames are sent to Duo for validation. For example, if this feature is enabled and NetBIOS domain is set to "TEST", an ownCloud user with username "user" will become "TEST\user" when sending the username to Duo.([https://github.com/elie195/duo_provider/issues/11](https://github.com/elie195/duo_provider/issues/11))
- July 6, 2017: Added proxy support for the "IP Bypass" feature. When IP Bypass is enabled, the plugin will now attempt to parse the "X-Forwarded-For" header, if present. If it's not present, it will fallback to using the source IP. **Note: enabling IP Bypass can be a security risk. Only enable it if you know what you're doing!**
- June 2, 2017: Migrated the app's settings into the ownCloud UI instead of using a configuration file (duo.ini). This was done in-order to avoid tripping the built-in ownCloud file integrity check (see [issue #6](https://github.com/elie195/duo_provider/issues/6) for more details). For this reason, please delete/move your current `duo.ini` config file so that ownCloud won't identify it as an "extra" file. The `duo_php` SDK has also been updated to the latest version available on [Github](https://github.com/duosecurity/duo_php).
- August 27, 2016: You may now configure specific client IP addresses to bypass Duo 2FA in duo.ini. Check duo.ini.example for more details. ([https://github.com/elie195/duo_provider/issues/3](https://github.com/elie195/duo_provider/issues/3))
- August 27, 2016: You may now configure an option in duo.ini to bypass Duo 2FA for LDAP users only. Check duo.ini.example for more details.([https://github.com/elie195/duo_provider/issues/4](https://github.com/elie195/duo_provider/issues/4))

This has been tested on ownCloud 10.16.0 Stable on Ubuntu 22.04 with PHP 7.4 installed according to ownCloud's docs: https://doc.owncloud.com/server/next/admin_manual/installation/quick_guides/ubuntu_22_04.html

See https://duo.com/docs/duoweb for more info on the Duo Web SDK.

Check out my ownCloud Application page: https://apps.owncloud.com/content/show.php?content=174748

**New (June 2, 2017)**: Now in the ownCloud Marketplace: https://marketplace.owncloud.com/apps/duo
