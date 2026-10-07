<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RenameTasksTableForStructureRefactor extends Migration
{
    public function up()
    {
        Schema::rename('tasks', 'tasks_old');
    }

    public function down()
    {
        Schema::rename('tasks_old', 'tasks');
    }
}
