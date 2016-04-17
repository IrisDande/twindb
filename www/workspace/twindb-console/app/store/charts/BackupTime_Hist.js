Ext.define('TwinDB.store.charts.BackupTime_Hist', {
    extend : 'Ext.data.Store',   
    model: 'TwinDB.model.charts.BackupTime_Hist',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getBackupTime_Hist.php',
        reader : {
            type : 'json',
            root : 'data'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        },
        actionMethods : {
            read : 'POST'
        }
    } /*,
    data: [
        { 'backup_time': '< 10s', n_instances: 25 },
        { 'backup_time': '< 1m', n_instances: 40 },
        { 'backup_time': '< 1h', n_instances: 50 },
        { 'backup_time': '< 1h', n_instances: 50 },
        { 'backup_time': '< 1h', n_instances: 50 },
        { 'backup_time': '< 1h', n_instances: 50 },
        { 'backup_time': '< 1h', n_instances: 50 },
        { 'backup_time': '< 3h', n_instances: 60 },
        { 'backup_time': '< 24h', n_instances: 1 },
        { 'backup_time': '> 24h', n_instances: 0 }
    ]
    */
});
