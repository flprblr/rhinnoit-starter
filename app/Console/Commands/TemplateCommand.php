<?php

namespace App\Console\Commands;

use App\Helpers\CommandHelper;
use Illuminate\Console\Command;

class TemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // CommandHelper::setLogChannel('single');
        CommandHelper::start($this);

        try {

            CommandHelper::progress($this, 'Procesing...');
            // CommandHelper::consoleProgress($this, 'Console Only');
            // CommandHelper::logProgress($this, 'Log Only');

            return CommandHelper::success($this);
        } catch (\Exception $e) {
            return CommandHelper::failure($this);
        }

    }
}
