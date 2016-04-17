Ext.define('TwinDB.view.UpgradeStorageStep2', { // #1
    extend: 'Ext.window.Window', // #2
    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Hidden',
        'Ext.form.field.Number',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.layout.container.HBox',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.upgradestoragestep2', // #3
    autoShow: true, // #4
    //height: 170, // #5
    width: 360, // #6
    layout: {
        type: 'fit' // #7
    },
    iconCls: 'drive_add', // #8
    title: 'Add more space: Payment details', // #9
    closeAction: 'hide', // #10
    closable: true, // #11
    defaultFocus: 'name',
    items: [
        {
            xtype: 'form', // #12
            frame: false, // #13
            bodyPadding: 15, // #14
            defaults: { // #15
                xtype: 'textfield', // #16
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'hidden', // #12
                    itemId: 'package_id',
                    name: 'package'
                },
                {
                    name: 'first_name',
                    fieldLabel: 'First name'
                },
                {
                    name: 'last_name',
                    fieldLabel: 'Last name'
                },
                {
                    xtype: 'numberfield',
                    name: 'cc_number',
                    fieldLabel: 'Card number',
                    allowBlank: false,
                    allowExponential: false,
                    hideTrigger: true
                },
                {
                    xtype:'fieldset',
                    title: 'Expires on',
                    layout:{
                        type: 'hbox',
                        pack: 'end'
                    },
                    items:[
                        {
                            xtype: 'combo',
                            name: 'exp_mon',
                            store : [
                                'January', 
                                'February',
                                'March',
                                'April',
                                'May',
                                'June',
                                'July',
                                'August',
                                'September',
                                'October',
                                'November',
                                'December'
                            ]
                        },
                        {
                            xtype: 'combo',
                            name: 'exp_year',
                            store: [
                                '2014',
                                '2015',
                                '2016',
                                '2017',
                                '2018',
                                '2019',
                                '2020',
                                '2021',
                                '2022',
                                '2023'
                            ]
                        }
                    ]
                },
                {
                    xtype: 'container',
                    layout:{
                        type: 'hbox',
                        pack: 'end'
                    },
                    items:[
                        {
                            xtype: 'numberfield',
                            name: 'cvv',
                            fieldLabel: 'Security code',
                            inputType: 'password',
                            allowBlank: false,
                            allowExponential: false,
                            hideTrigger: true
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
                            itemId: 'prev',
                            iconCls: 'resultset_previous',
                            text: 'Back'
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'next',
                            formBind: true, // #27
                            iconCls: 'resultset_next',
                            text: 'Next'
                        }
                    ]
                }       
            ]
        }
    ]
});
