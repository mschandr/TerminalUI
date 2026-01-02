<?php

namespace TerminalUI\Components;

use TerminalUI\Core\{View, Rect};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Events\{Event, KeyEvent};

/**
 * Button Component
 *
 * Interactive button with click/press handling
 *
 * Features:
 * - Click handler callback
 * - Hover/focus states
 * - Keyboard activation (Enter/Space)
 * - Custom styling
 */
class Button extends View
{
    protected string $label;
    protected ?\Closure $onClick = null;

    protected bool $hovered = false;
    protected bool $pressed = false;

    public function __construct(
        string $label,
        Rect|StyleSheet $boundsOrStyle,
        ?\Closure $onClick = null,
        ?string $name = null
    ) {
        parent::__construct($boundsOrStyle, $name);
        $this->label = $label;
        $this->onClick = $onClick;
    }

    /**
     * Draw the button
     */
    public function draw(): void
    {
        if (!$this->visible) {
            return;
        }

        $pos = $this->getAbsolutePosition();

        // Determine button state colors
        $bg = $this->getBackgroundColor();
        $fg = $this->getForegroundColor();

        // Build button content
        $padding = $this->style->has('padding') ? $this->style->get('padding') : 1;
        $content = str_repeat(' ', $padding) . $this->label . str_repeat(' ', $padding);
        $width = mb_strlen($content);

        // Apply colors and styling
        $output = '';
        $output .= $this->moveCursor($pos['x'], $pos['y']);
        $output .= $bg->toBackgroundCode();
        $output .= $fg->toForegroundCode();

        // Bold if focused
        if ($this->focused) {
            $output .= "\033[1m";
        }

        // Render with border or simple
        if ($this->style->has('border')) {
            $output .= "[ {$content} ]";
        } else {
            $output .= $content;
        }

        // Reset
        $output .= "\033[0m";

        echo $output;

        // Draw children
        $this->drawChildren();
    }

    /**
     * Get background color based on state
     */
    protected function getBackgroundColor(): Color
    {
        if ($this->pressed) {
            return $this->style->has('background-pressed')
                ? $this->style->get('background-pressed')
                : Color::BRIGHT_BLACK;
        }

        if ($this->focused) {
            return $this->style->has('background-focus')
                ? $this->style->get('background-focus')
                : ($this->style->has('background') ? $this->style->get('background') : Color::BLUE);
        }

        return $this->style->has('background')
            ? $this->style->get('background')
            : Color::BLUE;
    }

    /**
     * Get foreground color based on state
     */
    protected function getForegroundColor(): Color
    {
        if ($this->focused) {
            return $this->style->has('foreground-focus')
                ? $this->style->get('foreground-focus')
                : Color::BRIGHT_WHITE;
        }

        return $this->style->has('foreground')
            ? $this->style->get('foreground')
            : Color::WHITE;
    }

    /**
     * Handle events
     */
    public function handleEvent(Event $event): bool
    {
        if (!$this->enabled || !$this->visible) {
            return false;
        }

        if ($event instanceof KeyEvent) {
            // Enter or Space activates button
            if ($this->focused && ($event->key === KeyEvent::ENTER || $event->key === ' ')) {
                $this->activate();
                return true;
            }
        }

        return parent::handleEvent($event);
    }

    /**
     * Activate button (trigger click handler)
     */
    public function activate(): void
    {
        if (!$this->enabled) {
            return;
        }

        // Visual feedback
        $this->pressed = true;
        $this->draw();
        usleep(100000); // 100ms press effect

        $this->pressed = false;

        // Trigger callback
        if ($this->onClick) {
            ($this->onClick)($this);
        }

        $this->invalidate();
    }

    /**
     * Set click handler
     */
    public function setOnClick(\Closure $callback): self
    {
        $this->onClick = $callback;
        return $this;
    }

    /**
     * Set button label
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        $this->invalidate();
        return $this;
    }

    /**
     * Get button label
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
