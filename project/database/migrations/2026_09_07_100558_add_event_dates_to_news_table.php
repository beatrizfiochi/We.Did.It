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
        Schema::table('news', function (Blueprint $table) {
            $table->date('event_start_date')->nullable()->after('description');
            $table->date('event_end_date')->nullable()->after('event_start_date');
        });

        // as que já existem não têm por onde saber a data do evento: fica a da
        // submissão, que é a melhor aproximação disponível
        DB::table('news')->whereNull('event_start_date')->update([
            'event_start_date' => DB::raw('DATE(created_at)'),
        ]);

        // só agora pode passar a obrigatória: com linhas a null o MySQL recusava
        Schema::table('news', function (Blueprint $table) {
            $table->date('event_start_date')->nullable(false)->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['event_start_date', 'event_end_date']);
        });
    }
};
