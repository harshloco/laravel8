<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableStockAddCompositeKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->index(
                ['product_id', 'production_date'],
                'stocks_product_id_production_date');
            $table->unique(['product_id', 'production_date'], 'unique_stocks_product_id_production_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_product_id_production_date');
            $table->dropUnique(['unique_stocks_product_id_production_date']);
        });
    }
}
