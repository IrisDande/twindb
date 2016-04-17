Ext.define('TwinDB.view.BackupKPI',{
    extend : 'Ext.container.Container',
    alias : 'widget.backup-kpi',

    requires: [
        'Ext.container.Container',
        'Ext.layout.container.Anchor',
        'Ext.layout.container.Fit',
        'Ext.layout.container.HBox',
        'Ext.panel.Panel',
        'TwinDB.view.MaxDataLossGrid',
        'TwinDB.view.charts.BackupTime_Hist',
        'TwinDB.view.charts.MaxBackupTime_Time',
        'TwinDB.view.charts.MaxRestoreTime_Time'
    ],

    require: [
        'TwinDB.charts.BackupTime_Hist',
        'TwinDB.charts.MaxBackupTime_Time',
        'TwinDB.charts.VolumeUsageDate'
    ],
    title: 'Key Performance Indicators',
    viewConfig: {
        loadMask: false
    },
    layout: 'anchor',
    defaults:{
        anchor: '100% 50%'
    },
    items: [
        {
            xtype: 'container',
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            defaults: {
                layout: 'fit',
                width: '50%',
                height: '100%'
            },
            items: [
                {
                    xtype: 'panel',
                    title: 'Maximum Backup Time',
                    items: [
                        {
                            xtype: 'maxbackuptime-time'
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    title: 'Backup Time Distribution',
                    items: [
                        {
                            xtype: 'backuptime-hist'
                        }
                    ]
                }
            ]
        },
        {
            xtype: 'container',
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            border: 5,
            defaults: {
                layout: 'fit',
                width: '50%',
                height: '100%'
            },
            items: [
                {
                    xtype: 'panel',
                    title: 'Maximum Restore Time',
                    items: [
                        {
                            xtype: 'maxrestoretime-time'
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    title: 'Maximum Data Loss',
                    items: [
                        {
                            xtype: 'maxdatalossgrid'
                        }
                    ]
                }
            ]
        
        }
    ]
});
