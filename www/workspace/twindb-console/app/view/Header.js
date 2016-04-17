Ext.define('TwinDB.view.Header', {
    extend: 'Ext.toolbar.Toolbar', // #1
    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Display',
        'TwinDB.view.ForgotPasswordWindow'
    ],
    alias: 'widget.appheader', // #2
    //height: 132, // #3
    ui: 'footer', // #4
    //style: 'border-bottom: 4px solid #4c72a4;', // #5
    style: {
        background: 'black'
    },
    layout: {
        type: 'hbox',
        pack: 'top'
    },
    items: [
        {
            xtype: 'image', // #6
            src: 'resources/TwinDB-logo-d95b44-white-small.png',
            height: 60
        },
        {
            xtype: 'tbfill' // #7
        },
        {
            itemId: 'viewas',
            xtype: 'combo',
            hidden: true,
            fieldLabel: 'View as',
            store: 'userListCombo',
            name: 'email',
            displayField: 'email'
        },
        {
            itemId: 'greating',
            xtype: 'displayfield',
            value: 'Welcome to TwinDB demo'
        },
        {
            xtype: 'tbseparator' // #8
        },
        {
            xtype: 'container',
            layout: {
                type: 'vbox',
                pack: 'end'
            },
            items:[
                {
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        pack: 'end'
                    },
                    items:[
                        {
                            xtype: 'button', // #9
                            text: 'Sign up',
                            itemId: 'signup',
                            iconCls: 'user_add'
                        },
                        {
                            xtype: 'button', // #9
                            text: 'Login',
                            itemId: 'login',
                            iconCls: 'door_in'
                        },
                        {
                            xtype: 'button', // #9
                            text: 'Logout',
                            itemId: 'logout',
                            iconCls: 'door_out'
                        }
                    ]
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        pack: 'end'
                    },
                    //width: 120,
                    items:[
                        {
                            itemId: 'forgotpassword',
                            xtype: 'box',
                            autoEl: {
                                tag: 'a',
                                href: '#',
                                cn: 'Forgot_password'
                            },
                            listeners: {
                                click: {
                                    element: 'el',
                                    fn: function(){
                                        console.log('Click on forgot password link');
                                        Ext.widget('forgotpassword');
                                    }
                                }
                            }
                        }
                    ]
                }
            ]
        }
    ]
});
