Ext.define('TwinDB.store.ServerGeneral',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.ServerGeneral',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getServerGeneral.php',
        reader : {
            type : 'json',
            root : 'data'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            },
            load: function(store, records, successful, eOpts){
                var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(mainpanel.getEl()).unmask();
            }
        },
        actionMethods :{
            read : 'POST'
        }
    }
});
