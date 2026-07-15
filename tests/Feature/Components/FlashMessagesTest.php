<?php

declare(strict_types=1);

namespace Tests\Feature\Components;

use App\View\Components\FlashMessages;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FlashMessagesTest extends TestCase
{
    #[Test]
    public function itDoesNotRenderWhenThereIsNoFlash(): void
    {
        $component = app(FlashMessages::class);

        $this->assertFalse($component->shouldRender());
    }

    #[Test]
    public function itReadsFromTheFlashArray(): void
    {
        session()->flash('flash', ['type' => 'success', 'message' => 'Operation completed.']);

        $component = app(FlashMessages::class);

        $this->assertTrue($component->shouldRender());
        $this->assertSame('success', $component->type);
        $this->assertSame('Operation completed.', $component->message);
    }

    #[Test]
    public function itReadsFromATypeSpecificSessionKey(): void
    {
        session()->flash('warning', 'This is a warning.');

        $component = app(FlashMessages::class);

        $this->assertTrue($component->shouldRender());
        $this->assertSame('warning', $component->type);
        $this->assertSame('This is a warning.', $component->message);
    }

    #[Test]
    public function itFallsBackToFlashTypeAndFlashMessage(): void
    {
        session()->flash('flash_type', 'danger');
        session()->flash('flash_message', 'Something went wrong.');

        $component = app(FlashMessages::class);

        $this->assertTrue($component->shouldRender());
        $this->assertSame('danger', $component->type);
        $this->assertSame('Something went wrong.', $component->message);
    }

    #[Test]
    public function itDefaultsToInfoWhenNoTypeIsGiven(): void
    {
        session()->flash('flash', ['message' => 'Just so you know.']);

        $component = app(FlashMessages::class);

        $this->assertSame('info', $component->type);
        $this->assertSame('Just so you know.', $component->message);
    }

    #[Test]
    public function itRendersTheView(): void
    {
        session()->flash('flash', ['type' => 'success', 'message' => 'All good.']);

        $view = $this->blade('<x-flash-messages />');

        $view->assertSee('All good.');
    }

    #[Test]
    public function itPrefersTheFlashArrayOverTypeSpecificKeys(): void
    {
        session()->flash('flash', ['type' => 'success', 'message' => 'From flash array.']);
        session()->flash('warning', 'Should be ignored.');

        $component = app(FlashMessages::class);

        $this->assertSame('success', $component->type);
        $this->assertSame('From flash array.', $component->message);
    }

    #[Test]
    public function itMapsErrorTypeToDanger(): void
    {
        session()->flash('flash', ['type' => 'error', 'message' => 'Something failed.']);

        $component = app(FlashMessages::class);

        $this->assertSame('danger', $component->type);
        $this->assertSame('Something failed.', $component->message);
    }

}
