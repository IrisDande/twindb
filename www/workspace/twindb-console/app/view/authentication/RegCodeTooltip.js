Ext.define('TwinDB.view.authentication.RegCodeTooltip', {
    extend: 'Ext.tip.QuickTip',
    alias: 'widget.regcodetooltip',
    target: 'invitation_code',
    anchor: 'top',
    anchorOffset: 60,
    width: 300,
    dismissDelay: 0,
    autoHide: false,
    title: '<div class="help">Registration is by invitations only</div>',
    html: '<div>If you have registration code, enter it here.</div>' 
        + '<div>If you would like to participate in TwinDB beta testing please drop an email to <a href="mailto:beta@twindb.com?Subject=Invitation%20request">beta@twindb.com</a></div>'
});
