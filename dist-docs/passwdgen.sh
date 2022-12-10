#!/bin/sh
#
# The sample shell script to create account. For MySQL
#
# Usage:
#  passwdgen --csv=csvfile [--sql]
#  passwdgen --user=username --password=password [--sql]
#
#  The content of "csvfile" has to two columns, first "username", sedcond "password."
#  These feilds has to divided with commna "," and don't quote them
#
#  by Masayuki Nii (nii@msyk.net)
#  2017/10/31
#

# Reference: http://www.usinezumi.com/blog/2016/10/16/304/

ECHO="/bin/echo"

optSQL=0
optGen=0
csvFile=
userName=
password=

check_arg_exit(){
	if [ -z "$OPTARG" ]; then
		echo "${0##*/}: option needs arg -- $opt"
		exit 1
	fi
}

# generate_hash_passwd username password
generate_hash_passwd(){
    SOLT=$(cat /dev/urandom | head -c 10 | base64 | fold -w 4 | head -n 1)
    VALUE=$(${ECHO} -n "$2${SOLT}" | openssl sha256 -sha256)
    for i in {1..4999}
    do
      VALUE=$(${ECHO} -n "${VALUE}" | xxd -r -p | openssl sha256 -sha256)
    done
    SOLTHEX=$(${ECHO} -n "${SOLT}" | xxd -ps)
    if [ ${optSQL} -eq 1 ]
    then
        ${ECHO} -n "INSERT authuser(username,initialpass,hashedpasswd) VALUES("
        ${ECHO}    "'$1','$2','${VALUE}${SOLTHEX}');"
    else
        ${ECHO} "'$1','$2','${VALUE}${SOLTHEX}'"
    fi
}

while getopts "sgc:u:p:-:" opt; do
	if [ ${opt} = "-" ]; then
		opt=$(echo ${OPTARG} | awk -F'=' '{print $1}')
		OPTARG=$(echo ${OPTARG} | awk -F'=' '{print $2}')
	fi

	case "$opt" in
		s | sql)
			optSQL=1
			;;
		g | generate)
			optGen=1
			;;
		c | csv)
		    check_arg_exit
		    csvFile="${OPTARG}"
			;;
		u | user)
		    check_arg_exit
		    userName="${OPTARG}"
			;;
		p | password)
		    check_arg_exit
		    password="${OPTARG}"
			;;
		? )
			exit 1
			;;
		* )
			echo "invalid option -- $opt"
			exit 1
	esac
done

if [ ${#csvFile} -gt 0 ]
then
    if [ ! -e "${csvFile}" ]
    then
		echo "'${csvFile}' doesn't exist."
		exit 1
    fi
    DT=$(cat "${csvFile}")
    for LN in ${DT}
    do
        UN=$(${ECHO} ${LN} | cut -d ',' -f 1)
        PASS=$(${ECHO} ${LN} | cut -d ',' -f 2)
        generate_hash_passwd "${UN}" "${PASS}"
    done
elif [ ${#password} -gt 0 ]
then
    generate_hash_passwd "${userName}" "${password}"
else
    echo "CSV file or Password is required."
    exit -1
fi

exit 0;
