<?php

const VALID_SERVER_DATA = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'SECRET',
    'HTTP_HOST' => 'localhost',
    'CONTENT_TYPE' => 'application/json'
];

const INVALID_SERVER_DATA_SECRET = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'INVALID_SECRET',
    'HTTP_HOST' => 'localhost',
    'CONTENT_TYPE' => 'application/json'
];

const INVALID_SERVER_DATA_CONTENT_TYPE = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'SECRET',
    'HTTP_HOST' => 'localhost',
    'CONTENT_TYPE' => 'application/invalid'
];

const INVALID_SERVER_DATA_DOMAIN = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'SECRET',
    'HTTP_HOST' => 'invalid',
    'CONTENT_TYPE' => 'application/json'
];