<?php

use App\Models\Central\User;
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
        Schema::create('central_user_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('credential_id', 256);
            $table->string('credential_name', 256);
            $table->longText('credential');
            $table->foreignIdFor(User::class);
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
        Schema::dropIfExists('central_user_credentials');
    }
};
