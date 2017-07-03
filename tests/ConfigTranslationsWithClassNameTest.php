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

use Vinkla\Tests\Translator\Models\ArticleTranslationsWithClassName;

/**
 * This is the test class for translations configuration with class name of translations model.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
class ConfigTranslationsWithClassNameTest extends AbstractTestCase
{
    use TestsArticleTranslations;

    protected function setUp()
    {
        parent::setUp();
        $this->classUnderTest = ArticleTranslationsWithClassName::class;
    }
}
