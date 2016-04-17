Ext.define('TwinDB.view.Storage', {
    extend: 'Ext.container.Container',
    alias: 'widget.storage',

    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Display',
        'Ext.form.field.Hidden',
        'Ext.form.field.Text',
        'Ext.layout.container.HBox',
        'Ext.toolbar.Fill',
        'Ext.toolbar.Toolbar'
    ],

    defaults: {
        margin: 5
        },
    layout:{
        type: 'hbox'
    },
    items:[{
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5
            },
            items:[
                {
                    xtype : 'hidden',
                    name : 'volume_id'
                },
                {
                    itemId: 'name',
                    xtype: 'textfield',
                    name: 'name',
                    value: 'loading...',
                    readOnly: false,
                    fieldLabel: 'Storage name',
                    allowBlank: false,
                    msgTarget: 'side',
                    maxLength: 127
                },
                {
                    xtype: 'displayfield',
                    fieldLabel: 'Size',
                    value: 'loading...',
                    name: 'size'
                },
                {
                    xtype: 'progressbar',
                    text: 'loading...',
                    name: 'used'
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        {
                            itemId: 'addmore',
                            xtype: 'button',
                            text: 'Add more space',
                            iconCls: 'drive_add'
                        },
                        {
                            xtype: 'tbfill'
                        },
                        {
                            itemId: 'save',
                            xtype: 'button',
                            text: 'Save',
                            iconCls: 'disk'
                        },
                        {
                            itemId: 'cancel',
                            xtype: 'button',
                            text: 'Close',
                            iconCls: 'cancel'
                        }
                    ]
                }
            ]
        }
    ]
});
