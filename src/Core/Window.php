<?php

namespace TerminalUI\Core;

use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;
use TerminalUI\Events\{Event, KeyEvent};

/**
 * Window Component
 *
 * A bordered container with title bar (like TWindow in Turbo Vision)
 *
 * Features:
 * - Border with customizable characters
 * - Title bar
 * - Closable (ESC or close button)
 * - Movable (future)
 * - Resizable (future)
 */
class Window extends View
{
    protected string $title;
    protected Border $border;
    protected bool $closable = true;
    protected bool $showTitle = true;

    protected ?Color $titleColor = null;
    protected ?Color $borderColor = null;

    public function __construct(
        Rect|StyleSheet $boundsOrStyle,
        string $title = '',
        ?string $name = null
    ) {
        parent::__construct($boundsOrStyle, $name);

        $this->title = $title;
        $this->border = Border::fromStyle($this->style->border());
        $this->titleColor = $this->style->has('title-color')
            ? $this->style->get('title-color')
            : Color::BRIGHT_WHITE;
        $this->borderColor = $this->style->borderColor();
    }

    /**
     * Draw the window
     */
    public function draw(): void
    {
        $pos = $this->getAbsolutePosition();

        // Draw border
        echo $this->drawBorder($pos['x'], $pos['y']);

        // Draw title if enabled
        if ($this->showTitle && $this->title) {
            echo $this->drawTitle($pos['x'], $pos['y']);
        }

        // Draw children (content)
        $this->drawChildren();
    }

    /**
     * Draw the border
     */
    protected function drawBorder(int $x, int $y): string
    {
        $output = '';
        $width = $this->bounds->width;
        $height = $this->bounds->height;

        // Apply border color
        $output .= $this->borderColor->toForegroundCode();

        // Top border
        $output .= $this->moveCursor($x, $y);
        $output .= $this->border->getTopLeft();
        $output .= str_repeat($this->border->getTop(), $width - 2);
        $output .= $this->border->getTopRight();

        // Side borders
        for ($row = 1; $row < $height - 1; $row++) {
            $output .= $this->moveCursor($x, $y + $row);
            $output .= $this->border->getLeft();
            $output .= $this->moveCursor($x + $width - 1, $y + $row);
            $output .= $this->border->getRight();
        }

        // Bottom border
        $output .= $this->moveCursor($x, $y + $height - 1);
        $output .= $this->border->getBottomLeft();
        $output .= str_repeat($this->border->getBottom(), $width - 2);
        $output .= $this->border->getBottomRight();

        // Reset color
        $output .= "\033[0m";

        return $output;
    }

    /**
     * Draw the title in the top border
     */
    protected function drawTitle(int $x, int $y): string
    {
        $output = '';
        $width = $this->bounds->width;

        // Calculate title position (centered)
        $titleLen = mb_strlen($this->title);
        $leftPadding = (int) (($width - $titleLen - 4) / 2); // -4 for corners and spaces

        // Position cursor in top border
        $output .= $this->moveCursor($x + $leftPadding + 1, $y);

        // Draw title with color
        $output .= $this->titleColor->toForegroundCode();
        $output .= "\033[1m"; // Bold
        $output .= " {$this->title} ";
        $output .= "\033[0m"; // Reset

        return $output;
    }

    /**
     * Handle events
     */
    public function handleEvent(Event $event): bool
    {
        // Let children handle first
        if (parent::handleEvent($event)) {
            return true;
        }

        // Window-level event handling
        if ($event instanceof KeyEvent) {
            return match($event->key) {
                "\x1b", "\e" => $this->closable ? $this->close() : false, // ESC
                "\t" => $this->focusNext(),
                default => false,
            };
        }

        return false;
    }

    /**
     * Close the window
     */
    public function close(): bool
    {
        if (!$this->closable) {
            return false;
        }

        $this->hide();
        $this->onClose();

        return true;
    }

    /**
     * Hook for close event
     */
    protected function onClose(): void {}

    // ==================== Configuration ====================

    public function setBorder(Border $border): self
    {
        $this->border = $border;
        return $this;
    }

    public function getBorder(): Border
    {
        return $this->border;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitleColor(Color $color): self
    {
        $this->titleColor = $color;
        return $this;
    }

    public function setBorderColor(Color $color): self
    {
        $this->borderColor = $color;
        return $this;
    }

    public function setClosable(bool $closable): self
    {
        $this->closable = $closable;
        return $this;
    }

    public function setShowTitle(bool $show): self
    {
        $this->showTitle = $show;
        return $this;
    }

    /**
     * Get content area (inside the border)
     */
    public function getContentBounds(): Rect
    {
        return Rect::make(
            x: 1,  // Inside left border
            y: 1,  // Inside top border
            width: $this->bounds->width - 2,
            height: $this->bounds->height - 2
        );
    }
}
