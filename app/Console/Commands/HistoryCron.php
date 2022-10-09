<?php

namespace App\Console\Commands;

use App\Models\History;
use App\Models\User;
use Illuminate\Console\Command;

class HistoryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user_list = User::all();

        foreach ($user_list as $ul) {
            $history = new History;
            $history->user_id = $ul->id;
            $history->balance = $ul->balance;
            $history->save();
        }
        return Command::SUCCESS;
    }
}
