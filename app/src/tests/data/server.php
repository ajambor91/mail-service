<?php
const TEST_DOMAIN = "localhost";
const VALID_ALLOWED_DOMAIN = [TEST_DOMAIN];
const VALID_SECRET = "SECRET";

const VALID_SERVER_DATA = [
    'REQUEST_URI' => 'http://' . TEST_DOMAIN . '/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_APP_SECRET' => VALID_SECRET,
    'HTTP_HOST' => 'localhost',
    'CONTENT_TYPE' => 'application/json'
];
const VALID_SERVER_DATA_HOMEPAGE = [
    'REQUEST_URI' => 'http://' . TEST_DOMAIN,
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_APP_SECRET' => VALID_SECRET,
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

const INVALID_SERVER_DATA_EMPTY_CONTENT_TYPE = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'SECRET',
    'HTTP_HOST' => 'localhost'
];

const INVALID_SERVER_DATA_EMPTY_SECRET = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'CONTENT_TYPE' => 'application/json',
    'HTTP_HOST' => 'localhost'
];

const INVALID_SERVER_DATA_EMPTY_HOST = [
    'REQUEST_URI' => 'http://localhost/send',
    'REQUEST_METHOD' => 'POST',
    'HTTP_SECRET' => 'SECRET',
    'CONTENT_TYPE' => 'application/json',
];