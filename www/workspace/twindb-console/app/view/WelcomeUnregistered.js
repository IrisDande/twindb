Ext.define('TwinDB.view.WelcomeUnregistered', {
    extend: 'Ext.container.Container',
    alias: 'widget.welcome-unregistered',

    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.Label',
        'Ext.form.field.TextArea',
        'Ext.layout.container.Anchor',
        'Ext.layout.container.HBox',
        'Ext.layout.container.VBox',
        'Ext.panel.Panel'
    ],

    defaults: {
        margin: 10
    },
    layout: {
        type: 'anchor'
    },
    items:[
        {
            xtype: 'container',
            anchor: '100%',
            autoScroll: true,
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            items: [
                {
                    itemId: 'step1-label',
                    xtype: 'label',
                    text: 'Step 1.',
                    style: {
                        color       : '#3399CC',
                        fontFamily  : 'monospace',
                        fontSize    : '36px'
                    }
                },
                {
                    itemId: 'signup-container',
                    xtype: 'panel',
                    title: 'Sign-up on TwinDB',
                    width: 800,
                    collapsible: true,
                    layout: {
                        type: 'hbox'
                    },
                    defaults: {
                        margin: '10 30 10 30'
                    },
                    items: [
                        {
                            xtype: 'container',
                            html: 'First step is to create an account in TwinDB backup service.',
                            width: 400
                        },
                        {
                            itemId: 'signup',
                            xtype: 'button',
                            text: 'Sign-up',
                            iconCls: 'user_add'
                        }
                    ]
                
                }
            ]
        },
        {
            xtype: 'container',
            anchor: '100%',
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            items: [
                {
                    xtype: 'label',
                    text: 'Step 2.',
                    style: {
                        color       : '#3399CC',
                        fontFamily  : 'monospace',
                        fontSize    : '36px'
                    }
                },
                {
                    xtype: 'panel',
                    title: 'Install TwinDB agent',
                    width: 800,
                    collapsible: true,
                    layout: {
                        type: 'hbox'
                    },
                    defaults: {
                        margin: '10 30 10 30'
                    },
                    items: [
                        {
                            xtype: 'container',
                            html: '<p>Second step is to install TwinDB agent on your MySQL server.' +
                                  '<p>TwinDB agent runs as service and executes command from TwinDB.',
                            width: 400
                        },
                        {
                            xtype: 'container',
                            layout: {
                                type: 'vbox'
                            },
                            defaults: {
                                margin: 5
                            },
                            items: [
                                {
                                    itemId: 'instructions-redhat',
                                    xtype: 'button',
                                    text: 'Show instruction for RedHat/CentOS/Amazon',
                                    iconCls: 'redhat'
                                },
                                {
                                    itemId: 'instructions-debian',
                                    xtype: 'button',
                                    text: 'Show instruction for Debian/Ubuntu',
                                    iconCls: 'debian'
                                }
                            ]
                        }
                    ]
                
                }
            ]
        },
        {
            xtype: 'container',
            anchor: '100%',
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            items: [
                {
                    xtype: 'label',
                    text: 'Step 3.',
                    style: {
                        color       : '#3399CC',
                        fontFamily  : 'monospace',
                        fontSize    : '36px'
                    }
                },
                {
                    xtype: 'panel',
                    title: 'Register TwinDB agent',
                    width: 800,
                    collapsible: true,
                    layout: {
                        type: 'vbox'
                    },
                    defaults: {
                        margin: '10 30 10 30'
                    },
                    items: [
                        {
                            xtype: 'container',
                            html: '<p>Third step is to register the TwinDB agent.' +
                                  '<p>To do so please run following command on MySQL server:'
                        },
                        {
                            xtype: 'container',
                            layout: {
                                type: 'vbox',
                                pack: 'center'
                            },
                            defaults: {
                                margin: 1
                            },
                            style: {
                                borderColor  : 'green',
                                borderStyle  : 'dotted'
                            },
                            items: [
                                {
                                    xtype: 'textarea',
                                    itemId: 'command_to_run',
                                    name: 'command_to_run',
                                    value: 'loading...',
                                    grow: true,
                                    width: 700,
                                    height: 22,
                                    readOnly: true,
                                    border: false,
                                    fieldStyle: {
                                        fontFamily   : 'courier new',
                                        fontSize     : '16px',
                                        border       : '0px'
                                    }
                                }
                            ]
                        }
                    ]
                
                }
            ]
        }
    ]
});
