//  Description:
//  Process a text file of ISBN and retrieve bibliographic information from the OCLC WorldCat Search API,
//  which only returns results into XML. Convert into JSON and create a simplified JSON object of
//  ISBN, title, author, summary, and an array of subjects.

// Modules
var fs = require('fs'),
  xml2js = require('xml2js');
var util = require('util'); // to inspect objects
var request = require('request');
var parser = {};
var moment = require('moment');// for date formatting
  moment().format();

// Variables
var key = process.env.OCLC_DEV_KEY;// store dev key in env variable for security
var textbook = {};
var debug = false;
var debug2 = false; // for when working on a single function
var path = './';
var isbnFile = 'isbns-sample.txt';
var dataFile = 'textbooks-info.txt';
var logFile = moment().format("YYYY-MM-DD")+'.log';
var isbnsToProcess=[]; // used for lfow control
var isbn=''; // used between request and listener
var url= ''; // used between request and listener
var obj, prop; //used between data retrieval from OCLC json object
var summaryMsg =''; // used between processISBNfile 
var countLoop=0; // used to flag end of isbn array
var alertMsg='';
var parserPrefix='oclc';


// controlled order of processing
// From http://blog.4psa.com/the-callback-syndrome-in-node-js/
function series() {
    var callbackSeries =  
      [
        init, //  Create a log file of YYYY-MM-DD format. Delete any file if it exists
        processISBNFile,
          // Open ISBN file
          //  For each line of the file:
          //    - Split line into an array by spaces
          //    - Trim leading/trailing spaces from array[0]
          //    - Verify array[0] is a valid ISBN. If not valid, write to log file and go to next line.
          //    - Check to see if this ISBN is in the "to be processed" array. If not, add it.
        getAndProcessData
          //  For each element of the "to be processed" array:
          //    - Send API request to OCLC for that ISBN
          //    - Receive request. If error, write to log file
          //    - Take only title, author, summary, ISBNs, and Subjects from the results
          //    - Construct valid JSON object
          //    - Append to output file
          //    - Write success message to log file.
      ];
 
    function next() {
        var callback = callbackSeries.shift();
        if (callback) {
            callback(next);
        }

    }
    next();
};

/// MAIN PROCESSING SECTION

series();


////FUNCTIONS


// Creates log and data files. Print file information to the console
function init(callback){
  fs.exists(logFile, function (exists) { // delete log file if run multiple times in one day
    if (exists){
      fs.unlink(logFile, function (error) {
        if (error) throw error;
        if (debug) console.log('successfully deleted log file before processing: ' + logFile);
      });
    }
    logMsg('Processing started. Using Input file name: '+isbnFile);
  });
  fs.exists(path+dataFile, function (exists) { // delete data output file if exists
    if (exists){
      fs.unlink(path+dataFile, function (error) {
        if (error) throw error;
      });
    }
    fs.appendFile(path+dataFile,  '"isbn","title","author","edition"\r\n', function (error) { 
      if (error) throw error;
    });
  });
  console.log(moment().format('YYYY-MM-DD HH:MM') + // output to console
    '\nProcessing started.'+
    '\n  Input file: \"' + isbnFile +
    '\"\n  Log file: \"' + logFile +
    '\"\n  JSON file created: \"' +dataFile + '\"');
  setTimeout(function() { callback(); }, 100);

}

// Process ISBN file. Create an array of valid ISBNs to send to API
function processISBNFile(callback){
    fs.readFile(path+isbnFile, 'utf8', function(error, fileData) { // cycle through input file
      // the data is passed to the callback in the second argument
      if(error){
        throw error;
      }
      if (debug) console.log('The file data is \n'+ fileData);
      var isbns=fileData.split('\n');
      var badISBNs=0;
      var dupeISBNs = 0;
      for (var i=0; i< isbns.length; i++){
        var isbnArr=isbns[i].split(' ');
        var tempISBN = isbnArr[0].trim().replace(/(\r\n|\n|\r)/gm,'');;// isbn will be first element in the array. Ignore spaces and line breaks
        var rt = checkISBN(tempISBN);// send to validator function
        if (rt==true){
          rt = checkIfInArray(isbnsToProcess,tempISBN);
          if (rt != true) {
            isbnsToProcess.push(tempISBN); // only add if not already in array
          }
          else {
            dupeISBNs += 1;
          }
          if (debug) console.log('here is the array of ISBNS to process '+isbnsToProcess.toString());
        }
        else{
          logMsg('"' +tempISBN + '" is not a valid ISBN');
          badISBNs += 1;
        }
      }
        summaryMsg ='There were '+isbns.length+' lines in the file. '+ isbnsToProcess.length+' were sent to the API to collect bibliographic data. '+badISBNs +' did not contain a valid ISBN, and ' + dupeISBNs +' were duplicates.';

    });
  setTimeout(function() { callback(); }, 100); // set callback for ordered processing
 }

// When data received, validate and extract data
function collectXMLdata(isbn){
  var jsonString, datafieldObj;
  parser = new xml2js.Parser({attrkey : parserPrefix});
  parser.addListener('end', function(result) {
    jsonString = JSON.stringify(result);
    if (checkResult(jsonString)==0){
        var testForJSON = new RegExp(/^\{/);
        var testForScripts = new RegExp(/\<script/);
        var good = testForJSON.test(jsonString);
        var bad = testForScripts.test(jsonString);
        if (debug)console.log(jsonString+'\n\ngood/bad '+good+' '+bad);
        var jsonObj = JSON.parse(jsonString);
        datafieldObj = jsonObj.record.datafield;
        var i=0;
        countLoop++;
        for (var key in datafieldObj) {

           obj = datafieldObj[key];
           for (prop in obj) {
              //check that it's not an inherited property
              if(obj.hasOwnProperty(prop)){
                i++;
                if (obj[prop]['tag']=='245'){
                  if (debug)console.log(isbn + '  ' +obj[prop]);
                  getTitleInfo();
                }
                if (obj[prop]['tag']=='100'){ // Not worring about corporate authors, committees, etc. Most likely leisure reading will have personal names
                  getAuthorInfo(); 
                }
                if (obj[prop]['tag']=='250'){
                  getEditionInfo();
                }

              }
           }
        }


    if (debug) console.log('length is '+isbnsToProcess.length + ' count is '+countLoop);

    logMsg(textbook.isbn + ' was processed successfully.');
      fs.appendFile(path+dataFile, textbook.isbn + ',"'+textbook.title +'","' + textbook.author + '","' + textbook.edition + '"\r\n', function (error) {
        if (error) throw error;
      });

   }// end check result
   else {
     logMsg(alertMsg);
   }
  }); // end parser listener

}

// Function for flow control
function getAndProcessData(callback){
  loopThroughISBNfile(); // need a callback so logging is in order
  setTimeout(function() { callback(); }, 100);
}


// Send an API request for each valid ISBN
function loopThroughISBNfile(){
  for (var i=0; i<isbnsToProcess.length; i++){
    isbn=isbnsToProcess[i];
    var url = createURL(isbn);
    if (debug2) console.log('using URL '+url+'\r\n');
    sendRequest(url, isbn, function(){
    });
  }
}


// Get edition info from the 250 field
function getEditionInfo(){
  var editionStr = obj['subfield'][0]['_'];
  editionStr = editionStr.trim();
  textbook['edition']=editionStr;
}

// Simple checks for JSON object and no <script tags
function checkResult(data){
  var testForJSON = new RegExp(/^\{/);
  var testForScripts = new RegExp(/\<script/);
  var good = testForJSON.test(data);
  var bad = testForScripts.test(data);
  if (bad==true){
    alertMsg='WARNING -- script tag found in response for ISBN '+isbn;
    return -1;
  }
  if (good==false){
    alertMsg='WARNING -- JSON not returned from xml2json Module for ISBN '+isbn;
    return -1;
  }
  return 0;
}

// Get title and author from 245 field
function getTitleInfo(){
    var titleStr='';
    for (var i=0; i<obj['subfield'].length; i++){
      if (obj['subfield'][i][parserPrefix]['code']=='a') {
        titleStr = obj['subfield'][i]['_'];
        var exp = new RegExp(/ :$/); // if there is a colon, move to a logical place: no space
        titleStr = titleStr.replace(exp,': ');
      }
      else if (obj['subfield'][i][parserPrefix]['code']=='b') {
          titleStr += obj['subfield'][i]['_'];
      }
    }
  
  exp = new RegExp(/ \/$/); // strip trailing ' /' from title
  titleStr = titleStr.replace(exp,'');
  textbook['title']=titleStr;
  if (debug) console.log(util.inspect(textbook, showHidden=true, depth=6, colorize=true));
}

function getAuthorInfo(){
  var authorStr = obj['subfield'][0]['_'];
  exp = new RegExp(/\.$/);
  authorStr = authorStr.replace(exp,''); // strip trailing period
  
  textbook['author']=authorStr;
  if (debug) console.log(util.inspect(textbook, showHidden=true, depth=6, colorize=true));  
}


//Collect isbn from response because we want to be sure to use IIT's isbn for the
// search, not a different one
function sendRequest(url, isbn, callback){
  request(url, 5000, function (error, response, xmlData) {
    if (!error && response.statusCode == 200) {
      textbook = new Object;
      textbook['isbn']=isbn;
      collectXMLdata(isbn);
      jsonData = parser.parseString(xmlData);
    }
  });
  callback(isbn);
}

// create API path
function createURL(isbn){
  url = 'http://www.worldcat.org/webservices/catalog/content/isbn/' + isbn + '?wskey='+key;
  // use oaiauth later
  url = encodeURI(url);// necessary?
  if(debug) console.log('url is '+url);
  return url;
}


// write a message to the log file with a timestamp
function logMsg(msg){
  var moment = require('moment');
  moment().format();
  var now = moment().format('YYYY-MM-DD HH:mm:ss');
  fs.appendFile(logFile, now + ' ' + msg + '\r\n', function (error) {
    if (error) throw error;
  });
}

// not full checksum processing, just ensuring that the split worked correctly
function checkISBN(isbn) {
  var exp, ret;
  if  (isbn.length === 10) {
    exp = new RegExp(/\b(^\d{10}$|^\d{9}x)$\b/i); // ISBN-10 can be 10 digits, or 9 digits + x (checksum of 10)
    if (debug) console.log('the length of '+isbn +' is '+ isbn.length);
  }
  else if (isbn.length === 13){
    exp = new RegExp(/^978\d{10}$/); // ISBN-13 has different checksum logic. only digits
  }
  else if (isbn.length === 14){
    exp = new RegExp(/^978-\d{10}$/); // ISBN-13 with a hyphen
  }
  else {
    if (debug) console.log('"'+isbn+'" is a not valid isbn.');
    return false; // quick check for length
    //
  }
    ret=exp.test(isbn);
    if (debug) console.log('regex returns '+ret);
    return ret;
}

// don't add duplicate ISBNs to the array
function checkIfInArray(arr, item){
  var flag = false;
  for (var i = 0; i < arr.length; i++){
    if (arr[i]==item){
      flag = true;
      break;
    }
  }
  return flag;
}
