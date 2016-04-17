Ext.define('TwinDB.view.ServerGeneral', {
    extend: 'Ext.container.Container',
    alias: 'widget.server-general',

    requires: [
        'Ext.button.Button',
        'Ext.container.Container',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Display',
        'Ext.form.field.Text',
        'Ext.layout.container.HBox',
        'Ext.layout.container.VBox'
    ],

    defaults: {
        margin: 5
        },
    layout:{
        type: 'hbox'
    },
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
                    itemId: 'name',
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: 'Hostname',
                    msgTarget: 'side',
                    maxLength: 64
                },
                {
                    xtype: 'combo',
                    name : 'time_zone',
                    store : 'timezoneCombo',
                    displayField: 'time_zone',
                    valueField : 'time_zone',
                    fieldLabel: 'Server timezone'
                },
                {
                    itemId: 'backupConfigCombo',
                    xtype: 'combo',
                    queryMode: 'local',
                    name : 'config_id',
                    store : 'BackupConfig',
                    displayField: 'name',
                    valueField : 'config_id',
                    fieldLabel: 'Backup configuration'
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
                },
                {
                    xtype: 'fieldset',
                    title: 'Server health status',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                    },
                    height: 200,
                    defaults: {
                        anchor: '100%',
                        margin: 5,
                        xtype: 'displayfield',
                        msgTarget: 'side',
                        readOnly: true,
                        //height: 20,
                        maxLength: 255/3
                    },
                    items: [
                        {
                            itemId: 'lastseen',
                            name: 'lastseen',
                            fieldLabel: 'Last seen',
                            value: 'loading...'
                        },
                        {
                            itemId: 'replication_status',
                            name: 'replication_status',
                            fieldLabel: 'Replication status',
                            value: 'loading...'
                        },
                        {
                            itemId: 'agent_permissions_status',
                            name: 'agent_permissions_status',
                            fieldLabel: 'Agent permissions',
                            width : '100%',
                            value: 'loading...'
                        }
                    ]
                }
            ]
        },
        {
            xtype: 'fieldset',
            items: [
                {
                    // replication topology
                    itemId: 'replicationTopology',
                    xtype: 'container',
                    width: 400,
                    height: 400
                }
            ]
        }
    ]
});
