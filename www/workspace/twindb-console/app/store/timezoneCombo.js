Ext.define('TwinDB.store.timezoneCombo',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.timezoneCombo',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getListTimezone.php',
        reader : {
            type : 'json',
            root : 'data'
            }
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        }
});
