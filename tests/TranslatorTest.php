<?php

/*
 * This file is part of Laravel AbstractTranslator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Tests\Translator;

use PHPUnit_Framework_TestCase;
use Vinkla\Translator\Contracts\TranslatableInterface as TranslatableContract;
use Vinkla\Translator\TranslatableTrait;

/**
 * This is the translator test class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class TranslatorTest extends PHPUnit_Framework_TestCase
{
    protected $foo;

    public function setUp()
    {
        $this->foo = new Foo();
    }

    public function testTranslatorTrait()
    {
        $this->assertTrue(method_exists($this->foo, 'translate'));
        $this->assertTrue(method_exists($this->foo, 'translations'));
    }

    public function testTranslatedAttributes()
    {
        $this->assertTrue(is_array($this->foo->translatedAttributes));
    }

    /**
     * @expectedException Exception
     */
    public function testException()
    {
        $this->foo->translator = null;
        $this->foo->translate()->title;
    }
}

class Foo implements TranslatableContract
{
    use TranslatableTrait;

    public $translator = 'FooTranslation';

    public $translatedAttributes = ['one', 'two'];
}

class FooTranslation
{
}
