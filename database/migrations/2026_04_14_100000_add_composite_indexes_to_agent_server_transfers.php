<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->index(['server_id', 'created_at'], 'idx_ast_server_created');
            $table->index(['server_id', 'status', 'created_at'], 'idx_ast_server_status_created');
            $table->index(['status', 'created_at'], 'idx_ast_status_created');
            $table->index(['source_node_id', 'status', 'created_at'], 'idx_ast_source_status_created');
            $table->index(['dest_node_id', 'status', 'created_at'], 'idx_ast_dest_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->dropIndex('idx_ast_server_created');
            $table->dropIndex('idx_ast_server_status_created');
            $table->dropIndex('idx_ast_status_created');
            $table->dropIndex('idx_ast_source_status_created');
            $table->dropIndex('idx_ast_dest_status_created');
        });
    }
};
