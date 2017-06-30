<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Tests\Translator;

use Vinkla\Tests\Translator\Models\ArticleTranslationsWithAnonymousClass;

/**
 * This is the test class for translations configuration with anonymous class.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
class ConfigTranslationsWithAnonymousClassTest extends AbstractTestCase
{
    use TestsArticleTranslations;

    protected function setUp()
    {
        parent::setUp();
        $this->classUnderTest = ArticleTranslationsWithAnonymousClass::class;
    }
}
