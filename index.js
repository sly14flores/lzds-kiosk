require('dotenv').config();
const moment = require('moment');

const DB_PASSWORD = process.env.DB_PASSWORD

const mysql = require('mysql');

const con = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : DB_PASSWORD,
  database : 'monitoring'
});

const updateSent = (con,aid) => {

  con.query('UPDATE attendances SET sms = ? WHERE id = ?', ['sent',aid], function(err,result) {
    if(err) throw err;
  });	

}

const init = (con,gsmModem) => {

  console.log('Initialising sending of sms')

  const now = moment().format("YYYY-MM-DD")
  let i = 0

  const smsNotify = (results,index,size) => {

    const item = results[index]

    /**
     * {
     * 	"id":500387,
     * 	"rfid":"0004528331",
     * 	"fullname":"Jenacia G. Sobremonte",
     * 	"time_log":"2022-10-28 05:10:03",
     *  "cp":"9100098008",
     *  "log_date":"Fri, Oct 28, 2022",
     *  "log_time":"05:10 AM",
     *  "logQ_date":"2022-10-28"
     * }
     */
    const aid = item.id
    // const cp = item.cp
    const cp = '09179245040'
    // const cp = '09172445929'
    const rfid = item.rfid
    const fullname = item.fullname
    const log_date = item.log_date
    const logQ_date = item.logQ_date
    const log_time = item.log_time

    const studentIn = `FROM: Lord of Zion Divine School. Good day. Dear parent/guardian, Your child ${fullname} has entered the school premises on ${log_date} at ${log_time} Wishing you a great day ahead. Thank you.`
    const studentOut = `FROM: Lord of Zion Divine School. Great day. Dear parent/guardian, Your child ${fullname} has left the campus on ${log_date} at ${log_time} Enjoy the rest of the day. God bless.`

    const queryStudent = 'SELECT id, @c:=@c+1 AS i FROM attendances, (SELECT @c:=0) c WHERE SUBSTRING(time_log,1,10) = ? AND rfid = ? AND id = ?'

    con.query(queryStudent, [logQ_date,rfid,aid], (err,result) => {

      if (err) throw err;

      if (result.length>0) {
        const key = result[0].i

        const msg = (key%2 == 1)?studentIn:studentOut

        /**
         * Send SMS
         */
        gsmModem.sendSMS(cp, `Lorem, Ipsum`, false, (result) => {

          (async () => {
            await logSmsSuccess.send({
              // text: msg,
              text: JSON.stringify(result),
            });
          })();

          if (result.data.response && result.data.response === 'Message Successfully Sent') {
            (async () => {
              await logSmsSuccess.send({
                // text: msg,
                text: JSON.stringify(result),
              });
            })();
            updateSent(con,aid)
            ++i
            if (i<size) {
              smsNotify(results,i,size)
            }
          }
    
        });

      }

    })

  }

  con.connect((err) => {
    if (err) throw err;
    console.log("Connected to database!");

    const queryLogs = `SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '${now}' AND attendances.id = 500066` // AND sms = 'queue'`

    con.query(queryLogs, (err, results) => {

      if (err) throw err;

      const size = results.length

      if (size>0) {
        smsNotify(results,i,size)
      }

    });
  })

}

// init(con)

const serialportgsm = require('serialport-gsm');
const { IncomingWebhook } = require('@slack/webhook');

const device = process.env.DEVICE_PATH
const deviceSignal = process.env.DEVICE_SIGNAL_URL
const smsLogs = process.env.DEVICE_SMS_LOGS
const smsSuccess = process.env.DEVICE_SMS_SUCCESS
const smsErrors = process.env.DEVICE_SMS_ERRORS

const logSignal = new IncomingWebhook(deviceSignal);
const logDevice = new IncomingWebhook(smsLogs);
const logSmsSuccess = new IncomingWebhook(smsSuccess);
const logSmsErrors = new IncomingWebhook(smsErrors);

var gsmModem = serialportgsm.Modem()
let options = {
  baudRate: 19200,
  dataBits: 8,
  parity: 'none',
  stopBits: 1,
  highWaterMark: 65536,
  xon: false,
  rtscts: false,
  xoff: false,
  xany: false,
  autoDeleteOnReceive: true,
  enableConcatenation: true,
  incomingCallIndication: true,
  incomingSMSIndication: true,
  pin: '',
  customInitCommand: 'AT^CURC=0',
  // cnmiCommand:'AT+CNMI=2,1,0,2,1',
  cnmiCommand:'AT+CNMI=2,1,0,2,1',

  logger: console
}


let phone = {
  name: "Sly Flores",
  number: "09179245040",
  numberSelf: "+639453749640",
  mode: "PDU"
}

// Port is opened
gsmModem.on('open', () => {

  (async () => {
    await logDevice.send({
      text: `Modem Sucessfully Opened`,
    });
  })();

  // now we initialize the GSM Modem
  gsmModem.initializeModem((msg, err) => {
    if (err) {
      console.log(`Error Initializing Modem - ${err}`);
    } else {

      (async () => {
        await logDevice.send({
          text: `InitModemResponse: ${JSON.stringify(msg)}`,
        });
      })();

      (async () => {
        await logDevice.send({
          text: `Configuring Modem for Mode: ${phone.mode}`,
        });
      })();

      // set mode to PDU mode to handle SMS
      gsmModem.setModemMode((msg,err) => {
        if (err) {
          console.log(`Error Setting Modem Mode - ${err}`);
        } else {

          (async () => {
            await logDevice.send({
              text: `Set Mode: ${JSON.stringify(msg)}`,
            });
          })();

          // get the Network signal strength
          gsmModem.getNetworkSignal((result, err) => {
            if (err) {
              (async () => {
                await logSignal.send({
                  text: `Error retrieving Signal Strength - ${err}`,
                });
              })();
            } else {
              (async () => {
                await logSignal.send({
                  text: `Signal Strength: ${JSON.stringify(result)}`,
                });
              })();
            }
          });

          (async () => {
            await logDevice.send({
              text: 'Ready to send SMS...',
            });
          })();

          gsmModem.deleteAllSimMessages((result, err) => {})

          /**
           * Init sending messages
           */
          init(con,gsmModem)

        }
      }, phone.mode);

    }
  });

  gsmModem.on('onNewMessageIndicator', data => {
    //indicator for new message only (sender, timeSent)
    // (async () => {
    //   await logDevice.send({
    //     text: `Event New Message Indication: ` + JSON.stringify(data),
    //   });
    // })();
  });

  gsmModem.on('onNewMessage', data => {
    //whole message data
    (async () => {
      await logDevice.send({
        text: `Event New Message: ` + JSON.stringify(data),
      });
    })();
  });

  gsmModem.on('onSendingMessage', data => {
    //whole message data
    (async () => {
      await logDevice.send({
        text: `Event Sending Message: ` + JSON.stringify(data),
      });
    })();
  });

  gsmModem.on('onNewIncomingCall', data => {
    //whole message data
    (async () => {
      await logDevice.send({
        text: `Event Incoming Call: ` + JSON.stringify(data),
      });
    })();
  });

  gsmModem.on('onMemoryFull', data => {
    //whole message data
    (async () => {
      await logDevice.send({
        text: `Event Memory Full: ` + JSON.stringify(data),
      });
    })();
  });

  gsmModem.on('close', data => {
    //whole message data
    (async () => {
      await logDevice.send({
        text: `Event Close: ` + JSON.stringify(data),
      });
    })();
  });

  gsmModem.on('error', result => {
    (async () => {
      await logDevice.send({
        text: `Event Close: ${result}`,
      });
    })();
  });

});

// gsmModem.open('/dev/ttyUSB0', options);
gsmModem.open(device, options);

// setTimeout(() => {
//   gsmModem.close(() => process.exit);
// }, 90000);