<?php

use Illuminate\Database\Schema\Blueprint;
use Flarum\Database\Migration;

return Migration::createTable(
    'redemption_codes',
    function (Blueprint $table) {
        $table->increments('id');
        $table->string('code', 32)->unique();
        $table->string('type', 20)->default('group_time'); 
        $table->text('payload'); // 存 {"groupId":10, "days":30}
        $table->boolean('is_used')->default(false);
        $table->integer('used_by')->unsigned()->nullable();
        $table->dateTime('used_at')->nullable();
        $table->timestamps();
        
        $table->foreign('used_by')->references('id')->on('users')->onDelete('cascade');
    }
);