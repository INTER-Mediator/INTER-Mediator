#!/bin/sh

DIR="$( cd "$( dirname "$0" )" && pwd )"
#IMPATH="${DIR}/../INTER-Mediator"
IMPATH="${DIR}/../src/INTER-Mediator"
SAMLPATH="${IMPATH}/vendor/simplesamlphp/simplesamlphp"

cp "${DIR}/acl.php" "${SAMLPATH}/config"
cp "${DIR}/authsources.php" "${SAMLPATH}/config"
cp "${DIR}/config.php" "${SAMLPATH}/config"
cp "${DIR}/saml20-idp-remote.php" "${SAMLPATH}/metadata"
