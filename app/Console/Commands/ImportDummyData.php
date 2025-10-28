<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fastkart:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dummy data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Data tables from database.');
        $sql = public_path('db.sql');
        $data = file_get_contents($sql);
        DB::unprepared($data);

        if (env('DUMMY_IMAGES_URL')) {
            if (function_exists('imIMgDuy')) {
                $this->info('Downloading dummy images from server, wait 5-10 minutes depends on your internet.');
                imIMgDuy();
            }
        }

        $this->call('storage:link');
    }
}
