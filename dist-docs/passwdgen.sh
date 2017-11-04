#!/bin/sh
#
# The sample shell script to create account. For MySQL
#
# Usage: ./passwordgen.sh csvfile
#  The content of "csvfile" has to two columns, first "username", sedcond "password."
#  These feilds has to divided with commna "," and don't quote them
#  Then, generate sql statements for "username" with "password" in "csvfile."
#
#  by Masayuki Nii (nii@msyk.net)
#  2017/10/31
#

if [ ! -s "$1" ]
then
    echo "CSV file '$1' does't exist."
    exit -1
fi
ECHO="/bin/echo"
DT=$(cat "$1")

for LN in ${DT}
do
    UN=$(${ECHO} ${LN} | cut -d ',' -f 1)
    PASS=$(${ECHO} ${LN} | cut -d ',' -f 2)

    CODE1=`expr $RANDOM % 95 + 32`
    CODE2=`expr $RANDOM % 95 + 32`
    CODE3=`expr $RANDOM % 95 + 32`
    CODE4=`expr $RANDOM % 95 + 32`
    ST="echo chr(${CODE1}).chr(${CODE2}).chr(${CODE3}).chr(${CODE4});"
    SOLT=$(php -r "${ST}")

    HASH=$(${ECHO} -n "${PASS}${SOLT}" | openssl sha1 -sha1)
    SOLTHEX=$(${ECHO} -n "${SOLT}" | xxd -ps)
    ${ECHO} -n "INSERT authuser SET "
    ${ECHO} -n "username='${UN}',"
    ${ECHO} -n "initialpass='${PASS}',"
    ${ECHO}    "hashedpasswd='${HASH}${SOLTHEX}';"

# echo "${UN},${PASS},${HASH}${SOLTHEX}"

done
