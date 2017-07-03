<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This is the country translations table migration class.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
final class CreateCountryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_translations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');

            $table->string('country_code', 2);
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');

            $table->string('locale')->index();

            $table->unique(['country_code', 'locale']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('country_translations', function (Blueprint $table) {
            $table->dropForeign(['country_code']);
        });

        Schema::drop('country_translations');
    }
}
