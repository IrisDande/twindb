Ext.define('TwinDB.view.ServerEncrypionKeys', {
    extend: 'Ext.container.Container',
    alias: 'widget.server-encryption',

    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.form.field.TextArea',
        'Ext.layout.container.HBox'
    ],

    defaults: {
        margin: 5
        },
    layout:{
        type: 'hbox'
    },
    autoScroll: true,
    server_id: null,
    items:[
        {
            xtype: 'form',
            header: false,
            defaults: {
                margin: 5,
                width: 400
            },
            items: [
                { 
                    itemId: 'server_id',
                    xtype: 'textfield',
                    name: 'server_id',
                    hidden: true
                },
                {
                    itemId: 'ssh_public_key',
                    xtype: 'textarea',
                    name: 'ssh_public_key',
                    fieldLabel: 'SSH public key',
                    grow: true,
                    width: 700,
                    fieldStyle: {
                        'fontFamily'   : 'courier new'
                    //    'fontSize'     : '8px'
                    }
                },
                {
                    xtype: 'textarea',
                    name : 'enc_public_key',
                    fieldLabel: 'GPG public key',
                    grow: true,
                    width: 700,
                    fieldStyle: {
                        'fontFamily'   : 'courier new'
                    //    'fontSize'     : '8px'
                    }
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        pack: 'end'
                    },
                    items: [
                        {
                            itemId: 'save',
                            xtype: 'button',
                            width: 100,
                            text: 'Save',
                            iconCls: 'disk'
                        }
                    ]
                }
            ]
        }
    ]
});
