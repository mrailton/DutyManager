<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FlashMessages extends Component
{
    private const STYLES = [
        'success' => ['border' => 'border-green-400', 'bg' => 'bg-green-50', 'text' => 'text-green-800', 'icon' => 'text-green-400'],
        'info' => ['border' => 'border-blue-400', 'bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'icon' => 'text-blue-400'],
        'warning' => ['border' => 'border-yellow-400', 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'icon' => 'text-yellow-400'],
        'danger' => ['border' => 'border-red-400', 'bg' => 'bg-red-50', 'text' => 'text-red-800', 'icon' => 'text-red-400'],
    ];

    public string $type;

    public string $message;

    public string $border;

    public string $bg;

    public string $text;

    public string $icon;

    public function __construct()
    {
        $flash = session('flash');
        $type = $flash['type'] ?? null;
        $message = $flash['message'] ?? null;

        if (null === $message) {
            foreach (['success', 'info', 'warning', 'danger'] as $t) {
                if ($msg = session($t)) {
                    $type = $t;
                    $message = $msg;
                    break;
                }
            }
        }

        if (null === $message) {
            $type = session('flash_type', 'info');
            $message = session('flash_message');
        }

        $this->type = $type ?? 'info';
        $this->message = $message ?? '';

        $style = self::STYLES[$this->type] ?? self::STYLES['info'];
        $this->border = $style['border'];
        $this->bg = $style['bg'];
        $this->text = $style['text'];
        $this->icon = $style['icon'];
    }

    public function shouldRender(): bool
    {
        return '' !== $this->message;
    }

    public function render(): View|Closure|string
    {
        return view('components.flash-messages');
    }
}
