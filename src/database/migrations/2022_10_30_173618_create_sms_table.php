<?php

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'author_UserID');
            $table->text('message');
            $table->boolean('processed')->default(false);
            $table->timestamps();
            $table->foreignIdFor(Tenant::class, 'Tenant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
};
