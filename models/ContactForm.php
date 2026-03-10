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
        // Four layers: obfuscated regex, collapsed letters, digit-noise detection,
        // and bounded-gap subsequence matching for split/inserted-letter obfuscation.
        $matchedByRegex = $pattern !== null && preg_match($pattern, $normalizedValue) === 1;
        $matchedByCollapsedText = $this->containsBlockedWordInCollapsedText($normalizedValue, $blockedWords);
        $matchedByDigitNoise = $this->containsBlockedWordIgnoringDigits($value, $blockedWords);
        $matchedByFuzzySubsequence = $this->containsBlockedWordAsFuzzySubsequence($value, $normalizedValue, $blockedWords);

        if ($matchedByRegex || $matchedByCollapsedText || $matchedByDigitNoise || $matchedByFuzzySubsequence) {
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

        $text = $this->normalizeUnicodeForBlacklist($text);

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

        // Handle common obfuscation variant: yeb* -> jeb* (e.g. "yeb@c").
        $text = preg_replace('/\by(?=eb)/u', 'j', $text) ?? $text;
        // Handle common obfuscation variant: qurwa -> kurwa.
        $text = preg_replace('/\bq(?=urwa)/u', 'k', $text) ?? $text;
        // Handle common obfuscation variant: curwa -> kurwa.
        $text = preg_replace('/\bc(?=urwa)/u', 'k', $text) ?? $text;

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

    // Metoda containsBlockedWordAsFuzzySubsequence.
    private function containsBlockedWordAsFuzzySubsequence($originalValue, $normalizedValue, array $blockedWords)
    {
        // Keep fuzzy mode for suspicious obfuscation only (digits/special chars),
        // otherwise regular text can produce false positives such as innocent words.
        if (!$this->hasObfuscationNoise((string) $originalValue)) {
            return false;
        }

        $valueForCompare = $this->collapseRepeatedLetters($this->lettersOnly((string) $normalizedValue));
        if ($valueForCompare === '') {
            return false;
        }

        foreach ($blockedWords as $word) {
            $normalizedWord = $this->normalizeForBlacklist((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalizedWord));

            // Skip very short words to reduce accidental matches in normal text.
            if ($wordForCompare === '' || strlen($wordForCompare) < 5) {
                continue;
            }

            if ($this->matchesWithMaxGap($valueForCompare, $wordForCompare, 5)) {
                return true;
            }
        }

        return false;
    }

    // Metoda hasObfuscationNoise.
    private function hasObfuscationNoise($value)
    {
        $text = trim((string) $value);
        if ($text === '') {
            return false;
        }

        // Digits or punctuation/symbols are treated as likely obfuscation signals.
        if (preg_match('/\d/u', $text) === 1) {
            return true;
        }

        return preg_match('/[^\p{L}\s]/u', $text) === 1;
    }

    // Metoda matchesWithMaxGap.
    private function matchesWithMaxGap($haystack, $needle, $maxGap)
    {
        $haystack = (string) $haystack;
        $needle = (string) $needle;
        $maxGap = (int) $maxGap;

        if ($haystack === '' || $needle === '') {
            return false;
        }

        $haystackLength = strlen($haystack);
        $needleLength = strlen($needle);
        if ($needleLength > $haystackLength) {
            return false;
        }

        for ($start = 0; $start < $haystackLength; $start++) {
            if ($haystack[$start] !== $needle[0]) {
                continue;
            }

            $currentPos = $start;
            $matched = true;

            for ($i = 1; $i < $needleLength; $i++) {
                $foundAt = -1;
                $searchFrom = $currentPos + 1;
                $searchTo = min($haystackLength - 1, $currentPos + $maxGap + 1);

                for ($j = $searchFrom; $j <= $searchTo; $j++) {
                    if ($haystack[$j] === $needle[$i]) {
                        $foundAt = $j;
                        break;
                    }
                }

                if ($foundAt === -1) {
                    $matched = false;
                    break;
                }

                $currentPos = $foundAt;
            }

            if ($matched) {
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

        $text = $this->normalizeUnicodeForBlacklist($text);

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

    // Metoda normalizeUnicodeForBlacklist.
    private function normalizeUnicodeForBlacklist($text)
    {
        $value = (string) $text;

        // Remove hidden Unicode format characters often used for evasion.
        $withoutHidden = preg_replace('/[\x{00AD}\x{034F}\x{061C}\x{180E}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FE00}-\x{FE0F}\x{FEFF}]/u', '', $value);
        if ($withoutHidden !== null) {
            $value = $withoutHidden;
        }

        // Normalize compatibility characters (e.g. full-width variants).
        if (class_exists('\\Normalizer')) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_KD);
            if ($normalized !== false) {
                $value = $normalized;
            }
        }

        // Drop combining marks after normalization.
        $withoutMarks = preg_replace('/\p{Mn}+/u', '', $value);
        if ($withoutMarks !== null) {
            $value = $withoutMarks;
        }

        if (function_exists('mb_convert_kana')) {
            $value = mb_convert_kana($value, 'asKV', 'UTF-8');
        }

        // Map common Cyrillic/Greek homoglyphs to Latin lookalikes.
        $value = strtr($value, [
            'а' => 'a', 'А' => 'a', 'е' => 'e', 'Е' => 'e', 'о' => 'o', 'О' => 'o',
            'р' => 'p', 'Р' => 'p', 'с' => 'c', 'С' => 'c', 'х' => 'x', 'Х' => 'x',
            'у' => 'y', 'У' => 'y', 'к' => 'k', 'К' => 'k', 'м' => 'm', 'М' => 'm',
            'т' => 't', 'Т' => 't', 'в' => 'b', 'В' => 'b', 'н' => 'h', 'Н' => 'h',
            'і' => 'i', 'І' => 'i', 'ї' => 'i', 'Ї' => 'i', 'ј' => 'j', 'Ј' => 'j',
            'ӏ' => 'l',
            'α' => 'a', 'Α' => 'a', 'β' => 'b', 'Β' => 'b', 'δ' => 'd', 'Δ' => 'd',
            'ε' => 'e', 'Ε' => 'e', 'ι' => 'i', 'Ι' => 'i', 'κ' => 'k', 'Κ' => 'k',
            'ν' => 'v', 'Ν' => 'v', 'ο' => 'o', 'Ο' => 'o', 'ρ' => 'p', 'Ρ' => 'p',
            'τ' => 't', 'Τ' => 't', 'υ' => 'u', 'Υ' => 'u', 'χ' => 'x', 'Χ' => 'x',
        ]);

        $normalizedSpaces = preg_replace('/\p{Z}+/u', ' ', $value);
        return $normalizedSpaces === null ? $value : $normalizedSpaces;
    }
}
