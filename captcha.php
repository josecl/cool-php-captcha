<?php
/**
 * Script para la generación de CAPTCHAS
 *
 * @author  José Rodríguez <jose.rodriguez@exec.cl>
 * @license LGPL
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 *
 */


session_start();



$captcha = new SimpleCaptcha();

// Change configuration...
//$captcha->wordsFile = 'words/es.txt';
//$captcha->session_var = 'secretword';

$captcha->CreateImage();













/**
 * SimpleCaptcha class
 *
 */
class SimpleCaptcha {

    /** Width of the image */
    public $width  = 200;

    /** Height of the image */
    public $height = 70;

    /** Dictionary word file (empty for randnom text) */
    public $wordsFile = 'words/en.txt';

    /** Min word length (for non-dictionary random text generation) */
    public $minWordLength = 5;

    /** Max word length (for non-dictionary random text generation) */
    public $maxWordLength = 8;

    /** Sessionname to store the original text */
    public $session_var = 'captcha';

    /** Background color in RGB */
    public $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB */
    public $colors = array(
        array(27,78,181), // blue
        array(22,163,35), // green
        array(214,36,7),  // red
    );

    /**
     * Font configuration
     *
     * - font: TTF file
     * - condensation: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public $fonts = array(
        'Candice'  => array('condensation' => -1, 'minSize' => 28, 'maxSize' => 36, 'font' => 'Candice.ttf'),
        'Jura'     => array('condensation' => -2, 'minSize' => 28, 'maxSize' => 34, 'font' => 'Jura.ttf'),
        'Times'    => array('condensation' => -2, 'minSize' => 28, 'maxSize' => 40, 'font' => 'TimesNewRomanBold.ttf'),
        'VeraSans' => array('condensation' => -1, 'minSize' => 20, 'maxSize' => 33, 'font' => 'VeraSansBold.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    public $Yperiod    = 13;
    public $Yamplitude = 15;
    public $Xperiod    = 12;
    public $Xamplitude = 4;

    /** letter rotation clockwise */
    public $maxRotation = 8;

    /** Internal image size factor (for better image quality) */
    public $scale = 2;

    /** Debug? */
    public $debug = false;


    /** GD image */
    public $im;










    public function __construct($config = array()) {
    }







    public function CreateImage() {
        $ini = microtime(true);

        $this->ImageAllocate();
        $text = $this->GetCaptchaText();
        $this->WriteText($text);

        $_SESSION[$this->session_var] = $text;

        $this->WaveImage();
        $this->ReduceImage();


        if ($this->debug) {
            imagestring($this->im, 1, 1, $this->height-8,
                "$text ".round((microtime(true)-$ini)*1000)."ms",
                $this->GdFgColor
            );
        }


        $this->WriteImage();
        $this->Cleanup();
    }









    /**
     * Creates the image resources
     */
    protected function ImageAllocate() {
        // Cleanup
        if (!empty($this->im)) {
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        // Background color
        $this->GdBgColor = imagecolorallocate($this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[0],
            $this->backgroundColor[0]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

        // Foreground color
        $color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);
    }





    /**
     * Text generation
     *
     * @return string Text
     */
    protected function GetCaptchaText() {
        $text = $this->GetDictionaryCaptchaText();
        if (!$text) {
            $text = $this->GetRandomCaptchaText();
        }
        return $text;
    }






    /**
     * Random text generation
     *
     * @return string Text
     */
    protected function GetRandomCaptchaText($length = null) {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstvwyz";
        $vocals = "aeiou";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++) {
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1);
            } else {
                $text .= substr($words, mt_rand(0, 22), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }









    /**
     * Random dictionary word generation
     *
     * @param boolean $extended Add extended "fake" words
     * @return string Word
     */
    function GetDictionaryCaptchaText($extended = false) {
        if (empty($this->wordsFile)) {
            return false;
        }

        $fp     = fopen($this->wordsFile, "r");
        $length = strlen(fgets($fp));
        if (!$length) {
            return false;
        }
        $line   = rand(0, (filesize($this->wordsFile)/$length)-1);
        if (fseek($fp, $length*$line) == -1) {
            return false;
        }
        $text = trim(fgets($fp));
        fclose($fp);


        /** Change ramdom volcals */
        if ($extended) {
            $text   = str_split($text, 1);
            $vocals = array('a', 'e', 'i', 'o', 'u');
            foreach ($text as $i => $char) {
                if (mt_rand(0, 1) && in_array($char, $vocals)) {
                    $text[$i] = $vocals[mt_rand(0, 4)];
                }
            }
            $text = implode('', $text);
        }

        return $text;
    }










    /**
     * Text insertion
     */
    protected function WriteText($text) {
        // Select the font configuration
        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $fontfile = 'fonts/'.$fontcfg['font'];

        // Text generation (char by char)
        $x      = 20*$this->scale;
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale;
            $coords   = imagettftext($this->im, $fontsize, $degree, $x, $y,
                $this->GdFgColor, $fontfile, substr($text, $i, 1));
            $x       += ($coords[2]-$x) + ($fontcfg['condensation']*$this->scale);
        }
    }



    /**
     * Wave filter
     */
    protected function WaveImage() {
        // X-axis wave generation
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/($this->scale*$this->Xperiod)) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                sin($k+$i/($this->scale*$this->Yperiod)) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }




    /**
     * Reduce the image to the final size
     */
    protected function ReduceImage() {
        // Reduzco el tamaño de la imagen
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }








    /**
     * File generation
     */
    protected function WriteImage() {
        header("Content-type: image/jpeg");
        imagejpeg($this->im, null, 80);
    }







    /**
     * Cleanup
     */
    protected function Cleanup() {
        imagedestroy($this->im);
    }
}
















?>
