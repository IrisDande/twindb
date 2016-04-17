Ext.define('TwinDB.store.ServerBackupCopies',{
    extend : 'Ext.data.TreeStore',
    model : 'TwinDB.model.ServerBackupCopies',
    autoLoad: false,
    server_id: null,
    proxy : {
        type : 'ajax',
        url : 'php/getServerBackupCopies.php',
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
    }
});
