<?php

namespace TerminalUI\Components;

use TerminalUI\Core\{View, Rect};
use TerminalUI\Styling\StyleSheet;
use TerminalUI\Layout\Border;
use TerminalUI\Events\Event;

/**
 * Panel Component
 *
 * Container for other components
 *
 * Features:
 * - Optional border
 * - Background color
 * - Padding
 * - Scrolling (future)
 */
class Panel extends View
{
    protected ?Border $border = null;

    public function __construct(Rect|StyleSheet $boundsOrStyle, ?string $name = null)
    {
        parent::__construct($boundsOrStyle, $name);

        // Create border if specified in style
        if ($this->style->has('border')) {
            $this->border = Border::fromStyle($this->style->border());
        }
    }

    /**
     * Draw the panel
     */
    public function draw(): void
    {
        if (!$this->visible) {
            return;
        }

        $pos = $this->getAbsolutePosition();

        // Draw background
        if ($this->style->has('background')) {
            echo $this->drawBackground($pos['x'], $pos['y']);
        }

        // Draw border if present
        if ($this->border) {
            echo $this->drawBorder($pos['x'], $pos['y']);
        }

        // Draw children
        $this->drawChildren();
    }

    /**
     * Draw background
     */
    protected function drawBackground(int $x, int $y): string
    {
        $output = '';
        $bg = $this->style->get('background');

        $output .= $bg->toBackgroundCode();

        for ($row = 0; $row < $this->bounds->height; $row++) {
            $output .= $this->moveCursor($x, $y + $row);
            $output .= str_repeat(' ', $this->bounds->width);
        }

        $output .= "\033[0m";

        return $output;
    }

    /**
     * Draw border
     */
    protected function drawBorder(int $x, int $y): string
    {
        $output = '';
        $width = $this->bounds->width;
        $height = $this->bounds->height;

        // Apply border color if specified
        if ($this->style->has('border-color')) {
            $output .= $this->style->get('border-color')->toForegroundCode();
        }

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
     * Set border
     */
    public function setBorder(Border $border): self
    {
        $this->border = $border;
        $this->invalidate();
        return $this;
    }

    /**
     * Get border
     */
    public function getBorder(): ?Border
    {
        return $this->border;
    }

    /**
     * Get content area (inside border and padding)
     */
    public function getContentBounds(): Rect
    {
        $padding = $this->style->has('padding') ? $this->style->get('padding') : 0;
        $borderSize = $this->border ? 1 : 0;

        $offset = $borderSize + $padding;

        return Rect::make(
            x: $offset,
            y: $offset,
            width: $this->bounds->width - (2 * $offset),
            height: $this->bounds->height - (2 * $offset)
        );
    }

    /**
     * Add text content to panel
     */
    public function addText(string $text, int $x = 0, int $y = 0): self
    {
        $label = new Label(
            $text,
            StyleSheet::create([
                'top' => $y,
                'left' => $x,
                'width' => $this->bounds->width - 2,
                'height' => $this->bounds->height - 2,
            ])
        );

        $this->add($label);
        return $this;
    }

    /**
     * Handle events
     */
    public function handleEvent(Event $event): bool
    {
        // Pass to children
        return parent::handleEvent($event);
    }
}
