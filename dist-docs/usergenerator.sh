#!/bin/sh
#
# The sample shell script to create many accounts. For MySQL
#
#  by Masayuki Nii (nii@msyk.net)
#  2012/6/29
#

COUNT=20;

while [ "${COUNT}" -gt 0 ]
do
    SOLT=$(cat /dev/urandom | base64 | fold -w 4 | head -n 1)
    PASS=$(cat /dev/urandom | base64 | fold -w 8 | head -n 1)
    VALUE=$(/bin/echo -n "${PASS}${SOLT}" | openssl sha256 -sha256)
    for i in {1..4999}
    do
      VALUE=$(/bin/echo -n "${VALUE}" | xxd -r -p | openssl sha256 -sha256)
    done
    SOLTHEX=$(/bin/echo -n "${SOLT}" | xxd -ps)
    UNUM=`expr 1000 + ${COUNT}`
#    echo -n "INSERT INTO authuser(id,username,initialpass,hashedpasswd) "
#    echo "VALUES(${UNUM},'ios${UNUM}', '${PASS}', '${HASH}${SOLTHEX}');"
#    echo "INSERT INTO authcor(user_id,dest_group_id) VALUES(${UNUM},102);"
    echo "${PASS},${VALUE}${SOLTHEX}"
    COUNT=`expr ${COUNT} - 1`
done

#admin, thirdparty422
#echo "INSERT INTO authuser(id,username,hashedpasswd) VALUE(101,'admin','af2f5abf4091e53559adcb96278937bc281b#930654455354');"
#echo "INSERT INTO authgroup(id,groupname) VALUE(101,'admin');"
#echo "INSERT INTO authgroup(id,groupname) VALUE(102,'iosreader');"
