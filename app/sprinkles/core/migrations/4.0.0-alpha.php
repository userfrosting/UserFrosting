<?php

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;

    /**
     * Keeps track of throttleable requests.
     */
    if (!$schema->hasTable('throttles')) {
        $schema->create('throttles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('ip')->nullable();
            $table->text('request_data')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->index('type');
            $table->index('ip');
        });
        echo "Created table 'throttles'..." . PHP_EOL;
    } else {
        echo "Table 'throttles' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Table for database sessions.
     */
    if (!$schema->hasTable('sessions')) {
        $schema->create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
        });
        echo "Created table 'sessions'..." . PHP_EOL;
    } else {
        echo "Table 'sessions' already exists.  Skipping..." . PHP_EOL;
    }