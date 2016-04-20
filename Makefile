server_version = 0.4.25
server_release = 1
rpmmacros = /usr/lib/rpm/macros:/usr/lib/rpm/redhat/macros:/etc/rpm/macros:spec/rpmmacros
build_dir = .build
src_dir = twindb-server-${server_version}
pwd := $(shell pwd)
top_dir = ${pwd}/${build_dir}/rpmbuild
deb_packages = build-essential devscripts debhelper

SENCHA_CMD_URL=https://cdn.sencha.com/cmd/5.1.3.61/SenchaCmd-5.1.3.61-linux-x64.run.zip
SENCHA_CMD_ARCHIVE=SenchaCmd-5.1.3.61-linux-x64.run.zip
SENCHA_CMD_UNZIP=SenchaCmd-5.1.3.61-linux-x64.run

.PHONY: help rpm sign rpmmacros monitoring-rpm staging-repo spec

all:
	make -C www/workspace/twindb-console/
help:
	@echo "Supported targets"
	@grep ^.*: Makefile | grep -v grep

clean:
	rm -fr .build
	rm -f spec/twindb-server.spec

archive:
	rm -rf "${build_dir}"
	mkdir -p "${build_dir}/${src_dir}"
	cp -R * "${build_dir}/${src_dir}"
	tar zcf "${build_dir}/${src_dir}.tar.gz" -C "${build_dir}" "${src_dir}"

installdirs:
	mkdir -p "${DESTDIR}/usr/bin"
	mkdir -p "${DESTDIR}/etc/cron.d"
	mkdir -p "${DESTDIR}/etc/init.d"
	mkdir -p "${DESTDIR}/etc/twindb/gnupg"
	mkdir -p "${DESTDIR}/etc/twindb/httpd/conf.d"
	mkdir -p "${DESTDIR}/etc/twindb/httpd/conf/ssl.crt"
	mkdir -p "${DESTDIR}/etc/twindb/httpd/conf/ssl.key"
	mkdir -p "${DESTDIR}/etc/logrotate.d"
	mkdir -p "${DESTDIR}/usr/share/twindb/inc"
	mkdir -p "${DESTDIR}/usr/share/twindb/ssh"
	mkdir -p "${DESTDIR}/usr/share/twindb/gpg"
	mkdir -p "${DESTDIR}/usr/share/twindb/dispatcher"
	mkdir -p "${DESTDIR}/usr/share/twindb/console"
	mkdir -p "${DESTDIR}/usr/share/twindb/db/updates"

install: installdirs
	#
	# Installing files for package -common
	#
	install -m 644 support/http/twindb-common.conf                  	${DESTDIR}/etc/twindb/httpd/conf.d
	test -f ${DESTDIR}/etc/twindb/config.php || \
		install -m 640 -o root -g apache config/config.php	${DESTDIR}/etc/twindb
	install -m 644 common/dispatcher_lib.php                        	${DESTDIR}/usr/share/twindb/inc
	install -m 644 common/general_lib.php                           	${DESTDIR}/usr/share/twindb/inc
	install -m 644 common/mysql_lib.php                             	${DESTDIR}/usr/share/twindb/inc
	install -m 644 common/variables.php                             	${DESTDIR}/usr/share/twindb/inc
	install -m 600 support/keys/twindb-dispatcher-ssh.key           	${DESTDIR}/usr/share/twindb/ssh
	install -m 600 support/keys/twindb-dispatcher-ssh.key.pub       	${DESTDIR}/usr/share/twindb/ssh
	install -m 644 support/logrotate/twindb                         	${DESTDIR}/etc/logrotate.d
	#
	# Installing files for package -dispatcher
	#
	install -m 644 support/cron/twindb-dispatcher.cron                      ${DESTDIR}/etc/cron.d
	install -m 644 support/http/twindb-dispatcher.conf                      ${DESTDIR}/etc/twindb/httpd/conf.d
	install -m 644 dispatcher/api.php                                       ${DESTDIR}/usr/share/twindb/dispatcher
	install -m 600 support/keys/twindb-dispatcher-gpg.key                   ${DESTDIR}/usr/share/twindb/gpg
	install -m 755 dispatcher/tasks/twindb-apply_retention_policy.php       ${DESTDIR}/usr/bin/twindb-apply_retention_policy
	install -m 755 dispatcher/tasks/twindb-schedule.php                     ${DESTDIR}/usr/bin/twindb-schedule
	install -m 755 dispatcher/tasks/twindb-volume_usage_snapshot.php        ${DESTDIR}/usr/bin/twindb-volume_usage_snapshot
	#
	# Installing files for package -www
	#
	install -m 644 support/http/twindb-console.conf                             ${DESTDIR}/etc/twindb/httpd/conf.d
	install -m 400 support/http/ssl.crt/ca-bundle-client.crt                    ${DESTDIR}/etc/twindb/httpd/conf/ssl.crt
	install -m 400 support/http/ssl.crt/twindb_com.crt                          ${DESTDIR}/etc/twindb/httpd/conf/ssl.crt
	install -m 400 support/http/ssl.key/twindb_com.key                          ${DESTDIR}/etc/twindb/httpd/conf/ssl.key
	cp -R www/workspace/build/production/TwinDB/*                               ${DESTDIR}/usr/share/twindb/console
	install -m 644 www/workspace/twindb-console/resources/icons/favicon.ico     ${DESTDIR}/usr/share/twindb/console
	#
	# Installing files for package -db
	#
	install -m 644 db/twindb-schema.sql           ${DESTDIR}/usr/share/twindb/db
	install -m 600 db/twindb-data.sql             ${DESTDIR}/usr/share/twindb/db
	for u in `ls db/updates` ; \
	do \
	    cp "db/updates/$$u" ${DESTDIR}/usr/share/twindb/db/updates ; \
	done
	install -m 755 db/twindb-db_upgrade.sh        ${DESTDIR}/usr/bin/twindb-db_upgrade
	#
	# Installing files for package -storage
	#
	install -m 600 storage/sshd_config                          ${DESTDIR}/etc/twindb
	install -m 600 storage/sshd_config_5                        ${DESTDIR}/etc/twindb
	install -m 755 storage/twindb-storage                       ${DESTDIR}/etc/init.d/
	install -m 644 support/keys/twindb-dispatcher-ssh.key.pub   ${DESTDIR}/usr/share/twindb/ssh
	install -m 755 storage/twindb-add_chroot_user.sh            ${DESTDIR}/usr/bin/twindb-add_chroot_user
	install -m 755 storage/twindb-register-storage.py           ${DESTDIR}/usr/bin/twindb-register-storage
	install -m 755 storage/twindb-storage-auth.sh               ${DESTDIR}/usr/bin/twindb-storage-auth


#=======================================================================================================================
# RPM stuff
#=======================================================================================================================
rpm: spec rpmmacros checktools
	rm -rf "${build_dir}"
	mkdir -p "${build_dir}/${src_dir}"
	cp -R * "${build_dir}/${src_dir}"
	mkdir -p "${top_dir}/SOURCES"
	tar zvcf "${top_dir}/SOURCES/${src_dir}.tar.gz" -C "${build_dir}" "${src_dir}"
	rpmbuild --macros=${rpmmacros} --define '_topdir ${top_dir}' -ba spec/twindb-server.spec

checktools: checkpackages checksencha checkrpmbuild

checkrpmbuild:
	@if test -z "`which rpmbuild`"; then \
		echo -e "Error: rpmbuild is not found. Please install package rpm-build:\nyum install rpm-build"; \
		yum install -y rpm-build ;\
	else \
		echo -e "The package rpmbuild is installed"; \
	fi

checksencha:
	@if test -z "`which sencha`"; then \
		echo -e "Error: sencha is not found. Go to http://sencha.com and install Sencha Cmd"; \
		wget -O /tmp/${SENCHA_CMD_ARCHIVE} ${SENCHA_CMD_URL} && \
		cd /tmp ;\
		unzip -o ${SENCHA_CMD_ARCHIVE} | grep inflating | awk '{ print $2}'; \
        	chmod a+x /tmp/${SENCHA_CMD_UNZIP} | ln -s /root/bin/Sencha/Cmd/5.1.3.61/* /usr/bin; \
		./${SENCHA_CMD_UNZIP} ;\
	else \
		echo "The package sencha is installed"; \
	fi

checkpackages:
	@for p in java-1.7.0-openjdk ruby rubygems; do \
		if test -z "`rpm -q $$p | grep -v 'is not installed'`"; then \
			echo -e "Error: $$p is not found. Please install package $$p:\nyum install $$p"; \
			yum install -y $$p \
#			exit -1; \
		else \
			echo -e "The package $$p is installed"; \
		fi; \
	done

spec:
	sed -e 's/@@VERSION@/${server_version}/' -e 's/@@RELEASE@/${server_release}/' \
			spec/twindb-server.spec.template > spec/twindb-server.spec
sign: 
	rpm --addsign ${top_dir}/RPMS/noarch/twindb-server-*-${server_version}-${server_release}.noarch.rpm

upload-rpm:
	package_cloud push twindb/internal/el/6 .build/rpmbuild/RPMS/noarch/twindb-server-*-${server_version}-${server_release}.noarch.rpm

rpmmacros:
	if ! test -f ~/.rpmmacros ; then cp spec/rpmmacros ~/.rpmmacros; fi

monitoring-rpm: rpmmacros
	rm -rf "${build_dir}/twindb-monitoring-0.0"
	mkdir -p "${build_dir}/twindb-monitoring-0.0"
	cp -R monitoring/* "${build_dir}/twindb-monitoring-0.0"
	tar zcf "${top_dir}/SOURCES/twindb-monitoring-0.0.tar.gz" -C "${build_dir}" twindb-monitoring-0.0
	rpmbuild -ba spec/twindb-monitoring.spec
	rpm --addsign ${top_dir}/RPMS/noarch/twindb-monitoring-*

staging_repo_version = "`grep Version: spec/twindb-staging.spec | awk '{ print $$2}' | head -1`"
staging_repo_release = "`grep Release: spec/twindb-staging.spec | awk '{ print $$2}' | head -1`"

staging-repo: rpmmacros
	rpmbuild -ba spec/twindb-staging.spec
	rpm --addsign ${top_dir}/RPMS/noarch/twindb-staging-${staging_repo_version}-${staging_repo_release}.noarch.rpm

#=======================================================================================================================
# Debian stuff
#=======================================================================================================================
deb-dependencies: checksencha
	@echo "Checking dependencies"
	@for p in ${deb_packages}; \
	do echo -n "$$p ... " ; \
	    if test -z "`dpkg -l | grep $$p`"; \
	    then \
	        echo "$$p ... NOT installed"; \
	        apt-get -y install $$p; \
	    else \
	        echo "installed"; \
	    fi ; \
	done

deb-changelog:
	@if test -z "`grep parent_location .bzr/branch/branch.conf  | grep 'twindb/$$'`"; \
	then \
	    echo "'make deb-changelog' must be run only in the trunk" ; \
	    exit 1 ;\
	fi
	@echo "Generating changelog"
	@export DEBEMAIL="TwinDB Packager (TwinDB packager key) <packager@twindb.com>" ; \
	export version=${server_version}-${server_release} ; \
	if ! test -z "$$(bzr stat)" ; \
	then \
		echo "Error: There are uncommitted changes in the tree:" ; \
		echo "$$(bzr stat)" ; \
		echo "Please commit and restart make deb-changelog" ; \
		exit 1 ; \
	fi ; \
	export last_tag=$$(bzr tags | sort -nrk 2 | head -1 | awk '{ print $$1}' ) ; \
	cd spec/ ; \
	dch -v $$version.UNRELEASED --package twindb-server --distribution UNRELEASED "New version $$version" ; \
	for revno in `bzr log -r tag:$$last_tag.. --line | grep -v "{$$last_tag}" | awk -F: '{ print $$1}'`; \
	do \
	    export log_msg="`bzr log -r $$revno --line`" ; \
	    echo "Adding log entry to version $$tag: $$log_msg" ; \
	    dch --append "$$log_msg" ; \
	done ; \
	dch --append "Updated changelog for version $$version" ; \
	bzr ci -m "Updated changelog for version $$version" ; \
	bzr tag $$version

build-deb: deb-dependencies archive
	if test -z "`head -1 spec/debian/changelog | grep ${server_version}`"; then \
	    echo ""; \
	    echo "Oops, somebody forgot to run 'make deb-changelog'. To fix run this:"; \
	    echo ""; \
	    echo "make deb-changelog"; \
	    echo ""; \
	    exit -1; \
	fi
	mv "${build_dir}/${src_dir}.tar.gz" "${build_dir}/twindb-server_${server_version}.orig.tar.gz"
	cp -R spec/debian/ "${build_dir}/${src_dir}"
	rm -f "${build_dir}/${src_dir}/debian/changelog"
	export distr=`lsb_release -sc` ; \
	sed s/UNRELEASED/$$distr/g spec/debian/changelog > "${build_dir}/${src_dir}/debian/changelog"
	cd "${build_dir}/${src_dir}" && debuild --preserve-envvar PATH -us -uc

sign-deb:
	cd "${build_dir}"; debsign -kpackager@twindb.com *.changes

upload-deb:
	ssh ubuntu@deb.twindb.com "mkdir -p ~/deb/`lsb_release -sc`"
	scp ${build_dir}/*.changes ${build_dir}/*.dsc ${build_dir}/*.tar.gz \
		${build_dir}/*.deb ubuntu@deb.twindb.com:~/deb/`lsb_release -sc`

	
