#!/bin/sh

SAMLLIBVER=2

DIR="$( cd "$( dirname "$0" )" && pwd )"
IMPATH="${DIR}/../INTER-Mediator"
SAMLPATH="${IMPATH}/vendor/simplesamlphp/simplesamlphp"
if [ ! -e "${IMPATH}" ]; then
  IMPATH="${DIR}/../src/INTER-Mediator"
  SAMLPATH="${IMPATH}/vendor/simplesamlphp/simplesamlphp"
  if [ ! -e "${IMPATH}" ]; then
    IMPATH="${DIR}/../../vendor/inter-mediator/inter-mediator"
    SAMLPATH="${DIR}/../../vendor/simplesamlphp/simplesamlphp"
    if [ ! -e "${SAMLPATH}" ]; then
      echo "[ERROR] Can't detect the SimpleSAMLphp."
      exit 1
    fi
  fi
fi
echo "SimpleSAMLphp Version: ${SAMLLIBVER}"
echo "INTER-Mediator: ${IMPATH}"
echo "SimpleSAMLphp: ${SAMLPATH}"

if [ ${SAMLLIBVER} = 1 ]; then
  cp -r "${SAMLPATH}/config-templates/"* "${DIR}"
  cp -r "${SAMLPATH}/metadata-templates/"* "${DIR}"
elif [ ${SAMLLIBVER} = 2 ]; then
  cp "${SAMLPATH}/config/config.php.dist" "${DIR}/config.php"
  cp "${SAMLPATH}/config/authsources.php.dist" "${DIR}/authsources.php"
  cp "${SAMLPATH}/config/acl.php.dist" "${DIR}/acl.php"
  cp "${SAMLPATH}/metadata/saml20-idp-remote.php.dist" "${DIR}/saml20-idp-remote.php"
  cp "${SAMLPATH}/metadata/saml20-idp-hosted.php.dist" "${DIR}/saml20-idp-hosted.php"
  cp "${SAMLPATH}/metadata/saml20-sp-remote.php.dist" "${DIR}/saml20-sp-remote.php"
fi;