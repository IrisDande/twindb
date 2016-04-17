Ext.define('TwinDB.store.Storage',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.Storage',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getStorage.php',
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
