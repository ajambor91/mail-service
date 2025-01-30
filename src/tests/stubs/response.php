<?php
const VALID_OUTPUT_MESSAGE = [
    "message" => "Message was send"
];

const INVALID_OUTPUT_BAD_REQUEST = ["message" => 'Invalid payload'];

const INVALID_OUTPUT_UNAUTHORIZED = ["message" => 'Invalid secret'];

const INVALID_OUTPUT_BAD_REQUEST_INVALID_CONTENT_TYPE = ["message" => 'Content-Type should be application/json'];

const INVALID_OUTPUT_BAD_REQUEST_INVALID_DOMAIN = ["message" => 'Domain not allowed'];