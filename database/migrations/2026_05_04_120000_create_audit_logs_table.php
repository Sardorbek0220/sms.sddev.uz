<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('action', 60);                  // login | logout | created | updated | deleted | viewed | settings_changed | ...
            $table->string('entity_type', 80)->nullable(); // User | Operator | WorkHour | ...
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('method', 8)->nullable();       // GET | POST | PUT | DELETE
            $table->string('path', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
