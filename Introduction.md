# Short version #
This project provides the `SimpleCaptcha` class.

Basic example:

```
session_start();
$captcha = new SimpleCaptcha();
// Change configuration...
//$captcha->wordsFile = 'words/es.txt'; // Enable spanish words
//$captcha->session_var = 'secretword'; // Change session variable
$captcha->CreateImage();
```


... will output:

![http://cool-php-captcha.googlecode.com/files/example.jpg](http://cool-php-captcha.googlecode.com/files/example.jpg)