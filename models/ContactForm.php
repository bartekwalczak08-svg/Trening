<?php

/**
 * Opis: Model domenowy używany w aplikacji.
 */

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\captcha\Captcha;

/**
 * ContactForm is the model behind the contact form.
 */
// Klasa ContactForm.
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * @return array the validation rules.
     */
    // Metoda rules.
    public function rules()
    {
        $rules = [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            [['subject', 'body'], 'validateContentBlacklist'],
        ];

        // Captcha is optional when graphic extensions are not available.
        if (self::isCaptchaAvailable()) {
            $rules[] = ['verifyCode', 'captcha'];
        }

        return $rules;
    }

    // Metoda isCaptchaAvailable.
    public static function isCaptchaAvailable()
    {
        try {
            Captcha::checkRequirements();
            return true;
        } catch (InvalidConfigException $e) {
            return false;
        }
    }

    /**
     * @return array customized attribute labels
     */
    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'name' => 'Imię',
            'email' => 'E-mail',
            'subject' => 'Temat',
            'body' => 'Wiadomość',
            'verifyCode' => 'Kod weryfikacyjny',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    // Metoda contact.
    public function contact($email)
    {
        if ($this->validate()) {
            // Store contact messages in DB so they are visible in admin list/DB tools.
            $record = new ContactMessage();
            $record->name = $this->name;
            $record->email = $this->email;
            $record->subject = $this->subject;
            $record->body = $this->body;
            $record->status = ContactMessage::STATUS_NEW;
            $record->created_at = time();
            $record->updated_at = time();

            return $record->save();
        }

        return false;
    }

    // Metoda validateContentBlacklist.
    public function validateContentBlacklist($attribute)
    {
        $value = trim((string) $this->$attribute);
        if ($value == '') {
            return;
        }

        $blockedWords = Yii::$app->params['contactBlacklistWords'] ?? [];
        $normalizedValue = $this->normalizeForBlacklist($value);
        $pattern = $this->buildBlacklistRegex($blockedWords);
        // Three layers: obfuscated regex, collapsed letters, and digit-noise detection.
        $matchedByRegex = $pattern !== null && preg_match($pattern, $normalizedValue) === 1;
        $matchedByCollapsedText = $this->containsBlockedWordInCollapsedText($normalizedValue, $blockedWords);
        $matchedByDigitNoise = $this->containsBlockedWordIgnoringDigits($value, $blockedWords);

        if ($matchedByRegex || $matchedByCollapsedText || $matchedByDigitNoise) {
            $this->addError($attribute, 'Wiadomość nie może zawierać wulgaryzmów.');
        }
    }

    // Metoda buildBlacklistRegex.
    private function buildBlacklistRegex(array $blockedWords)
    {
        $tokens = [];
        foreach ($blockedWords as $word) {
            $token = $this->buildObfuscatedWordPattern((string) $word);
            if ($token !== null) {
                $tokens[] = $token;
            }
        }

        if (empty($tokens)) {
            return null;
        }

        // Compiled once per validation call to keep matching predictable.
        return '/(?:' . implode('|', $tokens) . ')/i';
    }

    // Metoda buildObfuscatedWordPattern.
    private function buildObfuscatedWordPattern($word)
    {
        $normalizedWord = $this->normalizeForBlacklist((string) $word);
        $lettersOnly = preg_replace('/[^a-z0-9]+/', '', $normalizedWord);
        if ($lettersOnly === null || $lettersOnly === '') {
            return null;
        }

        $chars = str_split($lettersOnly);
        if (empty($chars)) {
            return null;
        }

        $parts = [];
        foreach ($chars as $char) {
            // Allow repeated letters and separators between letters.
            $parts[] = preg_quote($char, '/') . '+';
        }

        $separator = '[^a-z0-9]*';

        return '(?<![a-z0-9])' . implode($separator, $parts) . '(?![a-z0-9])';
    }

    // Metoda normalizeForBlacklist.
    private function normalizeForBlacklist($value)
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        // Normalize common Polish diacritics and leetspeak substitutions.
        $text = strtr($text, [
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ż' => 'z',
            'ź' => 'z',
            '@' => 'a',
            '4' => 'a',
            '0' => 'o',
            '1' => 'i',
            '!' => 'i',
            '3' => 'e',
            '5' => 's',
            '$' => 's',
            '7' => 't',
            '+' => 't',
            '8' => 'b',
            '9' => 'g',
        ]);

        return $text;
    }

    // Metoda containsBlockedWordInCollapsedText.
    private function containsBlockedWordInCollapsedText($normalizedValue, array $blockedWords)
    {
        // Catch stretched forms like "kuuurwa" after removing noise.
        $valueForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalizedValue));
        if ($valueForCompare === '') {
            return false;
        }

        foreach ($blockedWords as $word) {
            $normalizedWord = $this->normalizeForBlacklist((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalizedWord));
            if ($wordForCompare !== '' && strpos($valueForCompare, $wordForCompare) !== false) {
                return true;
            }
        }

        return false;
    }

    // Metoda lettersOnly.
    private function lettersOnly($text)
    {
        $result = preg_replace('/[^a-z]+/', '', (string) $text);
        return $result === null ? '' : $result;
    }

    // Metoda collapseRepeatedLetters.
    private function collapseRepeatedLetters($text)
    {
        $result = preg_replace('/(.)\1+/', '$1', (string) $text);
        return $result === null ? (string) $text : $result;
    }

    // Metoda containsBlockedWordIgnoringDigits.
    private function containsBlockedWordIgnoringDigits($value, array $blockedWords)
    {
        // Catch forms where digits are inserted between letters, e.g. "je123bac".
        $normalized = $this->normalizeWithoutLeet((string) $value);
        $valueForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalized));
        if ($valueForCompare === '') {
            return false;
        }

        foreach ($blockedWords as $word) {
            $wordNormalized = $this->normalizeWithoutLeet((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($wordNormalized));
            if ($wordForCompare !== '' && strpos($valueForCompare, $wordForCompare) !== false) {
                return true;
            }
        }

        return false;
    }

    // Metoda normalizeWithoutLeet.
    private function normalizeWithoutLeet($value)
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        return strtr($text, [
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ż' => 'z',
            'ź' => 'z',
        ]);
    }
}
