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

use Vinkla\Tests\Translator\Models\Article;

/**
 * This is the translator test class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class ConfigTranslationsWithRelationTest extends AbstractTestCase
{
    use TestsArticleTranslations;

    protected function setUp()
    {
        parent::setUp();
        $this->classUnderTest = Article::class;
    }
}
