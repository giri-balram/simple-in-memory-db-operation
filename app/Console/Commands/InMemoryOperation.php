<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DatabaseOperationController;

class InMemoryOperation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inmemory:dboperation';
    protected $dbOperation;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database Opeartion for most common commands GET, SET, UNSET and NUMEQUALTO';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct( DatabaseOperationController $dbOperation )
    {
        parent::__construct();
        $this->dbOperation = $dbOperation;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dbOperation->readCommands();
    }
}
