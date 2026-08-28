<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Testing\TestResponse;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Without this the test client sends no cookies on a JSON request at
         * all — it models fetch() without credentials. The site's own AJAX is
         * jQuery on the same origin, which does send them, and the cart and
         * the wish list are both recognised by one, so a test without this
         * would be testing something the site never does.
         */
        $this->withCredentials();
    }

    /**
     * Carries whatever the response set into the requests that follow.
     *
     * Cookies are how the shop recognises a visitor who is not signed in —
     * their cart and their wish list both hang off one. A browser sends back
     * what it was given; the test client does not, so without this every
     * request would look like a new person and nothing spanning more than one
     * request could be tested at all.
     *
     * The values are passed on exactly as they came, still encrypted: the
     * middleware decrypts them on the way in, which is what the application
     * expects to read.
     */
    protected function keepCookies(TestResponse $response): TestResponse
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $value = $cookie->getValue();

            if ($value === null || $value === '') {
                unset($this->defaultCookies[$cookie->getName()]);

                continue;
            }

            // The test client encrypts what it is given, so it has to be given
            // the plain value — the response carries the encrypted one.
            try {
                $value = CookieValuePrefix::remove(decrypt($value, false));
            } catch (Throwable) {
                // Not encrypted: carried through untouched.
            }

            $this->defaultCookies[$cookie->getName()] = $value;
        }

        return $response;
    }
}
