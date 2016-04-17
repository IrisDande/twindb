Ext.define('TwinDB.view.GeneralProfile', {
    extend: 'Ext.container.Container',
    alias: 'widget.generalprofile',

    requires: [
        'Ext.button.Button',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.layout.container.HBox',
        'Ext.layout.container.VBox',
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
                    itemId: 'first_name',
                    xtype: 'textfield',
                    name: 'first_name',
                    fieldLabel: 'First name',
                    msgTarget: 'side',
                    maxLength: 255/3
                },
                {
                    itemId: 'last_name',
                    xtype: 'textfield',
                    name: 'last_name',
                    fieldLabel: 'Last name',
                    msgTarget: 'side',
                    maxLength: 255/3
                },
                {
                    xtype: 'fieldset',
                    title: 'Contact information',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        margin: 5,
                        xtype: 'textfield',
                        msgTarget: 'side',
                        maxLength: 255/3
                    },
                    items: [
                        {
                            itemId: 'email',
                            name: 'email',
                            fieldLabel: 'Email',
                            readOnly: true,
                            vtype: 'email'
                        },
                        {
                            itemId: 'phone',
                            name: 'phone',
                            fieldLabel: 'Phone'
                        },
                        {
                            itemId: 'skype',
                            name: 'skype',
                            fieldLabel: 'Skype'
                        }
                    ]
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
                            itemId: 'save',
                            xtype: 'button',
                            text: 'Save',
                            iconCls: 'disk',
                            formBind: true
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
