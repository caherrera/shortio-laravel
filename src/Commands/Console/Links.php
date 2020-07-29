<?php

namespace Shortio\Laravel\Commands\Console;

use Illuminate\Console\Command;
use Shortio\Laravel\Model\Link;

class Links extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shortio:links
        {--domain : example \'mydomain.com\'}
        {--originalURL : example "https://google.com"}
        {--path= : Set path value to new link }
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
    protected $description = 'get a list of short links on Short.io';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $data   = collect($this->arguments())
            ->merge($this->options())
            ->filter(
                function ($value) {
                    return ! empty($value);
                }
            )
            ->except(['command']);
        $links  = collect(Link::all());
        $header = collect($links->first()->getAttributes())->only(['id','path','title','originalURL','expiresAt','createdAt'])->keys()->all();
        $this->table(
            $header,
            $links->map(
                function ($link) use ($header) {
                    return collect($link->toArray())->only($header);
                }
            )
        );
    }
}
