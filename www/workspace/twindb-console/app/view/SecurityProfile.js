Ext.apply(Ext.form.field.VTypes, {
    password: function(val, field) {
        if (field.initialPassField) {
            var pwd = field.up('form').down('#' + field.initialPassField);
                    return (val == pwd.getValue());
        }
        return true;
    },
    passwordText: 'Passwords do not match'
});

Ext.define('TwinDB.view.SecurityProfile', {
    extend: 'Ext.container.Container',
    alias: 'widget.securityprofile',

    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.form.field.TextArea',
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
    autoScroll: true,
    items:[
        {
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5
            },
            items:[
                {
                    xtype: 'fieldset',
                    title: 'Change password',
                    layout: {
                        type: 'vbox'
                        },
                    defaults: {
                        anchor: '100%',
                        width: 250,
                        margin: 5,
                        xtype: 'textfield',
                        maxLength: 255/3,
                        inputType: 'password',
                        allowBlank: false,
                        msgTarget: 'under'
                    },
                    items: [
                        {
                            //id : 'pass',
                            itemId: 'password1',
                            name: 'password1',
                            fieldLabel: 'New password'
                        },
                        {
                            itemId: 'password2',
                            name: 'password2',
                            fieldLabel: 'Repeat the new password',
                            vtype: 'password',
                            initialPassField: 'password1'
                        },
                        {
                            xtype: 'container',
                            width: 250,
                            layout: {
                                type: 'hbox',
                                pack: 'end'
                            },
                            items:[
                                {
                                    anchor: '100%',
                                    itemId: 'changepass',
                                    xtype: 'button',
                                    text: 'Change password',
                                    iconCls: 'key-go',
                                    formBind: true
                                }
                            ]
                        }
                    ]
                }
                
            ]
        },
        {
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5
            },
            items:[
                {
                    itemId: 'gpg_pub_key',
                    xtype: 'textarea',
                    name: 'gpg_pub_key',
                    fieldLabel: 'GPG public key',
                    msgTarget: 'side',
                    grow: true,
                    width: 700,
                    //maxHeight: 250,
                    fieldStyle: {
                        'fontFamily'   : 'courier new'
                        //'fontSize'     : '8px'
                    }
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
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
