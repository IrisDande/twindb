Ext.define('TwinDB.view.authentication.CapsLockTooltip', {
    extend: 'Ext.tip.QuickTip',
    alias: 'widget.capslocktooltip',
    target: 'password',
    anchor: 'top',
    anchorOffset: 60,
    width: 300,
    dismissDelay: 0,
    autoHide: false,
    title: '<div class="capslock">Caps Lock is On</div>',
    html: '<div>Having Caps Lock on may cause you to enter your password incorrectly.</div>' 
        + '<div>You should press Caps Lock to turn it off before entering your password.</div>'
});
