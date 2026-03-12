<?php

namespace tests\unit\models;

use app\models\ContactForm;
use yii\mail\MessageInterface;

class ContactFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    public function testEmailIsSentOnContact()
    {
        $model = new ContactForm();

        $model->attributes = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'subject' => 'very important letter subject',
            'body' => 'body of current message',
            'verifyCode' => 'testme',
        ];

        verify($model->contact('admin@example.com'))->notEmpty();

        // using Yii2 module actions to check email was sent
        $this->tester->seeEmailIsSent();

        /** @var MessageInterface $emailMessage */
        $emailMessage = $this->tester->grabLastSentEmail();
        verify($emailMessage)->instanceOf('yii\mail\MessageInterface');
        verify($emailMessage->getTo())->arrayHasKey('admin@example.com');
        verify($emailMessage->getFrom())->arrayHasKey('noreply@example.com');
        verify($emailMessage->getReplyTo())->arrayHasKey('tester@example.com');
        verify($emailMessage->getSubject())->equals('very important letter subject');
        verify($emailMessage->toString())->stringContainsString('body of current message');
    }

    public function testBlacklistAllowsInnocentKurkaPhrase()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'wody galon kurka wodna';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksGreekHomoglyphVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = "K\u{03C5}rwa";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksDigitAndSymbolObfuscation()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'k-u-r-123-w-a';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksYebAtCVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['jebac'];

        try {
            $model = new ContactForm();
            $model->body = 'yeb@ć';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksQurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'qurwa';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksCurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'curwa';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksStylizedCwelVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = '🅒wᴇ𝘓';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksMixedScriptCwelVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = "\u{0441}w\u{0435}\u{04CF}";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksEnclosedKurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = '🅚🅤🅡🅦🅐';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksZeroWidthSplitCwelVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = "c\u{200B}w\u{200D}e\u{FEFF}l";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksComplementSymbolCweluVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwelu'];

        try {
            $model = new ContactForm();
            $model->body = '∁welu';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksGreekGammaInKurwoVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwo'];

        try {
            $model = new ContactForm();
            $model->body = 'kuΓwo';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksMixedScriptUnknownLetterSubstitution()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = "ku\u{0531}wa";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksReportedMixedScriptKurwaBypass()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'Ӄひя🆆𖦹';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksMixedScriptsAndSymbolKurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'KひЯ🆆𖦹';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksMultiScriptKurwaVariantWithoutLatin()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'Ӄひя𝕎𖦹';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksReportedTurnedKMixedScriptKurwaBypass()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'ʞひя🆆𖦹';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksWildcardOnlyMixedScriptForAnyBlockedWord()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = 'ʗひя𖦹';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksReportedMixedConfusableCweluBypass()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwelu'];

        try {
            $model = new ContactForm();
            $model->body = 'ꉔω|ℰ𝓛u';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksSeparatorHeavyMixedScriptCweluBypass()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwelu'];

        try {
            $model = new ContactForm();
            $model->body = 'ꓚ-ω-|-ℰ-𝓛-u';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistAllowsPureSingleScriptCyrillicPhrase()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'это просто обычное сообщение';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistAllowsPureNonLatinPhrase()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'שלום עולם';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksTrailingGreekYpogegrammeniVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwelu'];

        try {
            $model = new ContactForm();
            $model->body = "cwel\u{037A}";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksTrailingGreekYpogegrammeniInKurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = "kurw\u{037A}";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksRegionalIndicatorEmojiLettersVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = '🇨🇼🇪🇱';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksCircledEmojiLettersVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = 'ⓒⓦⓔⓛ';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksSpacedLetterVariantWithInsertedWord()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['zjebie'];

        try {
            $model = new ContactForm();
            $model->body = 'ty z j e siema b i e';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksCw31AsCwelVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['cwel'];

        try {
            $model = new ContactForm();
            $model->body = 'ćw31';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksLeetSubstitutionForAnyBlockedWord()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['szmata'];

        try {
            $model = new ContactForm();
            $model->body = '$2m474';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistAllowsPositivePhraseWithEmoji()
    {
        $model = new ContactForm();
        $model->body = 'Super jestem giga chad ❤😍😍😍😍';

        verify($model->validate(['body']))->true();
        verify($model->getErrors('body'))->empty();
    }

    public function testBlacklistDoesNotBlockNeutralWordContainingShortRoot()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['zyd'];

        try {
            $model = new ContactForm();
            $model->body = 'Paweł pochodzi z rodziny żydowskiej.';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistDoesNotBlockNeutralWordWithShortRootAndDiacritics()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['lodz'];

        try {
            $model = new ContactForm();
            $model->body = 'To dobra łódzka inicjatywa społeczna.';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistDoesNotBlockNeutralLongerWordContainingShortToken()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['deb'];

        try {
            $model = new ContactForm();
            $model->body = 'Bierzemy udział w debacie publicznej.';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistAllowsReportedSentenceAboutFamilyBackground()
    {
        $model = new ContactForm();
        $model->body = 'Paweł O. (O-block) pochodzi z rodziny żydowskich bankierów.';

        verify($model->validate(['body']))->true();
        verify($model->getErrors('body'))->empty();
    }

    public function testBlacklistAllowsSymbolAlphabetSequenceWithoutProfanity()
    {
        $model = new ContactForm();
        $model->body = '🆎 🆑 🆒 🆓 🆔 🆕 🆖 🆗 🆙 🆚🅰 🅱 🅾 🅿🄰 🄱 🄲 🄳 🄴 🄵 🄶 🄷 🄸 🄹 🄺 🄻 🄼 🄽 🄾 🄿 🅀 🅁 🅂 🅃 🅄 🅅 🅆 🅇 🅈 🅉🅐 🅑 🅒 🅓 🅔 🅕 🅖 🅗 🅘 🅙 🅚 🅛 🅜 🅝 🅞 🅟 🅠 🅡 🅢 🅣 🅤 🅥 🅦 🅧 🅨 🅩🅰 🅱 🅲 🅳 🅴 🅵 🅶 🅷 🅸 🅹 🅺 🅻 🅼 🅽 🅾 🅿 🆀 🆁 🆂 🆃 🆄 🆅 🆆 🆇 🆈 🆉';

        verify($model->validate(['body']))->true();
        verify($model->getErrors('body'))->empty();
    }
}
