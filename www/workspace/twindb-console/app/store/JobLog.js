Ext.define('TwinDB.store.JobLog',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.JobLog',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getJobLog.php',
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
