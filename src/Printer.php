<?php

/*
 * This file is part of the kujira-phpunit-printer.
 *
 * (c) Cyril Barragan <cyril.barragan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sccs\PHPUnit;

/**
 * SCCS Result printer
 *
 * It overrides the defaults printer, displaying cross red mark
 * for the failing tests and a green check mark for the passing ones.
 *
 * @author Cyril Barragan <cyril.barragan@gmail.com>
 * @author Botond Szasz <botond.szasz@safechargecardservices.com>
 * @package sccs-phpunit-printer
 */
class Printer extends \PHPUnit_TextUI_ResultPrinter
{
    protected $className;
    protected $previousClassName;
    protected $previousLineLength;

    public function __construct($out = null, $verbose = false, $colors = true, $debug = false)
    {
        ob_start();
        $this->autoFlush = true;
        $this->previousLineLength = 0;
        parent::__construct($out, $verbose, $colors, $debug);
    }

    /**
     * {@inheritdoc}
     */
    protected function writeProgress($progress)
    {
        if ($this->debug) {
            return parent::writeProgress($progress);
        }

        if ($this->previousClassName !== $this->className) {
            echo "\n\t";
            $line = "\033[01;36m".$this->className."\033[0m" . ' ';
            $line .= str_pad('', 3 - (strlen($line) % 3), ' ', STR_PAD_RIGHT);
            if ($this->previousLineLength > strlen($line)) {
                $line = str_pad($line, $this->previousLineLength, ' ', STR_PAD_RIGHT);
            }
            if (strlen($line) > $this->previousLineLength) {
                $this->previousLineLength = strlen($line);
            }
            echo $line;
            $this->previousClassName = $this->className;
        }

        switch ($progress) {
            // success
            case '.':
                $output = "\033[01;32m" . '[+]' . "\033[0m";
                break;
            // failed
            case 'F':
            case "\033[41;37mF\033[0m":
                $output = "\033[01;31m" . '[-]' . "\033[0m";
                break;
            case 'I':
            case "\033[33;1mI\033[0m":
                $output = "\033[01;33m" . '[I]' . "\033[0m";
                break;
            case 'E':
            case "\033[31;1mE\033[0m":
                $output = "\033[01;31m" . '[E]' . "\033[0m";
                break;
            case 'S':
            case "\033[36;1mS\033[0m":
                $output = "\033[01;36m" . '[S]' . "\033[0m";
                break;
            default:
                $output = $progress;
        }

        echo "$output";
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->className = get_class($test);
        parent::startTest($test);
    }

    /**
     * {@inheritdoc}
     */
    protected function printDefects(array $defects, $type)
    {
        $count = count($defects);

        if ($count == 0) {
            return;
        }

        $i = 1;

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function printDefectTrace(\PHPUnit_Framework_TestFailure $defect)
    {
        $this->write($this->formatExceptionMsg($defect->getExceptionAsString()));

        $trace = \PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException()
        );

        if (!empty($trace)) {
            $this->write("\n" . $trace);
        }

        $e = $defect->thrownException()->getPrevious();

        while ($e) {
            $this->write(
                "\nCaused by\n" .
                \PHPUnit_Framework_TestFailure::exceptionToString($e). "\n" .
                \PHPUnit_Util_Filter::getFilteredStacktrace($e)
            );

            $e = $e->getPrevious();
        }
    }

    /**
     * Add colors and removes superfluous informations
     *
     * @param string $exceptionMessage
     * @return string
     */
    protected function formatExceptionMsg($exceptionMessage)
    {
        $exceptionMessage = str_replace("+++ Actual\n", '', $exceptionMessage);
        $exceptionMessage = str_replace("--- Expected\n", '', $exceptionMessage);
        $exceptionMessage = str_replace("@@ @@\n", '', $exceptionMessage);
        $exceptionMessage = preg_replace("/(Failed.*)$/m", " \033[01;31m$1\033[0m", $exceptionMessage);
//        $exceptionMessage = preg_replace("/\-+(.*)$/m", "\n \033[01;32m$1\033[0m", $exceptionMessage);
//        $exceptionMessage = preg_replace("/\++(.*)$/m", " \033[01;31m$1\033[0m", $exceptionMessage);

        return $exceptionMessage;
    }
}
