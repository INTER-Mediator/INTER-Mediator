/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
let port = process.argv[2] ? process.argv[2] : 21000
let acceptClient = '0.0.0.0/0'

let parser = require('../../node_modules/inter-mediator-expressionparser/index')
// const querystring = require('querystring')
// console.log(parser.evaluate('a+b',{a:3,b:4}))
let url = require('url')
let http = require('http')
let app = http.createServer(handler)
let io = require('socket.io')(app)
let requestBroker = {}

app.listen(port)

if (!app.listening) {
  process.exit(1)
}

/*
   Server core
 */
function handler (req, res) {
  var reqParams = url.parse(req.url, true)
  var ipaddr = cleanUpIPAddress(req.socket.remoteAddress)
  if (!isIncludeIPAddress(ipaddr, acceptClient)) {
    console.log('client ip out of range: ' + ipaddr + '[' + new Date() + ']')
    res.end('ERROR')
    return
  }
  var postData = '';
  if (req.method == 'POST') {
    req.on('data', function (data) {
      postData += data;
    });
    req.on('end', function () {
      requestProcessing(reqParams, res, postData)
    });
  } else if (req.method == 'GET'){
    requestProcessing(reqParams, res, postData)
  } else {
    res.writeHead(405, {'Content-Type': 'text/html; charset=utf-8'})
    res.end('Not Supporting Method\n')
  }
}

function requestProcessing(reqParams, res, postData) {
  if (reqParams.pathname in requestBroker) {
    requestBroker[reqParams.pathname](reqParams.query, res, postData)
  } else {
    res.writeHead(403, {'Content-Type': 'text/html; charset=utf-8'})
    res.end('Not Found\n')
  }
}

requestBroker['/info'] = function (params, res, postData) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
  res.write('Service Server is active.')
  res.end('\n')
}

requestBroker['/eval'] = function (params, res, postData) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'})
  let jsonData = JSON.parse(postData)
  let rule = jsonData.expression;
  let values = jsonData.values;
  let result = parser.evaluate(rule, values)

  res.write(result ? 'true' : 'false')
  res.end('\n')
}

/*
  Automatic processing
 */
setInterval(function () {

}, 3000)

/*
  Socket processing
 */
io.on('connection', function (socket) {
  console.log(socket.id + '/connected')
  socket.emit('connected')
  socket.on('init', function (req) {
    socket.join(req.room)
    socket.join(socket.id)
    console.log(req.room + '/' + socket.id + '/init')
  })
  socket.on('disconnect', function () {
    console.log(socket.id + '/disconnect')
  })
})

/*
  Network Utility
 */
function cleanUpIPAddress (str) {
  if (str.match(/::ffff:/)) {
    return str.substr(7)
  }
  return str
}

function isIncludeIPAddress (ipaddr, range) {
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
