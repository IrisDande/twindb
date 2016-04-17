Ext.define('TwinDB.store.SecurityProfile',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.SecurityProfile',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getSecurityProfile.php',
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
