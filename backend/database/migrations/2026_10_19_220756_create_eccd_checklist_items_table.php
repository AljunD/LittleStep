
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('eccd_checklist_items')) {
            Schema::create('eccd_checklist_items', function (Blueprint $table) {
                $table->id();

                $table->enum('domain', [
                    'gross_motor',
                    'fine_motor',
                    'self_help',
                    'receptive_language',
                    'expressive_language',
                    'cognitive',
                    'social_emotional',
                ]);

                $table->string('activity');
                $table->text('materials_and_procedure')->nullable();
                $table->unsignedSmallInteger('item_number')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eccd_checklist_items');
    }
};
