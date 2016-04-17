Ext.define('TwinDB.view.InstructionsDebian', {
    extend: 'Ext.window.Window',
    alias: 'widget.instructions-debian',

    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.layout.container.Anchor',
        'Ext.toolbar.Fill'
    ],

    defaults: {
        margin: 10
        },
    layout: 'anchor',
    autoShow: true,
    width: 800,
    height: 500,

    iconCls: 'debian',
    title: 'Instructions for Ubuntu and Debian',
    closeAction: 'hide',
    closable: true,
    maximizable: true,
    items:[
        {
            xtype: 'container',
            anchor: '100% 100%',
            html: '<iframe src="https://docs.google.com/document/d/11_000PuzeYPPC0Um1k0z9EkvqO0gkpGQrUJkNVBDdJ0/pub?embedded=true" ' +
            'frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"\></iframe>'
        }
    ],
    dockedItems: [
        {
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    itemId: 'cancel',
                    iconCls: 'cancel',
                    text: 'Close',
                    handler: function(button) {
                        var win = button.up('window');
                        win.close();
                    }
                }
            ]
        }       
    ]
});
