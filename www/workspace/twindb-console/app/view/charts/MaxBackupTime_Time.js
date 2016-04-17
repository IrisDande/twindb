Ext.define('TwinDB.view.charts.MaxBackupTime_Time', {
    extend: 'Ext.chart.Chart',
    alias: 'widget.maxbackuptime-time',

        requires: [
            'Ext.chart.axis.Numeric',
            'Ext.chart.axis.Time',
            'Ext.chart.series.Line'
        ],

        animate: true,
    store: 'charts.MaxBackupTime_Time', // #1
    theme: 'Sky',
    axes: [{
            type: 'Numeric',
            decimals: 0,
            position: 'left',
            fields: ['backup_time'],
            title: 'backup time, seconds',
            labelTitle: { font: '8px' },
            grid: {
                odd: {
                    opacity: 1,
                    fill: '#ddd',
                    stroke: '#bbb',
                    'stroke-width': 1
                }
            }
        },
        {
            type: 'Time',
            position: 'bottom',
            fields: ['date'],
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
            yField: ['backup_time'],
            style: {
                opacity: 0.93
            },
            tips: {
                trackMouse: true,
                width: 150,
                height: 20,
                renderer: function(storeItem) {
                    function pad(number, length) {
                        var str = '' + number;
                        while (str.length < length) {
                            str = '0' + str;
                        }
                    return str;
                    }
                    var hours = Math.floor(storeItem.get('backup_time') / 3600);
                    var minutes = Math.floor((storeItem.get('backup_time') - hours * 3600) / 60);
                    var seconds = Math.floor(storeItem.get('backup_time') - hours * 3600  - minutes * 60);
                    var interval = pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2);
                    this.setTitle(storeItem.get('date') + ': ' + interval);
                }
            }
        }]
    }
);
