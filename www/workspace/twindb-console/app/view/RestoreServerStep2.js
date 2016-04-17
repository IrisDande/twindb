Ext.define('TwinDB.view.RestoreServerStep2', { 
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.restore-server-step2',
    autoShow: true,
    width: 800,
    maxHeight: 600,
    layout: {
        type: 'fit'
    },
    iconCls: 'database_refresh',
    title: 'Directory to restore',
    closeAction: 'hide',
    closable: true,
    defaultFocus: 'restore_dir',
    items: [
        {
            xtype: 'form',
            bodyPadding: 15,
            autoScroll: true,
            defaults: { 
                anchor: '100%'
            },
            items: [
                {
                    itemId: 'backup_copy_id',
                    name: 'backup_copy_id',
                    xtype: 'textfield',
                    allowBlank: false,
                    hidden: true
                },
                {
                    itemId: 'server_id',
                    name: 'server_id',
                    xtype: 'textfield',
                    allowBlank: false,
                    hidden: true
                },
                {
                    itemId: 'restore_dir',
                    name: 'restore_dir',
                    xtype: 'textfield',
                    labelAlign: 'top',
                    allowBlank: false,
                    fieldLabel: 'Please enter full path where to restore the backup copy'
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        {
                            xtype: 'tbfill' //#24
                        },
                        {
                            xtype: 'button', // #25
                            itemId: 'cancel',
                            iconCls: 'cancel',
                            text: 'Cancel',
                            handler: function(button) {
                                var win = button.up('window');
                                win.close();
                            }
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'prev',
                            iconCls: 'resultset_previous',
                            text: 'Back',
                            handler: function(button) {
                                var win = button.up('window');
                                win.close();
                                win.previous.show();
                            }
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'go',
                            iconCls: 'bullet_go',
                            text: 'Restore',
                            formBind: true
                        }
                    ]
                }       
            ]
        }
    ]
});
