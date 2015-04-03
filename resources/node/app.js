//var request = require('request');
//
//
//var form = {
//    first: 0,
//    count: 100
//};
//
//
//request.post('http://82.208.47.250:8080/eDeska/eDeskaAktualni.jsp', {form: form}, function (error, response, body) {
//    if (!error && response.statusCode == 200) {
//        console.log(body); // Show the HTML for the Google homepage.
//    }
//});



var express = require('express');
var app = express();
var bodyParser = require('body-parser');

app.use(bodyParser.urlencoded({     // to support URL-encoded bodies
    extended: true
}));

app.all('/', function (req, res) {
    console.log(req.headers);
    console.log(req.body);
    res.send('Hello World!');
});


var server = app.listen(3000, function () {

    var host = server.address().address;
    var port = server.address().port;

    console.log('Example app listening at http://%s:%s', host, port);

});