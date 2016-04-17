Ext.define('TwinDB.view.ForgotPasswordWindow', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Display',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.forgotpassword',
    autoShow: true,
    width: 360,
    layout: {
        type: 'fit'
    },
    iconCls: 'key',
    title: 'Password reminder',
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
                    xtype: 'displayfield',
                    value: '<p>Please enter e-mail that was used for registration in TwinDB'+
                        '<p>We will send password reset link to this e-mail'

                },
                {
                    itemId: 'email',
                    name: 'email',
                    fieldLabel: 'E-mail',
                    allowBlank: false,
                    vtype: 'email',
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
                            iconCls: 'key-go',
                            text: 'Submit'
                        }
                    ]
                }       
            ]
        }
    ]
});
