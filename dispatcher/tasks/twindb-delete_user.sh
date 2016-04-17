set -eux
function usage(){
    echo "Usage:"
    echo "`basename $0` <user_id>"
}

if test -z "$1"
then
    usage
    exit
fi

user_id=$1

mysql -e "delete from config where user_id=$user_id" twindb
mysql -e "delete from retention_policy where user_id=$user_id" twindb
mysql -e "delete from volume where user_id=$user_id" twindb
mysql -e "delete from user where user_id=$user_id" twindb
#sudo umount /var/$project_short_name-sftp/user_id_$user_id/dev/pts
#sudo userdel user_id_$user_id
#sudo rm -rf "/var/$project_short_name-sftp/user_id_$user_id"
