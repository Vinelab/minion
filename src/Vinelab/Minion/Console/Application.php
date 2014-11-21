<?php namespace Vinelab\Minion\Console;

use Vinelab\Minion\Console\Commands\RunCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Application extends BaseApplication {

    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('minion', '@git-comment@');

        $this->add(new RunCommand());
    }

    /**
     * Get the all the version details.
     *
     * @return string
     */
    public function getLongVersion()
    {
        $version = parent::getLongVersion().' by <comment>Vinelab</comment>';
        $commit  = '@git-commit@';

        if ('@'.'git-commit@' !== $commit) {
            $version .= ' ('.substr($commit, 0, 7).')';
        }

        return $version;
    }
}
