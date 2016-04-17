Ext.define('TwinDB.store.RegCode',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.RegCode',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getRegCode.php',
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
