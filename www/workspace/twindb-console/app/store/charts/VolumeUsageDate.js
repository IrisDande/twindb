Ext.define('TwinDB.store.charts.VolumeUsageDate', {
    extend : 'Ext.data.Store',   
    model: 'TwinDB.model.charts.VolumeUsageDate',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getChartVolumeUsageDate.php',
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
    /*
    data: [
        { date: new Date(2014, 10, 1), usage: 25 },
        { date: new Date(2014, 10, 2), usage: 22 },
        { date: new Date(2014, 10, 3), usage: 27 },
        { date: new Date(2014, 10, 4), usage: 27 },
        { date: new Date(2014, 10, 5), usage: 29 },
        { date: new Date(2014, 10, 6), usage: 30 },
        { date: new Date(2014, 10, 7), usage: 35 },
        { date: new Date(2014, 10, 8), usage: 40 }
    ]
    */
});
