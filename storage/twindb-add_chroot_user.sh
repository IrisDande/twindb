#!/usr/bin/env bash

set -eux
project_short_name="twindb"
CHROOT_DIR=/var/${project_short_name}-sftp
if test -z "$1" || test -z "$2"
then
	echo "Usage:"
	echo "    `basename $0` <username> <password>"
	exit 1
fi

set -u
username="$1"
pass="$2"

mkdir -p "$CHROOT_DIR/$username"
shell=`grep /bash /etc/shells | head -1`
if test -z "$shell"
then
    echo "bash isn't registered as shell"
    exit 1
fi

if test -z "`grep "^$username:" /etc/passwd`"
then
	useradd -g ${project_short_name} -d "/home" -M -s ${shell} "$username" --password ${pass}
fi

mkdir -p "$CHROOT_DIR/$username/bin"
mkdir -p "$CHROOT_DIR/$username/usr"
ln -fs ../bin "$CHROOT_DIR/$username/usr"
mkdir -p "$CHROOT_DIR/$username/usr/bin"
mkdir -p "$CHROOT_DIR/$username/dev/pts"
mkdir -p "$CHROOT_DIR/$username/lib"
ln -fs lib "$CHROOT_DIR/$username/lib64"


BINARIES="bash ls cat du scp ssh strace"
for b in ${BINARIES}
do
    # copy binaries
    b_full=`which ${b}`
    cp ${b_full} "$CHROOT_DIR/$username/bin"
    # Libraries
    for l in `ldd ${b_full}`
    do
        if test -f "${l}"
        then
            cp "${l}" "${CHROOT_DIR}/${username}/lib64"
        fi
    done
done

# special files

test -c "$CHROOT_DIR/$username/dev/urandom" || mknod "$CHROOT_DIR/$username/dev/urandom" c 1 9
test -c "$CHROOT_DIR/$username/dev/null" || mknod -m 666 "$CHROOT_DIR/$username/dev/null" c 1 3
test -c "$CHROOT_DIR/$username/dev/zero" || mknod -m 666 "$CHROOT_DIR/$username/dev/zero" c 1 5
test -c "$CHROOT_DIR/$username/dev/tty" || mknod -m 666 "$CHROOT_DIR/$username/dev/tty"  c 5 0

if test -z "`grep "$CHROOT_DIR/$username/dev/pts" /etc/mtab`"
then
	mount -t devpts devpts  "$CHROOT_DIR/$username/dev/pts"
fi

install -m 700 -o "$username" -g ${project_short_name} -d "$CHROOT_DIR/$username/home/.ssh"
touch "$CHROOT_DIR/$username/home/.ssh/authorized_keys"
chown "$username":${project_short_name} "$CHROOT_DIR/$username/home/.ssh/authorized_keys"
chmod 600 "$CHROOT_DIR/$username/home/.ssh/authorized_keys"

# other files
mkdir -p "$CHROOT_DIR/$username/etc"
echo 'export HOME=/home' > "$CHROOT_DIR/$username/etc/profile"
echo 'export PATH=$PATH:/bin' > "$CHROOT_DIR/$username/etc/profile"
echo 'cd' >> "$CHROOT_DIR/$username/etc/profile"
cp -pf /etc/ld.so.cache "$CHROOT_DIR/$username/etc"

dummy_file=`mktemp`
dummy_dir=`mktemp -d`
files=`strace scp "$dummy_file" "$dummy_dir" 2>&1 | grep open | grep -v "$dummy_file" | awk -F\" '{ print $2}'`
for f in ${files}
do
	if [ "$f" = "/etc/passwd" ]; then continue; fi
	if test -f "$f"
	then
		d=`dirname "$f"`
		mkdir -p "$CHROOT_DIR/$username/$d"
		cp "$f" "$CHROOT_DIR/$username/$d"
	fi
done

rm -f "$dummy_file"
rm -rf "$dummy_dir"

grep /etc/passwd -e "^root" -e ^${username}: > "$CHROOT_DIR/$username/etc/passwd"
test -f /etc/resolv.conf && cp /etc/resolv.conf "$CHROOT_DIR/$username/etc/"
test -f /etc/nsswitch.conf && cp /etc/nsswitch.conf "$CHROOT_DIR/$username/etc/"
test -f /lib64/libnss_dns.so.2 && cp -fp /lib64/libnss_dns.so.2 "$CHROOT_DIR/$username/lib64"

chown -R ${username}:${project_short_name} "$CHROOT_DIR/$username/home"
