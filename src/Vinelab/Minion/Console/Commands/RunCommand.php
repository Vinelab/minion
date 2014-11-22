<?php namespace Vinelab\Minion\Console\Commands;

use Vinelab\Minion\Minion;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class RunCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WAMP router';

    /**
     * The available command options.
     *
     * @var array
     * @since 1.3.0
     */
    protected $options = array(
        'realm',
        'host',
        'port',
        'register',
    );

    /**
     * Run the command. Executed immediately.
     *
     * @return int CLI tool exit code.
     */
    public function fire()
    {
        $options = [];

        if ($this->option('realm')) {
            $options['realm'] = $this->option('realm');
        }

        if ($this->option('host')) {
            $options['host'] = $this->option('host');
        }

        if ($this->option('port')) {
            $options['port'] = $this->option('port');
        }

        $m = new Minion();

        if ($this->option('register')) {
            foreach ($this->option('register') as $provider) {
                $m->register($provider);
            }
        }

        $m->run($options);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['realm', null, InputOption::VALUE_OPTIONAL, 'Specify WAMP realm to be used'],
            ['host', null, InputOption::VALUE_OPTIONAL, 'Specify the server host'],
            ['port', null, InputOption::VALUE_OPTIONAL, 'Specify the server port'],
            ['register', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Register provider classes']
        ];
    }
}
