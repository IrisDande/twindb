Ext.define('TwinDB.view.Dashboard',{
    extend : 'Ext.container.Container',
    alias : 'widget.dashboard',

    requires: [
        'Ext.layout.container.Anchor',
        'Ext.layout.container.HBox',
        'Ext.panel.Panel',
        'TwinDB.view.JobGrid',
        'TwinDB.view.charts.VolumeUsageDate'
    ],

    require: [
        'TwinDB.view.JobGrid',
        'TwinDB.charts.VolumeUsageDate'
    ],
    title: 'Dashboard',
    viewConfig: {
        loadMask: false
    },
    layout: 'anchor',
    defaults:{
        anchor: '100% 50%'
    },
    items: [
        {
            xtype: 'panel',
            title: 'Recent jobs',
            layout: {
                type: 'hbox',
                pack: 'left'
            },
            defaults: {
                width: '100%',
                height: '100%',
                anchor: '100% 100%'
            },
            items: [
                {
                    xtype: 'jobgrid'
                }
            ]
        },
        {
            xtype: 'panel',
            title: 'Storage usage',
            layout: {
                type: 'hbox',
                pack: 'left'
            },
            border: 5,
            defaults: {
                width: '80%',
                height: '100%',
                anchor: '100% 100%'
            },
            items: [
                {
                    xtype: 'volumeusagedate'
                }
            ]
        
        }
    ]
});
