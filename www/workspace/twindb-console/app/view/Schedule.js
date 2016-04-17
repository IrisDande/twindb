Ext.define('TwinDB.view.Schedule', {
    extend: 'Ext.container.Container',
    alias: 'widget.schedule',

    requires: [
        'Ext.button.Button',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Hidden',
        'Ext.form.field.Number',
        'Ext.form.field.Text',
        'Ext.form.field.Time',
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
    params: null,
    items:[{
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5
            },
            items:[
                {
                    xtype : 'hidden',
                    name : 'schedule_id'
                },
                {
                    itemId: 'name',
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: 'Schedule name',
                    allowBlank: false,
                    msgTarget: 'side',
                    maxLength: 127
                },
                {
                    xtype: 'fieldset',
                    title: 'Incremental backup',
                    layout: {
                        type: 'hbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        margin: 5
                    },
                    items: [
                        {
                            itemId: 'ntimes',
                            fieldLabel: 'Every',
                            name: 'ntimes',
                            maxValue: 30,
                            minValue: 1,
                            xtype: 'numberfield',
                            labelWidth: 30,
                            width: 90
                        },
                        {
                            xtype: 'combo',
                            name : 'frequency_unit',
                            store : ['Hour', 'Day', 'Week'],
                            width: 90
                        },
                        {
                            itemId: 'start_time',
                            name: 'start_time',
                            fieldLabel: 'starting at',
                            labelWidth: 80,
                            xtype: 'timefield',
                            minValue: '00:00',
                            maxValue: '23:30',
                            format: 'H:i:s',
                            increment: 30,
                            width: 160
                        },
                        {
                            xtype: 'combo',
                            name : 'time_zone',
                            store : 'timezoneCombo',
                            displayField: 'time_zone',
                            valueField : 'time_zone',
                            width: 150
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    title: 'Full backup',
                    layout: {
                        type: 'hbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        margin: 5
                    },
                    items: [
                        {
                            xtype: 'combo',
                            fieldLabel : 'Take full copy',
                            name : 'full_copy',
                            store : ['Daily', 'Weekly', 'Monthly','Quarterly','Yearly']
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
