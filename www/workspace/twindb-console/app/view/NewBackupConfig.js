Ext.define('TwinDB.view.NewBackupConfig', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.newbackupconfig',
    autoShow: true,

    width: 360,
    layout: {
        type: 'fit'
    },
    iconCls: 'cog',
    title: 'Add backup configuration',
    closeAction: 'hide',
    closable: true,
    defaultFocus: 'name',
    items: [
        {
            xtype: 'form',
            frame: false,
            bodyPadding: 15,
            defaults: {
                xtype: 'textfield',
                anchor: '100%',
                labelWidth: 60
            },
            items: [
                {
                    itemId: 'name',
                    name: 'name',
                    fieldLabel: 'Backup configuration',
                    labelWidth: 120,
                    maxLength: 127/3,
                    allowBlank: false,
                    msgTarget: 'under'
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
                            formBind: true,
                            iconCls: 'cog_add',
                            text: 'Submit'
                        }
                    ]
                }       
            ]
        }
    ]
});
