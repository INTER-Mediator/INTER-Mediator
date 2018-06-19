/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
let port = process.argv[2] ? process.argv[2] : 21000;
let acceptClient = "0.0.0.0/0";
let acceptSend = "0.0.0.0/0";

let parser = require('../lib/js_lib/js-expression-eval-parser');
//console.log(parser.evaluate('a+b',{a:3,b:4}));
let url = require('url');
let http = require('http');
let app = http.createServer(handler);
let io = require('socket.io')(app);
let requestBroker = {};

app.listen(port);

requestBroker['/info'] = function (params, res) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
  res.write('Service Server is active.');
  res.end('\n');
};

requestBroker['/eval'] = function (params, res) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
  res.write('Service Server is active.');
  res.end('\n');
};

function handler(req, res) {
  var reqParams = url.parse(req.url, true);
  var ipaddr = cleanUpIPAddress(req.socket.remoteAddress);
  if (!isIncludeIPAddress(ipaddr, acceptClient)) {
    console.log('client ip out of range: ' + ipaddr + '[' + new Date() + ']');
    res.end('ERROR');
    return;
  }

  if (reqParams.pathname in requestBroker) {
    requestBroker[reqParams.pathname](reqParams.query, res);
  } else {
    res.writeHead(403, {'Content-Type': 'text/html; charset=utf-8'});
    res.end('Not Found\n');
  }
}

setInterval(function () {

}, 3000);


io.on('connection', function (socket) {
  console.log(socket.id + '/connected');
  socket.emit('connected');
  socket.on("init", function (req) {
    socket.join(req.room);
    socket.join(socket.id);
    console.log(req.room + "/" + socket.id + "/init");
  });
  socket.on('disconnect', function () {
    console.log(socket.id + "/disconnect");
  });
});

requestBroker["/"] = function (params, res) {
  var db = default_dbName, pbxLines = '[""]';
  if (params.d) {
    db = decodeURIComponent(params.d);
  }
  if (params.l) {
    pbxLines = decodeURIComponent(params.l).replace(/[^0-9=]/g, "");
    if (pbxLines.length == 0) {
      pbxLines = '[""]';
    } else {
      pbxLines = "['" + pbxLines.split("=").join("','") + "']";
    }
  }
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
  var bodyStyle = 'margin:0;padding:0;font-size:10pt;text-align:center;border:none;'
    + 'background-color:#EEEEEE;color:#286D86;';
  res.write('<!doctype html><html><head>'
    + '<script src="/socket.io/socket.io.js"></script>'
    + '<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>'
    + '<script>\n'
    + 'var pbxLines = ' + pbxLines + ';\n'
    + 'var socket = [], index = 0;\n'
    + 'for(index = 0; index < pbxLines.length; index++){\n'
    + ' var ix = index;var pt = pbxLines[index];\n'
    + ' socket[ix] = io.connect();\n'
    + ' socket[ix].on("connected", (function(){\n'
    + '  var i = ix; var p = pt; return function(){\n'
    + '   socket[i].emit("init", {"room": "/" + p});\n'
    + ' }})());\n'
    + ' socket[ix].on("call", function(msg){\n'
    + '  var dbName = "' + db + '";\n'
    + '  if(navigator.appVersion.indexOf("Mac")!=-1){dbName = encodeURIComponent(dbName);}\n'
    + '  var tel = encodeURIComponent(msg.tel);\n'
    + '  var serial = encodeURIComponent(msg.serial);\n'
    + '  var line = encodeURIComponent(msg.line);\n'
    + '  document.ifr.location = "fmp://$/" + dbName + "?script=' + scriptName
    + '&$telNum="+tel+"&$serial="+serial+"&$line="+line+"&$delay="+msg.delay;\n'
    + ' });\n'
    + '}\n'
    + 'window.onunload = function(){'
    + 'for(var i=0; i<index;i++){socket[i].disconnect();}'
    + '};\n'
    + 'window.onbeforeunload = function(e) {'
    + 'for(var i=0; i<index;i++){socket[i].disconnect();}return void(0);'
    + '};\n'
    + '</script><style>BODY{' + bodyStyle + '}</style></head>'
    + '<body>電話待ち</body><iframe name="ifr" style="display:none"></iframe></html>'
  )
  ;
  res.end('\n');
};

requestBroker["/token"] = function (params, res) {
  if (!params.m) {
    res.write("ERROR");
    res.end('\n');
    console.log("ERROR: The m parameter doesn't exist." + '[' + new Date() + ']');
    return;
  }
  var param = params.m;
  var exec = require('child_process').exec;
  var result = "ERROR";
  var child = exec(encryptCommand(param), function (err, stdout, stderr) {
    res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
    if (!err) {
      result = stdout.replace(/[^0-9A-Fa-f]/g, "");
    } else {
      console.log(err);
      console.log(stderr);
    }
    res.write(result);
    res.end('\n');
    console.log("/token returns data. " + '[' + new Date() + ']');
  });
};

requestBroker["/test"] = function (params, res) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
  res.write("clientCounter=" + clientCounter + "<br/>");
  res.end('\n');
};

function encryptCommand(param) {
  //console.log("echo '" + param + "'|openssl rsautl -encrypt -pubin -inkey public.pem|xxd -ps");
  return "echo '" + param + "'|openssl rsautl -encrypt -pubin -inkey public.pem|xxd -ps";
}
/*
 Private Key Generating:
 openssl genrsa -out private.pem 4096
 Generate Public Key from Private Key:
 openssl rsa -pubout -in private.pem -out public.pem
 Decript:
 echo -n "-hex-"|perl -pe 's/([0-9a-f]{2})/chr hex $1/gie'|openssl rsautl -decrypt -inkey private.pem
 echo -n "-hex-"|xxd -r -p|openssl rsautl -decrypt -inkey private.pem
 */

function cleanUpIPAddress(str) {
  if (str.match(/::ffff:/)) {
    return str.substr(7);
  }
  return str;
}

function isIncludeIPAddress(ipaddr, range) {
  var ipArray = ipaddr.split(".");
  if ((ipaddr == "127.0.0.1") || (ipaddr == "::1")) {
    return true;
  }
  if (ipArray.length != 4) {
    return false;
  }
  var rangeArray = range.split("/");
  if (rangeArray.length != 2) {
    return false;
  }
  var rangeIPArray = rangeArray[0].split(".");
  if (rangeIPArray.length != 4) {
    return false;
  }
  var ipNum = ((Number(ipArray[0]) * 256
    + Number(ipArray[1])) * 256
    + Number(ipArray[2])) * 256
    + Number(ipArray[3]);
  var rangeIpNum = ((Number(rangeIPArray[0]) * 256
    + Number(rangeIPArray[1])) * 256
    + Number(rangeIPArray[2])) * 256
    + Number(rangeIPArray[3]);
  var digit = Math.pow(2, 32 - rangeArray[1]);
  var ipaddrNet = Math.floor(ipNum / digit) * digit;
  if (ipaddrNet == rangeIpNum) {
    return true;
  }
  return false;
}
