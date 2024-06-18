<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'user', 'jury'])->default('user');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

        });
        \DB::table('users')->insert(array('name' => 'Admin', 'email' => 'admin@example.com',
        'password' => '$2y$10$opPY4F14rl8ME6lZnbMOCOcfEk7mTWCAPgs9s58Px2VPDqhlIFVxm', 'role' => 'admin'
    ));
    //Admin#0987
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
