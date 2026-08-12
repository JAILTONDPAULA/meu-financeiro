<?php

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Bloqueio de comandos destrutivos
|--------------------------------------------------------------------------
|
| Comandos que apagam ou revertem o schema/dados do banco são interrompidos
| antes de executar. Para liberar pontualmente, defina no .env:
|
|     DB_ALLOW_DESTRUCTIVE=true
|
*/

Event::listen(function (CommandStarting $event) {
    $blocked = [
        'migrate:fresh' => 'apaga todas as tabelas e reexecuta as migrations',
        'migrate:refresh' => 'reverte e reexecuta todas as migrations',
        'migrate:reset' => 'reverte todas as migrations',
        'migrate:rollback' => 'desfaz as migrations já aplicadas',
        'db:wipe' => 'remove todas as tabelas, views e types do banco',
    ];

    if (! array_key_exists($event->command, $blocked)) {
        return;
    }

    if (filter_var(env('DB_ALLOW_DESTRUCTIVE', false), FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $output = $event->output;

    $output->writeln('');
    $output->writeln('  <fg=white;bg=red> BLOQUEADO </> O comando <options=bold>'.$event->command.'</> '.$blocked[$event->command].'.');
    $output->writeln('  Banco atual: <options=bold>'.config('database.connections.'.config('database.default').'.database').'</>');
    $output->writeln('  Para liberar, defina <options=bold>DB_ALLOW_DESTRUCTIVE=true</> no .env.');
    $output->writeln('');

    exit(1);
});
