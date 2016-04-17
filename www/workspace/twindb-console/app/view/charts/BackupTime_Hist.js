Ext.define('TwinDB.view.charts.BackupTime_Hist', {
    extend: 'Ext.chart.Chart',
    alias: 'widget.backuptime-hist',

        requires: [
            'Ext.chart.axis.Category',
            'Ext.chart.axis.Numeric',
            'Ext.chart.axis.Time',
            'Ext.chart.series.Column'
        ],

        animate: true,
    store: 'charts.BackupTime_Hist', // #1
    //legend: {
    //    position: 'bottom'
    //},
    theme: 'Sky',
    axes: [{
            type: 'Numeric',
            decimals: 0,
            //dateFormat: 'h:i:s',
            position: 'left',
            fields: ['n_jobs'],
            title: 'number of jobs',
            labelTitle: { font: '8px' },
            grid: {
                odd: {
                    opacity: 1,
                    fill: '#ddd',
                    stroke: '#bbb',
                    'stroke-width': 1
                }
            }
            //minimum: 0,
            //adjustMinimumByMajorUnit: 0
        },
        {
            type: 'Category',
            position: 'bottom',
            fields: ['backup_time'],
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
            type: 'column',
            highlight: true,
            axis: 'left',
            xField: 'backup_time',
            yField: ['n_jobs'],
            style: {
                opacity: 0.93
            },
            tips: {
                trackMouse: true,
                width: 220,
                height: 20,
                renderer: function(storeItem, item) {
                    var n_jobs = storeItem.get('n_jobs');
                    var jobs_str = ' job';
                    if (n_jobs != 1) {
                        jobs_str += 's';
                    }
                    this.setTitle('Backup time ' + storeItem.get('backup_time') + ': ' + n_jobs + jobs_str);
                }
            }
        }]
    }
);
