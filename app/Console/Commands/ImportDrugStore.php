<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;

class ImportDrugStore extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drug:import {--to=all : mysql | pgsql | all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import DrugStore from SQLite to MySQL and/or PostgreSQL';

    /**
     * Execute the console command.
     */

private function importTable($sourceTable, $targetTable, $connection, $map = null)
{
    $rows = DB::connection('sqlite_old')->table($sourceTable)->get();

    foreach ($rows as $row) {
        $data = (array) $row;

        // timestamps سازگار
        if (Schema::connection($connection)->hasColumn($targetTable, 'created_at')) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        if ($map) {
            $data = $map($data);
        }

        DB::connection($connection)->table($targetTable)->insert($data);
    }
}

public function handle()
{
    $target = $this->option('to');

    $targets = match ($target) {
        'mysql' => ['mysql'],
        'pgsql' => ['pgsql_remote'],
        default => ['mysql', 'pgsql_remote'],
    };

    foreach ($targets as $connection) {

        $this->info("🚀 Importing into {$connection}");

        DB::connection($connection)->transaction(function () use ($connection) {

            Schema::connection($connection)->disableForeignKeyConstraints();

            // 1️⃣ مستقل‌ها - نام جدول در SQLite و MySQL یکسان است
            $this->importTable('goroh_daroei', 'goroh_daroei', $connection);
            $this->importTable('goroh_darmani', 'goroh_darmani', $connection);
            $this->importTable('goroh_darmani_detail', 'goroh_darmani_detail', $connection);
            $this->importTable('shekl_daro', 'shekl_daro', $connection);
            $this->importTable('sherkat_daroei', 'sherkat_daroei', $connection);
            $this->importTable('sherkat_varedkonande', 'sherkat_varedkonande', $connection);

            // 2️⃣ وابسته - نام جدول در SQLite: 'DrugInfo'، در MySQL: 'drug_info'
            $this->importTable('DrugInfo', 'drug_info', $connection, function ($data) {
                return [
                    'cod' => $data['cod'],
                    'goroh_darmani_detail_cod' => $data['goroh_darmani_detail_cod'],
                    'goroh_daroei_cod' => $data['goroh_daroei_cod'],
                    'goroh_farmakologic_cod' => $data['goroh_farmakologic_cod'],
                    'goroh_darmani_cod' => $data['goroh_darmani_cod'],
                    'nam_fa' => $data['nam_fa'],
                    'nam_en' => $data['nam_en'],
                    'mavaredmasraf' => $data['mavaredmasraf'],
                    'meghdarmasraf' => $data['meghdarmasraf'],
                    'masrafdarhamelegi' => $data['masrafdarhamelegi'],
                    'masrafdarshirdehi' => $data['masrafdarshirdehi'],
                    'manemasraf' => $data['manemasraf'],
                    'avarez' => $data['avarez'],
                    'tadakhol' => $data['tadakhol'],
                    'mekanismtasir' => $data['mekanismtasir'],
                    'nokte' => $data['nokte'],
                    'hoshdar' => $data['hoshdar'],
                    'sharayetnegahdari' => $data['sharayetnegahdari'],
                    'ashkal_daroei' => $data['ashkal_daroei'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            Schema::connection($connection)->enableForeignKeyConstraints();
        });

        $this->info("✅ {$connection} done");
    }

    $this->info("🎉 Import ALL tables finished successfully");
}

}
