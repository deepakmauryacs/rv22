<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $indexes = [
                [['vendor_id'], 'vendor_products_vendor_id_index'],
                [['product_id'], 'vendor_products_product_id_index'],
                [['vendor_status'], 'vendor_products_vendor_status_index'],
                [['approval_status', 'updated_at'], 'vendor_products_approval_status_updated_at_index'],
            ];

            foreach ($indexes as [$columns, $name]) {
                if (! $this->indexExists('vendor_products', $name)) {
                    $table->index($columns, $name);
                }
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (! $this->indexExists('vendors', 'vendors_user_id_index')) {
                $table->index(['user_id'], 'vendors_user_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $indexes = [
                'vendor_products_vendor_id_index',
                'vendor_products_product_id_index',
                'vendor_products_vendor_status_index',
                'vendor_products_approval_status_updated_at_index',
            ];

            foreach ($indexes as $name) {
                if ($this->indexExists('vendor_products', $name)) {
                    $table->dropIndex($name);
                }
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if ($this->indexExists('vendors', 'vendors_user_id_index')) {
                $table->dropIndex('vendors_user_id_index');
            }
        });
    }

    /**
     * Determine if the given index already exists on the table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $tableName = $connection->getTablePrefix() . $table;

        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $result = $connection->select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
                return ! empty($result);
            case 'pgsql':
                $result = $connection->select(
                    "SELECT 1 FROM pg_indexes WHERE schemaname = ANY (current_schemas(false)) AND tablename = ? AND indexname = ?",
                    [$tableName, $indexName]
                );
                return ! empty($result);
            case 'sqlite':
                $result = $connection->select("PRAGMA index_list('{$tableName}')");
                foreach ($result as $row) {
                    $name = is_array($row) ? ($row['name'] ?? null) : ($row->name ?? null);
                    if ($name === $indexName) {
                        return true;
                    }
                }
                return false;
            default:
                return false;
        }
    }
};
