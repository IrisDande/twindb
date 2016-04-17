Name:		twindb-monitoring
Version:	0.0
Release:	30%{?dist}
Summary:	Configuration files to monitor TwinDB infrastructure

Group:		Applications/Databases
License:	Commercial
URL:		http://monitoring.twindb.com/nagios/
Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

BuildArch:  noarch
BuildRequires:	coreutils
Requires:	    nagios percona-nagios-plugins nagios-plugins-all nagios-plugins-nrpe chkconfig

%description
Configuration files to monitor TwinDB infrastructure


%prep
%setup -q


%build
make


%install
rm -rf %{buildroot}
make install DESTDIR=%{buildroot}


%clean
rm -rf %{buildroot}

%files
%defattr(644,root,root,755)
%{_sysconfdir}/nagios/conf.d/app-04.cfg
%{_sysconfdir}/nagios/conf.d/app-03.cfg
%{_sysconfdir}/nagios/conf.d/db-02.cfg
%{_sysconfdir}/nagios/conf.d/db-03.cfg
%{_sysconfdir}/nagios/conf.d/monitoring.cfg
%{_sysconfdir}/nagios/conf.d/commands.cfg
%{_sysconfdir}/nagios/conf.d/contacts.cfg
%{_sysconfdir}/nagios/conf.d/templates.cfg
%attr(755, root, root)%{_libdir}/nagios/plugins/check_chat

%post
echo "Enabling nagios service"
chkconfig nagios on
echo "Enabling httpd service"
chkconfig httpd on
echo "Restarting nagios service"
/etc/init.d/nagios checkconfig && /etc/init.d/nagios restart
echo "Restarting httpd service"
/etc/init.d/httpd configtest && /etc/init.d/httpd graceful
# Create index.html to shut up check_http check
touch /var/www/html/index.html

%changelog
* Sat Sep 6 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0
- Initial package
