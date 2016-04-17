Ext.define('TwinDB.view.ServerBackupCopies', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.server-backupcopies',
    defaults: {
        margin: 5
    },
    store: 'ServerBackupCopies',
    rootVisible: false,
    columns: [
        {
            text: 'backup_copy_id',
            dataIndex: 'backup_copy_id',
            hidden: true
        },
        {
            xtype: 'treecolumn',
            text: 'File name',
            dataIndex: 'name',
            width: 500
        },
        {
            text: 'Taken',
            dataIndex: 'finish_actual',
            width: 150
        },
        {
            text: 'Size',
            dataIndex: 'size'
        },
        {
            text: 'backup_type',
            dataIndex: 'backup_type',
            hidden: true
        }

    ]
});
