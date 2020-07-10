/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
const fs = require('fs')
const crypto = require('crypto')

let port = process.argv[2] ? process.argv[2] : 21000
let acceptClient = '0.0.0.0/0'
const parser = require('../../node_modules/inter-mediator-expressionparser/index')
const jsSHA = require('../../node_modules/jssha/src/sha.js')

// const querystring = require('querystring')
// console.log(parser.evaluate('a+b',{a:3,b:4}))
let url = require('url')
let http = require('http')
let app = http.createServer(handler)
let io = require('socket.io')(app, {
  pingTimeout: 60000, // https://github.com/socketio/socket.io/issues/3259#issuecomment-448058937
})
let requestBroker = {}

let shaObj = new jsSHA('SHA-256', 'TEXT')
shaObj.setHMACKey('INTERMediatorOnPage.authChallenge', 'TEXT')
shaObj.update('INTERMediatorOnPage.authHashedPassword')
const serverKey = shaObj.getHMAC('HEX')

app.listen(port)

if (!app.listening) {
  process.exit(1)
}

let verCode = getVersionCode()
console.log(`Booted Service Server of INTER-Mediator: Version Code = ${verCode}`)

/*
   Server core
 */
function handler(req, res) {
  var reqParams = url.parse(req.url, true)
  var ipaddr = cleanUpIPAddress(req.socket.remoteAddress)
  if (!isIncludeIPAddress(ipaddr, acceptClient)) {
    console.log('client ip out of range: ' + ipaddr + '[' + new Date() + ']')
    res.end('ERROR')
    return
  }
  var postData = ''
  if (req.method == 'POST') {
    req.on('data', function (data) {
      postData += data
    })
    req.on('end', function () {
      requestProcessing(reqParams, res, postData)
    })
  } else if (req.method == 'GET') {
    requestProcessing(reqParams, res, postData)
  } else {
    res.writeHead(405, {'Content-Type': 'text/html; charset=utf-8'})
    res.end('Not Supporting Method\n')
  }
}

function requestProcessing(reqParams, res, postData) {
  if (reqParams.pathname in requestBroker) {
    console.log(postData)
    requestBroker[reqParams.pathname](reqParams.query, res, postData)
  } else {
    res.writeHead(403, {'Content-Type': 'text/html; charset=utf-8'})
    res.end('Not Found\n')
  }
}

requestBroker['/info'] = function (params, res, postData) {
  const data = JSON.parse(postData)
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
  if (data.vcode != verCode) {
    res.write('Different version of Server Server requested, and Service Server should be shutdown.')
  } else {
    res.write('Service Server is active.')
  }
  res.write(' Request Version:' + data.vcode)
  res.write(' Server Version:' + verCode)
  res.end('\n')
}

requestBroker['/eval'] = function (params, res, postData) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
  let jsonData = JSON.parse(postData)
  let rule = jsonData.expression
  let values = jsonData.values
  let result = parser.evaluate(rule, values)

  res.write(result ? 'true' : 'false')
  res.end('\n')
}

function getVersionCode() {
  let fc = fs.readFileSync('composer.json')
  const jsonObj = JSON.parse(fc)
  const hash = crypto.createHash('sha256')
  hash.update(jsonObj.time + jsonObj.version)
  return hash.digest('hex')
}

/*
  Automatic processing
 */
//setInterval(function () {
// process.exit() // This doesn't work becase the forever attempts to reboot this.
//}, 10000)

const watching = {}
/*
  Socket processing
 */
io.on('connection', (socket) => {
  console.log(socket.id + '/connected')
  socket.emit('connected')
  socket.on('init', function (req) {
    watching[req.clientid] = {startdt: new Date(), socketid: socket.id}
    console.log('watching=', watching)
  })
  socket.on('disconnect', function () {
    for (const oneClient of Object.keys(watching)) {
      if (watching[oneClient].socketid == socket.id) {
        delete watching[oneClient]
        break
      }
    }
    console.log('watching=', watching)
  })
})

io.on('init', (socket) => {
})

/*
  Network Utility
 */
function cleanUpIPAddress(str) {
  if (str.match(/::ffff:/)) {
    return str.substr(7)
  }
  return str
}

function isIncludeIPAddress(ipaddr, range) {
  var ipArray = ipaddr.split('.')
  if ((ipaddr === '127.0.0.1') || (ipaddr === '::1')) {
    return true
  }
  if (ipArray.length !== 4) {
    return false
  }
  var rangeArray = range.split('/')
  if (rangeArray.length !== 2) {
    return false
  }
  var rangeIPArray = rangeArray[0].split('.')
  if (rangeIPArray.length !== 4) {
    return false
  }
  var ipNum = ((Number(ipArray[0]) * 256 +
    Number(ipArray[1])) * 256 +
    Number(ipArray[2])) * 256 +
    Number(ipArray[3])
  var rangeIpNum = ((Number(rangeIPArray[0]) * 256 +
    Number(rangeIPArray[1])) * 256 +
    Number(rangeIPArray[2])) * 256 +
    Number(rangeIPArray[3])
  var digit = Math.pow(2, 32 - rangeArray[1])
  var ipaddrNet = Math.floor(ipNum / digit) * digit
  if (ipaddrNet === rangeIpNum) {
    return true
  }
  return false
}
