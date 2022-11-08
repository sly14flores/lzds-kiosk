var express = require('express');
var app = express();
var bodyParser = require('body-parser');

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

var modem = require('modem').Modem();
modem.open('/dev/ttyUSB0', function() {});
// modem.open('COM26', function() {});

var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'sly',
  database : 'monitoring'
});
connection.connect();

app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.post('/send', function(req, httpres) {
	
	var aid = req.body['id'];
	// var cp = '09179245040';
	var cp = req.body['cp'];
	var rfid = req.body['rfid'];
	var fullname = req.body['fullname'];
	var log_date = req.body['log_date'];
	var logQ_date = req.body['logQ_date'];
	var log_time = req.body['log_time'];

	var studentIn = 'FROM: Lord of Zion Divine School. Good day. Dear parent/guardian, Your child '+fullname+' has entered the school premises on '+log_date+' at '+log_time+'. Wishing you a great day ahead. Thank you.';
	var studentOut = 'FROM: Lord of Zion Divine School. Great day. Dear parent/guardian, Your child '+fullname+' has left the campus on '+log_date+' at '+log_time+'. Enjoy the rest of the day. God bless.';	

	connection.query('SELECT id, @c:=@c+1 AS i FROM attendances, (SELECT @c:=0) c WHERE SUBSTRING(time_log,1,10) = ? AND rfid = ?', [logQ_date,rfid], function(err,res) {
		
		res.forEach(function(item,index){
			
			if (item['id'] == aid) {
			
				/* if (item['i'] == 1) msg = studentIn;
				if (item['i'] == 2) msg = studentOut;
				if (item['i'] == 3) msg = studentIn;
				if (item['i'] == 4) msg = studentOut; */

				if (item['i']%2 == 1) msg = studentIn;
				if (item['i']%2 == 0) msg = studentOut;
				
				// send sms
				// console.log('Sending SMS to '+aid);	

				  modem.sms({
					receiver:cp,
					text:msg,
					encoding:'16bit'
				  }, function(err, sent_ids) {
					// console.log('>>', arguments);			
					if(err) {
						
						// console.log('Message not sent.\t');
						// httpres.send('Message not sent');
						httpres.send(err);
					
					} else {

						connection.query('UPDATE attendances SET sms = ? WHERE id = ?', ['sent',aid], function(err,res) {
							if(err) throw err;
						});					
						httpres.send('SMS sent to '+aid);

					}
				  });

				//
	
			}
			
		});
	
	});
	
	// httpres.send(req.body);

});

var server = app.listen(8080, function () {

  var host = server.address().address;
  var port = server.address().port;

  console.log("SMS gateway listening at http://%s:%s", host, port)

});


/* modem.open('/dev/ttyUSB0', function() {
	
  modem.getMessages(function() {
	// console.log(arguments);
	if (arguments.length > 0) {
		
		for (i=0; i < arguments[0].length; i++) {
			modem.deleteMessage(arguments[0][i]['indexes'][0], function() {});		
		}
		
	}	
	// process.exit();
  });

  modem.on('sms received', function(sms) {
	console.log(sms);
  });	
	
}); */