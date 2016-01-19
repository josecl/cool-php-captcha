El objetivo de este proyecto es la generación de captchas de seguridad básica generando imágenes relativamente amigables para el usuario.



# Example #
This project provides the `SimpleCaptcha` class.

The images are similar to google captchas.



Basic example:

```
session_start();
$captcha = new SimpleCaptcha();
// Change configuration...
//$captcha->wordsFile = null;           // Disable dictionary words
//$captcha->wordsFile = 'words/es.txt'; // Enable spanish words
//$captcha->session_var = 'secretword'; // Change session variable
$captcha->CreateImage();
```


... will output:

![http://cool-php-captcha.googlecode.com/files/example.jpg](http://cool-php-captcha.googlecode.com/files/example.jpg)




You can validate the php captcha with:

```

if (empty($_SESSION['captcha']) || strtolower(trim($_REQUEST['captcha'])) != $_SESSION['captcha']) {
    return "Invalid captcha";
}

```



<br>

You can see a live example here: <a href='http://joserodriguez.cl/cool-php-captcha'>http://joserodriguez.cl/cool-php-captcha</a>.<br>
<br>
<br>
<br>
<br>
<br>
<br>


<h1>More examples</h1>
Background and foreground colors, dictionary words, non-dictionary random words, blur, shadows, JPEG and PNG support:<br>
<br>
<img src='http://cool-php-captcha.googlecode.com/files/examples.jpg' />



