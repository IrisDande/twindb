Name:       twindb-release    
Version:    0.0.7
Release:    7
Summary:    TwinDB Releases Repository

Group:      Applications/Databases
License:    GPL version 2
URL:        https://twindb.com

BuildArch:      noarch
AutoReq: 0
BuildRequires:  coreutils
Requires:       gnupg
Requires:       yum
Requires:       openssl

%description
The package provides TwinDB releases repository configuration

%prep

%build

%install

# YUM after 3.2.27 version supports SSL
read  a b c <<< $( yum --version | head -1 | sed 's/\./ /g')
if [ "$a" -gt 3 ]
then
    proto=https
else
    if [ "$b" -gt 2 ]
    then
        proto=https
    else
        if [ "$c" -gt 26 ]
        then
            proto=https
        else
            proto=http
        fi
    fi
fi

install -d $RPM_BUILD_ROOT/%{_sysconfdir}/yum.repos.d
cat > $RPM_BUILD_ROOT/%{_sysconfdir}/yum.repos.d/twindb-release.repo <<_EOF_
[twindb]
name=TwinDB - Public
baseurl=$proto://repo.twindb.com/rpm/\$releasever/\$basearch
enabled = 1
gpgcheck=1
priority=9
gpgkey=https://repo.twindb.com/packager-twindb.asc
_EOF_

if [ "$proto" = "https" ]
then
cat >> $RPM_BUILD_ROOT/%{_sysconfdir}/yum.repos.d/twindb-release.repo <<_EOF_
sslverify=1
sslclientcert=/var/lib/yum/client.cert
sslclientkey=/var/lib/yum/client.key
_EOF_
fi

%post
if ! test -f /var/lib/yum/client.key
then
    openssl genrsa -out /var/lib/yum/client.key 1024
fi
chmod 400 /var/lib/yum/client.key
if ! test -f /var/lib/yum/client.cert
then
    openssl req -new -x509 -days 3650 -text -key /var/lib/yum/client.key -out /var/lib/yum/client.cert -batch
fi

%files
%defattr(644, root, root, 755>)
%{_sysconfdir}/yum.repos.d/twindb-release.repo

%changelog
* Wed Dec 22 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0.7-1
- Added SSL auth for twindb-server-* packages

* Wed Dec 4 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0.6-1
- Removed third party repositories

* Wed Dec 4 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0.4-1
- Copied TwinDB, Oracle and Percona GPG public keys to https://repo.twindb.com

* Wed Dec 3 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0.3-1
- Include Oracle repo

* Wed Nov 26 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0.0.1-6
- Updated version

* Sun Sep 28 2014 Aleksandr Kuzminsky <aleks@twindb.com> - 0-6
- Removed epel
- Updated percona repo

* Sun Apr 20 2014 Aleksandr Kuzminsky <akuzminsky@twindb.com> - 0-5
- Moved public certificate to the main website

* Mon Oct 10 2013 Aleksandr Kuzminsky <aleksandr.kuzminsky@doppeln.com> - 0-3
- Initial package.
