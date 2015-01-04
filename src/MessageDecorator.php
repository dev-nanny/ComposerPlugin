<?php

namespace DevNanny\Composer\Plugin;

class MessageDecorator
{
    const PADDING_CHARACTER = ' ';

    private $subject = <<<'TXT'
        _
     .-' '-.
    /       \
   |,-,-,-,-,|
        |   ___
        |  _)_(_
        |  (/ \) /
       (\  _\_/_
        \\/ \ / \
         \/(   )|
            )_(||
           /   \|
           |   |n
           |   / \
           |___|_|
            \|/
    jgs    _/L\_
TXT;

    private $maxLineLength = 56;

    private $subjectWidth = 18;

    final public function decorate($message)
    {
        $messageLines = array();

        $length = strlen($message);

        if ($length < $this->maxLineLength) {
            $messageLines = array($message);
        } else {
            $words = explode(' ', $message);
            $currentLine = '';
            foreach ($words as $word) {
                if (strlen($currentLine . ' ' . $word) < $this->maxLineLength) {
                    $currentLine .= ' ' . $word;
                } else {
                    array_push($messageLines, $currentLine);
                    $currentLine = $word;
                }
            }
            array_push($messageLines, $currentLine);
        }

        return $this->addMessageLines($messageLines, $this->subject);
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
        return $this->pad($line) . self::PADDING_CHARACTER . '.--------------------------------------------------------.'
        ;
    }

    /**
     * @param $line
     *
     * @return string
     */
    private function addBalloonEnd($line)
    {
        return $this->pad($line) . self::PADDING_CHARACTER . '`--------------------------------------------------------`';
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
            str_pad($message, $this->maxLineLength, self::PADDING_CHARACTER, STR_PAD_BOTH) .
        self::PADDING_CHARACTER . '|';
    }

    /**
     * @param $line
     *
     * @return string
     */
    private function pad($line)
    {
        return str_pad($line, $this->subjectWidth, self::PADDING_CHARACTER);
    }
}

/*EOF*/
