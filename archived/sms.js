var util = require('util');

var modem = require('modem').Modem();

var mysql = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'sly',
  database : 'monitoring'
});
connection.connect();

var _rfid = '';
var decChars = {
	"nbsp": "0000000000000000",
	"00001e0000000000": "1",
	"00001f0000000000": "2",
	"0000200000000000": "3",	
	"0000210000000000": "4",
	"0000220000000000": "5",
	"0000230000000000": "6",	
	"0000240000000000": "7",
	"0000250000000000": "8",
	"0000260000000000": "9",
	"0000270000000000": "0",
	"retChar": "0000280000000000"
};

modem.open('/dev/ttyUSB0', function() {
	
  modem.getMessages(function() {
	// console.log(arguments);
	if (arguments.length > 0) {
		
		for (i=0; i < arguments[0].length; i++) { // delete messages
			modem.deleteMessage(arguments[0][i]['indexes'][0], function() {});		
		}
		
	}	
	// process.exit();
  });

  modem.on('sms received', function(sms) {
	console.log(sms);
  });
  
var rfs = require('fs').createReadStream("/dev/hidraw2", {bufferSize: 1, encoding: 'hex'});

rfs.on('open', function(fd){
  console.log("RFID Scanner initialized...");
});

rfs.on('end', function(fd){
  fd.close();
  connection.end();
});

rfs.on('data', function(chunk){
	
	if (chunk.toString() == decChars['nbsp']) return true;
	
	if (chunk.toString() == decChars['retChar']) {
		
		var rfidCache = _rfid;
		console.log('RFID Scanner triggered for ID: '+rfidCache);
	
		var d = new Date();
		var jd = d.toJSON();
		var tday = jd.substring(0,10);
		
		var pt = 'Student';
		// var pt = 'Guest';

		setTimeout(function() {
		
			connection.query("SELECT attendances.id id, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, profiles.cp cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = ? AND SUBSTRING(attendances.time_log,1,10) = ? AND attendances.rfid = ? AND attendances.sms = ? ORDER BY attendances.id DESC LIMIT 1", [pt,tday,rfidCache,'queue'], function(err,res) {
				if(err) throw err;
				if (res.length) {
					// console.log(res);
					
					var aid = res[0]['id'];
					var cp = res[0]['cp'];
					var studentIn = 'FROM: Lord of Zion Divine School. Good day. Dear parent/guardian, Your child '+res[0]['fullname']+' has entered the school premises on '+res[0]['log_date']+' at '+res[0]['log_time']+'. Wishing you a great day ahead. Thank you.';
					var studentOut = 'FROM: Lord of Zion Divine School. Great day. Dear parent/guardian, Your child '+res[0]['fullname']+' has left the campus on '+res[0]['log_date']+' at '+res[0]['log_time']+'. Enjoy the rest of the day. God bless.';
					
					connection.query('SELECT * FROM attendances WHERE SUBSTRING(time_log,1,10) = ? AND rfid = ?', [tday,rfidCache], function(err,res) {
					
						console.log(res.length + ' time(s)');
						var msg = studentIn;
						if (res.length == 2) msg = studentOut;
						if (res.length == 3) msg = studentIn;
						if (res.length == 4) msg = studentOut;
						
						//
						if (res.length <= 4) {
						
						  console.log('Sending SMS to '+cp);
						  console.log(msg);
							
						  modem.sms({
							receiver:cp,
							text:msg,
							encoding:'16bit'
						  }, function(err, sent_ids) {
							// console.log('>>', arguments);			
							if(err) {
								
								console.log('Message not sent.\t');
								console.log(err);
								
							} else {
									
								console.log('SMS sent to '+rfidCache);
								connection.query('UPDATE attendances SET sms = ? WHERE id = ?', ['sent',aid], function(err,res) {
									if(err) throw err;
								});			

							}
						  });
						  
						}
						//
					
					});
					
				}
			});
			
		}, 1000);
	
		_rfid = '';	
		
	} else {
		_rfid += decChars[chunk.toString()];
	}	

});
	
});