# Mail Service

**A simply app for sending e-mails written in PHP. Send request & send message**
---

## About
Mail Service is a lightweight PHP service that enables sending emails via POST requests. Simply configure the SMTP credentials in the .env file, and the service will handle sending plain text or HTML messages. It supports CORS authorization, allowing for secure integration with frontend applications, and is fully tested using PHPUnit, ensuring reliability. Thanks to its modular architecture, the service works exceptionally well as a microservice in containerized environments like Docker, making it easy to scale and integrate with other system components. Easy to use, it is perfect for quick deployment in projects requiring email functionality.

## How it works

Send request for http://your-domain.example/send with below payload:

```text
{ 
    "message": "Your message content", 
    "title": "Message to you!", 
    "recipientMail": "recipient@mail.example", 
    "ccMail": "copy@mail.example", 
    "bccMail": 
    "hidden-copy@mail.example" 
}
```

Also you can set multiple recipients, cc and bcc like this:

```text
{ 
    "message": "Your message content", 
    "title": "Message to you!", 
    "recipientMail": ["recipient@mail.example", "another-recipient@mail.example"], 
    "ccMail": ["copy@mail.example", "another-copy@mail.example"], 
    "bccMail": ["hidden-copy@mail.example", "another-hidden@mail.example"] 
}
```

If you want to send a message as html you can send payload as this:

```text
{ 
    "message": { 
        "content": "Your message content", 
    },
    "isHTML": true,
    "title": "Message to you!", 
    "recipientMail": ["recipient@mail.example", "another-recipient@mail.example"], 
    "ccMail": ["copy@mail.example", "another-copy@mail.example"], 
    "bccMail": ["hidden-copy@mail.example", "another-hidden@mail.example"] 
}
```

Template file is set in templates directory in project root. You can change this file and set your own keys for
replacement in {{ key }}, also you can set different template for each message like this:

```text
{ 
    "message": { 
        "content": "Your message content", 
    },
    "template": "your_template_name",
    "isHTML": true,
    "title": "Message to you!", 
    "recipientMail": ["recipient@mail.example", "another-recipient@mail.example"], 
    "ccMail": ["copy@mail.example", "another-copy@mail.example"], 
    "bccMail": ["hidden-copy@mail.example", "another-hidden@mail.example"] 
}
```

And create your template file with same name as in payload.

## Getting started

Clone this repo, then run:

``` bash
composer install
composer dump-autoload
```

Fill the .example.env file with your email account details and configure the necessary values, then rename it to .env.

Set up your server rewrite conditions in the index.php file located in the main directory. The repository includes an example Apache configuration for reference.

When sending requests, include a Secret header with a value that matches the Secret key in your .env file. The secret can be any string. Additionally, ensure the Content-Type header is set to application/json. It is strongly recommended to specify allowed domains for security.

If you're using Docker, you can integrate this project into your container stack or run it as a standalone container.

This application is compatible with any hosting environment that supports a PHP interpreter.

### Debug Mode
You can enable debug mode set IS_DEBUG on true in .env file, when this option is enabled all server messages will contains additional debugMessage field

### Logs
Logs are in /logs directory, each request has unique id

### Testing
App contains tests utilizing PHPUnit, you can run these typing:
```bash
php .\vendor\bin\phpunit
```
### Other

App supports PSR-4 autoload via composer and utilizing PHPMailer and DotEnv libraries, also Mail Service supports testing with PHPUnit

## Contributing ##

Feel free to open issues or submit pull requests. All contributions are welcome!

## License

This project is licensed under the [MIT License](LICENSE).  
Feel free to use, modify, and distribute it as long as the terms of the license are followed.