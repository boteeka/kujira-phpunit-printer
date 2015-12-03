<?php

namespace Sccs\PHPUnit;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * SCCS Result printer
 *
 * @author  Botond Szasz <botond.szasz@safechargecardservices.com>
 * @package sccs-phpunit-printer
 */
class Printer extends \PHPUnit_TextUI_ResultPrinter
{
    protected $className;
    protected $previousClassName;
    protected $previousLineLength;
    protected $outputFormatter;


    /**
     * Printer constructor.
     *
     * @param null          $out
     * @param boolean|true  $verbose
     * @param boolean|true  $colors
     * @param boolean|false $debug
     */
    public function __construct($out = null, $verbose = true, $colors = true, $debug = false)
    {
        $this->autoFlush          = true;
        $this->previousLineLength = 0;
        $this->outputFormatter    = new OutputFormatter(
            true,
            [
                'bullet'     => new OutputFormatterStyle('yellow'),
                'testclass'  => new OutputFormatterStyle('cyan'),
                'test'       => new OutputFormatterStyle('magenta'),
                'sep'        => new OutputFormatterStyle('white'),
                'success'    => new OutputFormatterStyle('black', 'green'),
                'failure'    => new OutputFormatterStyle('black', 'red'),
                'incomplete' => new OutputFormatterStyle('black', 'white'),
                'skipped'    => new OutputFormatterStyle('black', 'cyan'),
                'stats'      => new OutputFormatterStyle('blue'),

            ]
        );
        parent::__construct($out, $verbose, $colors, $debug);
        ob_start();
    }


    /**
     * {@inheritdoc}
     */
    protected function writeProgress($progress)
    {
        $outputFormatter = $this->outputFormatter;

        if ($this->debug) {
            return parent::writeProgress($progress);
        }

        switch ($progress) {
            // success
            case '.':
                $output = '<success>[PASS]</success>';
                break;
            // failed
            case 'F':
            case "\033[41;37mF\033[0m":
                $output = '<failure>[FAIL]</failure>';
                break;
            case 'I':
            case "\033[33;1mI\033[0m":
                $output = '<incomplete>[INCOMPLETE]</incomplete>';
                break;
            case 'E':
            case "\033[31;1mE\033[0m":
                $output = '<error>[ERROR]</error>';
                break;
            case 'S':
            case "\033[36;1mS\033[0m":
                $output = '<skipped>[SKIPPED]</skipped>';
                break;
            default:
                $output = $progress;
        }

        $this->write($outputFormatter->format($output));
    }


    /**
     * {@inheritdoc}
     */
    public function write($buffer)
    {
        parent::write($buffer);
        @ob_flush();
    }


    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $outputFormatter = $this->outputFormatter;

        parent::startTest($test);
        $this->className = get_class($test);
        $message         = $outputFormatter->format(
            sprintf(
                "\n <bullet>></bullet> <testclass>%s</testclass><sep>:</sep><test>%s</test> ",
                $this->className,
                $test->getName()
            )
        );
        $this->write($message);
    }


    /**
     * {@inheritdoc}
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        $outputFormatter = $this->outputFormatter;

        $numAssertions = $test->getNumAssertions();

        $message = $outputFormatter->format(
            sprintf(
                ' <stats>(assertions: %s; time: %01.2f sec.)</stats>',
                $numAssertions,
                $time
            )
        );

        $this->write($message);
    }
}
