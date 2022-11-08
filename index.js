require('dotenv').config();
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
  cnmiCommand:'AT+CNMI=2,1,0,2,1',

  logger: console
}


let phone = {
  name: "Sly Flores",
  number: "09179245040",
  numberSelf: "+639453749640",
  mode: "PDU"
}

const sendSMS = (gsmModem, number, message) => {

    gsmModem.sendSMS(number, message, false, (result) => {

      // console.log(`Callback Send: Message ID: ${result.data.messageId},` +
      //     `${result.data.response} To: ${result.data.recipient} ${JSON.stringify(result)}`);

      // (async () => {
      //   await logSmsSuccess.send({
      //     text: `Callback Send: Message ID: ${result.data.messageId},` +
      //     `${result.data.response} To: ${result.data.recipient} ${JSON.stringify(result)}`,
      //   });
      // })();

      if (result.data.response && result.data.response === 'Message Successfully Sent') {
        (async () => {
          await logSmsSuccess.send({
            text: `SMS sent`,
          });
        })();
        sendSMS(gsmModem, phone.number, 'Test SMS')
      }

    });

}

// Port is opened
gsmModem.on('open', () => {
  console.log(`Modem Sucessfully Opened`);

  // now we initialize the GSM Modem
  gsmModem.initializeModem((msg, err) => {
    if (err) {
      console.log(`Error Initializing Modem - ${err}`);
    } else {

      console.log(`InitModemResponse: ${JSON.stringify(msg)}`);

      console.log(`Configuring Modem for Mode: ${phone.mode}`);
      // set mode to PDU mode to handle SMS
      gsmModem.setModemMode((msg,err) => {
        if (err) {
          console.log(`Error Setting Modem Mode - ${err}`);
        } else {

          console.log(`Set Mode: ${JSON.stringify(msg)}`);

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

          sendSMS(gsmModem, phone.number, 'Test SMS')

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