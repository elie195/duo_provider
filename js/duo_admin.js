(function ($, OC) {

    $(document).ready(function () {
        $('#advcd-div').hide();
        if ($('#netbios-domain-checkbox').is(':checked')) {
            $('#netbios-input').show();
            $('#netbios-label').show();
        } else {
            $('#netbios-input').hide();
            $('#netbios-label').hide();
        }

        // Show client_secret as password field by default
        $('#client-secret-input').attr("type", "password");

        // Strip quotes from text inputs on blur
        $('#client-id-input, #client-secret-input, #host-input').blur(function () {
            $(this).val($(this).val().replace(/['"]+/g, ''));
        });

        let advOpen = false;
        $('#advcd-btn').click(function (e) {
            e.preventDefault();
            advOpen = !advOpen;
            $('#advcd-div').css('display', advOpen ? 'block' : 'none');
        });

        $('#netbios-domain-checkbox').change(function () {
            if (this.checked) {
                $('#netbios-input').show();
                $('#netbios-label').show();
            } else {
                $('#netbios-input').hide();
                $('#netbios-label').hide();
            }
        });

        // Click to reveal client secret, re-hide on blur
        $('#client-secret-input').click(function () {
            $('#client-secret-input').attr("type", "text");
        });

        $('#client-secret-input').blur(function () {
            $('#client-secret-input').attr("type", "password");
        });

        $('#save-btn').click(function () {
            var clientId = $('#client-id-input').val();
            var clientSecret = $('#client-secret-input').val();
            var host = $('#host-input').val();
            var globalEnabled = $('#enabled-checkbox').is(':checked');
            var ipEnabled = $('#ip-bypass-checkbox').is(':checked');
            var ldapEnabled = $('#ldap-bypass-checkbox').is(':checked');
            var rawIpList = $('#ip-bypass-list').val().replace(/\n/g, ",").replace(/,+$/, "");
            var netbiosEnabled = $('#netbios-domain-checkbox').is(':checked');
            var netbiosDomain = $('#netbios-input').val().toUpperCase();

            // Create ipList and networkList arrays
            var ipListArray = rawIpList.split(",");
            var networkList = [];
            var ipList = [];
            ipListArray.forEach(function (element) {
                element = element.trim();
                if (!element) { return; }
                if (element.indexOf("/") > -1) {
                    networkList.push(element);
                } else {
                    ipList.push(element);
                }
            });

            // Comma-separated strings for storage
            ipList = ipList.join();
            networkList = networkList.join();

            // Validate required fields
            if (!clientId || !clientSecret || !host) {
                alert("Error: Client ID, Client Secret, and API Hostname fields must be filled out");
                return;
            }
            if (!netbiosDomain && netbiosEnabled) {
                alert("Error: NetBIOS domain must be specified if the option is enabled");
                return;
            }

            var url = OC.generateUrl('/apps/duo/save-settings');
            var data = {
                client_id: clientId,
                client_secret: clientSecret,
                host: host,
                globalEnabled: globalEnabled,
                ipEnabled: ipEnabled,
                ldapEnabled: ldapEnabled,
                ipList: ipList,
                networkList: networkList,
                netbiosEnabled: netbiosEnabled,
                netbiosDomain: netbiosDomain
            };
            $.post(url, data).done(function (response) {
                console.log("Successfully saved Duo config");
                $('#success-msg').removeAttr("hidden");
            }).fail(function (xhr, status, error) {
                alert("Error saving Duo config. Check browser console for details");
                console.log(error);
            });
        });

        $('#reset-btn').click(function () {
            var url = OC.generateUrl('/apps/duo/reset-settings');
            $.get(url).done(function (response) {
                $('#success-msg').attr("hidden", "hidden");
                $('#reset-success-msg').removeAttr("hidden");
            });
        });
    });

})(jQuery, OC);