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
 * Model formularza kontaktowego.
 *
 * Odpowiada za walidację danych wejściowych, zapis wiadomości do bazy
 * oraz wielowarstwowe wykrywanie treści z blacklisty (w tym obfuskacji).
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * Definicja reguł walidacji formularza kontaktowego.
     */
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

    /**
     * Sprawdza, czy środowisko obsługuje CAPTCHA.
     */
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
     * Etykiety pól wyświetlane w formularzu.
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Imię'),
            'email' => 'E-mail',
            'subject' => Yii::t('app', 'Temat'),
            'body' => Yii::t('app', 'Wiadomość'),
            'verifyCode' => Yii::t('app', 'Kod weryfikacyjny'),
        ];
    }

    /**
     * Waliduje formularz i zapisuje zgłoszenie kontaktowe do bazy.
     *
     * Parametr $email jest utrzymany dla zgodności z domyślnym szkieletem Yii.
     */
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

    /**
     * Walidator treści oparty o blacklistę.
     *
     * Łączy kilka strategii wykrywania obfuskacji, aby blokować zarówno
     * proste, jak i celowo zniekształcone wersje zakazanych słów.
     */
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
        $matchedByMixedScriptSubstitution = $this->containsBlockedWordWithNonLatinSubstitutions($value, $blockedWords);
        $matchedByLeetAlternatives = $this->containsBlockedWordWithLeetAlternatives($value, $blockedWords);

        if ($matchedByRegex || $matchedByCollapsedText || $matchedByDigitNoise || $matchedByFuzzySubsequence || $matchedByMixedScriptSubstitution || $matchedByLeetAlternatives) {
            $this->addError($attribute, Yii::t('app', 'Wiadomość nie może zawierać wulgaryzmów.'));
        }
    }

    /**
     * Buduje regex zbiorczy dla wszystkich słów z blacklisty.
     */
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

    /**
     * Tworzy regex dla pojedynczego słowa z dopuszczeniem separatorów i powtórzeń.
     */
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

    /**
     * Normalizuje tekst do porównań blacklisty.
     *
     * Obejmuje normalizację Unicode, małe litery, polskie znaki i część leetspeak.
     */
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

    /**
     * Wykrywa słowa blacklisty po usunięciu szumu i redukcji powtórzeń liter.
     */
    private function containsBlockedWordInCollapsedText($normalizedValue, array $blockedWords)
    {
        // Catch stretched forms like "kuuurwa" while preserving token boundaries.
        $tokens = $this->collapsedLetterTokens((string) $normalizedValue);
        if (empty($tokens)) {
            return false;
        }

        foreach ($blockedWords as $word) {
            $normalizedWord = $this->normalizeForBlacklist((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalizedWord));
            if ($wordForCompare === '') {
                continue;
            }

            if (in_array($wordForCompare, $tokens, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zwraca wyłącznie litery ASCII a-z.
     */
    private function lettersOnly($text)
    {
        $result = preg_replace('/[^a-z]+/', '', (string) $text);
        return $result === null ? '' : $result;
    }

    /**
     * Redukuje serie tych samych znaków do pojedynczego wystąpienia.
     */
    private function collapseRepeatedLetters($text)
    {
        $result = preg_replace('/(.)\1+/', '$1', (string) $text);
        return $result === null ? (string) $text : $result;
    }

    /**
     * Wykrywa słowa blacklisty z cyframi i innym szumem wstawianym między litery.
     */
    private function containsBlockedWordIgnoringDigits($value, array $blockedWords)
    {
        // Catch forms where digits are inserted between letters, e.g. "je123bac".
        $normalized = $this->normalizeWithoutLeet((string) $value);
        $tokens = $this->collapsedLetterTokens($normalized);
        if (empty($tokens)) {
            return false;
        }

        foreach ($blockedWords as $word) {
            $wordNormalized = $this->normalizeWithoutLeet((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($wordNormalized));
            if ($wordForCompare === '') {
                continue;
            }

            if (in_array($wordForCompare, $tokens, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zwraca tokeny literowe po normalizacji i redukcji powtórzeń.
     *
     * Dzięki tokenizacji unikamy fałszywych trafień typu rdzeń słowa
     * występujący przypadkowo wewnątrz innego, neutralnego wyrazu.
     *
     * @return string[]
     */
    private function collapsedLetterTokens($text)
    {
        $value = trim((string) $text);
        if ($value === '') {
            return [];
        }

        $rawTokens = preg_split('/[^a-z]+/i', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($rawTokens) || empty($rawTokens)) {
            return [];
        }

        $tokens = [];
        foreach ($rawTokens as $token) {
            $letters = $this->lettersOnly((string) $token);
            if ($letters === '') {
                continue;
            }

            $tokens[] = $this->collapseRepeatedLetters($letters);
        }

        return $tokens;
    }

    /**
     * Sprawdza dopasowanie jako podciąg z ograniczoną przerwą między literami.
     */
    private function containsBlockedWordAsFuzzySubsequence($originalValue, $normalizedValue, array $blockedWords)
    {
        // Keep fuzzy mode only when noise appears inside letter sequences
        // (e.g. k-u-r-w-a), not just anywhere in the message.
        if (!$this->hasInterLetterObfuscationNoise((string) $originalValue)) {
            return false;
        }

        $candidates = $this->fuzzyCandidates((string) $normalizedValue);
        if (empty($candidates)) {
            return false;
        }

        foreach ($blockedWords as $word) {
            $normalizedWord = $this->normalizeForBlacklist((string) $word);
            $wordForCompare = $this->collapseRepeatedLetters($this->lettersOnly($normalizedWord));

            // Skip very short words to reduce accidental matches in normal text.
            if ($wordForCompare === '' || strlen($wordForCompare) < 5) {
                continue;
            }

            foreach ($candidates as $candidate) {
                if ($this->matchesWithMaxGap($candidate, $wordForCompare, 5)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Buduje kandydatów do fuzzy-matchingu bez sklejania całego zdania w jeden token.
     *
     * Dzięki temu redukujemy false positive z liter rozrzuconych po wielu słowach,
     * a jednocześnie nadal wykrywamy obfuskację pojedynczymi literami.
     *
     * @return string[]
     */
    private function fuzzyCandidates($normalizedValue)
    {
        $rawTokens = preg_split('/[^a-z]+/i', (string) $normalizedValue, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($rawTokens) || empty($rawTokens)) {
            return [];
        }

        $candidates = [];
        $singleLetterSequence = '';

        foreach ($rawTokens as $token) {
            $collapsed = $this->collapseRepeatedLetters($this->lettersOnly((string) $token));
            if ($collapsed === '') {
                continue;
            }

            $candidates[] = $collapsed;

            if (strlen($collapsed) === 1) {
                $singleLetterSequence .= $collapsed;
            }
        }

        if (strlen($singleLetterSequence) >= 5) {
            $candidates[] = $singleLetterSequence;
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Wykrywa sygnały obfuskacji między literami (separatory, cyfry, rozstrzał liter).
     */
    private function hasInterLetterObfuscationNoise($value)
    {
        $text = trim((string) $value);
        if ($text === '') {
            return false;
        }

        // Require separators/digits between letters to classify as obfuscation.
        if (preg_match('/\p{L}[\p{Mn}\p{Mc}\p{Me}\p{Sk}\p{So}\p{Pd}\p{Pc}\d]+\p{L}/u', $text) === 1) {
            return true;
        }

        // Treat many isolated single-letter tokens as likely spaced-letter obfuscation.
        if (preg_match_all('/(?<!\p{L})\p{L}(?!\p{L})/u', $text, $matches) !== false && count($matches[0]) >= 3) {
            return true;
        }

        return false;
    }

    /**
     * Wykrywa mieszanie skryptów (np. cyrylica/greka zamiast liter łacińskich).
     */
    private function containsBlockedWordWithNonLatinSubstitutions($rawValue, array $blockedWords)
    {
        $text = trim((string) $rawValue);
        if ($text === '') {
            return false;
        }

        $withoutHidden = preg_replace('/[\x{00AD}\x{034F}\x{061C}\x{180E}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FE00}-\x{FE0F}\x{FEFF}]/u', '', $text);
        if ($withoutHidden !== null) {
            $text = $withoutHidden;
        }

        // Keep these confusables attached to token flow for mixed-script matching.
        $text = strtr($text, [
            'ͺ' => '¤',
            'ͅ' => '¤',
        ]);

        if (class_exists('\\Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_KD);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        // Only inspect tokens that look like deliberate Unicode obfuscation.
        // This keeps pure single-script messages (e.g. Hebrew/Cyrillic sentences) allowed.
        if (!$this->hasSuspiciousUnicodeObfuscationTokens($text)) {
            return false;
        }

        // Replace every non-ASCII character with a wildcard marker.
        $masked = preg_replace('/[^\x00-\x7F]/u', '?', $text);
        if ($masked === null || $masked === '') {
            return false;
        }

        $candidates = $this->buildMixedScriptWildcardCandidates($masked);
        if (empty($candidates)) {
            return false;
        }

        foreach ($candidates as $token) {
            // Evaluate suspicious tokens containing wildcard marks, even when no ASCII survived.
            if (strpos($token, '?') === false) {
                continue;
            }

            // Very short wildcard-only tokens are too ambiguous and can raise false positives.
            if (preg_match('/[a-z0-9]/i', $token) !== 1 && strlen($token) < 4) {
                continue;
            }

            foreach ($blockedWords as $word) {
                $normalizedWord = $this->normalizeForBlacklist((string) $word);
                $lettersOnly = preg_replace('/[^a-z0-9]+/', '', $normalizedWord);
                if ($lettersOnly === null || $lettersOnly === '') {
                    continue;
                }

                $chars = str_split($lettersOnly);
                $parts = [];
                foreach ($chars as $char) {
                    $parts[] = '(?:' . preg_quote($char, '/') . '|\?)+';
                }

                $separator = '[^a-z0-9?]*';
                $pattern = '/^' . implode($separator, $parts) . '$/i';
                if (preg_match($pattern, $token) === 1) {
                    return true;
                }

                // Fallback for separator-heavy obfuscation where only a wildcard skeleton remains.
                if ($this->matchesWithWildcardAndMaxGap($token, $lettersOnly, 3)) {
                    return true;
                }

                // Fallback for mixed-script replacements that collapse to missing tail letters.
                $withoutWildcards = str_replace('?', '', $token);
                if ($withoutWildcards !== '') {
                    $distance = levenshtein($withoutWildcards, $lettersOnly);
                    if ($distance >= 0 && $distance <= 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Buduje kandydatów tokenów dla wykrywania mixed-script po zamianie znaków na wildcardy.
     *
     * Dodaje także wersję "szkieletu" bez separatorów, aby łapać rozbijanie słowa
     * symbolami typu "-", "|" itp.
     *
     * @return string[]
     */
    private function buildMixedScriptWildcardCandidates($masked)
    {
        $value = (string) $masked;
        if ($value === '') {
            return [];
        }

        $tokens = preg_split('/[^a-z0-9?]+/i', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            $tokens = [];
        }

        $candidates = [];
        foreach ($tokens as $token) {
            $normalized = strtolower((string) $token);
            if ($normalized !== '') {
                $candidates[] = $normalized;
            }
        }

        $wordChunks = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($wordChunks)) {
            $wordChunks = [];
        }

        foreach ($wordChunks as $chunk) {
            $skeleton = preg_replace('/[^a-z0-9?]+/i', '', strtolower((string) $chunk));
            if ($skeleton !== null && $skeleton !== '') {
                $candidates[] = $skeleton;
            }
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Sprawdza dopasowanie needle w tokenie z wildcardami i ograniczoną przerwą.
     */
    private function matchesWithWildcardAndMaxGap($haystack, $needle, $maxGap)
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
            if ($haystack[$start] !== '?' && $haystack[$start] !== $needle[0]) {
                continue;
            }

            $currentPos = $start;
            $matched = true;

            for ($i = 1; $i < $needleLength; $i++) {
                $foundAt = -1;
                $searchFrom = $currentPos + 1;
                $searchTo = min($haystackLength - 1, $currentPos + $maxGap + 1);

                for ($j = $searchFrom; $j <= $searchTo; $j++) {
                    if ($haystack[$j] === '?' || $haystack[$j] === $needle[$i]) {
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

    /**
     * Wykrywa podejrzane tokeny Unicode typowe dla obfuskacji blacklisty.
     */
    private function hasSuspiciousUnicodeObfuscationTokens($text)
    {
        $value = (string) $text;

        $tokens = preg_split('/[^\p{L}\p{N}\p{So}\p{Sk}\p{Mn}\p{Mc}\p{Me}]+/u', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens) || empty($tokens)) {
            $tokens = [];
        }

        foreach ($tokens as $token) {
            if ($this->isSuspiciousUnicodeToken((string) $token)) {
                return true;
            }
        }

        // Handle separator-heavy obfuscation spread across a single chunk.
        $chunks = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($chunks)) {
            $chunks = [];
        }

        foreach ($chunks as $chunk) {
            $chunkValue = (string) $chunk;
            if ($chunkValue === '' || preg_match('/[^\x00-\x7F]/u', $chunkValue) !== 1) {
                continue;
            }

            if ($this->countUnicodeScripts($chunkValue) >= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Token uznajemy za podejrzany, jeśli miesza skrypty lub litery z symbolami/cyframi.
     */
    private function isSuspiciousUnicodeToken($token)
    {
        $value = (string) $token;
        if ($value === '' || preg_match('/[^\x00-\x7F]/u', $value) !== 1) {
            return false;
        }

        $hasLatin = preg_match('/\p{Latin}/u', $value) === 1;
        $withoutLatin = preg_replace('/\p{Latin}+/u', '', $value);
        $hasNonLatinLetter = $withoutLatin !== null && preg_match('/\p{L}/u', $withoutLatin) === 1;
        $hasDigit = preg_match('/\d/u', $value) === 1;
        $hasSymbol = preg_match('/[\p{So}\p{Sk}]/u', $value) === 1;

        if (($hasLatin && $hasNonLatinLetter) || ($hasNonLatinLetter && ($hasDigit || $hasSymbol))) {
            return true;
        }

        return $this->countUnicodeScripts($value) >= 2;
    }

    /**
     * Liczy liczbę różnych skryptów literowych obecnych w tokenie.
     */
    private function countUnicodeScripts($token)
    {
        $value = (string) $token;
        if ($value === '') {
            return 0;
        }

        $scriptPatterns = [
            '/\p{Latin}/u',
            '/\p{Cyrillic}/u',
            '/\p{Greek}/u',
            '/\p{Armenian}/u',
            '/\p{Hebrew}/u',
            '/\p{Arabic}/u',
            '/\p{Devanagari}/u',
            '/\p{Bengali}/u',
            '/\p{Georgian}/u',
            '/\p{Thai}/u',
            '/\p{Hiragana}/u',
            '/\p{Katakana}/u',
            '/\p{Han}/u',
        ];

        $count = 0;
        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Wykrywa słowa blacklisty zapisane w wariantach leet (np. e=3, l=1).
     */
    private function containsBlockedWordWithLeetAlternatives($rawValue, array $blockedWords)
    {
        $text = $this->normalizeWithoutLeet((string) $rawValue);
        if ($text === '') {
            return false;
        }

        foreach ($blockedWords as $word) {
            $pattern = $this->buildLeetAwareWordPattern((string) $word);
            if ($pattern !== null && preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buduje regex dla słowa z uwzględnieniem alternatyw leet dla każdej litery.
     */
    private function buildLeetAwareWordPattern($word)
    {
        $normalizedWord = $this->normalizeWithoutLeet((string) $word);
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
            $alternatives = $this->leetAlternativesForLetter($char);
            $escapedAlternatives = array_map(static function ($item) {
                return preg_quote($item, '/');
            }, $alternatives);

            $parts[] = '(?:' . implode('|', $escapedAlternatives) . ')+';
        }

        $separator = '[^a-z]*';

        return '/(?<![a-z])' . implode($separator, $parts) . '(?![a-z])/i';
    }

    /**
     * Zwraca dozwolone zamienniki leet dla pojedynczej litery.
     */
    private function leetAlternativesForLetter($char)
    {
        $base = strtolower((string) $char);
        $map = [
            'a' => ['a', '4', '@'],
            'b' => ['b', '8'],
            'e' => ['e', '3'],
            'g' => ['g', '9'],
            'i' => ['i', '1', '!', '|'],
            'l' => ['l', '1', '!', '|'],
            'o' => ['o', '0'],
            's' => ['s', '5', '$'],
            't' => ['t', '7', '+'],
            'z' => ['z', '2'],
        ];

        return $map[$base] ?? [$base];
    }

    /**
     * Pomocnicza detekcja obecności znaków sugerujących obfuskację.
     */
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

    /**
     * Sprawdza, czy needle występuje w haystack jako podciąg z limitem przerw.
     */
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

    /**
     * Normalizuje Unicode i polskie znaki, bez mapowania leet.
     */
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

    /**
     * Normalizuje problematyczne znaki Unicode i homoglify do postaci porównywalnej.
     */
    private function normalizeUnicodeForBlacklist($text)
    {
        $value = (string) $text;

        // Handle Greek iota-subscript style confusables before mark stripping.
        $value = strtr($value, [
            'ͺ' => 'u',
            'ͅ' => 'u',
        ]);

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

        $value = $this->mapEmojiLetterSymbolsToAscii($value);

        // Map stylized/enclosed Latin letters commonly used to bypass blacklists.
        $value = strtr($value, [
            '∁' => 'c',
            '🅐' => 'A', '🅑' => 'B', '🅒' => 'C', '🅓' => 'D', '🅔' => 'E', '🅕' => 'F',
            '🅖' => 'G', '🅗' => 'H', '🅘' => 'I', '🅙' => 'J', '🅚' => 'K', '🅛' => 'L',
            '🅜' => 'M', '🅝' => 'N', '🅞' => 'O', '🅟' => 'P', '🅠' => 'Q', '🅡' => 'R',
            '🅢' => 'S', '🅣' => 'T', '🅤' => 'U', '🅥' => 'V', '🅦' => 'W', '🅧' => 'X',
            '🅨' => 'Y', '🅩' => 'Z',
            '🅰' => 'A', '🅱' => 'B', '🅲' => 'C', '🅳' => 'D', '🅴' => 'E', '🅵' => 'F',
            '🅶' => 'G', '🅷' => 'H', '🅸' => 'I', '🅹' => 'J', '🅺' => 'K', '🅻' => 'L',
            '🅼' => 'M', '🅽' => 'N', '🅾' => 'O', '🅿' => 'P', '🆀' => 'Q', '🆁' => 'R',
            '🆂' => 'S', '🆃' => 'T', '🆄' => 'U', '🆅' => 'V', '🆆' => 'W', '🆇' => 'X',
            '🆈' => 'Y', '🆉' => 'Z',
            'ᴬ' => 'A', 'ᴮ' => 'B', 'ᶜ' => 'C', 'ᴰ' => 'D', 'ᴱ' => 'E', 'ᶠ' => 'F',
            'ᴳ' => 'G', 'ᴴ' => 'H', 'ᴵ' => 'I', 'ᴶ' => 'J', 'ᴷ' => 'K', 'ᴸ' => 'L',
            'ᴹ' => 'M', 'ᴺ' => 'N', 'ᴼ' => 'O', 'ᴾ' => 'P', 'Q' => 'Q', 'ᴿ' => 'R',
            'ˢ' => 'S', 'ᵀ' => 'T', 'ᵁ' => 'U', 'ⱽ' => 'V', 'ᵂ' => 'W', 'ˣ' => 'X',
            'ʸ' => 'Y', 'ᶻ' => 'Z',
            'ᴀ' => 'a', 'ʙ' => 'b', 'ᴄ' => 'c', 'ᴅ' => 'd', 'ᴇ' => 'e', 'ꜰ' => 'f',
            'ɢ' => 'g', 'ʜ' => 'h', 'ɪ' => 'i', 'ᴊ' => 'j', 'ᴋ' => 'k', 'ʟ' => 'l',
            'ᴍ' => 'm', 'ɴ' => 'n', 'ᴏ' => 'o', 'ᴘ' => 'p', 'ǫ' => 'q', 'ʀ' => 'r',
            'ꜱ' => 's', 'ᴛ' => 't', 'ᴜ' => 'u', 'ᴠ' => 'v', 'ᴡ' => 'w', 'x' => 'x',
            'ʏ' => 'y', 'ᴢ' => 'z', 'ʞ' => 'k',
            // Targeted confusables from newly reported bypass variants.
            'ꉔ' => 'c',
        ]);

        // Map common Cyrillic/Greek homoglyphs to Latin lookalikes.
        $value = strtr($value, [
            'а' => 'a', 'А' => 'a', 'е' => 'e', 'Е' => 'e', 'о' => 'o', 'О' => 'o',
            'р' => 'p', 'Р' => 'p', 'с' => 'c', 'С' => 'c', 'х' => 'x', 'Х' => 'x',
            'у' => 'y', 'У' => 'y', 'к' => 'k', 'К' => 'k', 'м' => 'm', 'М' => 'm',
            'т' => 't', 'Т' => 't', 'в' => 'b', 'В' => 'b', 'н' => 'h', 'Н' => 'h',
            'г' => 'r', 'Г' => 'r',
            'і' => 'i', 'І' => 'i', 'ї' => 'i', 'Ї' => 'i', 'ј' => 'j', 'Ј' => 'j',
            'ӏ' => 'l', 'Ӄ' => 'k', 'ӄ' => 'k', 'я' => 'r', 'Я' => 'r',
            // Targeted confusables observed in production bypass attempts.
            'ひ' => 'u', '𖦹' => 'a',
            'α' => 'a', 'Α' => 'a', 'β' => 'b', 'Β' => 'b', 'δ' => 'd', 'Δ' => 'd',
            'ε' => 'e', 'Ε' => 'e', 'ι' => 'i', 'Ι' => 'i', 'κ' => 'k', 'Κ' => 'k',
            'γ' => 'r', 'Γ' => 'r',
            'ν' => 'v', 'Ν' => 'v', 'ο' => 'o', 'Ο' => 'o', 'ρ' => 'p', 'Ρ' => 'p',
            'τ' => 't', 'Τ' => 't', 'υ' => 'u', 'Υ' => 'u', 'χ' => 'x', 'Χ' => 'x',
            'ω' => 'w', 'Ω' => 'w',
        ]);

        $normalizedSpaces = preg_replace('/\p{Z}+/u', ' ', $value);
        return $normalizedSpaces === null ? $value : $normalizedSpaces;
    }

    /**
     * Mapuje emoji/symbole literowe (circled/squared/regional indicators) do ASCII.
     */
    private function mapEmojiLetterSymbolsToAscii($value)
    {
        $text = (string) $value;
        if ($text === '' || !function_exists('mb_ord')) {
            return $text;
        }

        $mapped = preg_replace_callback('/[\x{24B6}-\x{24CF}\x{24D0}-\x{24E9}\x{1F130}-\x{1F169}\x{1F1E6}-\x{1F1FF}]/u', static function ($matches) {
            $char = $matches[0] ?? '';
            if ($char === '') {
                return $char;
            }

            $cp = mb_ord($char, 'UTF-8');
            if ($cp === false) {
                return $char;
            }

            // Circled Latin capitals A-Z.
            if ($cp >= 0x24B6 && $cp <= 0x24CF) {
                return chr(ord('A') + ($cp - 0x24B6));
            }

            // Circled Latin lowercase a-z.
            if ($cp >= 0x24D0 && $cp <= 0x24E9) {
                return chr(ord('a') + ($cp - 0x24D0));
            }

            // Squared/negative-squared Latin capitals A-Z.
            if ($cp >= 0x1F130 && $cp <= 0x1F149) {
                return chr(ord('A') + ($cp - 0x1F130));
            }
            if ($cp >= 0x1F150 && $cp <= 0x1F169) {
                return chr(ord('A') + ($cp - 0x1F150));
            }

            // Regional indicator symbols (used in flag emoji sequences).
            if ($cp >= 0x1F1E6 && $cp <= 0x1F1FF) {
                return chr(ord('A') + ($cp - 0x1F1E6));
            }

            return $char;
        }, $text);

        return $mapped === null ? $text : $mapped;
    }
}
