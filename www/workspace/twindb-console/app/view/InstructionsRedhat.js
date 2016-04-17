Ext.define('TwinDB.view.InstructionsRedhat', {
    extend: 'Ext.window.Window',
    alias: 'widget.instructions-redhat',

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

    iconCls: 'redhat',
    title: 'Instructions for RedHat, CentOS, or Amazon Linux',
    closeAction: 'hide',
    closable: true,
    maximizable: true,
    items:[
        {
            xtype: 'container',
            anchor: '100% 100%',
            html: '<iframe src="https://docs.google.com/document/d/1Ks7fs4h9_RnWt-CmhmQrMqj4w105fYLu7fij-A3hwZM/pub?embedded=true" ' +
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
