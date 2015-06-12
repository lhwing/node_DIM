var pg = require('pg');
var http = require('http');
var io = require('socket.io');
var url = require('url');
var fs = require('fs');
var es = require('./escapeString');
var cookies = require('./cookies');
var qs = require('querystring');

function handlePost(req,res,callback){
  var body = '';
  req.on('data', function(data){ body += data;});
  req.on('end', function() {
    callback(qs.parse(body));
  });
}



//request to the port...
var server = http.createServer(function(req,res){
  //req.headers.cookie && console.log(req.headers.cookie);  //read the cookie
  var ck = cookies(req.headers.cookie);
  //as a router to serve different location
  var path = url.parse(req.url).pathname;
  switch (path){
    case '/':


      if (req.method == 'POST'){
        handlePost(req,res,function(data) {
          console.log(data);
          if (data.name)
            res.writeHead(200, {
              'Content-Type':'text/html',
              'Set-Cookie':['name='+data.name+'; max-age=2000;']});
          else
            res.writeHead(200, {
              'Content-Type':'text/html',
              'Set-Cookie':['post=letTest; max-age=2000;']});
          res.write("data");
          res.end();
        });
      }
      if (req.method == 'GET'){
        var get = url.parse(req.url,true).query;
        res.writeHead(200, {
          'Content-Type':'text/html',
          'Set-Cookie':'SSID=Ap4GTEq; max-age=2000;HttpOnly '});
        //HttpOnly:only here can see, socket cannot see
        res.write('Hello, World!\
                  <input><input><button type="button">POST</button>\
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>\
        <script>$(document).ready(function(){$("button").click(function(){\
        var data={};data[$("input:eq(0)").val()]=$("input:eq(1)").val();\
        $.ajax({url: "/",type: "POST",async: true,\
        data: data,\
        dataType: "html"});});});\
        </script>');
        res.end();
      }
      break;
    case '/socket.html':
          fs.readFile(__dirname + path, function(error, data) {
            if (error){
              res.writeHead(404);
              res.write("opps this doesn't exist - 404");
            } else {
              res.writeHead(200, {"Content-Type": "text/html"});
              res.write(data, "utf8");
            }
            res.end();
          });
          break;
    default:
      fs.readFile(__dirname+ '/public' + path, function(error, data) {
        if (error){
          res.writeHead(404);
          res.write("opps this doesn't exist - 404");
        } else {
          res.writeHead(200);
          res.write(data);
        }
        res.end();
      });
      break;
  }
});

server.listen(3000);

var conString = "postgres://postgres:123@localhost/test_news";
var client = new pg.Client(conString);
client.connect();
function pqsql(sql, callback) {
  client.query(sql, function(err,result){
  if (err){
    return console.error("fail sql",err);
  }
    if (callback)
      return callback(null,result);
  });
}
// client.query("INSERT INTO user(name) VALUES($1)", ["'; DROP TABLE user;"], function (err, result) {
//   // ...
// });
var serv_io = io.listen(server);
var max_id = 0;
var history_msg = [];
var history_draw = [];
serv_io.sockets.on('connection', function(socket) {
    //init. the chatroom for new client...
    pqsql("SELECT * FROM talks " +
      "where date >= NOW() - '2 hour'::INTERVAL AND  id >"
      + max_id, function(err,result){
    if (err) return console.error("fail",err);

    if (result.rowCount){
      var new_id = result.rows[result.rowCount-1].id;
      if (max_id == new_id) //multiple access at the same time... wait for other page finish plz
        return console.log("too fast");
      max_id = result.rows[result.rowCount-1].id;
      Array.prototype.push.apply(history_msg,result.rows); //is it blocking?
    }
    socket.emit('message',{message:history_msg});
    history_draw.forEach(function (e){socket.emit('gdraw',e);});

    socket.emit('setName',cookies(socket.handshake.headers.cookie).name);

    });

});
setInterval(function() {
  serv_io.sockets.emit('clear_draw');
  history_draw = [];
}, 1000*10);
setInterval(function() {
  history_msg = []; //clear the pervious one evey hour
  max_id = 0;
  pqsql("SELECT * FROM talks " +
    "where date >= NOW() - '1 hour'::INTERVAL AND  id >"
    + max_id, function(err,result){
  if (err) return console.error("fail",err);

  if (result.rowCount){
    var new_id = result.rows[result.rowCount-1].id;
    if (max_id == new_id) //multiple access at the same time... wait for other page finish plz
      return console.log("too fast");
    max_id = result.rows[result.rowCount-1].id;
    Array.prototype.push.apply(history_msg,result.rows); //is it blocking?
  }
  serv_io.sockets.emit('message',{message:history_msg});});
}, 1000*60*60); //1 hours...
serv_io.sockets.on('connection', function(socket) {
  setInterval(function() {
    serv_io.sockets.emit('date', {'date': new Date()});
    socket.handshake.headers.cookie = "test=123";
  }, 1000);


  socket.on('send',function(data){
    console.log('get');
    var date = es(data.date);
    var name = es(data.name);
    var text = es(data.text);
    //console.log(socket.handshake.headers.cookie);
    console.log(cookies(socket.handshake.headers.cookie));
    var sql = "INSERT INTO talks(date,name,text) VALUES('"+
                date+"','"+ name +"','"+ text +"')";
    pqsql(sql);

    serv_io.sockets.emit('receive',data); //broadcast to all node
  });
  socket.on('draw',function(data){
    history_draw.push(data);
    serv_io.sockets.emit('gdraw',data);
  });
});
