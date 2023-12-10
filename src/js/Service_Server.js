/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
const acceptClient = '0.0.0.0/0'

// Command line parameters
const port = process.argv[2] ? process.argv[2] : 21000
const origin = process.argv[3] ? process.argv[3] : '*'
const keyPath = process.argv[4] ? process.argv[4] : ''
const certPath = process.argv[5] ? process.argv[5] : ''
const CAPath = process.argv[6] ? process.argv[6] : ''

// Loading modules
const fs = require('fs')
const crypto = require('crypto')
const parser = require('../../node_modules/inter-mediator-expressionparser/index')
const url = require('url')
const express = require('express')
const cors = require('cors')
let io = require("socket.io");
const app = express()
const http = require('http')
const https = require('https')

// Create Server
let svr = null
if (keyPath && certPath) {
  const options = {key: fs.readFileSync(keyPath), cert: fs.readFileSync(certPath)}
  if (CAPath) {
    options['ca'] = fs.readFileSync(CAPath)
  }
  svr = https.createServer(options, app)
} else {
  svr = http.createServer(app)
}
svr.listen(port)
/* Usually body parsing codes below is common pattern of express with json communications.
   I met the trouble with use(cors()) and body parsing codes, so both can't work together.
   I couldn't write codes for the native express way. 2021-3-8 by msyk.
 */
//const bodyParser = require('body-parser')
//app.use(bodyParser.urlencoded({extended: true}))
//app.use(bodyParser.json()) // This will be stop the CORS module's operation oops.
app.use(cors())

app.post('/info', function (req, res, next) {
  handler(req, res)
})
app.post('/eval', function (req, res, next) {
  handler(req, res)
})
app.post('/trigger', function (req, res, next) {
  handler(req, res)
})

const requestBroker = {}
const verCode = getVersionCode()
console.log(`Booted Service Server of INTER-Mediator: Version Code = ${verCode}`)

// For wss: setup, http://uorat.hatenablog.com/entry/2015/08/30/234757
let ioServer = io(svr, {
  cors: {
    origin: [origin],
    methods: ["GET", "POST"]
  },
  pingTimeout: 60000, // https://github.com/socketio/socket.io/issues/3259#issuecomment-448058937
})
/*
Socket processing
*/
ioServer.on('connection', (socket) => {
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

ioServer.on('init', (socket) => {
})

/*
   Server core
 */
function handler(req, res) {
  const reqParams = url.parse(req.url, true)
  const ipaddr = cleanUpIPAddress(req.socket.remoteAddress)
  if (!isIncludeIPAddress(ipaddr, acceptClient)) {
    console.log('client ip out of range: ' + ipaddr + '[' + new Date() + ']')
    res.end('ERROR')
    return
  }
  let postData = ''
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
    //console.log(postData)
    requestBroker[reqParams.pathname](reqParams.query, res, postData)
  } else {
    res.writeHead(403, {'Content-Type': 'text/html; charset=utf-8'})
    res.end('Not Found\n')
  }
}

requestBroker['/info'] = function (params, res, postData) {
  const data = JSON.parse(postData)

  if (ioServer === null) {
  }

  //res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
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

requestBroker['/trigger'] = function (params, res, postData) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
  let jsonData = JSON.parse(postData)
  for (const id in watching) {
    if (jsonData.clients.indexOf(id) > -1) {
      console.log(`Emit to: ${id}`)
      ioServer.to(watching[id].socketid).emit('notify', jsonData)
    }
  }
  let result = true

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

/* Signal handling */
process.once("SIGHUP", function () {
  //reloadSomeConfiguration();
  console.log("Try to RESTART with SIGHUP signal")
})

process.once('SIGUSR2', function () {
  console.log("Try to STOP with SIGUSR2 signal")
  // gracefulShutdown(function () {
  //   process.kill(process.pid, 'SIGUSR2');
  // });
});

/*
  Automatic processing
 */
//setInterval(function () {
// process.exit() // This doesn't work because the forever attempts to reboot this.
//}, 10000)

const watching = {}

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
  const ipArray = ipaddr.split('.')
  if ((ipaddr === '127.0.0.1') || (ipaddr === '::1')) {
    return true
  }
  if (ipArray.length !== 4) {
    return false
  }
  const rangeArray = range.split('/')
  if (rangeArray.length !== 2) {
    return false
  }
  const rangeIPArray = rangeArray[0].split('.')
  if (rangeIPArray.length !== 4) {
    return false
  }
  const ipNum = ((Number(ipArray[0]) * 256 +
    Number(ipArray[1])) * 256 +
    Number(ipArray[2])) * 256 +
    Number(ipArray[3])
  const rangeIpNum = ((Number(rangeIPArray[0]) * 256 +
    Number(rangeIPArray[1])) * 256 +
    Number(rangeIPArray[2])) * 256 +
    Number(rangeIPArray[3])
  const digit = Math.pow(2, 32 - rangeArray[1])
  const ipaddrNet = Math.floor(ipNum / digit) * digit
  if (ipaddrNet === rangeIpNum) {
    return true
  }
  return false
}
