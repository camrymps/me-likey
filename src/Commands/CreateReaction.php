<?php

namespace Camrymps\MeLikey\Commands;

use Illuminate\Console\Command;

class CreateReaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reaction:create {reaction_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new (me-likey) reaction.';

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
     * @return mixed
     */
    public function handle()
    {
        $file_contents = "<?php" . PHP_EOL . PHP_EOL .
                         "namespace Camrymps\MeLikey\Reactions;" . PHP_EOL . PHP_EOL .
                         "class " . $this->argument('reaction_name') . PHP_EOL .
                         "{" . PHP_EOL .
                         "use ReactionTrait;" . PHP_EOL .
                         "}";

        \File::put(
            \dirname(__DIR__) . '/Reactions/' . $this->argument('reaction_name') . '.php',
            $file_contents
        );
    }
}
