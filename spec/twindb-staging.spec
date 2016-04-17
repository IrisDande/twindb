Name:		twindb-staging
Version:	0.0.1
Release:	2
Summary:	TwinDB Staging Repository

Group:		Applications/Databases
License:	GPL version 2
URL:		https://twindb.com

BuildArch:      noarch
BuildRequires:	coreutils
Requires:	gnupg redhat-lsb sed gawk

%description
The package provides TwinDB staging releases repository configuration

%prep

%build

%install
install -d %{buildroot}/%{_sysconfdir}/yum.repos.d
cat > %{buildroot}/%{_sysconfdir}/yum.repos.d/twindb-staging.repo <<_EOF_
[twindb-staging]
name=TwinDB - Staging
baseurl=http://repo-staging.twindb.com/rpm/\$releasever/\$basearch
gpgcheck=1
gpgkey=https://repo.twindb.com/packager-twindb.asc
priority=9

_EOF_

%post
rel=6
sed -i -e "s/@@RELEASEVER@/$rel/" %{_sysconfdir}/yum.repos.d/twindb-staging.repo

%files
%defattr(644, root, root, 755>)
%{_sysconfdir}/yum.repos.d/twindb-staging.repo

%changelog
* Sun Nov 7 2014 Aleksandr Kuzminsky <aleksandr.kuzminsky@doppeln.com> - 0.0.1
- Initial package.
