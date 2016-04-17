Ext.define('TwinDB.store.attributeCombo',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.attributeCombo',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getListAttributes.php',
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
