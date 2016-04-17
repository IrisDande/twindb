Ext.define('TwinDB.view.unregisterServer', {
    extend: 'Ext.window.Window',
    alias: 'widget.unregister-server',

    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Checkbox',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],

    autoShow: true,
    //height: 170, // #5
    width: 600, // #6
    layout: {
        type: 'fit' // #7
    },
    iconCls: 'database_delete',
    title: 'Please confirm unregistration',
    closeAction: 'hide',
    closable: true,
    server_id: null,
    server_name: '',
    items: [
        {
            xtype: 'form',
            frame: false,
            bodyPadding: 15,
            defaults: {
                xtype: 'checkbox',
                anchor: '100%'
            },
            items: [
                {
                    itemId: 'confirm-unregister',
                    name: 'confirm-unregister',
                    boxLabel: 'Are you sure you want to unregister server?'
                },
                {
                    itemId: 'delete-backups',
                    name: 'delete-backups',
                    boxLabel: 'Also delete all backup copies taken from this server'
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
                            text: 'Cancel'
                        },
                        {
                            xtype: 'button',
                            itemId: 'submit',
                            iconCls: 'bullet_go',
                            text: 'Unregister',
                            disabled: true
                        }
                    ]
                }
            ]
        }
    ]
});
