<?php

namespace Ongoing\Inventarios\Console;

use Illuminate\Console\Command;

class DemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventarios:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commando de demostracion';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info("Proceso finalizado");

        return 0;
    }
}
