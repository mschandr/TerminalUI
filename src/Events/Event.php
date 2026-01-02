<?php

namespace TerminalUI\Events;

/**
 * Base Event Class
 *
 * All events inherit from this (like TEvent in Turbo Vision)
 *
 * Event Types:
 * - KeyEvent (keyboard input)
 * - MouseEvent (mouse clicks, movement)
 * - CommandEvent (menu selections, button clicks)
 * - FocusEvent (focus gained/lost)
 */
abstract class Event
{
    protected bool $handled = false;

    /**
     * Mark this event as handled (stops propagation)
     */
    public function setHandled(bool $handled = true): void
    {
        $this->handled = $handled;
    }

    /**
     * Check if event has been handled
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->handled = true;
    }
}
