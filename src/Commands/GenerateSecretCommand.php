<?php namespace BayAreaWebPro\JsonWebToken\Commands;

use Illuminate\Console\Command;
use BayAreaWebPro\JsonWebToken\JsonWebToken;

class GenerateSecretCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'jwt:secret';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate JWT Signature Secret';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->info("JWT Secret");
        $this->info(JsonWebToken::generateSecret(64));
    }
}
