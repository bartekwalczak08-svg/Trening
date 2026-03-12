<?php

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

class LanguageSelector extends Component implements BootstrapInterface
{
    public string $defaultLanguage = 'pl-PL';

    /** @var string[] */
    public array $supportedLanguages = ['pl-PL', 'en-US'];

    public string $queryParam = 'lang';
    public string $sessionKey = 'app.language';
    public string $cookieName = 'app_language';
    public int $cookieDuration = 31536000;

    public function bootstrap($app): void
    {
        $language = $this->resolveLanguage() ?? $this->defaultLanguage;
        $app->language = $language;
    }

    private function resolveLanguage(): ?string
    {
        $request = Yii::$app->request;

        $requestedLanguage = $request->get($this->queryParam);
        if (is_string($requestedLanguage) && $this->isSupported($requestedLanguage)) {
            $this->persistLanguage($requestedLanguage);

            return $requestedLanguage;
        }

        if (Yii::$app->has('session')) {
            $session = Yii::$app->session;
            $sessionLanguage = $session->get($this->sessionKey);

            if (is_string($sessionLanguage) && $this->isSupported($sessionLanguage)) {
                return $sessionLanguage;
            }
        }

        $cookieLanguage = $request->cookies->getValue($this->cookieName);
        if (is_string($cookieLanguage) && $this->isSupported($cookieLanguage)) {
            return $cookieLanguage;
        }

        return null;
    }

    private function persistLanguage(string $language): void
    {
        if (Yii::$app->has('session')) {
            Yii::$app->session->set($this->sessionKey, $language);
        }

        if (!Yii::$app->has('response')) {
            throw new InvalidConfigException('Response component is required to persist language cookie.');
        }

        Yii::$app->response->cookies->add(new Cookie([
            'name' => $this->cookieName,
            'value' => $language,
            'expire' => time() + $this->cookieDuration,
            'httpOnly' => true,
            'sameSite' => Cookie::SAME_SITE_LAX,
        ]));
    }

    private function isSupported(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }
}
