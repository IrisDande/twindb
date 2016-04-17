Ext.define('TwinDB.view.charts.VolumeUsageDate', {
    extend: 'Ext.chart.Chart',
    alias: 'widget.volumeusagedate',

        requires: [
            'Ext.chart.axis.Numeric',
            'Ext.chart.axis.Time',
            'Ext.chart.series.Line'
        ],

        animate: true,
    store: 'charts.VolumeUsageDate', // #1
    //legend: {
    //    position: 'bottom'
    //},
    theme: 'Sky',
    axes: [{
            type: 'Numeric',
            position: 'left',
            fields: ['usage'],
            title: 'used storage, %',
            labelTitle: { font: '8px' },
            grid: {
                odd: {
                    opacity: 1,
                    fill: '#ddd',
                    stroke: '#bbb',
                    'stroke-width': 1
                }
            },
            minimum: 0,
            maximum: 100,
            adjustMinimumByMajorUnit: 0
        },
        {
            type: 'Time',
            position: 'bottom',
            fields: ['date'],
            //title: 'Date',
            dateFormat: 'Y M d',
            grid: true,
            label: {
                rotate: {
                    degrees: 315
                }
            }
        }],
        series: [{
            type: 'line',
            highlight: true,
            axis: 'left',
            xField: 'date',
            yField: ['usage'],
            style: {
                opacity: 0.93
            },
            tips: {
                trackMouse: true,
                width: 120,
                height: 20,
                renderer: function(storeItem) {
                    this.setTitle(storeItem.get('date') + ': ' + storeItem.get('usage') + '%');
                }
            }
        }]
    }
);
