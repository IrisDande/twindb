Ext.define('TwinDB.controller.Login', {
    extend: 'Ext.app.Controller',
    views: [
        'Signup',
        'Login',
        'Header',
        'authentication.CapsLockTooltip',
        'authentication.RegCodeTooltip'
    ],
    requires:[
        'Ext.form.field.VTypes',
        'Ext.util.DelayedTask',
        'TwinDB.util.Util'
    ],
    refs: [
        {
            ref: 'capslockTooltip',
            selector: 'capslocktooltip'
        },
        {
            ref: 'regcodeTooltip',
            selector: 'regcodetooltip'
        }
    ],
    init: function() {

        Ext.apply(Ext.form.field.VTypes, {
            password: function(val, field) {
                if (field.initialPassField) {
                    var pwd = field.up('form').down('#' + field.initialPassField);
                    return (val == pwd.getValue());
                }
                return true;
            },
            passwordText: 'Passwords do not match'
        });

        this.control({
            "login form button#submit": {
                click: this.onButtonClickSubmit
            },
            "login form button#cancel": {
                click: this.onButtonClickCancel
            },
            "login form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "login form textfield[name=password]": {
                keypress: this.onTextfieldKeyPress
            },
            "login": {
                close: this.LoginWindowClose
            },
            "alpha-notice": {
                close: this.showLoginWindow
            },
            "alpha-notice button#ok": {
                click: this.AlphaNoticeClose
            },
            "appheader button#signup": {
                click: function(){
                    var signup_win = Ext.ComponentQuery.query('signup')[0];
                    if(signup_win){
                        signup_win.show();
                    } else {
                        Ext.create('TwinDB.view.Signup');
                    }
                }
            },
            "appheader button#login": {
                click: function(){
                    var login_window = Ext.ComponentQuery.query('login')[0];
                    if(login_window){
                        login_window.show();
                    } else {
                        Ext.create('TwinDB.view.Login');
                    }
                }
            },
            "appheader button#logout": {
                click: this.onButtonClickLogout
            },
            "#invitation_code": {
                focus: function(){
                    console.log('#invitation_code is in focus');
                    Ext.widget('regcodetooltip');
                    this.getRegcodeTooltip().show();
                },
                blur: function(){
                    console.log('#invitation_code is out of focus');
                    var tooltip = this.getRegcodeTooltip();
                    if ( tooltip ) {
                        var task = new Ext.util.DelayedTask(function() {
                            tooltip.close();
                        });
                        task.delay(500);
                    }
                }
            },
            "signup button#cancel": {
                click: this.onButtonClickCancel
            },
            "signup button#submit": {
                click: this.registerUser
            },
            "signup form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "forgotpassword button#cancel": {
                click: this.onButtonClickCancel
            },
            "forgotpassword button#submit": {
                click: this.resetPassword
            },
            "forgotpassword form textfield": {
                specialkey: this.onTextfieldSpecialKey
            }
        });
    },
    onButtonClickSubmit: function(button) {

        console.log('login submit');
        var formPanel = button.up('form'),
            login = button.up('login'),
            email = formPanel.down('textfield[name=email]').getValue(),
            pass = formPanel.down('textfield[name=password]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Authenticating...');
            Ext.Ajax.request({
                url: 'php/login.php',
                params: {
                    email: email,
                    password: pass
                    },
                success: function(conn, response, options, eOpts) {
                    Ext.get(login.getEl()).unmask();
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        TwinDB.authenticated = true;
                        localStorage.setItem("registeredUser", true); // #12
                        login.close(); // #4
                        //Ext.create('TwinDB.view.Viewport'); // #5
                        win_wait.close();
                        // Reopen TwinDB console
                        TwinDB.util.Util.updateInterface();
                        //location.href = '';
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    onButtonClickCancel: function(button) {
        console.log('login cancel');
        button.up('window').close();
    },
    onTextfieldSpecialKey: function(field, e, options) {
        console.log('Special key event handler after key #' + e.getKey() + ' pressed'); // #6
        if (e.getKey() == e.ENTER){
            var submitBtn = field.up('form').down('button#submit');
            submitBtn.fireEvent('click', submitBtn, e, options);
        }
        if (e.getKey() == e.ESC){
            field.up('window').close();
        }
    },
    onTextfieldKeyPress: function(field, e) {
        var charCode = e.getCharCode(); // #1
        if((e.shiftKey && charCode >= 97 && charCode <= 122) || // #2
        (!e.shiftKey && charCode >= 65 && charCode <= 90)){
            if(this.getCapslockTooltip() === undefined){ // #3
                Ext.widget('capslocktooltip'); // #4
            }
            this.getCapslockTooltip().show(); // #5
        } else {
            if(this.getCapslockTooltip() !== undefined){ // #6
                this.getCapslockTooltip().hide(); // #7
            }
        }
    },
    LoginWindowClose: function(){

        console.log('authenticated = ' + TwinDB.authenticated);
        TwinDB.util.Util.updateAuthControls();
    },
    showLoginWindow: function(){
        var loginWindow = Ext.ComponentQuery.query('login')[0];
        loginWindow.show();
    },
    showAlphaNotice: function(callback){

        Ext.Msg.show({
            title: 'Alpha testing notice',
            msg: 'Greetings from TwinDB team!\n'
                + 'We started alpha testing of TwinDB online backups.\n'
                + 'If you would like to participate please drop us email on <a href="mailto:alpha@twindb.com?Subject=TwinDB%20alpha%20testing" target="_top">alpha@twindb.com</a>',
            iconCls: 'brick',
            icon: Ext.MessageBox.INFO,
            buttons: Ext.MessageBox.OK,
            fn: callback
        });
    },
    AlphaNoticeClose: function(button){
        button.up('window').close();
    },
    onButtonClickLogout: function() {
        Ext.Msg.show({
            title: 'Confirm logout',
            msg: 'Are you sure you want to log out?',
            icon: Ext.Msg.QUESTION,
            buttons: Ext.Msg.YESNO,
            fn: function(btn){
                if (btn == 'yes' ) {
                    console.log('Logging out');
                    var win_wait = Ext.Msg.wait('Logging out...');
                    Ext.Ajax.request({
                        url: 'php/logout.php',
                        success: function(conn, response, options, eOpts) {
                        var result = Ext.JSON.decode(conn.responseText, true); // #1
                            if (!result){ // #2
                                result = {};
                                result.success = false;
                                result.msg = conn.responseText;
                            }
                            if (result.success) { // #3
                                TwinDB.authenticated = false;
                                localStorage.setItem("registeredUser", true); // #12
                                TwinDB.util.Util.updateInterface();
                                win_wait.close();
                                // Reopen TwinDB console
                                //location.href = '';
                            } else {
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        },
                        failure: function(conn, response, options, eOpts) {
                            TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                        }
                    });
                }
            }
        });
    },
    registerUser: function(button) {
        var formPanel = button.up('form'),
            signup_win = button.up('signup'),
            email = formPanel.down('textfield[name=email]').getValue(),
            pass = formPanel.down('textfield[name=password]').getValue();

        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Registering new user...');
            Ext.Ajax.request({
                url: 'php/signup.php',
                params: {
                    email: email,
                    password: pass
                    },
                success: function(conn, response, options, eOpts) {
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        TwinDB.authenticated = true;
                        localStorage.setItem("registeredUser", true); // #12
                        signup_win.close();
                        win_wait.close();
                        TwinDB.util.Util.updateInterface();
                        // Reopen TwinDB console
                        //location.href = '';
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    resetPassword: function(button, e) {

        var formPanel = button.up('form'),
            forgotpassword_win = button.up('forgotpassword'),
            email = formPanel.down('textfield[name=email]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Resetting password...');
            Ext.Ajax.request({
                url: 'php/forgotpassword.php',
                params: {
                    email: email
                    },
                success: function(conn, response, options, eOpts) {
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        TwinDB.authenticated = false;
                        forgotpassword_win.close();
                        win_wait.close();
                        Ext.Msg.show({
                            title: 'Password reset link is ready',
                            msg: '<p>We accepted your request to reset password.' + 
                                '<p>A reset password link has been sent to <strong>' + email + '</strong>.' +
                                '<p>Please open the e-mail and follow the instuctions in it.',
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO
                        });
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    }
});
