(function ($, OC) {

    $(document).ready(function () {
        //alert("Loaded duo.js");
        $('#advcd-div').hide();
        $('#skey-input').attr("type","password");

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

        $('#skey-input').click(function() {
          $('#skey-input').attr("type","text");
        });

        $('#skey-input').blur(function() {
          $('#skey-input').attr("type","password");
        });

        $('#save-btn').click(function() {
          var ikey = $('#ikey-input').val();
          var skey = $('#skey-input').val();
          var host = $('#host-input').val();
          var akey = $('#akey-input').val();
          var globalEnabled = $('#enabled-checkbox').is(':checked');
          var ipEnabled = $('#ip-bypass-checkbox').is(':checked');
          var ldapEnabled = $('#ldap-bypass-checkbox').is(':checked');
          var ipList = $('#ip-bypass-list').val().replace(/\n/g, ",").replace(/,+$/, "");
          //Check that required fields have been filled out
          if (!ikey || !skey || !host) {
            alert("Error: IKEY, SKEY, and Hostname fields must be filled out");
            return;
          }
          //akey is optional, so we set it to a predefined value if it's not set/specified
          if (!akey) {
            akey = "8749032634b9c0ee14fa785c3e59b424a13d2073";
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
            ipList: ipList
          };
          $.post(url, data).success(function (response) {
            console.log("Successfully saved Duo config");
            $('#success-msg').removeAttr("hidden");
          });
        });
        $('#reset-btn').click(function() {
          var url = OC.generateUrl('/apps/duo/reset-settings');
          $.get(url).success(function (response) {
            $('#success-msg').attr("hidden");
            $('#reset-success-msg').removeAttr("hidden");
          });
        });
    });

})(jQuery, OC);
