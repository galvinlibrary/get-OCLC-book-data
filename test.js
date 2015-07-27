var fs = require('fs');
var util = require('util'); // to inspect objects


// execute callbacks in parallel

// executes the callbacks one after another
function series() {
    var callbackSeries = [async1, async2, 
                          async3, async4];
 
    function next() {
        var callback = callbackSeries.shift();
        if (callback) {
            callback(next);
        }
        else {
            finish();
        }
    }
    next();
};
 
// run the example
series();

 
// prints text and waits one second
function async1(callback) {
    console.log('async1');
    setTimeout(function() { callback(); }, 2000);
}
 
// prints text and waits half a second
function async2(callback) {
    console.log('async2');
    setTimeout(function() { callback(); }, 500);
}
 
// prints text and waits two seconds
function async3(callback) {
    console.log('async3');
    setTimeout(function() { callback(); }, 500);
}
 
// prints text and waits a second and a half
function async4(callback) {
    console.log('async4:');
    setTimeout(function() { callback(); }, 500);
}
 
// prints text
function finish() { console.log('Finished.'); }