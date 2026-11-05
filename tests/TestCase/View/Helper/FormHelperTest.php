<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\View\AppView;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * App\View\Helper\FormHelper Test Case
 */
class FormHelperTest extends TestCase
{
    /**
     * A hint beside a field is shown under it. Cake would pass it through to the input as an
     * attribute, where it renders as nothing at all.
     *
     * @return void
     * @link \App\View\Helper\FormHelper::control()
     */
    public function testAHintIsShownUnderTheField(): void
    {
        $view = new AppView(new ServerRequest());

        $control = $view->Form->control('name', ['help' => 'What to call it <exactly>']);

        $this->assertStringContainsString(
            '<small class="hint">What to call it &lt;exactly&gt;</small>',
            $control,
        );
        $this->assertStringNotContainsString('help=', $control);
    }

    /**
     * A field without a hint looks as it always did.
     *
     * @return void
     * @link \App\View\Helper\FormHelper::control()
     */
    public function testAFieldWithoutAHintHasNone(): void
    {
        $view = new AppView(new ServerRequest());

        $this->assertStringNotContainsString('hint', $view->Form->control('name', ['help' => '']));
    }
}
