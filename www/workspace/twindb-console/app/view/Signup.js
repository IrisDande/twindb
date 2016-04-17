Ext.define('TwinDB.view.Signup', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.layout.container.VBox',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.signup',
    autoShow: true,
    //height: 170,
    width: 360,
    layout: {
        type: 'fit'
    },
    iconCls: 'user_add',
    title: 'Registration form',
    closeAction: 'hide',
    closable: true,
    defaultFocus: 'email',
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
                    itemId: 'email',
                    name: 'email',
                    fieldLabel: 'E-mail',
                    allowBlank: false, 
                    vtype: 'email', 
                    msgTarget: 'under'
                },
                /*
                {
                    id: 'invitation_code',
                    itemId: 'invitation_code',
                    name: 'invitation_code',
                    fieldLabel: 'Invitation code',
                    allowBlank: false, 
                    minLength: 32,
                    maxLength: 32,
                    vtype: 'alphanum', 
                    msgTarget: 'under'
                },
                */
                {
                    xtype: 'fieldset',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        xtype: 'textfield', 
                        labelWidth: 100,
                        width: 280,
                        margin: 5
                    },
                    items: [
                        {
                            id: 'signup_password',
                            inputType: 'password', // #19
                            name: 'password',
                            fieldLabel: 'Password',
                            maxLength: 64,
                            msgTarget: 'under',
                            enableKeyEvents: true
                        },
                        {
                            id: 'signup_password2',
                            inputType: 'password', // #19
                            name: 'password2',
                            fieldLabel: 'Repeat password',
                            maxLength: 64,
                            msgTarget: 'under',
                            vtype: 'password',
                            initialPassField: 'signup_password',
                            enableKeyEvents: true
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
                            xtype: 'tbfill' //#24
                        },
                        {
                            xtype: 'button', // #25
                            itemId: 'cancel',
                            iconCls: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'submit',
                            formBind: true, // #27
                            iconCls: 'user_add',
                            text: 'Register'
                        }
                    ]
                }       
            ]
        }
    ]
});
