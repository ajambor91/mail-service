# Mail Service

**A simple PHP application for sending emails via requests.**
---

## About
Mail Service is a lightweight PHP microservice designed to send emails through POST requests. By simply configuring your SMTP credentials within the `.env` file, the service seamlessly handles the dispatch of both plain text and HTML messages. It incorporates CORS authorization, ensuring secure integration with frontend applications, and undergoes thorough testing with PHPUnit to guarantee reliability. Its modular architecture makes it an ideal microservice for containerized environments like Docker, facilitating effortless scaling and integration with other system components. User-friendly and straightforward, Mail Service is perfect for rapid deployment in projects requiring email functionality. The application supports SMTPS, TLS, and the `mail()` function, allowing compatibility with any email service provider or your own mail server (e.g., Postfix).

## How it works

Send a POST request to `http://your-domain.example/send` with the following JSON payload:

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

Fill in your email account details and configure the necessary values in the .example.env file, then rename it to .env. This file is located in the project's root directory.
Set up your server rewrite rules in the index.php file, which is located in the main directory. The repository includes an example Apache configuration for reference.
When sending requests, include a Secret header with a value that matches the X-APP-SECRET key found in your .env file. This secret can be any string. Additionally, ensure the Content-Type header is set to application/json. It is strongly recommended to specify allowed domains for enhanced security.
If you're using Docker, you can integrate this project into your container stack or run it as a standalone container. The repository contains three `docker-compose` files:

1.  `docker-compose.yml` - A basic Dockerfile utilizing Apache as the web server. It does not include dependency installation via Composer.
2.  `docker-compose.php-fpm.yml` - Configuration with Nginx and PHP-FPM.
3.  `docker-compose.php-fpm-compose.yml` - The same configuration as above, but in this file, Composer will install the dependencies, making it ideal for rapid deployments.

This application is compatible with any hosting environment that supports a PHP interpreter.



### Debug Mode
You can enable debug mode by setting IS_DEBUG to true in the .env file. When this option is enabled, all server responses will include an additional debugMessage field. You can also enable PHPMailer's debug mode for more detailed output regarding email sending.

### Logs
Logs are in /logs directory, each request has unique id, and logging is compatible with PSR-3 standard. You can set log level in .env file.

### Testing
App contains tests utilizing PHPUnit, you can run these typing:
```bash
php .\vendor\bin\phpunit
```
### Other

App supports PSR-4 autoload via composer and utilizing PHPMailer and DotEnv libraries, also Mail Service supports testing with PHPUnit.

## Contributing ##

Feel free to open issues or submit pull requests. All contributions are welcome!

## License

This project is licensed under the [MIT License](LICENSE).  
Feel free to use, modify, and distribute it as long as the terms of the license are followed.