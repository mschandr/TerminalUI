<?php

namespace TerminalUI\Components;

use TerminalUI\Core\{View, Rect};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Events\{Event, KeyEvent};

/**
 * Input Field Component
 *
 * Single-line text input
 *
 * Features:
 * - Text editing
 * - Cursor positioning
 * - Character insertion/deletion
 * - Home/End navigation
 * - Selection (future)
 * - Password mode (future)
 */
class Input extends View
{
    protected string $value = '';
    protected int $cursorPos = 0;
    protected int $scrollOffset = 0;

    protected ?string $placeholder = null;
    protected bool $passwordMode = false;
    protected ?int $maxLength = null;

    protected ?\Closure $onChange = null;
    protected ?\Closure $onSubmit = null;

    public function __construct(
        Rect|StyleSheet $boundsOrStyle,
        string $initialValue = '',
        ?string $name = null
    ) {
        parent::__construct($boundsOrStyle, $name);
        $this->value = $initialValue;
        $this->cursorPos = mb_strlen($initialValue);
    }

    /**
     * Draw the input field
     */
    public function draw(): void
    {
        if (!$this->visible) {
            return;
        }

        $pos = $this->getAbsolutePosition();

        // Get colors
        $bg = $this->focused
            ? ($this->style->has('background-focus') ? $this->style->get('background-focus') : Color::BRIGHT_BLACK)
            : ($this->style->has('background') ? $this->style->get('background') : Color::BLACK);
        $fg = $this->focused
            ? ($this->style->has('foreground-focus') ? $this->style->get('foreground-focus') : Color::BRIGHT_WHITE)
            : ($this->style->has('foreground') ? $this->style->get('foreground') : Color::WHITE);

        // Get display value
        $displayValue = $this->getDisplayValue();

        // Adjust scroll offset
        $this->adjustScrollOffset();

        // Calculate visible portion
        $visibleWidth = $this->bounds->width - 2; // -2 for padding
        $visibleValue = mb_substr($displayValue, $this->scrollOffset, $visibleWidth);

        $output = '';
        $output .= $this->moveCursor($pos['x'], $pos['y']);
        $output .= $bg->toBackgroundCode();
        $output .= $fg->toForegroundCode();

        // Render with padding
        $output .= ' ' . str_pad($visibleValue, $visibleWidth) . ' ';

        // Draw cursor if focused
        if ($this->focused && $this->enabled) {
            $cursorX = $pos['x'] + 1 + ($this->cursorPos - $this->scrollOffset);
            $output .= $this->moveCursor($cursorX, $pos['y']);
        }

        $output .= "\033[0m";

        echo $output;

        // Draw children
        $this->drawChildren();
    }

    /**
     * Get display value (with placeholder or password masking)
     */
    protected function getDisplayValue(): string
    {
        if (empty($this->value) && $this->placeholder && !$this->focused) {
            return $this->placeholder;
        }

        if ($this->passwordMode) {
            return str_repeat('*', mb_strlen($this->value));
        }

        return $this->value;
    }

    /**
     * Adjust scroll offset to keep cursor visible
     */
    protected function adjustScrollOffset(): void
    {
        $visibleWidth = $this->bounds->width - 2;

        // Scroll right if cursor is beyond visible area
        if ($this->cursorPos >= $this->scrollOffset + $visibleWidth) {
            $this->scrollOffset = $this->cursorPos - $visibleWidth + 1;
        }

        // Scroll left if cursor is before visible area
        if ($this->cursorPos < $this->scrollOffset) {
            $this->scrollOffset = $this->cursorPos;
        }
    }

    /**
     * Handle events
     */
    public function handleEvent(Event $event): bool
    {
        if (!$this->enabled || !$this->visible) {
            return false;
        }

        if ($event instanceof KeyEvent && $this->focused) {
            // Handle special keys
            if ($event->key === KeyEvent::ENTER) {
                return $this->submit();
            }

            if ($event->key === KeyEvent::BACKSPACE) {
                return $this->backspace();
            }

            if ($event->key === KeyEvent::DELETE) {
                return $this->delete();
            }

            if ($event->key === KeyEvent::ARROW_LEFT) {
                return $this->moveCursorLeft();
            }

            if ($event->key === KeyEvent::ARROW_RIGHT) {
                return $this->moveCursorRight();
            }

            if ($event->key === KeyEvent::HOME) {
                return $this->moveToStart();
            }

            if ($event->key === KeyEvent::END) {
                return $this->moveToEnd();
            }

            // Handle printable characters
            if ($event->isPrintable()) {
                return $this->insertCharacter($event->key);
            }
        }

        return parent::handleEvent($event);
    }

    /**
     * Insert character at cursor
     */
    protected function insertCharacter(string $char): bool
    {
        // Check max length
        if ($this->maxLength && mb_strlen($this->value) >= $this->maxLength) {
            return false;
        }

        // Insert at cursor position
        $this->value = mb_substr($this->value, 0, $this->cursorPos)
            . $char
            . mb_substr($this->value, $this->cursorPos);

        $this->cursorPos++;
        $this->triggerChange();
        $this->invalidate();

        return true;
    }

    /**
     * Delete character before cursor (backspace)
     */
    protected function backspace(): bool
    {
        if ($this->cursorPos === 0) {
            return false;
        }

        $this->value = mb_substr($this->value, 0, $this->cursorPos - 1)
            . mb_substr($this->value, $this->cursorPos);

        $this->cursorPos--;
        $this->triggerChange();
        $this->invalidate();

        return true;
    }

    /**
     * Delete character at cursor
     */
    protected function delete(): bool
    {
        if ($this->cursorPos >= mb_strlen($this->value)) {
            return false;
        }

        $this->value = mb_substr($this->value, 0, $this->cursorPos)
            . mb_substr($this->value, $this->cursorPos + 1);

        $this->triggerChange();
        $this->invalidate();

        return true;
    }

    /**
     * Move cursor left
     */
    protected function moveCursorLeft(): bool
    {
        if ($this->cursorPos > 0) {
            $this->cursorPos--;
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Move cursor right
     */
    protected function moveCursorRight(): bool
    {
        if ($this->cursorPos < mb_strlen($this->value)) {
            $this->cursorPos++;
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Move to start
     */
    protected function moveToStart(): bool
    {
        if ($this->cursorPos !== 0) {
            $this->cursorPos = 0;
            $this->scrollOffset = 0;
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Move to end
     */
    protected function moveToEnd(): bool
    {
        $end = mb_strlen($this->value);

        if ($this->cursorPos !== $end) {
            $this->cursorPos = $end;
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Submit (Enter key)
     */
    protected function submit(): bool
    {
        if ($this->onSubmit) {
            ($this->onSubmit)($this->value);
            return true;
        }

        return false;
    }

    /**
     * Trigger change callback
     */
    protected function triggerChange(): void
    {
        if ($this->onChange) {
            ($this->onChange)($this->value);
        }
    }

    // ==================== Configuration ====================

    /**
     * Set value
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        $this->cursorPos = mb_strlen($value);
        $this->invalidate();
        return $this;
    }

    /**
     * Get value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Clear value
     */
    public function clear(): self
    {
        $this->value = '';
        $this->cursorPos = 0;
        $this->scrollOffset = 0;
        $this->invalidate();
        return $this;
    }

    /**
     * Set placeholder
     */
    public function setPlaceholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        $this->invalidate();
        return $this;
    }

    /**
     * Set password mode
     */
    public function setPasswordMode(bool $enabled): self
    {
        $this->passwordMode = $enabled;
        $this->invalidate();
        return $this;
    }

    /**
     * Set max length
     */
    public function setMaxLength(?int $length): self
    {
        $this->maxLength = $length;
        return $this;
    }

    /**
     * Set change callback
     */
    public function setOnChange(\Closure $callback): self
    {
        $this->onChange = $callback;
        return $this;
    }

    /**
     * Set submit callback
     */
    public function setOnSubmit(\Closure $callback): self
    {
        $this->onSubmit = $callback;
        return $this;
    }
}
