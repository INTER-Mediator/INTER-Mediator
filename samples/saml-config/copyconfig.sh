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
  cp "${DIR}/acl.php" "${SAMLPATH}/config"
  cp "${DIR}/authsources.php" "${SAMLPATH}/config"
  cp "${DIR}/config.php" "${SAMLPATH}/config"
  cp "${DIR}/saml20-idp-remote.php" "${SAMLPATH}/metadata"
elif [ ${SAMLLIBVER} = 2 ]; then
  cp "${DIR}/acl.php" "${SAMLPATH}/config"
  cp "${DIR}/authsources.php" "${SAMLPATH}/config"
  cp "${DIR}/config.php" "${SAMLPATH}/config"
  cp "${DIR}/saml20-idp-hosted.php" "${SAMLPATH}/metadata"
  cp "${DIR}/saml20-idp-remote.php" "${SAMLPATH}/metadata"
  cp "${DIR}/saml20-sp-remote.php" "${SAMLPATH}/metadata"
  if [ -e "${DIR}/sp.crt" ]; then
    cp "${DIR}/sp.crt" "${SAMLPATH}/cert"
  fi
  if [ -e "${DIR}/sp.pem" ]; then
    cp "${DIR}/sp.pem" "${SAMLPATH}/cert"
  fi
fi;
