<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_referrer_and_source_tag_to_clicks.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clicks', function (Blueprint $t) {
            if (!Schema::hasColumn('clicks','referrer')) {
                $t->string('referrer', 2048)->nullable()->after('ip');
            }
            if (!Schema::hasColumn('clicks','source_tag')) {
                $t->string('source_tag', 64)->nullable()->after('referrer');
            }
            // opcional: host del referrer para indexar rápido
            if (!Schema::hasColumn('clicks','referrer_host')) {
                $t->string('referrer_host', 191)->nullable()->after('referrer');
                $t->index(['referrer_host']);
            }
        });
    }

    public function down(): void {
        Schema::table('clicks', function (Blueprint $t) {
            if (Schema::hasColumn('clicks','referrer_host')) {
                $t->dropIndex(['referrer_host']);
                $t->dropColumn('referrer_host');
            }
            if (Schema::hasColumn('clicks','source_tag')) {
                $t->dropColumn('source_tag');
            }
            if (Schema::hasColumn('clicks','referrer')) {
                $t->dropColumn('referrer');
            }
        });
    }
};
