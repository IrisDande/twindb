Ext.define('TwinDB.view.Login', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.login',
    autoShow: true,

    width: 360,
    layout: {
        type: 'fit'
    },
    iconCls: 'key',
    title: 'Please Login',

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
                {
                    id: 'password',
                    inputType: 'password',
                    name: 'password',
                    fieldLabel: 'Password',
                    maxLength: 64,
                    msgTarget: 'under',
                    enableKeyEvents: true
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
