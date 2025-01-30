# Mail Service

**A simply app for sending e-mails written in PHP. Send request & send message**
---

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

Fill .example.env file with your email account data and set necessary values, then rename it to .env.
Set your server rewrite condition at index.php in main directory and send message, repository contains example apache
config.
You have to send requests with "Secret" header which key must be same as Secret in your .env file. The secret can be any
string, also you have to set Content-Type header as application/json. Strongly recommended to set allowed domains.

If you're using docker, you can add this project to your container stack or run it as container.
This app should run on any hosting which support PHP interpreter.

### Debug Mode
You can enable debug mode set IS_DEBUG on true in .env file, when this option is enabled all server messages will contains additional debugMessage field


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