<?php

namespace DevNanny\Composer\Plugin;

use DevNanny\Composer\Plugin\Interfaces\DecoratorInterface;

class MessageDecorator implements DecoratorInterface
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const DECORATION_FILE = 'dev-nanny.ascii';
    const MAX_LINE_LENGTH = 72;
    const PADDING_CHARACTER = ' ';
    const WORD_DELIMITER = ' ';

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function decorate($message)
    {
        $messageLines = array();

        $length = strlen($message);

        if ($length < $this->getLineLength()) {
            $messageLines = array($message);
        } else {
            $words = explode(self::WORD_DELIMITER, $message);
            $currentLine = '';
            foreach ($words as $word) {
                if (strlen($currentLine . self::WORD_DELIMITER . $word) < $this->getLineLength()) {
                    $currentLine .= self::WORD_DELIMITER . $word;
                } else {
                    array_push($messageLines, $currentLine);
                    $currentLine = $word;
                }
            }
            array_push($messageLines, $currentLine);
        }

        return $this->addMessageLines($messageLines, $this->getSubject());
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return int
     */
    private function getLineLength()
    {
        static $maximumLineLength;

        if ($maximumLineLength === null) {
            $maximumLineLength = self::MAX_LINE_LENGTH -
                $this->getSubjectWidth() -
                strlen('|' . self::PADDING_CHARACTER . self::PADDING_CHARACTER . '|')
            ;
        }

        return $maximumLineLength;
    }

    /**
     * @return string
     */
    private function getSubject()
    {
        return file_get_contents(__DIR__ . '/' . self::DECORATION_FILE);
    }

    /**
     * @return int
     */
    private function getSubjectWidth()
    {
        static $subjectWidth;

        if ($subjectWidth === null) {
            $subject = explode("\n", $this->getSubject());
            foreach ($subject as $line) {
                $length = strlen($line);
                if ($length > $subjectWidth) {
                    $subjectWidth = $length;
                }
            }
        }

        return $subjectWidth;
    }

    private function addMessageLines(array $messageLines, $subject)
    {
        $output = array();

        array_unshift($messageLines, '');
        array_push($messageLines, '');

        $subject = explode("\n", $subject);
        foreach ($subject as $lineNumber => $line) {
            if ($lineNumber !== 0 && isset($messageLines)) {
                if ($lineNumber === 1) {
                    $line = $this->addBalloonStart($line);
                } elseif (count($messageLines) === 0) {
                    unset($messageLines);
                    // Add the end of the balloon
                    $line = $this->addBalloonEnd($line);
                } elseif($lineNumber === count($subject)-1) {
                    $line = $this->addBalloonLine($line, array_shift($messageLines));
                } else {
                    $line = $this->addBalloonLine($line, array_shift($messageLines));
                }
            }
            array_push($output, $line);
        }

        if (isset($messageLines)) {
            while (count($messageLines) > 0) {
                $line = $this->addBalloonLine('', array_shift($messageLines));
                array_push($output, $line);
            }
            array_push($output, $this->addBalloonEnd(''));
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * @param $line
     *
     * @return string
     */
    private function addBalloonStart($line)
    {
        return $this->buildBalloonEdge($line, '.');
    }

    /**
     * @param $line
     *
     * @return string
     */
    private function addBalloonEnd($line)
    {
        return $this->buildBalloonEdge($line, '`');
    }

    /**
     * @param $line
     *
     * @param $message
     *
     * @return string
     */
    private function addBalloonLine($line, $message)
    {
        return $this->pad($line) . '|' . self::PADDING_CHARACTER .
            str_pad($message, $this->getLineLength(), self::PADDING_CHARACTER, STR_PAD_BOTH) .
        self::PADDING_CHARACTER . '|';
    }

    /**
     * @param $line
     *
     * @return string
     */
    private function pad($line)
    {
        return str_pad($line, $this->getSubjectWidth(), self::PADDING_CHARACTER);
    }

    /**
     * @param string $line
     * @param string $corner
     *
     * @return string
     */
    private function buildBalloonEdge($line, $corner)
    {
        return $this->pad($line) . self::PADDING_CHARACTER .
        ($corner . str_repeat('-', $this->getLineLength()) . $corner);
    }
}

/*EOF*/
