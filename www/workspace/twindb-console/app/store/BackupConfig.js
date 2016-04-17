Ext.define('TwinDB.store.BackupConfig',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.BackupConfig',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getBackupConfig.php',
        reader : {
            type : 'json',
            root : 'data'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        },
        actionMethods :{
            read : 'POST'
        }
    }
});
