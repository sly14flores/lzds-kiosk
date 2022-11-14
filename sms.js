require('dotenv').config()
const device = process.env.DEVICE_PATH

const modem = require('modem').Modem()
modem.open(device, () => {

    console.log('Connected')

})