<?php

namespace AnourValar\LaravelAtom\Tests\Http\Middleware\Web;

use AnourValar\LaravelAtom\Http\Middleware\Web\Locale;
use AnourValar\LaravelAtom\Tests\AbstractSuite;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Symfony\Component\HttpFoundation\Response;

class LocaleTest extends AbstractSuite
{
    /**
     * Locale is taken from the request input when it is supported.
     *
     * @return void
     */
    public function test_locale_from_request(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'ru']);
        $this->handle($request);

        $this->assertSame('ru', \App::getLocale());
        $this->assertSame('ru', $request->session()->get('locale'));
    }

    /**
     * Request locale has priority over the session value and overwrites it.
     *
     * @return void
     */
    public function test_request_locale_has_priority_over_session(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'en'], ['locale' => 'ru']);
        $this->handle($request);

        $this->assertSame('en', \App::getLocale());
        $this->assertSame('en', $request->session()->get('locale'));
    }

    /**
     * Locale falls back to the session value when the request value is missing/unsupported.
     *
     * @return void
     */
    public function test_locale_from_session(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'unsupported'], ['locale' => 'ru']);
        $this->handle($request);

        $this->assertSame('ru', \App::getLocale());
        // Session value is read, not rewritten.
        $this->assertSame('ru', $request->session()->get('locale'));
    }

    /**
     * Locale is taken from the user preference when request and session do not resolve it.
     *
     * @return void
     */
    public function test_locale_from_user_preference(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'unsupported'], ['locale' => 'unsupported'], $this->user('ru'));
        $this->handle($request);

        $this->assertSame('ru', \App::getLocale());
        // The user-preference branch does not persist anything to the session.
        $this->assertSame('unsupported', $request->session()->get('locale'));
    }

    /**
     * The user preference is applied even when it is not listed in the config.
     *
     * @return void
     */
    public function test_user_preference_is_not_validated_against_config(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest([], [], $this->user('de'));
        $this->handle($request);

        $this->assertSame('de', \App::getLocale());
    }

    /**
     * Falls back to the default locale when nothing else resolves.
     *
     * @return void
     */
    public function test_fallback_locale(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'de']);

        $request = $this->makeRequest();
        $this->handle($request);

        $this->assertSame('de', \App::getLocale());
    }

    /**
     * Falls back to the default locale when the user has an empty preference.
     *
     * @return void
     */
    public function test_fallback_when_user_preference_is_empty(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'de']);

        $request = $this->makeRequest([], [], $this->user(null));
        $this->handle($request);

        $this->assertSame('de', \App::getLocale());
    }

    /**
     * A non-string request locale (e.g. an array) is ignored and treated as empty.
     *
     * @return void
     */
    public function test_non_string_request_locale_is_ignored(): void
    {
        config(['models.user.locale' => ['en' => [], 'ru' => []], 'app.fallback_locale' => 'de']);

        $request = $this->makeRequest(['locale' => ['ru']]);
        $this->handle($request);

        $this->assertSame('de', \App::getLocale());
        $this->assertNull($request->session()->get('locale'));
    }

    /**
     * A custom config path may be provided as a middleware parameter.
     *
     * @return void
     */
    public function test_custom_config_path(): void
    {
        config(['custom.locales' => ['fr' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'fr']);
        $this->handle($request, 'custom.locales');

        $this->assertSame('fr', \App::getLocale());
        $this->assertSame('fr', $request->session()->get('locale'));
    }

    /**
     * A missing config path does not raise an error and falls back to the default.
     *
     * @return void
     */
    public function test_missing_config_path_falls_back(): void
    {
        config(['app.fallback_locale' => 'en']);

        $request = $this->makeRequest(['locale' => 'ru']);
        $this->handle($request, 'does.not.exist');

        $this->assertSame('en', \App::getLocale());
    }

    /**
     * The response produced by the next handler is returned untouched.
     *
     * @return void
     */
    public function test_returns_next_response(): void
    {
        config(['models.user.locale' => ['en' => []], 'app.fallback_locale' => 'en']);

        $request = $this->makeRequest();
        $response = (new Locale())->handle($request, fn ($req) => new Response('passed', 201));

        $this->assertSame('passed', $response->getContent());
        $this->assertSame(201, $response->getStatusCode());
    }

    /**
     * Run the middleware against a request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $configPath
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function handle(Request $request, ?string $configPath = null): Response
    {
        $next = fn ($req) => new Response('ok');

        if ($configPath === null) {
            return (new Locale())->handle($request, $next);
        }

        return (new Locale())->handle($request, $next, $configPath);
    }

    /**
     * Build a request with an attached session and an optional user.
     *
     * @param array $query
     * @param array $session
     * @param object|null $user
     * @return \Illuminate\Http\Request
     */
    private function makeRequest(array $query = [], array $session = [], ?object $user = null): Request
    {
        $request = Request::create('/', 'GET', $query);

        $store = new Store('test_session', new ArraySessionHandler(120));
        foreach ($session as $key => $value) {
            $store->put($key, $value);
        }
        $request->setLaravelSession($store);

        if ($user !== null) {
            $request->setUserResolver(fn () => $user);
        }

        return $request;
    }

    /**
     * Build a user implementing the locale-preference contract.
     *
     * @param string|null $locale
     * @return \Illuminate\Contracts\Translation\HasLocalePreference
     */
    private function user(?string $locale): HasLocalePreference
    {
        return new class ($locale) implements HasLocalePreference
        {
            public function __construct(private ?string $locale)
            {
            }

            public function preferredLocale()
            {
                return $this->locale;
            }
        };
    }
}
