Ext.define('TwinDB.view.Server', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.server',

    requires: [
        'TwinDB.view.JobGridServer',
        'TwinDB.view.ServerBackupCopies',
        'TwinDB.view.ServerEncrypionKeys',
        'TwinDB.view.ServerGeneral'
    ],

    defaults: {
        margin: 5
    },
    items:[
        {
            title: 'General',
            xtype: 'server-general'
        },
        {
            title: 'Encryption',
            xtype: 'server-encryption'
        },
        {
            title: 'Backup copies',
            xtype: 'server-backupcopies'
        },
        {
            title: 'Jobs',
            xtype: 'jobgrid-server'
        }
    ]
});
