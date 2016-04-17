Ext.define('TwinDB.view.JobGrid',{
    extend : 'Ext.grid.Panel',
    alias : 'widget.jobgrid',

    requires: [
        'Ext.grid.column.RowNumberer',
        'Ext.toolbar.Paging',
        'Ext.util.Format',
        'Ext.ux.grid.FiltersFeature'
    ],

    store: 'JobGrid',
    viewConfig: {
        loadMask: false
    },
    border: true,

    features: [{
        ftype: 'filters',
        // encode and local configuration options defined previously for easier reuse
        encode: true, // json encode the filter query
        local: false

    }],
    emptyText: 'No Matching Records',
    columns:[
        { 
            text: 'job_id',
            dataIndex: 'job_id',
            hidden: true
        },
        /*
        { 
            xtype: 'rownumberer' 
        },
        */
        {
            text: 'Server',
            dataIndex: 'name',
            filterable: true,
            flex: 1,
            filter: {
                type: 'string'
            }
        },
        {
            text: 'Type',
            dataIndex: 'type',
            width: 50,
            flex: 1,
            filter: {
                type: 'list',
                options: ['backup', 'restore']
            }
        },
        {
            text: 'Status',
            dataIndex: 'status',
            flex: 1,
            renderer: function(val) {
                if ( val == 'Failed' ) {
                    return '<span style="color:red;">' + val + '</span>';
                } else if ( val == 'Finished' ) {
                    return '<span style="color:green;">' + val + '</span>';
                } else if(val == 'Scheduled') {
                    return '<span style="color:grey;">' + val + '</span>';
                }
                    return val;
            },
            filter: {
                type: 'list',
                options: ['Scheduled','In progress','Finished','Failed']
            }
        },
        {
            text: 'Scheduled',
            dataIndex: 'start_scheduled',
            type: 'datecolumn',
            flex: 1,
            renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),
            filter: {
                type: 'datetime',
                date: {
                    format: 'Y-m-d'
                },

                time: {
                    format: 'H:i:s',
                    increment: 1
                },
                menuItems : ['before', 'after']
            }
        },
        {
            text: 'Started',
            dataIndex: 'start_actual',
            type: 'datecolumn',
            flex: 1,
            renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),
            filter: {
                type: 'datetime',
                date: {
                    format: 'Y-m-d'
                },

                time: {
                    format: 'H:i:s',
                    increment: 1
                },
                menuItems : ['before', 'after']
            },
            width: 120
        },
        {
            text: 'Finished',
            dataIndex: 'finish_actual',
            type: 'datecolumn',
            flex: 1,
            renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),
            filter: {
                type: 'datetime',
                date: {
                    format: 'Y-m-d'
                },

                time: {
                    format: 'H:i:s',
                    increment: 1
                },
                menuItems : ['before', 'after']

            },
            width: 120
        }
    ],
    dockedItems: [{
        xtype: 'pagingtoolbar',
        store: 'JobGrid',   // same store GridPanel is using
        dock: 'bottom',
        displayInfo: true,
        items: [
            {
                text: 'Clear filters',
                handler: function () {
                    console.log('This:');
                    console.log(this);
                    this.up('jobgrid').filters.clearFilters();
                }
            }
        ]
    }]
});
