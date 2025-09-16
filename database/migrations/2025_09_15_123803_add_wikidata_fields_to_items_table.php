<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('wikidata_qid')->nullable()->after('location');
            $table->text('image_url')->nullable()->after('wikidata_qid');
            
            $table->index('wikidata_qid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['wikidata_qid']);
            $table->dropColumn(['wikidata_qid', 'image_url']);
        });
    }
};