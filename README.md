# Duo 2FA provider for ownCloud

## About
Two-factor authentication (2FA) framework was added to ownCloud 9.1. This project leverages this new framework to integrate Duo 2FA into ownCloud.

~~Currently, some modifications to the core TwoFactorAuthentication framework were necessary, specifically to allow the Duo "iframe" to be displayed on the page, due to the default CSP restrictions. The changes are included in my fork of the ownCloud core repo: https://github.com/elie195/core~~

**Update:** The changes have been merged into the ownCloud master branch, and will be available as of ownCloud 9.2: https://github.com/owncloud/core/tree/master

**Update - 3/23/2017:** The changes in ownCloud core are now targetted at version 10.0 instead of 9.2. 

**Update - 4/17/2017:** If you _really_ want to use this plugin in the current "stable" ownCloud release, see the "Patching an unsupported ownCloud core installation" instructions below.

**Update - 6/2/2017:** This plugin is verified working on version 10.0.2RC1


## Requirements

- PHP >=5.6 (Duo SDK requirement) - See guide at the bottom for Ubuntu 14.04 instructions
- Duo application settings (IKEY, SKEY, HOST)
- ownCloud 10.0 or later (https://github.com/owncloud/core)
    
## Installation


### Automatically through ownCloud Marketplace (ownCloud 10.0+)
1. Download the "Duo Two-Factor Provider" app from the [ownCloud Marketplace](https://marketplace.owncloud.com/):

    ![Image of Duo in Marketplace](https://github.com/elie195/duo_provider/raw/dev/screenshots/market_duo.png)
    
2. Follow steps 2 and 3 from the "Manually" section

### Manually
1. Clone this repo to the 'apps/duo' directory of your ownCloud installation. i.e.:

    ```
    cd /var/www/owncloud/apps && git clone https://github.com/elie195/duo_provider.git duo
    ```

2. Ensure the app is enabled in the ownCloud GUI

    ![Image of Duo app in settings](https://github.com/elie195/duo_provider/raw/master/screenshots/duo.PNG)

3. Configure your own **IKEY**, **SKEY**, **HOST** values under **Settings** > **Admin section** > **Additional**:

    ![Image of Duo settings](https://github.com/elie195/duo_provider/raw/master/screenshots/settings.png)

    

### Patching an unsupported ownCloud core installation (use at your own risk!)

1. Download and configure the plugin normally per the installation instructions on Github

2. Patch your existing ownCloud installation with the changes required for the Duo plugin to work:

	```
    wget -O /var/www/owncloud/duo_provider.patch https://github.com/owncloud/core/commit/d1c9c10fda3afc54e19f24245fd55372c4f48371.patch
	cd /var/www/owncloud && git apply duo_provider.patch
    ```
    
3. Modify the `min-version` attribute of the `owncloud` element in `duo/appinfo/info.xml` to reflect the ownCloud version you're currently running (**"9.1"**, for example) 

Using this workaround will most likely cause the built-in code integrity check to fail (and might have some other unintended side-effects). This workaround is unsupported, so exercise caution!


## Notes

**HTTPS _MUST_ be enabled on your ownCloud server for this plugin to work!**

**The "Clear settings" button in the settings will also disable the Duo plugin itself! Once the plugin is disabled, its settings won't show up on the "Additional" settings page. You must re-enable the app from the "Apps" settings page to get the Duo settings to show up again.

### LDAP integration

If you're using LDAP, the 2FA won't work right off the bat, since ownCloud refers to LDAP users via their UUID, so I'm not able to pass the plaintext username to Duo, and the authentication fails. See issue #2 for more details.

To change the LDAP settings so that the internal identifier uses the username instead of the UUID, do the following (I'm using AD LDAP, so the attributes are named accordingly): Go into "Expert" mode in the ownCloud LDAP settings, and set "Internal Username Attribute" to "sAMAccountName". Note that this only affects new users. Existing users must be deleted and recreated, so use at your own risk.

### Added features
- August 21, 2017: Added a "Generate" button in the Admin panel for the AKEY field. This allows an administrator to easily generate a new AKEY.
- August 12, 2017: Added ability to prepend usernames with a custom NetBIOS domain name before usernames are sent to Duo for validation. For example, if this feature is enabled and NetBIOS domain is set to "TEST", an ownCloud user with username "user" will become "TEST\user" when sending the username to Duo.([https://github.com/elie195/duo_provider/issues/11](https://github.com/elie195/duo_provider/issues/11))
- July 6, 2017: Added proxy support for the "IP Bypass" feature. When IP Bypass is enabled, the plugin will now attempt to parse the "X-Forwarded-For" header, if present. If it's not present, it will fallback to using the source IP. **Note: enabling IP Bypass can be a security risk. Only enable it if you know what you're doing!**
- June 2, 2017: Migrated the app's settings into the ownCloud UI instead of using a configuration file (duo.ini). This was done in-order to avoid tripping the built-in ownCloud file integrity check (see [issue #6](https://github.com/elie195/duo_provider/issues/6) for more details). For this reason, please delete/move your current `duo.ini` config file so that ownCloud won't identify it as an "extra" file. The `duo_php` SDK has also been updated to the latest version available on [Github](https://github.com/duosecurity/duo_php).
- August 27, 2016: You may now configure specific client IP addresses to bypass Duo 2FA in duo.ini. Check duo.ini.example for more details. ([https://github.com/elie195/duo_provider/issues/3](https://github.com/elie195/duo_provider/issues/3))
- August 27, 2016: You may now configure an option in duo.ini to bypass Duo 2FA for LDAP users only. Check duo.ini.example for more details.([https://github.com/elie195/duo_provider/issues/4](https://github.com/elie195/duo_provider/issues/4))

### Misc

As of version 2.3.0, there is now a "Generate AKEY" button in the admin settings panel. This button will automatically generate a 40-bit AKEY. If an AKEY is not entered, the AKEY will be automatically generated. The "AKEY" is an application-specific secret string. If strong security is important to you (hey, you're setting up a security-oriented plugin here afterall), feel free to generate your own "AKEY" by executing the following Python code:

    import os, hashlib
    print hashlib.sha1(os.urandom(32)).hexdigest()

Or if you're using Python3:

    import os, hashlib
    print(hashlib.sha1(os.urandom(32)).hexdigest())

You may then take this new AKEY and insert it into your config.

This has been tested on ownCloud 10.0 (cloned from "master" branch of the official ownCloud repo) on a CentOS 7 server, as well as an Ubuntu 14.04 server where ownCloud was installed from packages (both with manually upgraded PHP: PHP 5.6.24 on CentOS 7, PHP 7.0.9-1 on Ubuntu 14.04). 

See https://duo.com/docs/duoweb for more info on the Duo Web SDK and additional details about the "AKEY" variable.

See https://www.digitalocean.com/community/tutorials/how-to-upgrade-to-php-7-on-ubuntu-14-04 for a PHP upgrade guide for Ubuntu 14.04

Check out my ownCloud Application page: https://apps.owncloud.com/content/show.php?content=174748
**New (June 2, 2017)**: Now in the ownCloud Marketplace: https://marketplace.owncloud.com/apps/duo
