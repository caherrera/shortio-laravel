<?php

namespace Shortio\Laravel\Commands\Console;

use Illuminate\Console\Command;
use Shortio\Laravel\Model\Link;

class Create extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shortio:create 
        {domain : example \'mydomain.com\'} 
        {originalURL : example "https://google.com"}
        
        {--path= : Set path value to new link }
        {--title= : Set title value to new link }
        {--icon= : Set icon value to new link }
        {--archived= : Set archived value to new link }
        {--iphoneURL= : Set iphoneURL value to new link }
        {--androidURL= : Set androidURL value to new link }
        {--splitURL= : Set splitURL value to new link }
        {--expiresAt= : Set expiresAt value to new link }
        {--expiredURL= : Set expiredURL value to new link }
        {--redirectType= : Set redirectType value to new link }
        {--cloaking= : Set cloaking value to new link }
        {--source= : Set source value to new link }
        {--AutodeletedAt= : Set AutodeletedAt value to new link }
        
    ';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a short link on Short.io';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $data = collect($this->arguments())
            ->merge($this->options())
            ->filter(
                function ($value) {
                    return ! empty($value);
                }
            )
            ->except(['command']);
        $link = new Link();
        $link->fill($data->toArray())->save();

        $this->info("Short Url successfully created {$link->shortURL}");
        $this->info($link);
    }
}