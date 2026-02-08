<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Config\Database;

require __DIR__ . '/../vendor/autoload.php';

new Database();

if (!Capsule::schema()->hasTable('users')) {
    Capsule::schema()->create('users', function ($table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
    echo "Users table created.\n";
} else {
    echo "Users table already exists.\n";
}

if (!Capsule::schema()->hasTable('associados')) {
    Capsule::schema()->create('associados', function ($table) {
        $table->increments('id');
        $table->string('cpf')->unique();
        $table->string('nome');
        $table->string('cidade');
        $table->string('estado');
        $table->string('telefone')->nullable();
        $table->string('email');
        $table->timestamps();
    });
    echo "Associados table created.\n";
} else {
    echo "Associados table already exists.\n";
}

if (!Capsule::schema()->hasTable('logs')) {
    Capsule::schema()->create('logs', function ($table) {
        $table->id();
        $table->string('method');
        $table->string('url');
        $table->integer('status');
        $table->string('user_id')->nullable();
        $table->text('details')->nullable();
        $table->timestamps();
    });
    echo "Logs table created.\n";
} else {
    echo "Logs table already exists.\n";
}
