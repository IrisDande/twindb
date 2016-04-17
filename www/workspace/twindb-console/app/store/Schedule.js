Ext.define('TwinDB.store.Schedule',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.Schedule',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getSchedule.php',
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
