<?php

const MESSAGE_RECIPIENT_MAIL = "recipient@mail.com";
const MESSAGE_MESSAGE_CONTENT = "Hello. This is a test message";
const MESSAGE_MESSAGE_TITLE = "This is a test message's title";
const VALID_RAW_MESSAGE = [
    "title" => MESSAGE_MESSAGE_TITLE,
    "recipientMail" => MESSAGE_RECIPIENT_MAIL,
    "message" => MESSAGE_MESSAGE_CONTENT
];


const VALID_RAW_SIMPLEST_MESSAGE = [
    "message" => MESSAGE_MESSAGE_CONTENT
];

const INVALID_RAW_MESSAGE = [
    "recipientMail" => "recipientmail.com",
    "message" => "Test message"
];

const CC_TEST_EMAIL = 'carboncopy@mail.test';
const ANOTHER_CC_TEST_EMAIL = 'anothercarboncopy@mail.test';
const BCC_TEST_EMAIL = 'blindcarboncopy@mail.test';

const ANOTHER_BCC_TEST_EMAIL = 'anotherblindcarboncopy@mail.test';
const TEST_TITLE = 'Test message';
const TEST_RECIPIENT_EMAIL = 'recipient@mail.test';

const TEST_MESSAGE = 'Hello! This is a test!';