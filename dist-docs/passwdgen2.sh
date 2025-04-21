#!/usr/bin/env node

const jsSHA = require('../node_modules/jssha/dist/sha.js')
const INTERMediatorLib = require('../src/js/INTER-Mediator-Lib.js')

if(process.argv.length < 2) {
  console.log("The first parameter has to be the password.")
  process.exit(1)
}
const password = process.argv[2]

let salt = '', saltHex = '', msg = ''
if(!process.argv[3]) {
  [salt, saltHex] = INTERMediatorLib.generateSalt()
  msg ='-- random salt generated'
} else {
  salt = process.argv[3]
}

const hash1 = INTERMediatorLib.generatePasswrdHashV1(password,salt)
const hash2m = INTERMediatorLib.generatePasswrdHashV2m(password,salt)
const hash2 = INTERMediatorLib.generatePasswrdHashV2(password,salt)

console.log(`Input Values: password = ${password} , salt = ${salt} (${saltHex}) ${msg}`)
console.log("Version 1 Hash Value = " + hash1)
console.log("Version 2m Hash Value = " + hash2m)
console.log("Version 2 Hash Value = " + hash2)
