<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateContributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->boolean('valid')->default(true);
        });

        Schema::table('aid_contributions', function (Blueprint $table) {
            $table->boolean('valid')->default(true);
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->boolean('valid')->default(true);
        });

        Schema::table('aid_reimbursements', function (Blueprint $table) {
            $table->boolean('valid')->default(true);
        });

        Schema::table('ret_fun_procedures', function (Blueprint $table) {
            $table->integer('contribution_regulate_days')->default(90);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropColumn('valid');
        });

        Schema::table('aid_contributions', function (Blueprint $table) {
            $table->dropColumn('valid');
        });
        
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('valid');
        });

        Schema::table('aid_reimbursements', function (Blueprint $table) {
            $table->dropColumn('valid');
        });

        Schema::table('ret_fun_procedures', function (Blueprint $table) {
            $table->dropColumn('contribution_regulate_days');
        });
    }
}
