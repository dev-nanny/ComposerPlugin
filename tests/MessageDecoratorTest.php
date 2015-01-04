<?php

namespace DevNanny\Composer\Plugin;

/**
 * @coversDefaultClass DevNanny\Composer\Plugin\MessageDecorator
 * @covers ::<!public>
 */
class MessageDecoratorTest extends \PHPUnit_Framework_TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var MessageDecorator */
    private $subject;

    protected function setUp()
    {
        $this->subject = new MessageDecorator();
    }
    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @covers ::decorate
     *
     * @param $message
     * @param $expected
     *
     * @dataProvider provideExpectedOutput
     */
    final public function testMessageDecoratorShouldReturnExpectedOutputWhenGivenMessageToDecorate($message, $expected)
    {
        $decorator = $this->subject;

        $actual = $decorator->decorate($message);

        $this->assertSame($expected, $actual);
    }
    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function provideExpectedOutput()
    {
        return array(
            array('message that fits on one line', <<<'TXT'
        _
     .-' '-.       .--------------------------------------------------------.
    /       \     |                                                          |
   |,-,-,-,-,|    |              message that fits on one line               |
        |   ___   |                                                          |
        |  _)_(_   `--------------------------------------------------------`
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
TXT
            ),
            array('message that spans across, not one, but two separate lines', <<<'TXT'
        _
     .-' '-.       .--------------------------------------------------------.
    /       \     |                                                          |
   |,-,-,-,-,|    |   message that spans across, not one, but two separate   |
        |   ___   |                          lines                           |
        |  _)_(_  |                                                          |
        |  (/ \) / `--------------------------------------------------------`
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
TXT
        ),
            array(
                'message that is long enough to span across not one, not two, but...' .
                ' wait for it... THREE! That`s right, three separate lines!',
                <<<'TXT'
        _
     .-' '-.       .--------------------------------------------------------.
    /       \     |                                                          |
   |,-,-,-,-,|    |    message that is long enough to span across not one,   |
        |   ___   |   not two, but... wait for it... THREE! That`s right,    |
        |  _)_(_  |                  three separate lines!                   |
        |  (/ \) /|                                                          |
       (\  _\_/_   `--------------------------------------------------------`
        \\/ \ / \
         \/(   )|
            )_(||
           /   \|
           |   |n
           |   / \
           |___|_|
            \|/
    jgs    _/L\_
TXT
            ),
            array(
                'long message but not so long that it goes beyond the image boundary' .
                str_repeat(' PADDING', 75),
                <<<'TXT'
        _
     .-' '-.       .--------------------------------------------------------.
    /       \     |                                                          |
   |,-,-,-,-,|    |   long message but not so long that it goes beyond the   |
        |   ___   |  image boundary PADDING PADDING PADDING PADDING PADDING  |
        |  _)_(_  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
        |  (/ \) /| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
       (\  _\_/_  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
        \\/ \ / \ | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
         \/(   )| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
            )_(|| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           /   \| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |   |n | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |   / \| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |___|_|| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
            \|/   |                                                          |
    jgs    _/L\_   `--------------------------------------------------------`
TXT
            ),
            array('message sooo long that it goes beyond the image boundary' .
                str_repeat(' PADDING', 138),
                <<<'TXT'
        _
     .-' '-.       .--------------------------------------------------------.
    /       \     |                                                          |
   |,-,-,-,-,|    |      message sooo long that it goes beyond the image     |
        |   ___   |     boundary PADDING PADDING PADDING PADDING PADDING     |
        |  _)_(_  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
        |  (/ \) /| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
       (\  _\_/_  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
        \\/ \ / \ | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
         \/(   )| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
            )_(|| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           /   \| | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |   |n | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |   / \| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
           |___|_|| PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
            \|/   | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
    jgs    _/L\_  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  | PADDING PADDING PADDING PADDING PADDING PADDING PADDING  |
                  |                                                          |
                   `--------------------------------------------------------`
TXT
            ),
        );
    }
}

/*EOF*/
