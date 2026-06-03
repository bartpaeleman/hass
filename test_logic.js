const fs = require('fs');
const html = fs.readFileSync('energy_dashboard.html', 'utf8');

const jsCodeMatch = html.match(/<script>([\s\S]*?)<\/script>/);
let flowLogic;
if(jsCodeMatch) {
    let jsCode = jsCodeMatch[1];

    // remove window/document stuff to test locally
    jsCode = jsCode.replace(/document\.getElementById/g, '(() => null)');
    jsCode = jsCode.replace(/pathEl\.classList/g, '({})');
    jsCode = jsCode.replace(/const flowLogic/g, 'flowLogic');

    eval(jsCode);

    const testData1 = {
        'sensor.zonneenergie_productie_nu': '500',
        'sensor.electriciteit_netverbruik_nu': '0',
        'sensor.batterij_status': 'Ontladen'
    };
    console.log("Test Solar to Home (Should be true):", flowLogic.solarToHome.isActive(testData1));

    const testData2 = {
        'sensor.zonneenergie_productie_nu': '500',
        'sensor.electriciteit_netverbruik_nu': '10',
        'sensor.batterij_status': 'Ontladen'
    };
    console.log("Test Solar to Home with net import (Should be false):", flowLogic.solarToHome.isActive(testData2));
}
