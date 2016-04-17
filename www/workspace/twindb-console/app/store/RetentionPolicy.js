Ext.define('TwinDB.store.RetentionPolicy',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.RetentionPolicy',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getRetentionPolicy.php',
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
