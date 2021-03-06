(function($, OC) {

    $(document).ready(function() {
        //alert("Loaded duo.js");
        $('#advcd-div').hide();
        if ($('#netbios-domain-checkbox').is(':checked')) {
            $('#netbios-input').show();
            $('#netbios-label').show();
        } else {
            $('#netbios-input').hide();
            $('#netbios-label').hide();
        }

        $('#skey-input').attr("type", "password");

        $('#ikey-input, #skey-input, #host-input, #akey-input').blur(function() {
            $(this).val($(this).val().replace(/['"]+/g, ''));
        });

        $('#advcd-btn').click(function() {
            if ($('#advcd').is(":visible")) {
                $('#advcd-div').hide();
            } else {
                $('#advcd-div').show();
            }
        });

        $('#netbios-domain-checkbox').change(function() {
            if (this.checked) {
                $('#netbios-input').show();
                $('#netbios-label').show();
            } else {
                $('#netbios-input').hide();
                $('#netbios-label').hide();
            }
        });

        $('#skey-input').click(function() {
            $('#skey-input').attr("type", "text");
        });

        $('#skey-input').blur(function() {
            $('#skey-input').attr("type", "password");
        });

        function genAKey() {
            var genUrl = OC.generateUrl('/apps/duo/gen-akey');
            $.get(genUrl).done(function(response) {
                console.log(response);
                return response;
            });
        }

        $('#gen-btn').click(function() {
            var genUrl = OC.generateUrl('/apps/duo/gen-akey');
            $.get(genUrl).done(function(response) {
                $('#akey-input').val(response);
            });
        });

        $('#save-btn').click(function() {
            var ikey = $('#ikey-input').val();
            var skey = $('#skey-input').val();
            var host = $('#host-input').val();
            var akey = $('#akey-input').val();
            var globalEnabled = $('#enabled-checkbox').is(':checked');
            var ipEnabled = $('#ip-bypass-checkbox').is(':checked');
            var ldapEnabled = $('#ldap-bypass-checkbox').is(':checked');
            var rawIpList = $('#ip-bypass-list').val().replace(/\n/g, ",").replace(/,+$/, ""); //Replace last ',' with ''
            var netbiosEnabled = $('#netbios-domain-checkbox').is(':checked');
            var netbiosDomain = $('#netbios-input').val().toUpperCase();
            //Create ipList and networkList arrays
            var ipListArray = rawIpList.split(",");
            var networkList = new Array();
            var ipList = new Array();
            ipListArray.forEach(function(element) {
                if (element.indexOf("/") > -1) {
                    networkList.push(element);
                } else {
                    ipList.push(element);
                }
            }, this);
            //Create comma-separated strings from arrays since that is the format we save the lists in the app's config
            ipList = ipList.join();
            networkList = networkList.join();
            //Check that required fields have been filled out
            if (!ikey || !skey || !host) {
                alert("Error: IKEY, SKEY, and Hostname fields must be filled out");
                return;
            }
            if (!netbiosDomain && netbiosEnabled) {
                alert("Error: NetBIOS domain must be specified if the option is enabled");
                return;
            }
            //akey is optional, so we set it to a predefined value if it's not set/specified
            if (!akey) {
                var genUrl = OC.generateUrl('/apps/duo/gen-akey');
                var request = new XMLHttpRequest();
                request.open('GET', genUrl, false); // `false` makes the request synchronous
                request.send(null);
                if (request.status === 200) {
                    $('#akey-input').val(request.responseText);
                }
                //akey = "8749032634b9c0ee14fa785c3e59b424a13d2073";
            }

            var url = OC.generateUrl('/apps/duo/save-settings');
            var data = {
                ikey: ikey,
                skey: skey,
                host: host,
                akey: akey,
                globalEnabled: globalEnabled,
                ipEnabled: ipEnabled,
                ldapEnabled: ldapEnabled,
                ipList: ipList,
                networkList: networkList,
                netbiosEnabled: netbiosEnabled,
                netbiosDomain: netbiosDomain
            };
            $.post(url, data).done(function(response) {
                console.log("Successfully saved Duo config");
                $('#success-msg').removeAttr("hidden");
            }).fail(function(xhr, status, error) {
                alert("Error saving Duo config. Check browser console for details");
                console.log(error);
            });
        });
        $('#reset-btn').click(function() {
            var url = OC.generateUrl('/apps/duo/reset-settings');
            $.get(url).success(function(response) {
                $('#success-msg').attr("hidden");
                $('#reset-success-msg').removeAttr("hidden");
            });
        });
    });

})(jQuery, OC);