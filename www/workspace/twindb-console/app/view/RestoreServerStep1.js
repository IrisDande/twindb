Ext.define('TwinDB.view.RestoreServerStep1', { 
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill',
        'TwinDB.view.ServerBackupCopies'
    ],
    alias: 'widget.restore-server-step1',
    autoShow: true,
    width: 800,
    maxHeight: 600,
    layout: {
        type: 'fit'
    },
    iconCls: 'database_refresh',
    title: 'Backup copy to restore',
    closeAction: 'hide',
    closable: true,
    next: null,
    server_id: null,
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
                    itemId: 'backup_copy',
                    xtype: 'server-backupcopies',
                    title: 'Please select backup copy to restore'
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
                            disabled: true
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'next',
                            iconCls: 'resultset_next',
                            text: 'Next',
                            disabled: true
                        }
                    ]
                }       
            ]
        }
    ]
});
