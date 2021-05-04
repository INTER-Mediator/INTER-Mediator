#!/bin/sh

DIR="$( cd "$( dirname "$0" )" && pwd )"
IMPATH="${DIR}/../src/INTER-Mediator"
SAMLPATH="${IMPATH}/vendor/simplesamlphp/simplesamlphp"

cp -r "${SAMLPATH}/config-templates/"* "${DIR}"
cp -r "${SAMLPATH}/metadata-templates/"* "${DIR}"
