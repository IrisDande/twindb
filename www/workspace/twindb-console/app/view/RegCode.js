Ext.define('TwinDB.view.RegCode', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.form.field.Display',
        'Ext.layout.container.VBox'
    ],
    alias: 'widget.regcode',
    autoShow: true,
    width: 500,
    height: 150,
    layout: {
        type: 'vbox'
    },
    iconCls: 'key',
    title: 'You registration code',
    closeAction: 'hide',
    closable: true,
    bodyPadding: 15,
    items: [
        {
            xtype: 'displayfield',
            name: 'reg_code_hint',
            anchor: '100%',
            value: 'To register a server run following command'
        },
        {
            xtype: 'displayfield',
            itemId: 'reg_code',
            name: 'reg_code',
            anchor: '100%',
            width: 420,
            value: '',
            fieldStyle: {
                'fontFamily'   : 'courier new',
                'fontSize'     : '14px'
            }
        }
    ],
    buttonAlign: 'center',
    buttons: [
        { 
            itemId: 'ok',
            text: 'Got it!' 
        }
    ]
});
