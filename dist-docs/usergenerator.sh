#!/sh/bin
#
# The sample shell script to create many accounts. For MySQL
#
#  by Masayuki Nii (nii@msyk.net)
#  2012/6/29
#

COUNT=10;

while [ "${COUNT}" \> 0 ]
do
    CODE1=`expr $RANDOM % 95 + 32`
    CODE2=`expr $RANDOM % 95 + 32`
    CODE3=`expr $RANDOM % 95 + 32`
    CODE4=`expr $RANDOM % 95 + 32`
    ST="echo chr(${CODE1}).chr(${CODE2}).chr(${CODE3}).chr(${CODE4});"
    SOLT=`php -r "${ST}" `

    CODE1=`expr $RANDOM % 26 + 65`
    CODE2=`expr $RANDOM % 26 + 65`
    CODE3=`expr $RANDOM % 10 + 48`
    CODE4=`expr $RANDOM % 10 + 48`
    CODE5=`expr $RANDOM % 26 + 97`
    CODE6=`expr $RANDOM % 26 + 97`
    CODE7=`expr $RANDOM % 10 + 48`
    CODE8=`expr $RANDOM % 10 + 48`
    ST="echo chr(${CODE1}).chr(${CODE2}).chr(${CODE3}).chr(${CODE4}).chr(${CODE5}).chr(${CODE6}).chr(${CODE7}).chr(${CODE8});"
    PASS=`php -r "${ST}" `

    HASH=`echo -n "${PASS}${SOLT}" | openssl sha1 -sha1`
    SOLTHEX=`echo -n "${SOLT}" | xxd -ps`
    UNUM=`expr 1000 + ${COUNT}`
    echo -n "INSERT INTO authuser(id,username,initialpass,hashedpasswd) "
    echo "VALUES(${UNUM},'ios${UNUM}', '${PASS}', '${HASH}${SOLTHEX}');"
    echo "INSERT INTO authcor(user_id,dest_group_id) VALUES(${UNUM},102);"

    COUNT=`expr ${COUNT} - 1`
done

#admin, thirdparty422
echo "INSERT INTO authuser(id,username,hashedpasswd) VALUE(101,'admin','af2f5abf4091e53559adcb96278937bc281b930654455354');"
echo "INSERT INTO authgroup(id,groupname) VALUE(101,'admin');"
echo "INSERT INTO authgroup(id,groupname) VALUE(102,'iosreader');"
